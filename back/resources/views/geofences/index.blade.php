@extends('layout')

@section('Titulo', 'PrecisoGPS - Geocercas')

@section('content')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Crear Geocercas en Wialon</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.plataformas') }}">Plataformas</a></li>
                        <li class="current">Crear Geocercas en Wialon</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section" id="plataformas">
            <div class="container">
                <div class="card shadow-lg p-4 bg-light">
                    <form action="{{ route('geocercas.crear') }}" method="POST" class="row g-3">
                        @csrf

                        <!-- TOKEN -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Token Nimbus:</label>
                            <div class="input-group">
                                <input type="text" name="token_nimbus" id="token_nimbus" class="form-control"
                                    placeholder="Ingrese el Token Nimbus" required>
                                <button type="button" class="btn btn-primary" id="btnCargarDepots">
                                    <i class="bi bi-arrow-clockwise"></i> Cargar Depots
                                </button>
                            </div>
                        </div>

                        <!-- DEPOT -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Depot ID (Nimbus):</label>
                            <select name="depot_id" id="depot_id" class="form-select" required disabled>
                                <option value="">Seleccione un depot...</option>
                            </select>
                        </div>

                        <!-- RECURSOS -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Recurso (Item ID de Wialon):</label>
                            <select name="item_id" id="item_id" class="form-select" required>
                                <option value="">Seleccione un recurso...</option>
                                @foreach ($recursos as $recurso)
                                    <option value="{{ $recurso['id'] }}">{{ $recurso['nm'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- GRUPOS DINÁMICOS -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Grupos de Geocercas:</label>
                            <div id="grupos-container">
                                <div class="grupo row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" name="grupos[0][nombre]" class="form-control"
                                            placeholder="Nombre del Grupo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="grupos[0][identificador]" class="form-select" required>
                                            <option value="">Seleccione el Identificador</option>
                                            <option value="number">Números secuenciales</option>
                                            <option value="letter">Letras de la "a" a la "z" minúscula</option>
                                            <option value="plain">Solo nombre</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-success mt-2" onclick="agregarGrupo()">
                                <i class="bi bi-plus-circle"></i> Agregar Otro Grupo
                            </button>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-send"></i> Enviar y Crear Geocercas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- SPINNER -->
    <div id="loading-spinner"
        class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex justify-content-center align-items-center text-white"
        style="z-index: 1050; opacity: 0; pointer-events: none;">
        <div class="text-center">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 fw-bold">Cargando depots, por favor espere...</p>
        </div>
    </div>

    <!-- JQuery y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // INICIALIZAR SELECT2
            $('#item_id').select2({
                placeholder: "Seleccione un recurso...",
                width: '100%',
                theme: 'bootstrap-5'
            });

            let contador = 1;

            window.agregarGrupo = function() {
                const container = document.getElementById('grupos-container');
                const div = document.createElement('div');
                div.classList.add('grupo', 'row', 'mb-2');
                div.innerHTML = `
                    <div class="col-md-6">
                        <input type="text" name="grupos[${contador}][nombre]" class="form-control" placeholder="Nombre del Grupo" required>
                    </div>
                    <div class="col-md-6">
                        <select name="grupos[${contador}][identificador]" class="form-select" required>
                            <option value="">Seleccione el Identificador</option>
                            <option value="number">Números secuenciales</option>
                            <option value="letter">Letras de la "a" a la "z" minúscula</option>
                            <option value="plain">Solo nombre</option>
                        </select>
                    </div>
                `;
                container.appendChild(div);
                contador++;
            }

            $('#btnCargarDepots').on('click', function() {
                const token = $('#token_nimbus').val();

                if (!token) {
                    alert('Ingrese el token Nimbus');
                    return;
                }

                $('#loading-spinner').css({
                    opacity: '1',
                    'pointer-events': 'auto'
                });

                $.ajax({
                    url: "{{ route('geocercas.obtenerDepots') }}",
                    type: 'POST',
                    data: {
                        token_nimbus: token,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        const selectDepot = $('#depot_id');
                        selectDepot.empty().append(
                            '<option value="">Seleccione un depot...</option>'
                        );

                        data.forEach(function(depot) {
                            selectDepot.append(
                                `<option value="${depot.id}">${depot.n}</option>`
                            );
                        });

                        selectDepot.prop('disabled', false);
                    },
                    error: function() {
                        alert('Error al cargar los depots. Verifique el token.');
                    },
                    complete: function() {
                        $('#loading-spinner').css({
                            opacity: '0',
                            'pointer-events': 'none'
                        });
                    }
                });
            });
        });
    </script>
@endsection
