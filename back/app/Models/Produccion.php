<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Produccion
 * 
 * @property int $id_produccion
 * @property int $id_hoja
 * @property int $nro_vuelta
 * @property string|null $hora_bajada
 * @property float|null $valor_bajada
 * 
 * @property HojaTrabajo $hoja_trabajo
 */
class Produccion extends Model
{
    protected $table = 'produccion';
    protected $primaryKey = 'id_produccion';
    public $timestamps = false;

    protected $fillable = [
        'id_hoja',
        'nro_vuelta',
        'hora_subida',
        'hora_bajada',
        'valor_vuelta' // nuevo campo
    ];
    
    public function hoja_trabajo()
    {
        return $this->belongsTo(HojaTrabajo::class, 'id_hoja');
    }
}
