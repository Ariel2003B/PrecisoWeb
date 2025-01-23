<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CARACTERISTICA
 * 
 * @property int $CAR_ID
 * @property string|null $DESCRIPCION
 * 
 * @property Collection|PLAN[] $p_l_a_n_s
 *
 * @package App\Models
 */
class CARACTERISTICA extends Model
{
	protected $table = 'CARACTERISTICAS';
	protected $primaryKey = 'CAR_ID';
	public $timestamps = false;

	protected $fillable = [
		'DESCRIPCION'
	];

	public function p_l_a_n_s()
	{
		return $this->belongsToMany(PLAN::class, 'PLANCARACTERISTICA', 'CAR_ID', 'PLA_ID')
					->withPivot('PCA_ID', 'POSEE');
	}
}
