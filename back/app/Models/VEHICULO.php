<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VEHICULO
 * 
 * @property int $VEH_ID
 * @property string|null $TIPO
 * @property string|null $PLACA
 * @property string|null $ESTADO
 * 
 * @property Collection|SIMCARD[] $s_i_m_c_a_r_d_s
 *
 * @package App\Models
 */
class VEHICULO extends Model
{
	protected $table = 'VEHICULO';
	protected $primaryKey = 'VEH_ID';
	public $timestamps = false;

	protected $fillable = [
		'TIPO',
		'PLACA',
		'ESTADO'
	];

	public function s_i_m_c_a_r_d_s()
	{
		return $this->hasMany(SIMCARD::class, 'VEH_ID');
	}
}
