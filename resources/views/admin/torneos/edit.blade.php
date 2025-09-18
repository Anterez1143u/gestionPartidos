<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .edit-bg { background: #1b2e47; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .edit-card { background: #232946; color: #fff; max-width: 700px; width: 100%; margin: auto; border-radius: 18px; box-shadow: 0 8px 32px rgba(13,110,253,0.10); overflow: hidden; }
        .edit-header { background: #00c896; color: #fff; padding: 24px 32px; display: flex; align-items: center; justify-content: space-between; }
        .edit-header h2 { font-size: 1.7rem; font-weight: 700; margin-bottom: 0; display: flex; align-items: center; gap: 10px; }
        .edit-body { padding: 32px; }
        .edit-footer { padding: 24px 32px; background: #232946; border-top: 1px solid #1b2e47; }
        .btn-save { background: #00c896; color: #fff; font-weight: 700; border-radius: 8px; padding: 14px 28px; font-size: 1.15rem; border: none; transition: background .2s; width: 100%; box-shadow: 0 2px 8px rgba(0,200,150,0.08); display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-save:hover { background: #0d6efd; }
        .form-label { font-weight: 700; font-size: 1.08rem; color: #fff; margin-bottom: 6px; letter-spacing: 0.5px; }
        .form-control, .form-select, textarea { background: #2a3550 !important; color: #fff !important; border-radius: 8px !important; border: 1.5px solid #00c896 !important; margin-bottom: 6px; font-size: 1.05rem; box-shadow: 0 2px 8px rgba(0,200,150,0.05); }
        .form-control:focus, .form-select:focus, textarea:focus { border-color: #0d6efd !important; box-shadow: 0 0 0 2px #0d6efd33; }
        .input-group { margin-bottom: 6px; }
        .form-check-label { font-size: 1.05rem; font-weight: 600; color: #fff; }
        .form-check-input { accent-color: #00c896; }

        /* Estilos de error */
        .is-invalid { border-color: #ff6b6b !important; box-shadow: 0 0 0 2px #ff6b6b33 !important; }
        .field-error { color: #ffb3b3; font-size: .92rem; margin: 6px 0 12px; }
        .alert { border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; border: 1px solid transparent; }
        .alert-error { background: #3a1f28; border-color: #b71c1c; color: #ffd7d7; }
        .alert-success { background: #133a2f; border-color: #00c896; color: #dffaf1; }
    </style>
    <div class="edit-bg">
        <div class="edit-card">
            <!-- Header -->
            <div class="edit-header">
                <h2>✏️ Editar torneo</h2>
                <button onclick="history.back()" class="btn btn-link text-white fs-4 p-0" style="color:#fff !important;">✕</button>
            </div>

            <!-- Body -->
            <div class="edit-body">
                <!-- Alertas -->
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error">
                        <strong>Revisa los campos marcados:</strong>
                        <ul style="margin:8px 0 0 18px; padding:0;">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('torneos.update', $torneo->id) }}" method="POST" id="torneoForm" class="row g-4">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label for="deporte" class="form-label">Deporte *</label>
                        @php $dep = old('deporte', $torneo->deporte ?? 'futboll'); @endphp
                        <select id="deporte" name="deporte" class="form-select">
                            <option value="futboll" {{ $dep==='futboll' ? 'selected' : '' }}>Fútbol</option>
                            <option value="baloncesto" {{ $dep==='baloncesto' ? 'selected' : '' }}>Baloncesto</option>
                            <option value="voley" {{ $dep==='voley' ? 'selected' : '' }}>Vóley</option>
                        </select>
                        @error('deporte') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="num_participantes" class="form-label">Nº de participantes *</label>
                        <div class="input-group">
                            <button type="button" id="decBtn" class="btn btn-outline-secondary">−</button>
                            <input id="num_participantes" name="num_participantes" type="number" min="2"
                                value="{{ old('num_participantes', $torneo->numero_participantes) }}"
                                class="form-control text-center fw-bold @error('num_participantes') is-invalid @enderror" style="max-width:90px;">
                            <button type="button" id="incBtn" class="btn btn-outline-secondary">+</button>
                        </div>
                        @error('num_participantes') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <!-- Formato (solo Fase única) -->
                    <div class="col-12">
                        <label class="form-label mb-2">Formato *</label>

                        <!-- Siempre única -->
                        <input type="hidden" name="fase_tipo" value="unica">

                        <div id="unicaGroup">
                            <label class="form-label">Formato de fase</label>
                            <select name="formato_unica" id="formato_unica" class="form-select">
                                @php
                                    $currentFormato = old('formato_unica', $torneo->formato_unica ?? $torneo->formato_fase_unica ?? 'liga');
                                @endphp
                                <option value="liga" {{ $currentFormato==='liga' ? 'selected' : '' }}>Liga</option>
                                <option value="cuadro_eliminatorio" {{ $currentFormato==='cuadro_eliminatorio' ? 'selected' : '' }}>Cuadro eliminatorio</option>
                            </select>
                        </div>

                        <!-- Eliminado: radios/bloques de Multifase y selects de 1ª/2ª fase -->
                    </div>

                    <div class="col-md-6">
                        <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                        <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio', $torneo->fecha_inicio) }}" class="form-control @error('fecha_inicio') is-invalid @enderror">
                        @error('fecha_inicio') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_fin" class="form-label">Fecha fin</label>
                        <input id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin', $torneo->fecha_fin) }}" class="form-control @error('fecha_fin') is-invalid @enderror">
                        @error('fecha_fin') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $torneo->descripcion) }}</textarea>
                        @error('descripcion') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </form>
            </div>

            <!-- Footer / CTA -->
            <div class="edit-footer">
                <button type="submit" form="torneoForm" class="btn-save">✏️ Actualizar torneo</button>
            </div>
        </div>
    </div>

    @include('admin.torneos.scripts-form')

    <script>
    (function(){
        const formatoUnica = document.getElementById('formato_unica');
        const deporteSel = document.getElementById('deporte'); // debe existir en el formulario de edición

        // Formatos permitidos por deporte (mismos para los 3)
        const formatosPorDeporte = {
            futbol: [
                { value: 'liga', label: 'Liga' },
                { value: 'cuadro_eliminatorio', label: 'Cuadro eliminatorio' },
            ],
            baloncesto: [
                { value: 'liga', label: 'Liga' },
                { value: 'cuadro_eliminatorio', label: 'Cuadro eliminatorio' },
            ],
            voley: [
                { value: 'liga', label: 'Liga' },
                { value: 'cuadro_eliminatorio', label: 'Cuadro eliminatorio' },
            ],
        };

        function rebuildFormatoUnicaOptions() {
            if(!formatoUnica || !deporteSel) return;
            const dep = (deporteSel.value || '').toLowerCase();
            const opts = formatosPorDeporte[dep] || formatosPorDeporte.futbol;
            const current = formatoUnica.value;
            formatoUnica.innerHTML = opts.map(o => `<option value="${o.value}">${o.label}</option>`).join('');
            // Mantener selección si sigue siendo válida
            const ok = Array.from(formatoUnica.options).some(o => o.value === current);
            formatoUnica.value = ok ? current : (opts[0]?.value || 'liga');
        }

        if (deporteSel) {
            deporteSel.addEventListener('change', rebuildFormatoUnicaOptions);
        }
        rebuildFormatoUnicaOptions();
    })();
    </script>
</x-app-layout>
