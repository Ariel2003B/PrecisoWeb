<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DOCUMENTOS_GENERADOS
 *
 * @property int $DOC_ID
 * @property int|null $SIM_ID
 * @property \Illuminate\Support\Carbon|null $FECHA_CREACION
 */
class DOCUMENTOS_GENERADOS extends Model
{
    protected $table = 'DOCUMENTOS_GENERADOS';
    protected $primaryKey = 'DOC_ID';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $casts = [
        'SIM_ID' => 'int',
        'FECHA_CREACION' => 'date',
    ];

    protected $fillable = [
        'SIM_ID',
        'FECHA_CREACION',
    ];

    public function simcard()
    {
        return $this->belongsTo(SIMCARD::class, 'SIM_ID', 'ID_SIM');
    }
}
