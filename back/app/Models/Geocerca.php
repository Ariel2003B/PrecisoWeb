<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Geocerca
 * 
 * @property int $id
 * @property int $sancion_id
 * @property string $nombre
 * @property int $sancion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Sancione $sancione
 *
 * @package App\Models
 */
class Geocerca extends Model
{
	protected $table = 'geocercas';

	protected $casts = [
		'sancion_id' => 'int',
		'sancion' => 'int'
	];

	protected $fillable = [
		'sancion_id',
		'nombre',
		'sancion'
	];

	public function sancione()
	{
		return $this->belongsTo(Sancione::class, 'sancion_id');
	}
}
