<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RESPUESTUM
 * 
 * @property int $RES_ID
 * @property int|null $RES_RES_ID
 * @property int $BLO_ID
 * @property string|null $AUTOR
 * @property Carbon|null $FECHACREACION
 * @property string|null $DESCRIPCION
 * 
 * @property BLOG $b_l_o_g
 * @property RESPUESTUM|null $r_e_s_p_u_e_s_t_u_m
 * @property Collection|RESPUESTUM[] $r_e_s_p_u_e_s_t_a
 *
 * @package App\Models
 */
class RESPUESTUM extends Model
{
	protected $table = 'RESPUESTA';
	protected $primaryKey = 'RES_ID';
	public $timestamps = false;

	protected $casts = [
		'RES_RES_ID' => 'int',
		'BLO_ID' => 'int',
		'FECHACREACION' => 'datetime'
	];

	protected $fillable = [
		'RES_RES_ID',
		'BLO_ID',
		'AUTOR',
		'FECHACREACION',
		'DESCRIPCION'
	];

	public function b_l_o_g()
	{
		return $this->belongsTo(BLOG::class, 'BLO_ID');
	}

	public function r_e_s_p_u_e_s_t_u_m()
	{
		return $this->belongsTo(RESPUESTUM::class, 'RES_RES_ID');
	}

	public function r_e_s_p_u_e_s_t_a()
	{
		return $this->hasMany(RESPUESTUM::class, 'RES_RES_ID');
	}
}
