<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia — {{ $event->topic }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ambient-shell min-h-screen font-sans antialiased">
    <a href="#contenido-principal" class="skip-link">Saltar al formulario de registro</a>

    <main id="contenido-principal" tabindex="-1" class="relative z-10 min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-5xl reveal" style="--reveal-delay: 80ms;">
            <div class="surface-card overflow-hidden rounded-[1.75rem]">

                <div class="relative overflow-hidden bg-navy-900 px-6 py-6 text-white sm:px-8">
                    <div class="absolute -right-12 -top-16 h-40 w-40 rounded-full bg-red-500/20 blur-2xl" aria-hidden="true"></div>
                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-[0.28em] text-gold-300">Registro público</span>
                            <h1 class="mt-2 font-serif text-3xl leading-tight sm:text-4xl">{{ $event->topic }}</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-white/70">Complete sus datos y registre su firma para confirmar la asistencia al evento.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-white/80 backdrop-blur">
                            {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                            <span class="mx-2 text-white/35">|</span>
                            {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}
                        </div>
                    </div>
                </div>

                {{-- Encabezado de formato institucional --}}
                <div class="institutional-sheet px-4 pt-4 sm:px-6">
                    <div class="w-full border border-black bg-white font-sans text-[9px] leading-tight"
                         style="display:grid; grid-template-columns: 35fr 201fr 45fr;">

                        {{-- Logo --}}
                        <div class="border-r border-black flex items-center justify-center p-1.5 min-h-[60px]">
                            <img src="{{ asset('images/logo_mw.png') }}" alt="Mister Wings"
                                 onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'font-bold text-xs\'>Mister Wings</span>';"
                                 class="max-h-12 w-auto">
                        </div>

                        {{-- Título central --}}
                        <div class="border-r border-black flex flex-col min-h-[60px]">
                            <div class="flex-1 flex items-center justify-center font-bold text-lg sm:text-xl tracking-wide"
                                 style="font-family:Impact,'Arial Black',sans-serif;">
                                FORMATO
                            </div>
                            <div class="border-t border-black flex-1 flex items-center justify-center font-bold text-lg sm:text-xl tracking-wide"
                                 style="font-family:Impact,'Arial Black',sans-serif;">
                                REGISTRO DE ASISTENCIA VIRTUAL
                            </div>
                        </div>

                        {{-- Metadatos (Código, Versión, Fecha) --}}
                        <div class="flex flex-col min-h-[60px]">
                            <div class="flex-1 px-1.5 py-0.5 leading-tight flex flex-col justify-center">
                                <div class="flex items-baseline gap-0.5">
                                    <span class="font-bold text-[8.5px]">Código:</span>
                                    <span class="text-[8.5px]">FO-SST-29</span>
                                </div>
                                <div class="flex items-baseline gap-0.5">
                                    <span class="font-bold text-[8.5px]">Versión:</span>
                                    <span class="text-[8.5px]">01</span>
                                </div>
                            </div>
                            <div class="border-t border-black flex-1 px-1.5 py-0.5 leading-tight flex flex-col justify-center">
                                <div class="font-bold text-[8.5px]">Fecha de edición:</div>
                                <div class="text-[8.5px]">22-Jun-2026</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Información del evento --}}
                @php
                    $reason = trim((string) ($event->reason ?? ''));
                
                    $normalizeReason = function ($value) {
                        return str_replace(
                            ['Á','É','Í','Ó','Ú','Ü','Ñ','á','é','í','ó','ú','ü','ñ'],
                            ['A','E','I','O','U','U','N','A','E','I','O','U','U','N'],
                            \Illuminate\Support\Str::upper(trim((string) $value))
                        );
                    };
                
                    /*
                     * Motivos parametrizados.
                     * OTRO solo aplica cuando $event->reason NO coincide exactamente
                     * con una de estas opciones.
                     */
                    $reasonOptions = [
                        'induccion' => 'INDUCCIÓN CORPORATIVA',
                        'reinduccion' => 'REINDUCCIÓN',
                        'capacitacion' => 'CAPACITACIÓN',
                        'divulgacion' => 'DIVULGACIÓN DE INFORMACIÓN',
                    ];
                
                    $reasonNorm = $normalizeReason($reason);
                    $selectedReasonKey = null;
                
                    foreach ($reasonOptions as $key => $label) {
                        if ($reasonNorm !== '' && $reasonNorm === $normalizeReason($label)) {
                            $selectedReasonKey = $key;
                            break;
                        }
                    }
                
                    $isOtherReason = $reasonNorm !== '' && $selectedReasonKey === null;
                @endphp
                <div class="institutional-sheet px-4 pb-4 sm:px-6">
                    <div class="w-full border-x border-b border-black bg-white font-sans text-black divide-y divide-black text-[9px]">

                        {{-- Fila 1: FECHA | TEMA | HORA INICIO | HORA FINAL --}}
                        <div class="flex flex-col sm:flex-row">
                            <div class="flex items-baseline px-2 py-1.5 sm:border-r sm:border-black sm:w-[14%]">
                                <span class="font-bold shrink-0">FECHA:</span>
                                <span class="ml-1 flex-1 min-w-[50px]">{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-baseline px-2 py-1.5 sm:border-r sm:border-black flex-1">
                                <span class="font-bold shrink-0">TEMA:</span>
                                <span class="ml-1 flex-1 min-w-[60px]">{{ $event->topic }}</span>
                            </div>
                            <div class="flex items-baseline px-2 py-1.5 sm:border-r sm:border-black sm:w-[18%]">
                                <span class="font-bold shrink-0">HORA INICIO:</span>
                                <span class="ml-1 flex-1 min-w-[40px]">{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}</span>
                            </div>
                            <div class="flex items-baseline px-2 py-1.5 sm:w-[18%]">
                                <span class="font-bold shrink-0">HORA FINAL:</span>
                                <span class="ml-1 flex-1 min-w-[40px]">{{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}</span>
                            </div>
                        </div>

                        {{-- Fila 2: DIRIGE | CARGO/ÁREA | LUGAR --}}
                        <div class="flex flex-col sm:flex-row">
                            <div class="flex items-baseline px-2 py-1.5 sm:border-r sm:border-black flex-1">
                                <span class="font-bold shrink-0">DIRIGE:</span>
                                <span class="ml-1 flex-1 min-w-[60px]">{{ $event->directedBy?->name ?? '' }}</span>
                            </div>
                            <div class="flex items-baseline px-2 py-1.5 sm:border-r sm:border-black flex-1">
                                <span class="font-bold shrink-0">CARGO/ÁREA:</span>
                                <span class="ml-1 flex-1 min-w-[50px]">{{ $event->directed_by_position ?? '' }}</span>
                            </div>
                            <div class="flex items-baseline px-2 py-1.5 flex-1">
                                <span class="font-bold shrink-0">LUGAR:</span>
                                <span class="ml-1 flex-1 min-w-[50px]">{{ $event->place ?? '' }}</span>
                            </div>
                        </div>

                        {{-- Fila 3: MOTIVO DE LA REUNIÓN + opciones --}}
                        <div class="flex flex-wrap items-center gap-x-1 gap-y-1 px-2 py-1.5">
                            <span class="font-bold mr-1">MOTIVO DE LA REUNIÓN:</span>
                        
                            @foreach ($reasonOptions as $key => $label)
                                @php
                                    $isSelected = $selectedReasonKey === $key;
                                @endphp
                        
                                <span class="{{ $isSelected
                                    ? 'inline-flex items-center rounded bg-red-100 px-1.5 py-0.5 font-black text-red-800 ring-1 ring-red-600'
                                    : 'font-bold ml-1'
                                }}">
                                    {{ $label }}
                                </span>
                        
                                <span class="{{ $isSelected
                                    ? 'inline-flex min-w-[18px] h-[18px] items-center justify-center rounded border border-red-700 bg-red-600 text-white text-[10px] font-black'
                                    : 'inline-flex min-w-[18px] h-[18px] items-center justify-center rounded border border-black/40 text-center font-bold'
                                }}">
                                    {{ $isSelected ? 'X' : '' }}
                                </span>
                            @endforeach
                        </div>

                        {{-- Fila 4: OTRO --}}
                        <div class="{{ $isOtherReason
                            ? 'flex items-center px-2 py-1.5 bg-red-50 ring-1 ring-inset ring-red-600'
                            : 'flex items-center px-2 py-1.5'
                        }}">
                            <span class="{{ $isOtherReason ? 'font-black text-red-800 shrink-0' : 'font-bold shrink-0' }}">
                                OTRO:
                            </span>
                        
                            <span class="{{ $isOtherReason ? 'ml-1 flex-1 min-w-[80px] font-black text-red-800' : 'ml-1 flex-1 min-w-[80px]' }}">
                                {{ $isOtherReason ? $reason : '' }}
                            </span>
                        
                            @if ($isOtherReason)
                                <span class="inline-flex min-w-[18px] h-[18px] items-center justify-center rounded border border-red-700 bg-red-600 text-white text-[10px] font-black">
                                    X
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($attachmentUrl && $isViewable)
                        <div class="mt-2 pt-2 border-t border-warm-200">
                            <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 text-sm font-medium text-navy-600 hover:text-navy-800 transition-colors group">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span
                                    class="underline underline-offset-2 decoration-warm-300 group-hover:decoration-navy-500 transition-all">Ver
                                    archivo adjunto</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Formulario --}}
                <div class="px-6 py-8 sm:px-10">
                    <div class="mb-7 flex flex-col gap-2 border-b border-warm-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Datos del asistente</span>
                            <h2 class="mt-1 font-serif text-2xl text-navy-900">Confirme su asistencia</h2>
                        </div>
                        <p class="max-w-sm text-sm text-warm-600">Seleccione sus nombres, apellidos, cargo y sede para completar el registro.</p>
                    </div>
                    @if (session('success'))
                        <div
                            class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200/70 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-sm shadow-emerald-900/5 animate-[fadeIn_0.4s_ease-out]" role="status" aria-live="polite">
                            <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="mb-6 flex items-start gap-3 rounded-xl border border-red-200/70 bg-red-50 px-5 py-4 text-sm text-red-800 shadow-sm shadow-red-900/5 animate-[fadeIn_0.4s_ease-out]" role="alert" aria-live="assertive" tabindex="-1" data-focus-on-load="true">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="mb-2 font-bold">Revise los campos marcados:</p>
                                <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('event.register', $event->slug) }}" id="attendanceForm"
                        class="space-y-6">
                        @csrf

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="first_names" class="block text-sm font-medium text-navy-800 mb-1.5">Nombres</label>
                                <input type="text" name="first_names" id="first_names" value="{{ old('first_names') }}"
                                    required autocomplete="given-name" aria-invalid="{{ $errors->has('first_names') ? 'true' : 'false' }}" @error('first_names') aria-describedby="first_names-error" @enderror
                                    class="form-control">
                                @error('first_names')
                                    <p id="first_names-error" class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_names" class="block text-sm font-medium text-navy-800 mb-1.5">Apellidos</label>
                                <input type="text" name="last_names" id="last_names" value="{{ old('last_names') }}"
                                    required autocomplete="family-name" aria-invalid="{{ $errors->has('last_names') ? 'true' : 'false' }}" @error('last_names') aria-describedby="last_names-error" @enderror
                                    class="form-control">
                                @error('last_names')
                                    <p id="last_names-error" class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="id_number" class="block text-sm font-medium text-navy-800 mb-1.5">Número de Identificación</label>
                                <input type="text" name="id_number" id="id_number" value="{{ old('id_number') }}"
                                    required inputmode="numeric" pattern="[0-9.\s-]+" maxlength="50" autocomplete="off"
                                    title="Ingrese únicamente números. Puede usar puntos, guiones o espacios."
                                    aria-invalid="{{ $errors->has('id_number') ? 'true' : 'false' }}" @error('id_number') aria-describedby="id_number-error" @enderror
                                    class="form-control">
                                @error('id_number')
                                    <p id="id_number-error" class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="position_id" class="block text-sm font-medium text-navy-800 mb-1.5">Cargo</label>
                                <select name="position_id" id="position_id" required
                                    aria-invalid="{{ $errors->has('position_id') ? 'true' : 'false' }}" @error('position_id') aria-describedby="position_id-error" @enderror
                                    class="form-control">
                                    <option value="">Seleccione un cargo</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                            {{ $position->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('position_id')
                                    <p id="position_id-error" class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="headquarter_id" class="block text-sm font-medium text-navy-800 mb-1.5">Sede</label>
                                <select name="headquarter_id" id="headquarter_id" required
                                    aria-invalid="{{ $errors->has('headquarter_id') ? 'true' : 'false' }}" @error('headquarter_id') aria-describedby="headquarter_id-error" @enderror
                                    class="form-control">
                                    <option value="">Seleccione una sede</option>
                                    @foreach ($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}" {{ old('headquarter_id') == $headquarter->id ? 'selected' : '' }}>
                                            {{ $headquarter->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('headquarter_id')
                                    <p id="headquarter_id-error" class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Firma --}}
                        <div>
                            <label id="signature-label" class="block text-sm font-medium text-navy-800 mb-1.5">Firma</label>
                            <p id="signature-help" class="mb-2 text-sm text-warm-700">Dibuje su firma en el recuadro o use el botón para generarla con su nombre.</p>
                            <div class="signature-frame @error('signature') is-invalid @enderror" role="group" aria-labelledby="signature-label" aria-describedby="signature-help signatureStatus @error('signature') signature-error @enderror">
                                <canvas id="signatureCanvas" class="w-full h-40 cursor-crosshair touch-none" aria-label="Lienzo para dibujar la firma" aria-describedby="signature-help"></canvas>
                                <div id="signaturePlaceholder"
                                    class="absolute inset-0 flex items-center justify-center pointer-events-none select-none transition-opacity duration-300">
                                    <span class="text-warm-400 text-sm">Dibuja tu firma aquí</span>
                                </div>
                            </div>
                            <input type="hidden" name="signature" id="signatureInput" aria-invalid="{{ $errors->has('signature') ? 'true' : 'false' }}">
                            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="useTypedSignature" class="secondary-action">
                                        Usar nombre como firma
                                    </button>
                                    <button type="button" id="clearSignature"
                                        class="secondary-action text-warm-700 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Limpiar firma
                                    </button>
                                </div>
                                <span id="signatureStatus" class="text-xs font-medium text-warm-600" role="status" aria-live="polite"></span>
                            </div>
                            @error('signature')
                                <p id="signature-error" class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" id="submitBtn"
                            class="primary-cta w-full px-6 py-3.5 text-sm disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100">
                            Registrar Asistencia
                        </button>
                    </form>
                </div>
                
            </div>
            <p class="mt-4 text-center text-[10px] font-medium uppercase tracking-[0.18em] text-warm-500 leading-tight"><strong>Aviso de privacidad:</strong> La información registrada en este formulario será utilizada exclusivamente para fines laborales y administrativos. La empresa garantiza el tratamiento confidencial de los datos personales conforme a la Ley 1581 de 2012, el Decreto 1074 de 2015 y su Política de Tratamiento de Datos Personales.</p>
            <p class="mt-6 text-center text-xs font-medium uppercase tracking-[0.22em] text-warm-600">Sistema de Registro de Asistencia</p>
        </div>
    </main>

</body>

</html>
