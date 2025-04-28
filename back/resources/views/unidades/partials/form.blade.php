<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Número de Habilitación <span class="required-asterisk">*</span></label>
        <input type="text" name="numero_habilitacion"
            value="{{ old('numero_habilitacion', $unidad->numero_habilitacion ?? '') }}" 
            class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Placa <span class="required-asterisk">*</span></label>
        <input type="text" name="placa" 
            value="{{ old('placa', $unidad->placa ?? '') }}" 
            class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Año de Fabricación</label>
        <input type="number" name="anio_fabricacion"
            value="{{ old('anio_fabricacion', $unidad->anio_fabricacion ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Chasis</label>
        <input type="text" name="chasis" 
            value="{{ old('chasis', $unidad->chasis ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Carrocería</label>
        <input type="text" name="carroceria" 
            value="{{ old('carroceria', $unidad->carroceria ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Tipo Especial</label>
        <input type="text" name="tipo_especial" 
            value="{{ old('tipo_especial', $unidad->tipo_especial ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Capacidad de Pasajeros</label>
        <input type="number" name="capacidad_pasajeros"
            value="{{ old('capacidad_pasajeros', $unidad->capacidad_pasajeros ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Puertas de Ingreso</label>
        <input type="number" name="puertas_ingreso"
            value="{{ old('puertas_ingreso', $unidad->puertas_ingreso ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Puertas Izquierdas</label>
        <input type="number" name="puertas_izquierdas"
            value="{{ old('puertas_izquierdas', $unidad->puertas_izquierdas ?? '') }}" 
            class="form-control">
    </div>

    <div class="col-md-12">
        <label class="form-label">Propietario</label>
        <select name="usu_id" id="usu_id" class="form-select">
            <option value="">-- Ninguno --</option>
            @foreach ($usuarios as $usuario)
                <option value="{{ $usuario->USU_ID }}"
                    {{ old('usu_id', $unidad->usu_id ?? '') == $usuario->USU_ID ? 'selected' : '' }}>
                    {{ $usuario->NOMBRE }} {{ $usuario->APELLIDO }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#usu_id').select2({
            placeholder: 'Selecciona un propietario',
            allowClear: true,
            width: '100%'
        });
    });
</script>
<style>
    .required-asterisk {
        color: red;
        margin-left: 4px;
        font-weight: bold;
    }
</style>
