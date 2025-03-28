<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class USUARIOPERMISO
 * 
 * @property int $USUPER_ID
 * @property int $USU_ID
 * @property int $PRM_ID
 * 
 * @property USUARIO $usuario
 * @property PERMISO $permiso
 *
 * @package App\Models
 */
class USUARIOPERMISO extends Model
{
    protected $table = 'USUARIOPERMISO';
    protected $primaryKey = 'USUPER_ID';
    public $timestamps = false;

    protected $fillable = [
        'USU_ID',
        'PRM_ID'
    ];

    public function usuario()
    {
        return $this->belongsTo(USUARIO::class, 'USU_ID');
    }

    public function permiso()
    {
        return $this->belongsTo(PERMISO::class, 'PRM_ID');
    }
}
