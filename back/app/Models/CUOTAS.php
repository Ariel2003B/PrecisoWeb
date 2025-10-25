<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CUOTAS
 *
 * @property int $CUO_ID
 * @property int|null $DET_ID
 * @property \Illuminate\Support\Carbon|null $FECHA_PAGO
 * @property string|null $VALOR_CUOTA
 * @property string|null $COMPROBANTE
 */
class CUOTAS extends Model
{
    protected $table = 'CUOTAS';
    protected $primaryKey = 'CUO_ID';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $casts = [
        'DET_ID' => 'int',
        'FECHA_PAGO' => 'date',
        'VALOR_CUOTA' => 'decimal:2',
    ];

    protected $fillable = [
        'DET_ID',
        'FECHA_PAGO',
        'VALOR_CUOTA',
        'COMPROBANTE',
    ];

    public function detalle(): BelongsTo
    {
        return $this->belongsTo(DETALLE_SIMCARD::class, 'DET_ID', 'DET_ID');
    }
}
