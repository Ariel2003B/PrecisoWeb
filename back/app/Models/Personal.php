<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Personal
 * 
 * @property int $id_personal
 * @property string|null $nombre
 * @property string $tipo
 * @property string|null $cedula
 * @property string|null $telefono
 * @property string|null $correo
 * 
 * @property Collection|HojaTrabajo[] $hojas_como_conductor
 * @property Collection|HojaTrabajo[] $hojas_como_ayudante
 */
class Personal extends Model
{
    protected $table = 'personal';
    protected $primaryKey = 'id_personal';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'cedula',
        'telefono',
        'correo'
    ];

    public function hojas_como_conductor()
    {
        return $this->hasMany(HojaTrabajo::class, 'id_conductor');
    }

    public function hojas_como_ayudante()
    {
        return $this->hasMany(HojaTrabajo::class, 'id_ayudante');
    }
}
