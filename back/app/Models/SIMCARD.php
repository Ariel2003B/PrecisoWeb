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
		'PROVEEDOR',
		'FOTO_SIM',
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
	public function cuotasServicio()
	{
		return $this->hasManyThrough(
			CUOTAS_SERVICIO::class,
			DETALLE_SIMCARD_SERVICIO::class,
			'SIM_ID',     // FK en DETALLE_SIMCARD_SERVICIO -> SIMCARD
			'SERV_ID',    // FK en CUOTAS_SERVICIO -> DETALLE_SIMCARD_SERVICIO
			'ID_SIM',     // PK SIMCARD
			'SERV_ID'     // PK DETALLE_SIMCARD_SERVICIO
		);
	}

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

	public function getPagosEstadoAttribute()
	{
		$hoy = Carbon::today()->toDateString();
		$proximo = Carbon::today()->addDays(5)->toDateString();

		// Estado por defecto
		$estado = 'AL_DIA';
		$color = 'success';
		$resumen = '-';
		$fuente = '-'; // Cuota / Servicio / Renovación
		$estadoServicio = null;
		$fechaServicio = null;
		$estadoCuota = null;
		$fechaCuota = null;

		// ---------------------------------------------
		// 1) CUOTAS PENDIENTES (COMPROBANTE NULL)
		// ---------------------------------------------
		$cuotasPend = collect();

		if ($this->relationLoaded('detalleVigente') && $this->detalleVigente) {
			$cuotasPend = $cuotasPend->merge(
				$this->detalleVigente->cuotas->whereNull('COMPROBANTE')
			);
		}

		// si también usas otros detalles
		if ($this->relationLoaded('detalleSimcards') && $this->detalleSimcards) {
			foreach ($this->detalleSimcards as $det) {
				$cuotasPend = $cuotasPend->merge(
					$det->cuotas->whereNull('COMPROBANTE')
				);
			}
		}

		// Tomamos la cuota más próxima
		if ($cuotasPend->count() > 0) {
			$cuotaMasCercana = $cuotasPend->sortBy('FECHA_PAGO')->first();
			$fecha = Carbon::parse($cuotaMasCercana->FECHA_PAGO)->toDateString();

			if ($fecha < $hoy) {
				$estado = 'VENCIDO';
				$color = 'danger';
				$fuente = 'Cuota';
				$estadoCuota = 'VENCIDO';
				$fechaCuota = $fecha;
				$resumen = 'Cuota pendiente vencida';
			} elseif ($fecha >= $hoy && $fecha <= $proximo && $estado !== 'VENCIDO') {
				// Solo si aún no marcamos VENCIDO por otra cosa
				$estado = 'PROXIMO';
				$color = 'warning';
				$fuente = 'Cuota';
				$estadoCuota = 'PROXIMO';
				$fechaCuota = $fecha;
				$resumen = 'Cuota próxima a vencer';
			}
		}

		// ---------------------------------------------
		// 2) CUOTAS DE SERVICIO PENDIENTES (COMPROBANTE NULL)
		// ---------------------------------------------
		$servCuotasPend = collect();

		if ($this->relationLoaded('servicioReciente') && $this->servicioReciente?->relationLoaded('cuotas')) {
			$servCuotasPend = $this->servicioReciente->cuotas->whereNull('COMPROBANTE');
		} elseif ($this->servicioReciente) {
			// fallback si no vino eager loaded
			$servCuotasPend = $this->servicioReciente->cuotas()->whereNull('COMPROBANTE')->get();
		}

		if ($servCuotasPend->count() > 0) {
			$cuotaServ = $servCuotasPend->sortBy('FECHA_PAGO')->first();
			$fechaServ = Carbon::parse($cuotaServ->FECHA_PAGO)->toDateString();

			if ($fechaServ < $hoy) {
				$estado = 'VENCIDO';
				$color = 'danger';
				$fuente = 'Servicio';
				$estadoServicio = 'VENCIDO';
				$fechaServicio = $fechaServ;
				$resumen = 'Cuota de servicio vencida';
			} elseif ($fechaServ >= $hoy && $fechaServ <= $proximo && $estado !== 'VENCIDO') {
				$estado = 'PROXIMO';
				$color = 'warning';
				$fuente = 'Servicio';
				$estadoServicio = 'PROXIMO';
				$fechaServicio = $fechaServ;
				$resumen = 'Cuota de servicio próxima a vencer';
			}
		}

		// ---------------------------------------------
		// 3) NUEVO: SERVICIO RECIENTE -> FECHA_SIGUIENTE_PAGO
		// ---------------------------------------------
		if ($this->servicioReciente && $this->servicioReciente->FECHA_SIGUIENTE_PAGO) {
			$fechaSig = Carbon::parse($this->servicioReciente->FECHA_SIGUIENTE_PAGO)->toDateString();

			// Si ya pasó la fecha siguiente de pago, aunque esté pagado, es VENCIDO
			if ($fechaSig < $hoy) {
				$estado = 'VENCIDO';
				$color = 'danger';
				$fuente = 'Renovación';
				$estadoServicio = 'VENCIDO';
				$fechaServicio = $fechaSig;
				$resumen = 'Servicio no renovado';
			}
			// Si está por llegar y aún no hay nada VENCIDO
			elseif ($fechaSig >= $hoy && $fechaSig <= $proximo && $estado !== 'VENCIDO') {
				$estado = 'PROXIMO';
				$color = 'warning';
				$fuente = 'Renovación';
				$estadoServicio = 'PROXIMO';
				$fechaServicio = $fechaSig;
				$resumen = 'Servicio próximo a renovar';
			}
		}

		return [
			'estado' => $estado,
			'color' => $color,
			'resumen' => $resumen,
			'fuente' => $fuente,
			'estado_servicio' => $estadoServicio,
			'fecha_servicio' => $fechaServicio,
			'estado_cuota' => $estadoCuota,
			'fecha_cuota' => $fechaCuota,
		];
	}
}
