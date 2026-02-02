<?php

namespace App\Http\Controllers;

use App\Models\DETALLE_SIMCARD;
use App\Models\DETALLE_SIMCARD_SERVICIO;
use App\Models\EMPRESA;
use App\Models\EQUIPO_ACCESORIO;
use App\Models\IVA_CONFIG;
use App\Models\SIMCARD;
use App\Models\USUARIO;
use App\Models\VENTA;
use App\Models\VENTA_DETALLE;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $estado = trim($request->get('estado', ''));

        $ventas = VENTA::query()
            ->with([
                'cliente:USU_ID,NOMBRE,APELLIDO,CORREO',
                'vendedor:USU_ID,NOMBRE,APELLIDO,CORREO',
                'empresa:EMP_ID,NOMBRE,RUC',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('NUMERO_VENTA', 'like', "%{$q}%")
                        ->orWhereHas('cliente', function ($c) use ($q) {
                            $c->where('NOMBRE', 'like', "%{$q}%")
                                ->orWhere('APELLIDO', 'like', "%{$q}%")
                                ->orWhere('CORREO', 'like', "%{$q}%");
                        })
                        ->orWhereHas('empresa', function ($e) use ($q) {
                            $e->where('NOMBRE', 'like', "%{$q}%")
                                ->orWhere('RUC', 'like', "%{$q}%");
                        });
                });
            })
            ->when($estado !== '', fn($query) => $query->where('ESTADO', $estado))
            ->orderByDesc('FECHA')
            ->paginate(10)
            ->withQueryString();

        // Para el filtro de estados (puedes ajustar a tus valores reales)
        $estados = ['PENDIENTE', 'PARCIAL', 'PAGADO', 'ANULADO'];

        return view('ventas.index', compact('ventas', 'q', 'estado', 'estados'));
    }

    public function create()
    {
        $hoy = Carbon::now()->toDateString();

        $ivaVigente = IVA_CONFIG::query()
            ->where('ESTADO', 'ACTIVO')
            ->whereDate('FECHA_DESDE', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('FECHA_HASTA')
                    ->orWhereDate('FECHA_HASTA', '>=', $hoy);
            })
            ->orderByDesc('FECHA_DESDE')
            ->first();

        $ivaPercent = (float) ($ivaVigente?->VALOR_IVA ?? 0);

        $equipos = EQUIPO_ACCESORIO::query()
            ->select('EQU_ID', 'EQU_NOMBRE', 'EQU_PRECIO', 'EQU_STOCK')
            ->orderBy('EQU_NOMBRE')
            ->get();

        // clientes y vendedores (por ahora activos; luego filtramos por rol/perfil)
        $usuarios = USUARIO::query()
            ->select('USU_ID', 'NOMBRE', 'APELLIDO', 'CORREO', 'CEDULA')
            ->where('ESTADO', 'A')
            ->orderBy('NOMBRE')
            ->get();

        $empresas = EMPRESA::query()
            ->select('EMP_ID', 'NOMBRE', 'RUC')
            ->where('ESTADO', 'A')
            ->orderBy('NOMBRE')
            ->get();

        return view('ventas.create', compact('equipos', 'ivaPercent', 'usuarios', 'empresas'));
    }


    public function simcardsDisponibles(Request $request)
    {
        $q = trim($request->get('q', ''));

        $simcards = SIMCARD::query()
            ->select('ID_SIM', 'NUMEROTELEFONO')
            ->whereNotNull('NUMEROTELEFONO')
            ->when($q !== '', fn($qq) => $qq->where('NUMEROTELEFONO', 'like', "%{$q}%"))
            ->where(function ($w) {
                $w->whereHas('detalleSimcards', fn($d) => $d->where('FACTURADO', 0))
                    ->orWhereHas('servicios', fn($s) => $s->where('FACTURADO', 0));
            })
            ->orderBy('NUMEROTELEFONO')
            ->limit(50)
            ->get();

        return response()->json($simcards);
    }

    public function simcardContratos(SIMCARD $sim)
    {
        // hardware (DETALLE_SIMCARD) no facturado
        $hardware = DETALLE_SIMCARD::query()
            ->where('SIM_ID', $sim->ID_SIM)
            ->where('FACTURADO', 0)
            ->orderByDesc('FECHA_ACTIVACION_RENOVACION')
            ->get()
            ->map(fn($d) => [
                'tipo' => 'HARDWARE',
                'id' => $d->DET_ID,
                'fecha' => optional($d->FECHA_ACTIVACION_RENOVACION)->toDateString(),
                'siguiente_pago' => optional($d->FECHA_SIGUIENTE_PAGO)->toDateString(),
                'plazo' => $d->PLAZO_CONTRATADO,
                'cuotas' => $d->NUMERO_CUOTAS,
                'valor_total' => (float) $d->VALOR_TOTAL,
            ]);


        // servicio (DETALLE_SIMCARD_SERVICIO) no facturado
        $servicio = DETALLE_SIMCARD_SERVICIO::query()
            ->where('SIM_ID', $sim->ID_SIM)
            ->where('FACTURADO', 0)
            ->orderByDesc('FECHA_SERVICIO')
            ->get()
            ->map(fn($s) => [
                'tipo' => 'SERVICIO',
                'id' => $s->SERV_ID,
                'fecha' => optional($s->FECHA_SERVICIO)->toDateString(),
                'siguiente_pago' => optional($s->FECHA_SIGUIENTE_PAGO)->toDateString(),
                'valor_pago' => (float) $s->VALOR_PAGO,
                'obs' => $s->OBSERVACION,
            ]);

        return response()->json([
            'sim' => [
                'ID_SIM' => $sim->ID_SIM,
                'NUMEROTELEFONO' => $sim->NUMEROTELEFONO,
            ],
            'hardware' => $hardware->values(),
            'servicio' => $servicio->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'TIPO_COMPROBANTE' => 'required|in:FACTURA,NOTA_VENTA',
            'PORCENTAJE_DESCUENTO' => 'nullable|numeric|min:0|max:100',
            'OBSERVACION' => 'nullable|string|max:255',

            'USU_ID_VENDEDOR' => 'required|integer|exists:USUARIO,USU_ID',

            // cliente o empresa (uno u otro)
            'USU_ID_CLIENTE' => 'nullable|integer|exists:USUARIO,USU_ID',
            'EMP_ID' => 'nullable|integer|exists:EMPRESA,EMP_ID',

            'SUBTOTAL' => 'required|numeric|min:0',
            'IVA' => 'required|numeric|min:0',
            'TOTAL' => 'required|numeric|min:0',

            'DETALLE_JSON' => 'required|string',
        ]);

        $items = json_decode($data['DETALLE_JSON'], true);
        if (!is_array($items) || count($items) === 0) {
            throw ValidationException::withMessages(['DETALLE_JSON' => 'Detalle vacío']);
        }

        // regla: empresa o cliente
        $facturaEmpresa = !empty($data['EMP_ID']);
        if ($facturaEmpresa) {
            $data['USU_ID_CLIENTE'] = null;
        } else {
            if (empty($data['USU_ID_CLIENTE'])) {
                throw ValidationException::withMessages(['USU_ID_CLIENTE' => 'Selecciona cliente']);
            }
            $data['EMP_ID'] = null;
        }

        return DB::transaction(function () use ($data, $items) {

            $hoy = now()->toDateString(); // 2026-02-02

            // Bloquea para evitar que 2 transacciones generen el mismo número
            $ultimo = \App\Models\VENTA::whereDate('CREATED_AT', $hoy)
                ->lockForUpdate()
                ->orderByDesc('VEN_ID')
                ->value('NUMERO_VENTA');

            // Si tu formato será: V-YYYYMMDD-0001
            $prefijo = 'V-' . now()->format('Ymd');

            $seq = 1;
            if ($ultimo && str_starts_with($ultimo, $prefijo)) {
                // extrae lo que viene después del prefijo y guion final
                // ejemplo: V-20260202-0007  => 0007
                $parte = substr($ultimo, strlen($prefijo) + 1);
                $seq = ((int) $parte) + 1;
            }

            $numeroVenta = $prefijo . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $venta = VENTA::create([
                'NUMERO_VENTA' => $numeroVenta,
                'FECHA' => now(),
                'TIPO_COMPROBANTE' => $data['TIPO_COMPROBANTE'],
                'SUBTOTAL' => $data['SUBTOTAL'],
                'IVA' => $data['IVA'],
                'TOTAL' => $data['TOTAL'],
                'PORCENTAJE_DESCUENTO' => $data['PORCENTAJE_DESCUENTO'] ?? 0,
                'ESTADO' => 'PENDIENTE',
                'USU_ID_VENDEDOR' => $data['USU_ID_VENDEDOR'],
                'USU_ID_CLIENTE' => $data['USU_ID_CLIENTE'],
                'EMP_ID' => $data['EMP_ID'],
            ]);

            foreach ($items as $it) {

                // EQUIPOS / ACCESORIOS
                if (empty($it['tipo'])) {

                    $equId = (int) ($it['equ_id'] ?? 0);
                    $cantidad = (int) ($it['cantidad'] ?? 0);

                    $precioSinIva = (float) ($it['precio'] ?? 0);
                    $subtotalSinIva = (float) ($it['subtotal'] ?? 0);

                    if (!$equId || $cantidad < 1) {
                        throw ValidationException::withMessages(['DETALLE_JSON' => 'Item de equipo inválido']);
                    }

                    $equipo = EQUIPO_ACCESORIO::lockForUpdate()->findOrFail($equId);
                    if ($cantidad > (int) $equipo->EQU_STOCK) {
                        throw ValidationException::withMessages(['DETALLE_JSON' => "Stock insuficiente para {$equipo->EQU_NOMBRE}"]);
                    }

                    // crear detalle
                    VENTA_DETALLE::create([
                        'VEN_ID' => $venta->VEN_ID,
                        'PRODUCTO_TIPO' => 'EQUIPO',
                        'EQU_ID' => $equId,
                        'SIM_ID' => null,
                        'CANTIDAD' => $cantidad,
                        'PRECIO' => $precioSinIva,        // <- SIN IVA
                        'SUBTOTAL' => $subtotalSinIva,    // <- SIN IVA
                    ]);

                    // descontar stock
                    $equipo->EQU_STOCK = (int) $equipo->EQU_STOCK - $cantidad;
                    $equipo->save();

                    continue;
                }
                if (($it['tipo'] ?? '') === 'EQUIPO'){

                    $equId = (int) ($it['equ_id'] ?? 0);
                    $cantidad = (int) ($it['cantidad'] ?? 0);

                    $precioSinIva = (float) ($it['precio'] ?? 0);
                    $subtotalSinIva = (float) ($it['subtotal'] ?? 0);

                    if (!$equId || $cantidad < 1) {
                        throw ValidationException::withMessages(['DETALLE_JSON' => 'Item de equipo inválido']);
                    }

                    $equipo = EQUIPO_ACCESORIO::lockForUpdate()->findOrFail($equId);
                    if ($cantidad > (int) $equipo->EQU_STOCK) {
                        throw ValidationException::withMessages(['DETALLE_JSON' => "Stock insuficiente para {$equipo->EQU_NOMBRE}"]);
                    }

                    // crear detalle
                    VENTA_DETALLE::create([
                        'VEN_ID' => $venta->VEN_ID,
                        'PRODUCTO_TIPO' => 'EQUIPO',
                        'EQU_ID' => $equId,
                        'SIM_ID' => null,
                        'CANTIDAD' => $cantidad,
                        'PRECIO' => $precioSinIva,        // <- SIN IVA
                        'SUBTOTAL' => $subtotalSinIva,    // <- SIN IVA
                    ]);

                    // descontar stock
                    $equipo->EQU_STOCK = (int) $equipo->EQU_STOCK - $cantidad;
                    $equipo->save();

                    continue;
                }
                // SIM CONTRATO
                if (($it['tipo'] ?? '') === 'SIM_CONTRATO') {

                    $simId = (int) ($it['sim_id'] ?? 0);
                    $contratoTipo = $it['contrato_tipo'] ?? '';
                    $contratoId = (int) ($it['contrato_id'] ?? 0);
                    $precio = (float) ($it['precio'] ?? 0);

                    if (!$simId || !$contratoId || !in_array($contratoTipo, ['HARDWARE', 'SERVICIO'])) {
                        throw ValidationException::withMessages(['DETALLE_JSON' => 'Contrato SIM inválido']);
                    }

                    VENTA_DETALLE::create([
                        'VEN_ID' => $venta->VEN_ID,
                        'TIPO' => 'SIM_CONTRATO',
                        'SIM_ID' => $simId,
                        'CANTIDAD' => 1,
                        'PRECIO' => $precio,
                        'SUBTOTAL' => $precio,
                    ]);

                    // marcar facturado
                    if ($contratoTipo === 'HARDWARE') {
                        DETALLE_SIMCARD::where('DET_ID', $contratoId)
                            ->where('SIM_ID', $simId)
                            ->update(['FACTURADO' => 1]);
                    } else {
                        DETALLE_SIMCARD_SERVICIO::where('SERV_ID', $contratoId)
                            ->where('SIM_ID', $simId)
                            ->update(['FACTURADO' => 1]);
                    }

                    continue;
                }

                throw ValidationException::withMessages(['DETALLE_JSON' => 'Tipo desconocido']);
            }

            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta creada: {$venta->NUMERO_VENTA}");
        });
    }
}
