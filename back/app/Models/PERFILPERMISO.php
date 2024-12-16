<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PERFILPERMISO
 * 
 * @property int $PERPER_ID
 * @property int|null $PRM_ID
 * @property int|null $PER_ID
 * 
 * @property PERFIL|null $p_e_r_f_i_l
 * @property PERMISO|null $p_e_r_m_i_s_o
 *
 * @package App\Models
 */
class PERFILPERMISO extends Model
{
	protected $table = 'PERFILPERMISO';
	protected $primaryKey = 'PERPER_ID';
	public $timestamps = false;

	protected $casts = [
		'PRM_ID' => 'int',
		'PER_ID' => 'int'
	];

	protected $fillable = [
		'PRM_ID',
		'PER_ID'
	];

	public function p_e_r_f_i_l()
	{
		return $this->belongsTo(PERFIL::class, 'PER_ID');
	}

	public function p_e_r_m_i_s_o()
	{
		return $this->belongsTo(PERMISO::class, 'PRM_ID');
	}
}
