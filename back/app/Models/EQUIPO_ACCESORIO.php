<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EQUIPO_ACCESORIO
 * 
 * @property int $EQU_ID
 * @property string $EQU_NOMBRE
 * @property float $EQU_PRECIO
 * @property string $EQU_ICONO
 * @property int $EQU_STOCK
 * @property string $CREATED_AT
 * @property string $UPDATED_AT
 *
 * @package App\Models
 */
class EQUIPO_ACCESORIO extends Model
{
    protected $table = 'EQUIPOS_ACCESORIOS';
    protected $primaryKey = 'EQU_ID';
    public $timestamps = true;

    protected $fillable = [
        'EQU_NOMBRE',
        'EQU_PRECIO',
        'EQU_ICONO',
        'EQU_STOCK'
    ];
}
