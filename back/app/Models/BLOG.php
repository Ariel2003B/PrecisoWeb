<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BLOG
 * 
 * @property int $BLO_ID
 * @property string $TITULO
 * @property string|null $AUTOR
 * @property string|null $CATEGORIA
 * @property Carbon|null $FECHACREACION
 * @property string|null $URL_IMAGEN
 * @property string $CONTENIDO
 * @property int|null $NUMEROCOMENTARIOS
 * 
 * @property Collection|RESPUESTUM[] $r_e_s_p_u_e_s_t_a
 * @property Collection|SUBTITULO[] $s_u_b_t_i_t_u_l_o_s
 *
 * @package App\Models
 */
class BLOG extends Model
{
	protected $table = 'BLOG';
	protected $primaryKey = 'BLO_ID';
	public $timestamps = false;

	protected $casts = [
		'FECHACREACION' => 'datetime',
		'NUMEROCOMENTARIOS' => 'int'
	];

	protected $fillable = [
		'TITULO',
		'AUTOR',
		'CATEGORIA',
		'FECHACREACION',
		'URL_IMAGEN',
		'CONTENIDO',
		'NUMEROCOMENTARIOS'
	];

	public function r_e_s_p_u_e_s_t_a()
	{
		return $this->hasMany(RESPUESTUM::class, 'BLO_ID');
	}

	public function s_u_b_t_i_t_u_l_o_s()
	{
		return $this->hasMany(SUBTITULO::class, 'BLO_ID');
	}
}
