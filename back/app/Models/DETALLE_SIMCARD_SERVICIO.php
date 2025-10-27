<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DETALLE_SIMCARD_SERVICIO
 *
 * @property int $SERV_ID
 * @property int $SIM_ID
 * @property \Illuminate\Support\Carbon $FECHA_SERVICIO
 * @property \Illuminate\Support\Carbon|null $FECHA_SIGUIENTE_PAGO
 * @property string $VALOR_PAGO
 * @property string|null $COMPROBANTE
 * @property string|null $OBSERVACION
 */
class DETALLE_SIMCARD_SERVICIO extends Model
{
    protected $table = 'DETALLE_SIMCARD_SERVICIO';
    protected $primaryKey = 'SERV_ID';
    public $timestamps = false;

    protected $casts = [
        'SIM_ID' => 'int',
        'FECHA_SERVICIO' => 'date',
        'FECHA_SIGUIENTE_PAGO' => 'date',
        'VALOR_PAGO' => 'decimal:2',
    ];

    protected $fillable = [
        'SIM_ID',
        'FECHA_SERVICIO',
        'FECHA_SIGUIENTE_PAGO',
        'VALOR_PAGO',
        'COMPROBANTE',
        'OBSERVACION',
    ];

    public function simcard(): BelongsTo
    {
        return $this->belongsTo(SIMCARD::class, 'SIM_ID', 'ID_SIM');
    }
}
