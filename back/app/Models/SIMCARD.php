<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SIMCARD
 * 
 * @property int $ID_SIM
 * @property string|null $RUC
 * @property string|null $PROPIETARIO
 * @property string|null $CUENTA
 * @property string|null $NUMEROTELEFONO
 * @property string|null $TIPOPLAN
 * @property string|null $PLAN
 * @property string|null $ICC
 * @property string|null $ESTADO
 * @property string|null $GRUPO
 * @property string|null $ASIGNACION
 * @property string|null $EQUIPO
 * @property int|null $VEH_ID
 * @property int|null $USU_ID
 * @property string|null $IMEI
 * @property string|null $MODELO_EQUIPO
 * @property string|null $MARCA_EQUIPO
 * @property string|null $PLATAFORMA
 * @property string|null $PROVEEDOR
 * @property VEHICULO|null $v_e_h_i_c_u_l_o
 * @property USUARIO|null $usuario
 *
 * @package App\Models
 */
class SIMCARD extends Model
{
	protected $table = 'SIMCARD';
	protected $primaryKey = 'ID_SIM';
	public $timestamps = false;

	protected $casts = [
		'VEH_ID' => 'int',
		'USU_ID' => 'int',

	];

	protected $fillable = [
		'RUC',
		'PROPIETARIO',
		'CUENTA',
		'NUMEROTELEFONO',
		'TIPOPLAN',
		'PLAN',
		'ICC',
		'ESTADO',
		'GRUPO',
		'ASIGNACION',
		'EQUIPO',
		'VEH_ID',
		'USU_ID',
		'IMEI',
		'ID_WIALON',
		'MODELO_EQUIPO',
		'MARCA_EQUIPO',
		'PLATAFORMA',
		'PROVEEDOR'
	];

	public function v_e_h_i_c_u_l_o()
	{
		return $this->belongsTo(VEHICULO::class, 'VEH_ID');
	}
	/** Nuevo: propietario (usuario) */
	public function usuario()
	{
		return $this->belongsTo(USUARIO::class, 'USU_ID', 'USU_ID');
	}
	/** Detalles de contrato/pagos de la SIM */
	public function detalleSimcards()
	{
		return $this->hasMany(DETALLE_SIMCARD::class, 'SIM_ID', 'ID_SIM');
	}
	// Último detalle por fecha de activación (para tomar cuotas vigentes)
	public function detalleVigente()
	{
		// Si tu Laravel soporta latestOfMany:
		return $this->hasOne(DETALLE_SIMCARD::class, 'SIM_ID', 'ID_SIM')->latestOfMany('FECHA_ACTIVACION_RENOVACION');

		// Si NO soporta latestOfMany, usa en su lugar:
		// return $this->hasOne(DETALLE_SIMCARD::class, 'SIM_ID', 'ID_SIM')->orderByDesc('FECHA_ACTIVACION_RENOVACION');
	}
	public function cuotas()
	{
		return $this->hasManyThrough(
			CUOTAS::class,           // Modelo destino
			DETALLE_SIMCARD::class,  // Modelo intermedio
			'SIM_ID',                // FK en DETALLE_SIMCARD -> SIMCARD
			'DET_ID',                // FK en CUOTAS -> DETALLE_SIMCARD
			'ID_SIM',                // PK en SIMCARD
			'DET_ID'                 // PK en DETALLE_SIMCARD
		);
	}

	public function documentosGenerados()
	{
		return $this->hasMany(DOCUMENTOS_GENERADOS::class, 'SIM_ID', 'ID_SIM');
	}
	public function servicios()
	{
		return $this->hasMany(DETALLE_SIMCARD_SERVICIO::class, 'SIM_ID', 'ID_SIM');
	}
	// Último servicio por fecha de servicio
	public function servicioReciente()
	{
		return $this->hasOne(DETALLE_SIMCARD_SERVICIO::class, 'SIM_ID', 'ID_SIM')
			->latestOfMany('FECHA_SERVICIO'); // requiere Laravel 8.42+; si no, avísame y te doy alternativa
	}
	// En App\Models\SIMCARD
	public function getClienteNombreAttribute(): ?string
	{
		if ($this->relationLoaded('usuario') && $this->usuario) {
			$ap = trim((string) $this->usuario->APELLIDO);
			$no = trim((string) $this->usuario->NOMBRE);
			$full = trim($ap . ' ' . $no);
			if ($full !== '')
				return $full;
		}
		// fallback si usas PROPIETARIO u otro campo
		return $this->PROPIETARIO ?: null;
	}

	public function getClienteCedulaAttribute()
	{
		return $this->usuario->CEDULA ?? null;
	}

	public function getPagosEstadoAttribute(): array
	{
		$hoy = Carbon::today();
		$limiteProximo = $hoy->copy()->addDays(5);

		/*
		 * ================= CUOTA PENDIENTE =================
		 */
		$cuotaPend = null;

		if ($this->relationLoaded('detalleVigente') || $this->relationLoaded('detalleSimcards')) {

			$detalle = $this->detalleVigente;

			// fallback: si no hay detalle vigente, usamos el último detalle
			if (!$detalle && $this->relationLoaded('detalleSimcards')) {
				$detalle = $this->detalleSimcards
					->sortByDesc('FECHA_ACTIVACION_RENOVACION')
					->first();
			}

			if ($detalle) {
				$cuotaPend = $detalle->cuotas()
					->whereNull('COMPROBANTE')   // SOLO cuotas pendientes
					->orderBy('FECHA_PAGO')
					->first();
			}
		}

		$cFecha = $cuotaPend && $cuotaPend->FECHA_PAGO
			? Carbon::parse($cuotaPend->FECHA_PAGO)
			: null;

		/*
		 * ================= SERVICIO PENDIENTE =================
		 * Solo se consideran servicios donde COMPROBANTE sea NULL (pendientes).
		 * Si ya tiene COMPROBANTE, se asume pagado y no afecta el estado.
		 */
		$servPend = $this->servicios()
			->whereNull('COMPROBANTE')                // SOLO servicios no pagados
			->orderBy('FECHA_SERVICIO')              // el más antiguo pendiente
			->first();

		// Aquí usamos FECHA_SERVICIO como "fecha de contrato" para evaluar el vencimiento.
		// Si prefieres usar FECHA_SIGUIENTE_PAGO, cambia FECHA_SERVICIO por FECHA_SIGUIENTE_PAGO.
		$sFecha = $servPend && $servPend->FECHA_SERVICIO
			? Carbon::parse($servPend->FECHA_SERVICIO)
			: null;

		/*
		 * ================= FUNCIÓN DE EVALUACIÓN =================
		 */
		$eval = function (?Carbon $fecha) use ($hoy, $limiteProximo) {
			if (!$fecha) {
				return null; // no hay nada pendiente
			}

			if ($fecha->lt($hoy)) {
				return 'VENCIDO';
			}

			if ($fecha->lte($limiteProximo)) {
				return 'PROXIMO';
			}

			return 'AL_DIA';
		};

		$estadoCuota = $eval($cFecha);
		$estadoServicio = $eval($sFecha);

		/*
		 * ================= ESTADO GENERAL =================
		 */
		$estados = collect([$estadoCuota, $estadoServicio])->filter();

		if ($estados->isEmpty()) {
			$estadoGeneral = 'AL_DIA';
		} elseif ($estados->contains('VENCIDO')) {
			$estadoGeneral = 'VENCIDO';
		} elseif ($estados->contains('PROXIMO')) {
			$estadoGeneral = 'PROXIMO';
		} else {
			$estadoGeneral = 'AL_DIA';
		}

		$color = match ($estadoGeneral) {
			'VENCIDO' => 'danger',
			'PROXIMO' => 'warning',
			default => 'success',
		};

		/*
		 * ================= RESUMEN (Servicio / Cuotas / Ambos) =================
		 */
		$partes = [];

		if ($estadoCuota === $estadoGeneral && $estadoGeneral !== 'AL_DIA') {
			$partes[] = 'Cuotas';
		}

		if ($estadoServicio === $estadoGeneral && $estadoGeneral !== 'AL_DIA') {
			$partes[] = 'Servicio';
		}

		if (empty($partes) && $estadoGeneral !== 'AL_DIA') {
			$resumen = 'Revisar detalle';
		} elseif (count($partes) === 2) {
			$resumen = 'Servicio y cuotas';
		} elseif (count($partes) === 1) {
			$resumen = $partes[0];
		} else {
			$resumen = '-';
		}

		return [
			'estado' => $estadoGeneral,
			'color' => $color,

			'estado_cuota' => $estadoCuota,
			'fecha_cuota' => $cFecha?->toDateString(),

			'estado_servicio' => $estadoServicio,
			'fecha_servicio' => $sFecha?->toDateString(),

			'resumen' => $resumen,
		];
	}



}
