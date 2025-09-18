<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .create-bg {
            background: #1b2e47;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .create-card {
            background: #232946;
            color: #fff;
            max-width: 700px;
            width: 100%;
            margin: auto;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            overflow: hidden;
        }
        .create-header {
            background: #00c896;
            color: #fff;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .create-header h2 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .create-body {
            padding: 32px;
        }
        .create-footer {
            padding: 24px 32px;
            background: #232946;
            border-top: 1px solid #1b2e47;
        }
        .btn-create {
            background: #00c896;
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            padding: 14px 28px;
            font-size: 1.15rem;
            border: none;
            transition: background .2s;
            width: 100%;
            box-shadow: 0 2px 8px rgba(0,200,150,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-create:hover {
            background: #0d6efd;
        }
        .form-label {
            font-weight: 700;
            font-size: 1.08rem;
            color: #fff;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .form-control, .form-select, textarea {
            background: #2a3550 !important;
            color: #fff !important;
            border-radius: 8px !important;
            border: 1.5px solid #00c896 !important;
            margin-bottom: 18px;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(0,200,150,0.05);
        }
        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 2px #0d6efd33;
        }
        .input-group {
            margin-bottom: 18px;
        }
        .form-check-label {
            font-size: 1.05rem;
            font-weight: 600;
            color: #fff;
        }
        .form-check-input {
            accent-color: #00c896;
        }
        .d-flex.gap-3.mb-2 {
            margin-bottom: 18px !important;
        }
    </style>
    <div class="create-bg">
        <div class="create-card">
            <!-- Header -->
            <div class="create-header">
                <h2>
                    🏆 Crear torneo
                </h2>
                <button onclick="history.back()" class="btn btn-link text-white fs-4 p-0" style="color:#fff !important;">✕</button>
            </div>

            <!-- Body -->
            <div class="create-body">
                <form action="{{ route('admin.torneos.store') }}" method="POST" id="torneoForm" class="row g-4">
                    @csrf

                    <div class="col-md-6">
                        <label for="deporte" class="form-label">Deporte *</label>
                        <select id="deporte" name="deporte" class="form-select">
                            <option value="">Selecciona un deporte</option>
                            <option value="futboll" {{ old('deporte')=='futboll' ? 'selected' : '' }}>Fútbol</option>
                            <option value="baloncesto" {{ old('deporte')=='baloncesto' ? 'selected' : '' }}>Baloncesto</option>
                            <option value="voley" {{ old('deporte')=='voley' ? 'selected' : '' }}>Vóley</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="num_participantes" class="form-label">Nº de equipos que participan *</label>
                        <div class="input-group">
                            <button type="button" id="decBtn" class="btn btn-outline-secondary">−</button>
                            <input id="num_participantes" name="num_participantes" type="number" min="2"
                                value="{{ old('num_participantes', 8) }}"
                                class="form-control text-center fw-bold" style="max-width:70px;">
                            <button type="button" id="incBtn" class="btn btn-outline-secondary">+</button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label mb-2">Formato *</label>

                        <!-- Solo fase única -->
                        <input type="hidden" name="fase_tipo" value="unica">

                        <div id="unicaGroup">
                            <label class="form-label">Formato de fase</label>
                            <select name="formato_unica" id="formato_unica" class="form-select">
                                <option value="liga" {{ old('formato_unica')=='liga' ? 'selected' : '' }}>Liga</option>
                                <option value="cuadro_eliminatorio" {{ old('formato_unica')=='cuadro_eliminatorio' ? 'selected' : '' }}>Cuadro eliminatorio</option>
                            </select>
                        </div>

                        <!-- Eliminado: radios de Fase única / Multifase y campos ocultos de multifase -->
                    </div>

                    <div class="col-md-6">
                        <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                        <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_fin" class="form-label">Fecha fin</label>
                        <input id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" class="form-control">{{ old('descripcion') }}</textarea>
                    </div>
                </form>
            </div>

            <!-- Footer / CTA -->
            <div class="create-footer">
                <button type="submit" form="torneoForm" class="btn-create">
                    🏆 Crear torneo
                </button>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const incBtn = document.getElementById('incBtn');
            const decBtn = document.getElementById('decBtn');
            const numInput = document.getElementById('num_participantes');
            const formatoUnica = document.getElementById('formato_unica');
            const deporteSel = document.getElementById('deporte');

            // Relación deporte -> formatos permitidos
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
                const dep = (deporteSel.value || '').toLowerCase();
                const opts = formatosPorDeporte[dep] || formatosPorDeporte.futbol;
                const current = formatoUnica.value;
                formatoUnica.innerHTML = opts.map(o => `<option value="${o.value}">${o.label}</option>`).join('');
                if ([...formatoUnica.options].some(o => o.value === current)) {
                    formatoUnica.value = current;
                }
            }

            incBtn.addEventListener('click', () => {
                numInput.value = Math.max(2, parseInt(numInput.value || 0) + 1);
            });
            decBtn.addEventListener('click', () => {
                numInput.value = Math.max(2, parseInt(numInput.value || 0) - 1);
            });

            deporteSel.addEventListener('change', rebuildFormatoUnicaOptions);

            // Inicialización
            rebuildFormatoUnicaOptions();
        })();
    </script>
</x-app-layout>