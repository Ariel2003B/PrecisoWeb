<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VEHICULO
 * 
 * @property int $VEH_ID
 * @property string|null $TIPO
 * @property string|null $PLACA
 * @property string|null $ESTADO
 *
 * @package App\Models
 */
class VEHICULO extends Model
{
	protected $table = 'VEHICULO';
	protected $primaryKey = 'VEH_ID';
	public $timestamps = false;

	protected $fillable = [
		'TIPO',
		'PLACA',
		'ESTADO'
	];
}
