<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Unidad
 * 
 * @property int $id_unidad
 * @property string|null $numero_habilitacion
 * @property string|null $placa
 * @property string|null $propietario
 * @property int|null $anio_fabricacion
 * @property string|null $chasis
 * @property string|null $carroceria
 * @property string|null $tipo_especial
 * @property int|null $capacidad_pasajeros
 * @property int|null $puertas_ingreso
 * @property int|null $puertas_izquierdas
 * 
 * @property Collection|HojaTrabajo[] $hojas_trabajo
 */
class Unidad extends Model
{
    protected $table = 'unidades';
    protected $primaryKey = 'id_unidad';
    public $timestamps = false;

    protected $fillable = [
        'numero_habilitacion',
        'placa',
        'propietario',
        'anio_fabricacion',
        'chasis',
        'carroceria',
        'tipo_especial',
        'capacidad_pasajeros',
        'puertas_ingreso',
        'puertas_izquierdas',
        'usu_id' // ← nuevo campo agregado
    ];

    public function hojas_trabajo()
    {
        return $this->hasMany(HojaTrabajo::class, 'id_unidad');
    }
    public function usuario()
    {
        return $this->belongsTo(USUARIO::class, 'usu_id');
    }
}
