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
 * @property string|null $IMEI
 * 
 * @property VEHICULO|null $v_e_h_i_c_u_l_o
 *
 * @package App\Models
 */
class SIMCARD extends Model
{
	protected $table = 'SIMCARD';
	protected $primaryKey = 'ID_SIM';
	public $timestamps = false;

	protected $casts = [
		'VEH_ID' => 'int'
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
		'IMEI'
	];

	public function v_e_h_i_c_u_l_o()
	{
		return $this->belongsTo(VEHICULO::class, 'VEH_ID');
	}
}
