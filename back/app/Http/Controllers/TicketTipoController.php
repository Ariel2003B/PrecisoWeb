<?php

namespace App\Http\Controllers;

use App\Models\EMPRESA;
use App\Models\TicketTipo;
use Illuminate\Http\Request;

class TicketTipoController extends Controller
{
    public function index($empId)
    {
        $empresa = EMPRESA::findOrFail($empId);
        $tickets = TicketTipo::where('EMP_ID', $empId)->orderBy('nombre')->get();

        return view('ticket_tipos.index', compact('empresa', 'tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'EMP_ID'  => 'required|exists:EMPRESA,EMP_ID',
            'nombre'  => 'required|string|max:100',
            'valor'   => 'required|numeric|min:0',
        ]);

        TicketTipo::create($request->only(['EMP_ID', 'nombre', 'valor']));

        return redirect()
            ->route('ticket-tipos.index', $request->EMP_ID)
            ->with('success', 'Tipo de ticket creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'valor'  => 'required|numeric|min:0',
        ]);

        $ticket = TicketTipo::findOrFail($id);
        $ticket->update($request->only(['nombre', 'valor']));

        return redirect()
            ->route('ticket-tipos.index', $ticket->EMP_ID)
            ->with('success', 'Tipo de ticket actualizado.');
    }

    public function toggleActivo($id)
    {
        $ticket = TicketTipo::findOrFail($id);
        $ticket->update(['activo' => !$ticket->activo]);

        return redirect()
            ->route('ticket-tipos.index', $ticket->EMP_ID)
            ->with('success', $ticket->activo ? 'Ticket activado.' : 'Ticket desactivado.');
    }
}
