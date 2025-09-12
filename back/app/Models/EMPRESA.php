<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class EMPRESA
 * 
 * @property int $EMP_ID
 * @property string $NOMBRE
 * @property string $RUC
 * @property string|null $DIRECCION
 * @property string|null $TELEFONO
 * @property string|null $CORREO
 * @property string $ESTADO
 * @property string|null $TOKEN
 * @property int|null $DEPOT
 * 
 * @property \Illuminate\Database\Eloquent\Collection|USUARIO[] $usuarios
 * 
 * @package App\Models
 */
class EMPRESA extends Model
{
    use HasFactory;

    protected $table = 'EMPRESA';
    protected $primaryKey = 'EMP_ID';
    public $timestamps = false;

    protected $fillable = [
        'NOMBRE',
        'RUC',
        'DIRECCION',
        'TELEFONO',
        'CORREO',
        'ESTADO',
        'IMAGEN',
        'TOKEN',
        'DEPOT'
    ];

    // Relación con USUARIO
    public function usuarios()
    {
        return $this->hasMany(USUARIO::class, 'EMP_ID');
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'EMP_ID');
    }

}
