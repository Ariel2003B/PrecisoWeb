<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SIMCARD_DEPENDENCIAS_LIBERADA extends Model
{
    protected $table = 'SIMCARD_DEPENDENCIAS_LIBERADAS';
    protected $primaryKey = 'ID';
    public $timestamps = false; // usamos FECHA_REGISTRO del schema

    protected $casts = [
        'SIM_ORIGEN_ID'       => 'int',
        'DETALLE_SERVICIO_ID' => 'int',
        'DETALLE_ID'          => 'int',
        'USU_ID'              => 'int',
        'SIM_DESTINO_ID'      => 'int',
        'FECHA_REGISTRO'      => 'datetime',
        'FECHA_REASIGNACION'  => 'datetime',
    ];

    protected $fillable = [
        'SIM_ORIGEN_ID',
        'DETALLE_SERVICIO_ID',
        'DETALLE_ID',
        'USU_ID',
        'SIM_DESTINO_ID',
        'ESTADO',
        'FECHA_REGISTRO',
        'FECHA_REASIGNACION',
    ];

    // Relaciones útiles (opcionales pero recomendadas)
    public function simOrigen()
    {
        return $this->belongsTo(SIMCARD::class, 'SIM_ORIGEN_ID', 'ID_SIM');
    }

    public function simDestino()
    {
        return $this->belongsTo(SIMCARD::class, 'SIM_DESTINO_ID', 'ID_SIM');
    }

    public function detalle()
    {
        return $this->belongsTo(DETALLE_SIMCARD::class, 'DETALLE_ID', 'DET_ID');
    }

    public function servicio()
    {
        return $this->belongsTo(DETALLE_SIMCARD_SERVICIO::class, 'DETALLE_SERVICIO_ID', 'SERV_ID');
    }

    public function usuario()
    {
        return $this->belongsTo(USUARIO::class, 'USU_ID', 'USU_ID');
    }
}
