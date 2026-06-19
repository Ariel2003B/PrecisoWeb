<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Nombre <span class="required-asterisk">*</span></label>
        <input type="text" name="descripcion" value="{{ old('descripcion', $ruta->descripcion ?? '') }}" class="form-control" required>
    </div>

    <div class="col-md-12">
        <label class="form-label">Empresa</label>
        <select name="EMP_ID" id="EMP_ID" class="form-select">
            <option value="">-- Ninguna --</option>
            @foreach ($empresas as $empresa)
                <option value="{{ $empresa->EMP_ID }}"
                    {{ old('EMP_ID', $ruta->EMP_ID ?? '') == $empresa->EMP_ID ? 'selected' : '' }}>
                    {{ $empresa->NOMBRE }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-12">
        <label class="form-label">Valor por pasajero ($)</label>
        <input type="number" name="valor_pasajero" step="0.0001" min="0"
               value="{{ old('valor_pasajero', $ruta->valor_pasajero ?? '0.00') }}"
               class="form-control"
               placeholder="Ej: 0.35">
        <small class="text-muted">Monto que se cobra por cada pasajero que sube en esta ruta.</small>
    </div>
</div>
