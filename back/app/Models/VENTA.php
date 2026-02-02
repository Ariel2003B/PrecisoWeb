<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VENTA extends Model
{
    protected $table = 'VENTA';
    protected $primaryKey = 'VEN_ID';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'NUMERO_VENTA',
        'FECHA',
        'TIPO_COMPROBANTE',
        'SUBTOTAL',
        'IVA',
        'TOTAL',
        'PORCENTAJE_DESCUENTO',
        'ESTADO',
        'USU_ID_VENDEDOR',
        'USU_ID_CLIENTE',
        'EMP_ID',
    ];

    protected $casts = [
        'FECHA' => 'datetime',
        'SUBTOTAL' => 'decimal:2',
        'IVA' => 'decimal:2',
        'TOTAL' => 'decimal:2',
        'PORCENTAJE_DESCUENTO' => 'decimal:2',
        'USU_ID_VENDEDOR' => 'int',
        'USU_ID_CLIENTE' => 'int',
        'EMP_ID' => 'int',
    ];

    public function vendedor()
    {
        return $this->belongsTo(USUARIO::class, 'USU_ID_VENDEDOR', 'USU_ID');
    }

    public function cliente()
    {
        return $this->belongsTo(USUARIO::class, 'USU_ID_CLIENTE', 'USU_ID');
    }

    public function empresa()
    {
        return $this->belongsTo(EMPRESA::class, 'EMP_ID', 'EMP_ID');
    }

    public function detalles()
    {
        return $this->hasMany(VENTA_DETALLE::class, 'VEN_ID', 'VEN_ID');
    }

    public function cuotas()
    {
        return $this->hasMany(CUOTA_VENTA::class, 'VEN_ID', 'VEN_ID');
    }
}
