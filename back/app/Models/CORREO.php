<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CORREO
 * 
 * @property int $COR_ID
 * @property string $EMAIL
 * @property string $CREATED_AT
 * @property string $UPDATED_AT
 *
 * @package App\Models
 */
class CORREO extends Model
{
    protected $table = 'CORREOS';
    protected $primaryKey = 'COR_ID';
    public $timestamps = true;

    protected $fillable = [
        'EMAIL'
    ];
}
