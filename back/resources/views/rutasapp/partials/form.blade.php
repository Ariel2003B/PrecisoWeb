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
</div>
