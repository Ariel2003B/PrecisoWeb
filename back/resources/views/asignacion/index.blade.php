@extends('layout')

@section('Titulo', 'Asignación de Unidades a Accionistas')

@section('content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Asignación de Unidades a Accionistas</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Asignación de Unidades</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section py-5">
            <div class="container d-flex justify-content-center">
                <div class="card p-4 shadow-sm" style="width: 100%; max-width: 600px;">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (count($usuarios) === 0 || count($unidades) === 0)
                        <div class="alert alert-danger">❌ No hay usuarios o unidades disponibles.</div>
                    @else
                        <form action="{{ route('asignacion.asignar') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="usu_id" class="form-label">Selecciona Accionista <span class="required-asterisk">*</span></label>
                                <select id="usu_id" name="usu_id" class="form-select" required>
                                    <option value="">-- Selecciona un accionista --</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{ $usuario->USU_ID }}">
                                            {{ $usuario->NOMBRE }} {{ $usuario->APELLIDO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="unidad_id" class="form-label">Selecciona Unidad Disponible <span class="required-asterisk">*</span></label>
                                <select id="unidad_id" name="unidad_id" class="form-select" required>
                                    <option value="">-- Selecciona una unidad --</option>
                                    @foreach ($unidades as $unidad)
                                        <option value="{{ $unidad->id_unidad }}">
                                            {{ $unidad->placa . ' (' . $unidad->numero_habilitacion . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Asignar Unidad</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <style>
        .required-asterisk {
            color: red;
            margin-left: 3px;
        }
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-selection__rendered {
            line-height: 38px !important;
        }
        .select2-selection__arrow {
            height: 38px !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            $('#usu_id').select2({
                placeholder: 'Buscar accionista...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron accionistas.";
                    }
                }
            });
            $('#unidad_id').select2({
                placeholder: 'Buscar unidad disponible...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron unidades.";
                    }
                }
            });
        });
    </script>
@endsection
