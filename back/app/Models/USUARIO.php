<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Class USUARIO
 * 
 * @property int $USU_ID
 * @property int|null $PER_ID
 * @property string|null $NOMBRE
 * @property string|null $APELLIDO
 * @property string|null $CORREO
 * @property string|null $CLAVE
 * @property string|null $ESTADO
 * 
 * @property PERFIL|null $p_e_r_f_i_l
 *
 * @package App\Models
 */
class USUARIO extends Authenticatable
{
	protected $table = 'USUARIO';
	protected $primaryKey = 'USU_ID';
	public $timestamps = false;

	protected $casts = [
		'PER_ID' => 'int'
	];

	protected $fillable = [
		'PER_ID',
		'NOMBRE',
		'APELLIDO',
		'CORREO',
		'CLAVE',
		'ESTADO'
	];

	// Sobrescribir el campo de contraseña
	public function setClaveAttribute($value)
	{
		$this->attributes['CLAVE'] = Hash::make($value);
	}

	// Cambiar el nombre de la contraseña para autenticación
	public function getAuthPassword()
	{
		return $this->CLAVE;
	}

	public function p_e_r_f_i_l()
	{
		return $this->belongsTo(PERFIL::class, 'PER_ID');
	}
}