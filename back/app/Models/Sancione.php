<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Sancione
 * 
 * @property int $id
 * @property string $unidad
 * @property int $vuelta
 * @property string $hora
 * @property int $total
 * @property float $valor_total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $fecha
 * @property string|null $ruta
 * 
 * @property Collection|Geocerca[] $geocercas
 *
 * @package App\Models
 */
class Sancione extends Model
{
	protected $table = 'sanciones';

	protected $casts = [
		'vuelta' => 'int',
		'total' => 'int',
		'valor_total' => 'float',
		'fecha' => 'datetime'
	];

	protected $fillable = [
		'unidad',
		'vuelta',
		'hora',
		'total',
		'valor_total',
		'fecha',
		'ruta'
	];

	public function geocercas()
	{
		return $this->hasMany(Geocerca::class, 'sancion_id');
	}
}
