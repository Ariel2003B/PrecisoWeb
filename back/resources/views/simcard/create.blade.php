@extends('layout')

@section('Titulo', 'Agregar SIM Card')

@section('content')
    <section class="container mt-5">
        <h1 class="text-center mb-4">Agregar SIM Card</h1>
        <form action="{{ route('simcards.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="PROPIETARIO" class="form-label">PROPIETARIO</label>
                <select name="PROPIETARIO" id="PROPIETARIO" class="form-control">
                    <option value="PRECISOGPS S.A.S.">PRECISOGPS S.A.S.</option>
                    <option value="VARGAS REINOSO CESAR GIOVANNY">VARGAS REINOSO CESAR GIOVANNY</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="CUENTA" class="form-label">Cuenta</label>
                <input type="text" name="CUENTA" id="CUENTA" class="form-control" maxlength="10"
                    value="{{ old('CUENTA') }}" placeholder="Ingrese la cuenta de la SIM" required>
            </div>
            <div class="mb-3">
                <label for="NUMEROTELEFONO" class="form-label">Número de Teléfono</label>
                <input type="text" name="NUMEROTELEFONO" id="NUMEROTELEFONO" class="form-control" maxlength="10"
                    value="{{ old('NUMEROTELEFONO') }}" placeholder="Ingrese el número de teléfono" required>
            </div>
            <div class="mb-3">
                <label for="PLAN" class="form-label">Plan</label>
                <input type="text" name="PLAN" id="PLAN" class="form-control" maxlength="255"
                    value="{{ old('PLAN') }}" placeholder="Ingrese el plan">
            </div>
            <div class="mb-3">
                <label for="TIPOPLAN" class="form-label">Código de Plan</label>
                <input type="text" name="TIPOPLAN" id="TIPOPLAN" class="form-control" maxlength="255"
                    value="{{ old('TIPOPLAN') }}" placeholder="Ingrese el tipo de plan" required>
            </div>
            <div class="mb-3">
                <label for="ICC" class="form-label">ICC</label>
                <input type="text" name="ICC" id="ICC" class="form-control" maxlength="255"
                    value="{{ old('ICC') }}" placeholder="Ingrese el ICC">
            </div>
            <div class="mb-3">
                <label for="GRUPO" class="form-label">Grupo</label>
                <input type="text" name="GRUPO" id="GRUPO" class="form-control" maxlength="255"
                    value="{{ old('GRUPO') }}" placeholder="Ingrese el grupo Ej: COMERCIALES, SIRENA, PRECISO GPS, etc.">
            </div>
            <div class="mb-3">
                <label for="ASIGNACION" class="form-label">Asignación</label>
                <input type="text" name="ASIGNACION" id="ASIGNACION" class="form-control" maxlength="25"
                    value="{{ old('ASIGNACION') }}" placeholder="Ingrese la asignación Ej: ABC1234 (01/2345), JUAN PEREZ, etc.">
                <button type="button" class="btn btn-info mt-2" id="fetchWialonData">Traer datos de Wialon</button>
            </div>
            <div class="mb-3">
                <label for="EQUIPO" class="form-label">Equipo</label>
                <select name="EQUIPO" id="EQUIPO" class="form-select">
                    <option value="">Sin asignar</option>
                    <option value="GPS">GPS</option>
                    <option value="MODEM">MODEM</option>
                    <option value="MOVIL">MOVIL</option>
                    <option value="COMPUTADOR ABORDO">COMPUTADOR ABORDO</option>
                    <option value="LECTOR DE QR">LECTOR DE QR</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="IMEI" class="form-label">IMEI</label>
                <input type="text" name="IMEI" id="IMEI" class="form-control" value="{{ old('IMEI') }}"
                    placeholder="Ingrese el IMEI del equipo">
            </div>
            <div class="mb-3">
                <label for="ESTADO" class="form-label">Estado</label>
                <select name="ESTADO" id="ESTADO" class="form-select" required>
                    <option value="ACTIVA">Activo</option>
                    <option value="ELIMINADA">Eliminada</option>
                    <option value="LIBRE">Libre</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('simcards.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
        <br><br>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <script>
        document.getElementById('fetchWialonData').addEventListener('click', function() {
            const asignacion = document.getElementById('ASIGNACION').value;

            if (!asignacion) {
                alert('Por favor, ingrese una asignación.');
                return;
            }

            fetch('{{ route('simcards.fetchWialonData') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        asignacion: asignacion
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById('ICC').value = data.icc || '';
                        document.getElementById('IMEI').value = data.imei || '';
                        document.getElementById('NUMEROTELEFONO').value = data.telefono || '';
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
@endsection

@section('jsCode', 'js/scriptNavBar.js')
