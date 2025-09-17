<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoStop extends Model
{
    protected $table = 'GEO_STOP';
    protected $primaryKey = 'GST_ID';
    public $timestamps = false;

    protected $fillable = [
        'EMP_ID',
        'NIMBUS_ID',
        'NOMBRE',
        'DEPOT',
        'VALOR_MINUTO',
        'ESTADO'
    ];

    /* Relaciones */
    public function empresa()
    {
        return $this->belongsTo(EMPRESA::class, 'EMP_ID');
    }

    /* Scopes */
    public function scopeDeEmpresa($q, $empId)
    {
        return $q->where('EMP_ID', $empId);
    }

    public function scopeActivas($q)
    {
        return $q->where('ESTADO', 'A');
    }

    /* Helpers */
    public static function tarifaPorNimbusId(int $empId, int $nimbusId): float
    {
        $valor = static::where('EMP_ID', $empId)
            ->where('NIMBUS_ID', $nimbusId)
            ->value('VALOR_MINUTO');

        return $valor !== null ? (float) $valor : 0.0;
    }

    /** Devuelve mapa [NIMBUS_ID => VALOR_MINUTO] para esa empresa */
    public static function mapaTarifas(int $empId, array $nimbusIds): array
    {
        return static::where('EMP_ID', $empId)
            ->whereIn('NIMBUS_ID', $nimbusIds)
            ->pluck('VALOR_MINUTO', 'NIMBUS_ID')
            ->map(fn($v) => (float) $v)
            ->toArray();
    }
}
