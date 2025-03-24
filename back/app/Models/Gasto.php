<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Gasto
 * 
 * @property int $id_gasto
 * @property int $id_hoja
 * @property string $tipo_gasto
 * @property float $valor
 * 
 * @property HojaTrabajo $hoja_trabajo
 */
class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id_gasto';
    public $timestamps = false;

    protected $fillable = [
        'id_hoja',
        'tipo_gasto',
        'valor'
    ];

    public function hoja_trabajo()
    {
        return $this->belongsTo(HojaTrabajo::class, 'id_hoja');
    }
}
