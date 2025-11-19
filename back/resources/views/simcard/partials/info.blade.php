@php
    use Illuminate\Support\Str;
@endphp

<div class="sim-info">

    {{-- ===================== DATOS PRINCIPALES ====================== --}}
    <h5 class="mb-1">SIM #{{ $simcard->ID_SIM }}</h5>
    <div class="text-muted mb-3 small">
        ICC: {{ $simcard->ICC ?? '—' }} · Número: {{ $simcard->NUMEROTELEFONO ?? '—' }}
    </div>

    <div class="row g-3">

        {{-- ===================== SIM ====================== --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2"><strong>Datos de la SIM</strong></div>
                <div class="card-body small">
                    <div><b>Plataforma:</b> {{ $simcard->PLATAFORMA ?? '—' }}</div>
                    <div><b>Plan:</b> {{ $simcard->PLAN ?? '—' }}</div>
                    <div><b>Tipo plan:</b> {{ $simcard->TIPOPLAN ?? '—' }}</div>
                    <div><b>Proveedor:</b> {{ $simcard->PROVEEDOR ?? '—' }}</div>

                    <div class="mt-2"><b>Estado:</b>
                        @if ($simcard->ESTADO === 'ACTIVA')
                            <span class="badge bg-success">Activa</span>
                        @elseif ($simcard->ESTADO === 'LIBRE')
                            <span class="badge bg-warning text-dark">Libre</span>
                        @else
                            <span class="badge bg-danger">Eliminada</span>
                        @endif
                    </div>

                    <hr>

                    <div><b>Grupo:</b> {{ $simcard->GRUPO ?? '—' }}</div>
                    <div><b>Asignación:</b> {{ $simcard->ASIGNACION ?? '—' }}</div>
                    <div><b>Equipo:</b> {{ $simcard->EQUIPO ?? '—' }}</div>
                    <div><b>IMEI:</b> {{ $simcard->IMEI ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- ===================== CLIENTE & VEHÍCULO ====================== --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2"><strong>Cliente / Vehículo</strong></div>
                <div class="card-body small">

                    {{-- Cliente --}}
                    <div class="mb-3">
                        <b>Cliente:</b><br>
                        @if ($simcard->usuario)
                            {{ $simcard->usuario->APELLIDO }} {{ $simcard->usuario->NOMBRE }}
                            <div class="text-muted small">
                                Ced: {{ $simcard->usuario->CEDULA }} · Tel: {{ $simcard->usuario->TELEFONO }}
                            </div>
                        @else
                            —
                        @endif
                    </div>

                    {{-- Vehículo --}}
                    <b>Vehículo asignado:</b><br>
                    @if ($simcard->v_e_h_i_c_u_l_o)
                        <div class="text-muted">
                            {{ $simcard->v_e_h_i_c_u_l_o->PLACA }}
                            ({{ $simcard->v_e_h_i_c_u_l_o->MODELO ?? '—' }})
                        </div>
                    @else
                        <span class="text-muted small">Sin vehículo asignado</span>
                    @endif
                </div>
            </div>
        </div>
    </div>



    {{-- ===================== CONTRATO VIGENTE ====================== --}}
    <div class="card mt-3 shadow-sm">
        <div class="card-header py-2">
            <strong>Contrato vigente</strong>
        </div>
        <div class="card-body small">

            @if (!$vigente)
                <div class="text-muted">No hay contrato vigente.</div>
            @else
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div><b>Activación:</b> {{ $vigente->FECHA_ACTIVACION_RENOVACION }}</div>
                        <div><b>Próx. pago:</b> {{ $vigente->FECHA_SIGUIENTE_PAGO }}</div>
                        <div><b>Plazo:</b> {{ $vigente->PLAZO_CONTRATADO }} meses</div>
                    </div>
                    <div class="col-sm-6">
                        <div><b>Valor total:</b> ${{ number_format($vigente->VALOR_TOTAL, 2) }}</div>
                        <div><b>Abonado:</b> ${{ number_format($vigente->VALOR_ABONADO, 2) }}</div>
                        <div><b>Saldo:</b> ${{ number_format($vigente->SALDO, 2) }}</div>
                    </div>
                </div>

                @if ($vigente->cuotas->count())
                    <hr>
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                                <th>Comp.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vigente->cuotas as $i => $c)
                                <tr>
                                    <td>#{{ $i + 1 }}</td>
                                    <td>{{ $c->FECHA_PAGO }}</td>
                                    <td>${{ number_format($c->VALOR_CUOTA, 2) }}</td>
                                    <td>
                                        @if ($c->COMPROBANTE)
                                            <a href="{{ doc_url($c->COMPROBANTE) }}" target="_blank"
                                                class="btn btn-xs btn-outline-primary">Ver</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            @endif
        </div>
    </div>

    {{-- CONTRATO VIGENTE (Hardware) --}}
    <div class="card mb-3">
        <div class="card-header fw-bold">Contrato vigente (Hardware)</div>
        <div class="card-body">
            @if ($vigente)
                <div class="row mb-2">
                    <div class="col-md-3">
                        <strong>Activación:</strong>
                        {{ optional($vigente->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Plazo:</strong> {{ $vigente->NUMERO_CUOTAS }} meses
                    </div>
                    <div class="col-md-3">
                        <strong>Total:</strong> ${{ number_format($vigente->VALOR_TOTAL, 2) }}
                    </div>
                </div>

                @if ($vigente->cuotas?->count())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha pago</th>
                                    <th>Valor</th>
                                    <th>Pagado</th>
                                    <th>Comp.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vigente->cuotas as $idx => $c)
                                    @php
                                        $esArchivo = \Illuminate\Support\Str::startsWith($c->COMPROBANTE, [
                                            'simcards/',
                                        ]);
                                    @endphp
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ optional($c->FECHA_PAGO)->format('Y-m-d') }}</td>
                                        <td>${{ number_format($c->VALOR_CUOTA, 2) }}</td>
                                        <td>{{ $c->COMPROBANTE ? 'Sí' : 'No' }}</td>
                                        <td>
                                            @if ($c->COMPROBANTE)
                                                @if ($esArchivo)
                                                    <button type="button"
                                                        class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                        data-url="{{ asset('back/storage/app/public/' . $c->COMPROBANTE) }}">
                                                        Ver
                                                    </button>
                                                @else
                                                    <button type="button"
                                                        class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                        data-url="{{ $c->COMPROBANTE }}">
                                                        Abrir
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">Sin cuotas registradas.</div>
                @endif
            @else
                <div class="text-muted">No hay contrato vigente.</div>
            @endif
        </div>
    </div>

    {{-- HISTORIAL DE SERVICIOS --}}
    <div class="card mb-3">
        <div class="card-header fw-bold">Historial de servicios</div>
        <div class="card-body p-2">
            @if ($servicios->count())
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha activación</th>
                                <th>Plazo (meses)</th>
                                <th>Valor</th>
                                <th>Siguiente pago</th>
                                <th>Comp.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servicios as $s)
                                @php
                                    $esArchivo = \Illuminate\Support\Str::startsWith($s->COMPROBANTE, ['simcards/']);
                                @endphp
                                <tr>
                                    <td>{{ optional($s->FECHA_SERVICIO)->format('Y-m-d') }}</td>
                                    <td>{{ $s->PLAZO_CONTRATADO }}</td>
                                    <td>${{ number_format($s->VALOR_PAGO, 2) }}</td>
                                    <td>{{ optional($s->FECHA_SIGUIENTE_PAGO)->format('Y-m-d') }}</td>
                                    <td>
                                        @if ($s->COMPROBANTE)
                                            @if ($esArchivo)
                                                <button type="button"
                                                    class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                    data-url="{{ asset('back/storage/app/public/' . $s->COMPROBANTE) }}">
                                                    Ver
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                    data-url="{{ $s->COMPROBANTE }}">
                                                    Abrir
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">No hay servicios registrados.</div>
            @endif
        </div>
    </div>

    {{-- HISTORIAL DE CONTRATOS (Hardware) --}}
    <div class="card mb-3">
        <div class="card-header fw-bold">Historial de contratos (Hardware)</div>
        <div class="card-body p-2">
            @forelse ($historial as $h)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div>
                            <strong>ID:</strong> {{ $h->DET_ID ?? ($h->id ?? '—') }} ·
                            <strong>Activación:</strong>
                            {{ optional($h->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d') }}
                        </div>
                        <div>
                            <strong>Plazo:</strong> {{ $h->NUMERO_CUOTAS }} meses ·
                            <strong>Total:</strong> ${{ number_format($h->VALOR_TOTAL, 2) }}
                        </div>
                    </div>

                    @if ($h->cuotas?->count())
                        <div class="table-responsive mt-2">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Valor</th>
                                        <th>Pagado</th>
                                        <th>Comp.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($h->cuotas as $idx => $c)
                                        @php
                                            $esArchivo = \Illuminate\Support\Str::startsWith($c->COMPROBANTE, [
                                                'simcards/',
                                            ]);
                                        @endphp
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ optional($c->FECHA_PAGO)->format('Y-m-d') }}</td>
                                            <td>${{ number_format($c->VALOR_CUOTA, 2) }}</td>
                                            <td>{{ $c->COMPROBANTE ? 'Sí' : 'No' }}</td>
                                            <td>
                                                @if ($c->COMPROBANTE)
                                                    @if ($esArchivo)
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                            data-url="{{ asset('back/storage/app/public/' . $c->COMPROBANTE) }}">
                                                            Ver
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-secondary btn-ver-comprobante"
                                                            data-url="{{ $c->COMPROBANTE }}">
                                                            Abrir
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="small text-muted mt-1">Sin cuotas registradas.</div>
                    @endif
                </div>
            @empty
                <div class="text-muted">No hay contratos registrados.</div>
            @endforelse
        </div>
    </div>


    {{-- ===================== DOCUMENTOS ====================== --}}
    <div class="card mt-3 shadow-sm">
        <div class="card-header py-2"><strong>Documentos generados</strong></div>
        <div class="card-body small">
            @if ($simcard->documentosGenerados->isEmpty())
                <span class="text-muted">No hay documentos.</span>
            @else
                <ul>
                    @foreach ($simcard->documentosGenerados as $doc)
                        <li>
                            {{ $doc->NOMBRE }} —
                            <a href="{{ $doc->URL }}" target="_blank">Abrir</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

</div>
