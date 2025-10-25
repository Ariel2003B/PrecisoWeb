<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DETALLE_SIMCARD
 *
 * @property int $DET_ID
 * @property int|null $SIM_ID
 * @property \Illuminate\Support\Carbon|null $FECHA_ACTIVACION_RENOVACION
 * @property \Illuminate\Support\Carbon|null $FECHA_SIGUIENTE_PAGO
 * @property int|null $PLAZO_CONTRATADO
 * @property string|null $VALOR_TOTAL
 * @property string|null $VALOR_ABONADO
 * @property string|null $SALDO
 * @property int|null $NUMERO_CUOTAS
 */
class DETALLE_SIMCARD extends Model
{
    protected $table = 'DETALLE_SIMCARD';
    protected $primaryKey = 'DET_ID';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $casts = [
        'SIM_ID' => 'int',
        'PLAZO_CONTRATADO' => 'int',
        'NUMERO_CUOTAS' => 'int',
        'FECHA_ACTIVACION_RENOVACION' => 'date',
        'FECHA_SIGUIENTE_PAGO' => 'date',
        'VALOR_TOTAL' => 'decimal:2',
        'VALOR_ABONADO' => 'decimal:2',
        'SALDO' => 'decimal:2',
    ];

    protected $fillable = [
        'SIM_ID',
        'FECHA_ACTIVACION_RENOVACION',
        'FECHA_SIGUIENTE_PAGO',
        'PLAZO_CONTRATADO',
        'VALOR_TOTAL',
        'VALOR_ABONADO',
        'SALDO',
        'NUMERO_CUOTAS',
    ];

    public function simcard(): BelongsTo
    {
        return $this->belongsTo(SIMCARD::class, 'SIM_ID', 'ID_SIM');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(CUOTAS::class, 'DET_ID', 'DET_ID');
    }

    public function scopeVigente($q, $fecha = null)
    {
        $d = $fecha ? Carbon::parse($fecha) : Carbon::today();
        return $q->whereDate('FECHA_ACTIVACION_RENOVACION', '<=', $d)
            ->whereDate('FECHA_SIGUIENTE_PAGO', '>=', $d);
    }
}
