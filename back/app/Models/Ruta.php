<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ruta
 * 
 * @property int $id_ruta
 * @property string|null $descripcion
 * 
 * @property Collection|HojaTrabajo[] $hojas_trabajo
 */
class Ruta extends Model
{
    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'EMP_ID'
    ];

    public function hojas_trabajo()
    {
        return $this->hasMany(HojaTrabajo::class, 'id_ruta');
    }

    public function empresa()
    {
        return $this->belongsTo(EMPRESA::class, 'EMP_ID');
    }

}
