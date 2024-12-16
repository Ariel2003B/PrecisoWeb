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
 * @property int|null $VEH_ID
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
		'VEH_ID'
	];

	public function v_e_h_i_c_u_l_o()
	{
		return $this->belongsTo(VEHICULO::class, 'VEH_ID');
	}
}
