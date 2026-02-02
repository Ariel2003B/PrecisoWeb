<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IVA_CONFIG extends Model
{
    protected $table = 'IVA_CONFIG';
    protected $primaryKey = 'IVA_ID';
    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';
    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'VALOR_IVA',
        'FECHA_DESDE',
        'FECHA_HASTA',
        'ESTADO',
        'OBSERVACION'
    ];

    protected $casts = [
        'VALOR_IVA' => 'decimal:2',
        'FECHA_DESDE' => 'date',
        'FECHA_HASTA' => 'date',
    ];
}
