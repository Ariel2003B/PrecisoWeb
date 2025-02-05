<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PLAN
 * 
 * @property int $PLA_ID
 * @property string|null $DESCRIPCION
 * @property float|null $PRECIO
 * @property string|null $TIEMPO
 * @property string|null $NOMBRE
 * 
 * @property Collection|CARACTERISTICA[] $c_a_r_a_c_t_e_r_i_s_t_i_c_a_s
 *
 * @package App\Models
 */
class PLAN extends Model
{
	protected $table = 'PLAN';
	protected $primaryKey = 'PLA_ID';
	public $timestamps = false;

	protected $casts = [
		'PRECIO' => 'float'
	];

	protected $fillable = [
		'DESCRIPCION',
		'PRECIO',
		'TIEMPO',
		'NOMBRE'
	];

	public function c_a_r_a_c_t_e_r_i_s_t_i_c_a_s()
	{
		return $this->belongsToMany(CARACTERISTICA::class, 'PLANCARACTERISTICA', 'PLA_ID', 'CAR_ID')
			->withPivot('PCA_ID', 'POSEE', 'ORDEN')
			->orderBy('ORDEN');
	}
}
