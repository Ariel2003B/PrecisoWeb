<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CUOTA_VENTA extends Model
{
    protected $table = 'CUOTAS_VENTA';
    protected $primaryKey = 'CVE_ID';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'VEN_ID',
        'FECHA_PAGO',
        'VALOR_CUOTA',
        'COMPROBANTE',
        'FECHA_REAL_PAGO',
        'OBSERVACION',
    ];

    protected $casts = [
        'VEN_ID' => 'int',
        'FECHA_PAGO' => 'date',
        'FECHA_REAL_PAGO' => 'date',
        'VALOR_CUOTA' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(VENTA::class, 'VEN_ID', 'VEN_ID');
    }
}
