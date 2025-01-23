<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PLANCARACTERISTICA
 * 
 * @property int $PCA_ID
 * @property int|null $PLA_ID
 * @property int|null $CAR_ID
 * @property bool|null $POSEE
 * 
 * @property CARACTERISTICA|null $c_a_r_a_c_t_e_r_i_s_t_i_c_a
 * @property PLAN|null $p_l_a_n
 *
 * @package App\Models
 */
class PLANCARACTERISTICA extends Model
{
	protected $table = 'PLANCARACTERISTICA';
	protected $primaryKey = 'PCA_ID';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'PCA_ID' => 'int',
		'PLA_ID' => 'int',
		'CAR_ID' => 'int',
		'POSEE' => 'bool'
	];

	protected $fillable = [
		'PLA_ID',
		'CAR_ID',
		'POSEE'
	];

	public function c_a_r_a_c_t_e_r_i_s_t_i_c_a()
	{
		return $this->belongsTo(CARACTERISTICA::class, 'CAR_ID');
	}

	public function p_l_a_n()
	{
		return $this->belongsTo(PLAN::class, 'PLA_ID');
	}
}
