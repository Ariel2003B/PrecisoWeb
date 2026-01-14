<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CUOTAS_SERVICIO extends Model
{
    protected $table = 'CUOTAS_SERVICIO';
    protected $primaryKey = 'CUOS_ID';
    public $timestamps = false;

    protected $casts = [
        'SERV_ID' => 'int',
        'FECHA_PAGO' => 'date',
        'FECHA_REAL_PAGO' => 'date',
        'VALOR_CUOTA' => 'decimal:2',
    ];

    protected $fillable = [
        'SERV_ID',
        'FECHA_PAGO',
        'FECHA_REAL_PAGO',
        'VALOR_CUOTA',
        'COMPROBANTE',
        'OBSERVACION',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(DETALLE_SIMCARD_SERVICIO::class, 'SERV_ID', 'SERV_ID');
    }
}
