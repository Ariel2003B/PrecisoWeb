<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

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
		'MARCA_EQUIPO'
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
}
