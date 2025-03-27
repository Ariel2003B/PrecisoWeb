<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionUsuario extends Model
{
    protected $table = 'produccion_usuario';
    protected $primaryKey = 'id_usuario_produccion';
    public $timestamps = true;

    protected $fillable = [
        'id_hoja',
        'nro_vuelta',
        'pasaje_completo',
        'pasaje_medio',
        'valor_vuelta',
        'usu_id'
    ];

    // Relación con HojaTrabajo
    public function hoja()
    {
        return $this->belongsTo(HojaTrabajo::class, 'id_hoja');
    }

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(USUARIO::class, 'usu_id');
    }
}
