<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HojaTrabajo
 * 
 * @property int $id_hoja
 * @property string $fecha
 * @property string $tipo_dia
 * @property int $id_conductor
 * @property int $id_ayudante
 * @property int $id_ruta
 * @property int $id_unidad
 * @property string|null $ayudante_nombre
 * 
 * @property Personal $conductor
 * @property Personal $ayudante
 * @property Ruta $ruta
 * @property Unidad $unidad
 * @property Collection|Gasto[] $gastos
 * @property Collection|Produccion[] $producciones
 */
class HojaTrabajo extends Model
{
    protected $table = 'hojas_trabajo';
    protected $primaryKey = 'id_hoja';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'tipo_dia',
        'id_conductor',
        'id_ayudante',
        'id_ruta',
        'id_unidad',
        'ayudante_nombre' // nuevo campo para ayudante ingresado manualmente
    ];

    public function conductor()
    {
        return $this->belongsTo(Personal::class, 'id_conductor');
    }

    public function ayudante()
    {
        return $this->belongsTo(Personal::class, 'id_ayudante');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'id_unidad');
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'id_hoja');
    }

    public function producciones()
    {
        return $this->hasMany(Produccion::class, 'id_hoja');
    }

    public function producciones_usuario()
    {
        return $this->hasMany(ProduccionUsuario::class, 'id_hoja');
    }

}
