@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    if (!function_exists('doc_url')) {
        function doc_url(?string $v)
        {
            if (!$v) {
                return null;
            }
            if (Str::startsWith($v, ['simcards/', 'public/', 'docs/', 'uploads/'])) {
                try {
                    return asset('back/storage/app/public/' . $v);
                } catch (\Throwable $e) {
                    return asset('back/storage/app/public/' . $v);
                }
            }
            return $v;
        }
    }

    if (!function_exists('doc_is_image')) {
        function doc_is_image(?string $v)
        {
            if (!$v) {
                return false;
            }
            $v = strtolower($v);
            return Str::endsWith($v, ['.jpg', '.jpeg', '.png', '.gif', '.webp']) ||
                Str::contains($v, ['mime=image/', 'data:image']);
        }
    }

    if (!function_exists('doc_is_pdf')) {
        function doc_is_pdf(?string $v)
        {
            if (!$v) {
                return false;
            }
            $v = strtolower($v);
            return Str::endsWith($v, ['.pdf']) || Str::contains($v, ['application/pdf']);
        }
    }
@endphp

@php
    $prop = $simcard->usuario;
    $veh = $simcard->v_e_h_i_c_u_l_o;
@endphp

<div class="sim-info">
    <div class="mb-3">
        <h5 class="mb-1">SIM #{{ $simcard->ID_SIM }}</h5>
        <div class="small text-muted">ICC: {{ $simcard->ICC ?? '—' }} | Número: {{ $simcard->NUMEROTELEFONO ?? '—' }}
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header py-2">
                    <strong>Datos de la SIM</strong>
                </div>
                <div class="card-body small">
                    <div><b>Cuenta:</b> {{ $simcard->CUENTA ?? '—' }}</div>
                    <div><b>Plan:</b> {{ $simcard->PLAN ?? '—' }} <span
                            class="text-muted">({{ $simcard->TIPOPLAN ?? '—' }})</span></div>
                    <div><b>Estado:</b>
                        @if ($simcard->ESTADO === 'ACTIVA')
                            <span class="badge bg-success">Activa</span>
                        @elseif ($simcard->ESTADO === 'ELIMINADA')
                            <span class="badge bg-danger">Eliminada</span>
                        @elseif ($simcard->ESTADO === 'LIBRE')
                            <span class="badge bg-warning text-dark">Libre</span>
                        @else
                            <span class="badge bg-secondary">{{ $simcard->ESTADO ?? '—' }}</span>
                        @endif
                    </div>
                    <div><b>Grupo:</b> {{ $simcard->GRUPO ?? '—' }}</div>
                    <div><b>Asignación:</b> {{ $simcard->ASIGNACION ?? '—' }}</div>
                    <div><b>Equipo:</b> {{ $simcard->EQUIPO ?? '—' }}</div>
                    <div><b>IMEI:</b> {{ $simcard->IMEI ?? '—' }}</div>
                    <div><b>Modelo / Marca:</b> {{ $simcard->MODELO_EQUIPO ?? '—' }} /
                        {{ $simcard->MARCA_EQUIPO ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header py-2">
                    <strong>Propietario & Vehículo</strong>
                </div>
                <div class="card-body small">
                    <div class="mb-2"><b>Propietario:</b>
                        @if ($prop)
                            {{ $prop->APELLIDO }} {{ $prop->NOMBRE }} <span
                                class="text-muted">(#{{ $prop->USU_ID }})</span><br>
                            <span class="text-muted">Tel: {{ $prop->TELEFONO ?? '—' }} | Correo:
                                {{ $prop->CORREO ?? '—' }}</span>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contrato vigente --}}
    <div class="card mt-3 shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <strong>Contrato vigente</strong>
            @if (!$vigente)
                <span class="badge bg-secondary">No hay vigente</span>
            @endif
        </div>
        <div class="card-body small">
            @if ($vigente)
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div><b>Activación/Renovación:</b>
                            {{ optional($vigente->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d') ?? '—' }}</div>
                        <div><b>Próx. pago:</b> {{ optional($vigente->FECHA_SIGUIENTE_PAGO)->format('Y-m-d') ?? '—' }}
                        </div>
                        <div><b>Plazo (meses):</b> {{ $vigente->PLAZO_CONTRATADO ?? '—' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div><b>Valor total:</b> ${{ number_format((float) $vigente->VALOR_TOTAL, 2) }}</div>
                        <div><b>Abonado:</b> ${{ number_format((float) $vigente->VALOR_ABONADO, 2) }}</div>
                        <div><b>Saldo:</b> ${{ number_format((float) $vigente->SALDO, 2) }}</div>
                        <div><b># Cuotas:</b> {{ $vigente->NUMERO_CUOTAS ?? 0 }}</div>
                    </div>
                </div>

                @if ($vigente->cuotas->count())
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 72px;">Cuota</th>
                                    <th>Fecha</th>
                                    <th>Valor</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vigente->cuotas as $i => $cuo)
                                    <tr>
                                        <td>#{{ $i + 1 }}</td>
                                        <td>{{ optional($cuo->FECHA_PAGO)->format('Y-m-d') ?? '—' }}</td>
                                        <td>${{ number_format((float) $cuo->VALOR_CUOTA, 2) }}</td>
                                        <td>
                                            @php
                                                $raw = $cuo->COMPROBANTE;
                                                $url = doc_url($raw);
                                                $isImg = doc_is_image($raw);
                                                $isPdf = doc_is_pdf($raw);
                                            @endphp

                                            @if ($url)
                                                @if ($isImg)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ $url }}" class="thumb-doc"
                                                            alt="Comprobante"
                                                            onclick="openViewer('{{ $url }}','image')"
                                                            style="cursor:pointer">
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-viewer"
                                                            onclick="openViewer('{{ $url }}','image')">
                                                            <i class="bi bi-eye"></i> Ver
                                                        </button>
                                                        <a href="{{ $url }}" target="_blank"
                                                            class="btn btn-outline-primary btn-viewer">
                                                            <i class="bi bi-box-arrow-up-right"></i> Abrir
                                                        </a>
                                                    </div>
                                                @elseif ($isPdf)
                                                    <button type="button" class="btn btn-outline-secondary btn-viewer"
                                                        onclick="openViewer('{{ $url }}','pdf')">
                                                        <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                                                    </button>
                                                    <a href="{{ $url }}" target="_blank"
                                                        class="btn btn-outline-primary btn-viewer ms-1">
                                                        <i class="bi bi-box-arrow-up-right"></i> Abrir
                                                    </a>
                                                @else
                                                    <a href="{{ $url }}" target="_blank"
                                                        class="btn btn-outline-primary btn-viewer">
                                                        <i class="bi bi-box-arrow-up-right"></i> Abrir
                                                    </a>
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
                @endif
            @else
                <div class="text-muted">No hay contrato activo para esta SIM.</div>
            @endif
        </div>
    </div>

    {{-- Historial de contratos --}}
    <div class="card mt-3 shadow-sm">
        <div class="card-header py-2"><strong>Historial de contratos</strong></div>
        <div class="card-body small">
            @if ($historial->count())
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Activación</th>
                                <th>Próx. pago</th>
                                <th>Plazo</th>
                                <th>Total</th>
                                <th>Abonado</th>
                                <th>Saldo</th>
                                <th># Cuotas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historial as $det)
                                <tr>
                                    <td>{{ $det->DET_ID }}</td>
                                    <td>{{ optional($det->FECHA_ACTIVACION_RENOVACION)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ optional($det->FECHA_SIGUIENTE_PAGO)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $det->PLAZO_CONTRATADO ?? '—' }}</td>
                                    <td>${{ number_format((float) $det->VALOR_TOTAL, 2) }}</td>
                                    <td>${{ number_format((float) $det->VALOR_ABONADO, 2) }}</td>
                                    <td>${{ number_format((float) $det->SALDO, 2) }}</td>
                                    <td>{{ $det->NUMERO_CUOTAS ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">Sin historial.</div>
            @endif
        </div>
    </div>

    {{-- Documentos generados --}}
    <div class="card mt-3 shadow-sm">
        <div class="card-header py-2"><strong>Documentos generados</strong></div>
        <div class="card-body small">
            @if ($simcard->documentosGenerados->count())
                <ul class="mb-0">
                    @foreach ($simcard->documentosGenerados as $doc)
                        <li>
                            <b>{{ $doc->NOMBRE ?? 'Documento' }}</b>
                            <span
                                class="text-muted">({{ optional($doc->CREADO_EN)->format('Y-m-d H:i') ?? '—' }})</span>
                            @if (!empty($doc->URL))
                                — <a href="{{ $doc->URL }}" target="_blank" rel="noopener">Abrir</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted">No hay documentos.</div>
            @endif
        </div>
    </div>
</div>

<style>
    .sim-info .card {
        border-radius: .6rem;
    }

    .sim-info .card-header {
        background: #f8f9fa;
    }
</style>
