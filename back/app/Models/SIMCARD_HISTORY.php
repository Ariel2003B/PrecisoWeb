<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SIMCARD_HISTORY
 * 
 * @property int $ID_HISTORY
 * @property int $ID_SIM
 * @property string|null $RUC
 * @property string|null $PROPIETARIO
 * @property string|null $CUENTA
 * @property string|null $NUMEROTELEFONO
 * @property string|null $TIPOPLAN
 * @property string|null $PLAN
 * @property string|null $ICC
 * @property string|null $ESTADO
 * @property string|null $GRUPO
 * @property string|null $ASIGNACION
 * @property string|null $EQUIPO
 * @property int|null $VEH_ID
 * @property string|null $IMEI
 * @property string $ACCION
 * @property string $USUARIO
 * @property \Carbon\Carbon $FECHA
 * 
 * @property SIMCARD $simcard
 *
 * @package App\Models
 */
class SIMCARD_HISTORY extends Model
{
    protected $table = 'SIMCARD_HISTORY';
    protected $primaryKey = 'ID_HISTORY';
    public $timestamps = false;

    protected $casts = [
        'ID_SIM' => 'int',
        'VEH_ID' => 'int',
        'FECHA' => 'datetime'
    ];

    protected $fillable = [
        'ID_SIM',
        'RUC',
        'PROPIETARIO',
        'CUENTA',
        'NUMEROTELEFONO',
        'TIPOPLAN',
        'PLAN',
        'ICC',
        'ESTADO',
        'GRUPO',
        'ASIGNACION',
        'EQUIPO',
        'VEH_ID',
        'IMEI',
        'ACCION',
        'USUARIO',
        'FECHA'
    ];

    public function simcard()
    {
        return $this->belongsTo(SIMCARD::class, 'ID_SIM');
    }
}
