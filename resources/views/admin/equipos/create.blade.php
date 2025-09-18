<x-app-layout>
    <style>
        body {
            background: #1b2e47 !important;
        }
        .equipo-bg {
            background: #1b2e47;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .equipo-card {
            background: #232946;
            color: #fff;
            max-width: 600px;
            width: 100%;
            margin: auto;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            padding: 36px 32px;
        }
        .equipo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .equipo-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            color: #00c896;
            margin-bottom: 0;
        }
        .equipo-header a {
            background: #00c896;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 18px;
            text-decoration: none;
            transition: background .2s;
        }
        .equipo-header a:hover {
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
        .jugadores-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .btn-remove {
            background: #ffe6e6;
            color: #c00;
            font-weight: 600;
            border-radius: 8px;
            padding: 7px 16px;
            border: none;
            transition: background .2s;
        }
        .btn-remove:hover {
            background: #ffb3b3;
        }
        .btn-add {
            background: #00c896;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 22px;
            border: none;
            margin-top: 8px;
            transition: background .2s;
        }
        .btn-add:hover {
            background: #0d6efd;
        }
        .btn-submit {
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
        .btn-submit:hover {
            background: #0d6efd;
        }
        .error-list {
            background: #ffe6e6;
            color: #c00;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 18px;
        }
    </style>
    <div class="equipo-bg">
        <div class="equipo-card">
            <div class="equipo-header">
                <h1>{{ isset($equipo) ? 'Editar equipo' : 'Crear equipo' }}</h1>
                <a href="{{ route('equipos.index') }}">Volver</a>
            </div>

            @if($errors->any())
                <div class="error-list">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($equipo) ? route('equipos.update', $equipo) : route('equipos.store') }}"
                  method="POST"
                  id="equipoForm"
                  class="space-y-6">
                @csrf
                @if(isset($equipo))
                    @method('PUT')
                @endif

                <div>
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input id="nombre" name="nombre" type="text"
                        value="{{ old('nombre', $equipo->nombre ?? '') }}"
                        class="form-control" required>
                </div>

                <div>
                    <label for="torneo_id" class="form-label">Torneo *</label>
                    <select id="torneo_id" name="torneo_id" class="form-select" required>
                        <option value="">Selecciona un torneo</option>
                        @php $validos = ['futboll','voley','baloncesto']; @endphp
                        @foreach($torneos as $torneo)
                            @continue(!in_array($torneo->deporte, $validos, true))
                            <option value="{{ $torneo->id }}"
                                    data-deporte="{{ $torneo->deporte }}"
                                {{ old('torneo_id', $equipo->torneo_id ?? '') == $torneo->id ? 'selected' : '' }}>
                                {{ $torneo->deporte ?? $torneo->nombre ?? 'Torneo #'.$torneo->id }}
                            </option>
                        @endforeach
                    </select>
                    <small id="jugadoresInfo" style="display:block;margin-top:6px;color:#00c896;font-weight:700;"></small>
                </div>

                <div>
                    <label class="form-label">Jugadores *</label>
                    <div id="jugadoresList">
                        @php
                            $oldJugadores = old('jugadores', $equipo->jugadores ?? []);
                            if (!is_array($oldJugadores)) $oldJugadores = json_decode($oldJugadores, true) ?? [$oldJugadores];
                        @endphp

                        @if(count($oldJugadores) > 0)
                            @foreach($oldJugadores as $j)
                                <div class="jugadores-row">
                                    <input type="text" name="jugadores[]" value="{{ $j }}" class="form-control flex-1" required>
                                    <button type="button" class="btn-remove">Eliminar</button>
                                </div>
                            @endforeach
                        @else
                            <div class="jugadores-row">
                                <input type="text" name="jugadores[]" placeholder="Nombre jugador" class="form-control flex-1" required>
                                <button type="button" class="btn-remove">Eliminar</button>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="addJugador" class="btn-add">Añadir jugador</button>
                </div>

                <div class="pt-4">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        {{ isset($equipo) ? 'Actualizar equipo' : 'Registrar equipo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function(){
            const recomendado = {
                futboll: 11,
                voley: 6,
                baloncesto: 5
            };

            const torneoSelect = document.getElementById('torneo_id');
            const info = document.getElementById('jugadoresInfo');
            const addBtn = document.getElementById('addJugador');
            const list = document.getElementById('jugadoresList');
            const submitBtn = document.getElementById('submitBtn');

            function makeRow(value = '') {
                const wrapper = document.createElement('div');
                wrapper.className = 'jugadores-row';
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'jugadores[]';
                input.placeholder = 'Nombre jugador';
                input.value = value;
                input.className = 'form-control flex-1';
                input.required = true;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn-remove';
                btn.textContent = 'Eliminar';
                btn.addEventListener('click', () => {
                    wrapper.remove();
                    updateInfo();
                });
                wrapper.appendChild(input);
                wrapper.appendChild(btn);
                return wrapper;
            }

            function deporteActual() {
                const opt = torneoSelect.options[torneoSelect.selectedIndex];
                return opt ? (opt.getAttribute('data-deporte') || '').trim() : '';
            }

            function actualizarEstadoBoton(ok) {
                submitBtn.disabled = !ok;
                submitBtn.style.opacity = ok ? '1' : '0.7';
                submitBtn.style.cursor = ok ? 'pointer' : 'not-allowed';
                submitBtn.title = ok ? '' : 'Debes tener al menos la cantidad recomendada de jugadores';
            }

            function updateInfo() {
                const dep = deporteActual();
                const count = list.querySelectorAll('.jugadores-row').length;

                if (!dep) {
                    info.textContent = 'Selecciona un torneo para ver la cantidad recomendada.';
                    actualizarEstadoBoton(true);
                    return;
                }

                const rec = recomendado[dep];
                if (!rec) {
                    info.textContent = `Deporte: ${dep}. (Sin regla de recomendación) Actual: ${count}`;
                    actualizarEstadoBoton(true);
                    return;
                }

                info.textContent = `Deporte: ${dep}. Mínimo recomendado: ${rec}. Actual: ${count}`;
                actualizarEstadoBoton(count >= rec);
            }

            addBtn.addEventListener('click', () => {
                list.appendChild(makeRow());
                updateInfo();
            });

            document.querySelectorAll('.btn-remove').forEach(b => {
                b.addEventListener('click', (e) => {
                    e.target.closest('.jugadores-row').remove();
                    updateInfo();
                });
            });

            torneoSelect.addEventListener('change', updateInfo);

            document.getElementById('equipoForm').addEventListener('submit', (e) => {
                const dep = deporteActual();
                const rec = recomendado[dep];
                const count = list.querySelectorAll('.jugadores-row').length;
                if (dep && rec && count < rec) {
                    e.preventDefault();
                    alert(`Debes registrar al menos ${rec} jugadores para ${dep}. Actualmente: ${count}.`);
                }
            });

            // Inicializar estado al cargar
            updateInfo();
        })();
    </script>
</x-app-layout>