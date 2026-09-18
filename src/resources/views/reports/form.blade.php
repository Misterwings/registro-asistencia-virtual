<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de Asistencias - {{ config('app.name') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="ambient-shell min-h-screen font-sans antialiased">
    <a href="#contenido-principal" class="skip-link">Saltar al formulario</a>

    @php
        $selectedHeadquarterIds = collect(request('headquarter_ids', request('headquarter_id') ? [request('headquarter_id')] : []))
            ->map(fn ($id) => (string) $id)
            ->all();
    @endphp

    <main id="contenido-principal" tabindex="-1" class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="surface-card reveal grid w-full max-w-4xl overflow-hidden rounded-[1.75rem] lg:grid-cols-[0.9fr_1.1fr]" style="--reveal-delay: 80ms;">
            <aside class="relative overflow-hidden bg-navy-900 p-8 text-white sm:p-10" aria-labelledby="report-title">
                <div class="absolute -left-16 -top-20 h-52 w-52 rounded-full bg-red-500/20 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-24 right-0 h-64 w-64 rounded-full bg-gold-300/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative flex h-full flex-col justify-between gap-10">
                    <div>
                        <span class="inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.22em] text-gold-300">Reportes</span>
                        <h1 id="report-title" class="mt-5 font-serif text-4xl leading-tight">Descarga de asistencias</h1>
                        <p class="mt-4 text-sm leading-6 text-white/70">Filtre por evento y seleccione una o varias sedes. Si no marca ninguna, se exportarán todas las asistencias del evento seleccionado.</p>
                    </div>

                    <div class="grid gap-3 text-sm">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <span class="block text-white/50">Formatos disponibles</span>
                            <strong class="mt-1 block text-lg">Hoja de cálculo y PDF</strong>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <span class="block text-white/50">Filtro opcional</span>
                            <strong class="mt-1 block text-lg">Una o varias sedes</strong>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="bg-white/90 p-6 sm:p-8 lg:p-10">
                <div class="mb-7">
                    <span class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Configurar exporte</span>
                    <h2 class="mt-2 font-serif text-3xl text-navy-900">Seleccione los datos</h2>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm shadow-red-900/5" role="alert" aria-live="assertive" tabindex="-1" data-focus-on-load="true">
                        <p class="mb-2 font-bold">Revise los campos marcados:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('attendances.export') }}" method="GET" class="space-y-6">
                    <div>
                        <label for="event_id" class="mb-2 block text-sm font-semibold text-navy-800">Evento</label>
                        <select name="event_id" id="event_id" required class="form-control" aria-invalid="{{ $errors->has('event_id') ? 'true' : 'false' }}" @error('event_id') aria-describedby="event_id-error" @enderror>
                            <option value="">Seleccionar evento</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->topic }} ({{ $event->date->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('event_id')
                            <p id="event_id-error" class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <fieldset data-headquarter-selector @error('headquarter_ids') aria-describedby="headquarter_ids-error" @enderror>
                        <legend class="mb-2 block text-sm font-semibold text-navy-800">Sedes</legend>
                        <p class="mb-3 text-xs leading-5 text-warm-600">Puede seleccionar varias sedes. Si no marca ninguna, se incluirán todas.</p>

                        <div class="mb-3 flex flex-wrap gap-2">
                            <button type="button" data-select-all-headquarters class="secondary-action px-3 py-2 text-xs">
                                Seleccionar todas
                            </button>
                            <button type="button" data-clear-headquarters class="secondary-action px-3 py-2 text-xs">
                                Limpiar selección
                            </button>
                        </div>

                        <div id="headquarter-options" class="grid max-h-64 gap-2 overflow-y-auto rounded-xl border border-warm-200 bg-warm-50/60 p-3 sm:grid-cols-2" role="group" aria-label="Sedes disponibles">
                            @foreach ($headquarters as $headquarter)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent bg-white px-3 py-2 text-sm text-navy-800 transition hover:border-gold-300 hover:bg-gold-50">
                                    <input type="checkbox" name="headquarter_ids[]" value="{{ $headquarter->id }}"
                                        {{ in_array((string) $headquarter->id, $selectedHeadquarterIds, true) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-warm-300 text-red-600 focus:ring-red-500">
                                    <span>{{ $headquarter->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p id="headquarter-selection-status" class="mt-2 text-xs font-medium text-warm-600" role="status" aria-live="polite"></p>
                        @error('headquarter_ids')
                            <p id="headquarter_ids-error" class="field-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset @error('format') aria-describedby="format-error" @enderror>
                        <legend class="mb-3 block text-sm font-semibold text-navy-800">Formato</legend>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="report-choice cursor-pointer p-4">
                                <input type="radio" name="format" value="xlsx" {{ request('format', 'xlsx') === 'xlsx' ? 'checked' : '' }} class="sr-only">
                                <div class="flex flex-col items-center text-center">
                                    <svg class="mb-2 h-8 w-8 text-green-600" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="text-sm font-bold text-navy-900">Hoja de cálculo</span>
                                    <span class="mt-0.5 text-xs text-warm-600">Archivo .xlsx</span>
                                </div>
                            </label>
                            <label class="report-choice cursor-pointer p-4">
                                <input type="radio" name="format" value="pdf" {{ request('format') === 'pdf' ? 'checked' : '' }} class="sr-only">
                                <div class="flex flex-col items-center text-center">
                                    <svg class="mb-2 h-8 w-8 text-red-600" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="text-sm font-bold text-navy-900">Documento PDF</span>
                                    <span class="mt-0.5 text-xs text-warm-600">Archivo .pdf</span>
                                </div>
                            </label>
                        </div>
                        @error('format')
                            <p id="format-error" class="field-error">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <button type="submit" class="primary-cta w-full px-6 py-4 text-sm">
                        Descargar reporte
                        <svg class="relative h-4 w-4" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ url('/admin/attendances') }}" class="text-sm font-semibold text-warm-600 transition-colors hover:text-navy-700">
                        &larr; Volver a Asistencias
                    </a>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
