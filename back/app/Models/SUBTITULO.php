<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SUBTITULO
 * 
 * @property int $SUB_ID
 * @property int $BLO_ID
 * @property int $NUMERO
 * @property string $TEXTO
 * @property string $CONTENIDO
 * 
 * @property BLOG $b_l_o_g
 *
 * @package App\Models
 */
class SUBTITULO extends Model
{
	protected $table = 'SUBTITULO';
	protected $primaryKey = 'SUB_ID';
	public $timestamps = false;

	protected $casts = [
		'BLO_ID' => 'int',
		'NUMERO' => 'int'
	];

	protected $fillable = [
		'BLO_ID',
		'NUMERO',
		'TEXTO',
		'CONTENIDO'
	];

	public function b_l_o_g()
	{
		return $this->belongsTo(BLOG::class, 'BLO_ID');
	}
}
