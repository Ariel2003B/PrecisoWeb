<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PERMISO
 * 
 * @property int $PRM_ID
 * @property string|null $DESCRIPCION
 * @property string|null $ESTADO
 * 
 * @property Collection|PERFIL[] $p_e_r_f_i_l_s
 *
 * @package App\Models
 */
class PERMISO extends Model
{
	protected $table = 'PERMISO';
	protected $primaryKey = 'PRM_ID';
	public $timestamps = false;

	protected $fillable = [
		'DESCRIPCION',
		'ESTADO'
	];

	public function p_e_r_f_i_l_s()
	{
		return $this->belongsToMany(PERFIL::class, 'PERFILPERMISO', 'PRM_ID', 'PER_ID')
					->withPivot('PERPER_ID');
	}
}
