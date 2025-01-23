<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PERFIL
 * 
 * @property int $PER_ID
 * @property string|null $DESCRIPCION
 * @property string|null $ESTADO
 * 
 * @property Collection|PERMISO[] $p_e_r_m_i_s_o_s
 * @property Collection|USUARIO[] $u_s_u_a_r_i_o_s
 *
 * @package App\Models
 */
class PERFIL extends Model
{
	protected $table = 'PERFIL';
	protected $primaryKey = 'PER_ID';
	public $timestamps = false;

	protected $fillable = [
		'DESCRIPCION',
		'ESTADO'
	];

	public function p_e_r_m_i_s_o_s()
	{
		return $this->belongsToMany(PERMISO::class, 'PERFILPERMISO', 'PER_ID', 'PRM_ID')
					->withPivot('PERPER_ID');
	}

	public function u_s_u_a_r_i_o_s()
	{
		return $this->hasMany(USUARIO::class, 'PER_ID');
	}
}
