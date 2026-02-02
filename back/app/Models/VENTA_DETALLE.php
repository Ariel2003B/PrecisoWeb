<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VENTA_DETALLE extends Model
{
    protected $table = 'VENTA_DETALLE';
    protected $primaryKey = 'VDE_ID';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'VEN_ID',
        'PRODUCTO_TIPO',
        'EQU_ID',
        'SIM_ID',
        'CANTIDAD',
        'PRECIO',
        'SUBTOTAL',
    ];

    protected $casts = [
        'VEN_ID' => 'int',
        'EQU_ID' => 'int',
        'SIM_ID' => 'int',
        'CANTIDAD' => 'int',
        'PRECIO' => 'decimal:2',
        'SUBTOTAL' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(VENTA::class, 'VEN_ID', 'VEN_ID');
    }

    public function equipo()
    {
        return $this->belongsTo(EQUIPO_ACCESORIO::class, 'EQU_ID', 'EQU_ID');
    }

    public function simcard()
    {
        return $this->belongsTo(SIMCARD::class, 'SIM_ID', 'ID_SIM');
    }
}
