<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionTicket extends Model
{
    protected $table = 'produccion_tickets';
    public $timestamps = false;

    protected $fillable = [
        'id_produccion',
        'id_ticket_tipo',
        'numero_inicio',
        'numero_fin',
    ];

    public function produccion()
    {
        return $this->belongsTo(Produccion::class, 'id_produccion');
    }

    public function ticketTipo()
    {
        return $this->belongsTo(TicketTipo::class, 'id_ticket_tipo');
    }

    public function getCantidadAttribute(): int
    {
        return max(0, $this->numero_fin - $this->numero_inicio);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->cantidad * ($this->ticketTipo->valor ?? 0);
    }
}
