<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTipo extends Model
{
    protected $table = 'ticket_tipos';
    public $timestamps = false;

    protected $fillable = [
        'EMP_ID',
        'nombre',
        'valor',
        'activo',
    ];

    public function empresa()
    {
        return $this->belongsTo(EMPRESA::class, 'EMP_ID');
    }

    public function produccionTickets()
    {
        return $this->hasMany(ProduccionTicket::class, 'id_ticket_tipo');
    }
}
