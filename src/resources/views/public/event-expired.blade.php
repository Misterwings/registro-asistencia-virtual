<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace vencido — {{ $event->topic }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="ambient-shell min-h-screen font-sans antialiased">
    <a href="#contenido-principal" class="skip-link">Saltar al contenido principal</a>

    <main id="contenido-principal" tabindex="-1" class="relative z-10 min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl reveal" style="--reveal-delay: 80ms;">
            <div class="surface-card overflow-hidden rounded-[1.75rem]">
                <div class="relative overflow-hidden bg-navy-900 px-6 py-8 text-white sm:px-10 sm:py-10">
                    <div class="absolute -right-12 -top-16 h-40 w-40 rounded-full bg-red-500/20 blur-2xl" aria-hidden="true"></div>
                    <div class="relative">
                        <span class="text-xs font-bold uppercase tracking-[0.28em] text-gold-300">Registro público</span>
                        <h1 class="mt-2 font-serif text-3xl leading-tight sm:text-4xl">Enlace vencido</h1>
                    </div>
                </div>

                <div class="px-6 py-10 text-center sm:px-10">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-red-50 text-red-600" aria-hidden="true">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M10.29 3.86l-7.36 12.75A2 2 0 004.66 19.6h14.68a2 2 0 001.73-2.99L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <h2 class="mt-6 font-serif text-2xl text-navy-900">El plazo de registro terminó</h2>
                    <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-warm-700">
                        El enlace de asistencia para <strong>{{ $event->topic }}</strong> ya no está disponible.
                    </p>
                    @if ($event->expiration_date)
                        <p class="mt-5 rounded-xl border border-warm-200 bg-warm-50 px-4 py-3 text-sm text-warm-700">
                            Estuvo disponible hasta el {{ $event->expiration_date->format('d/m/Y') }}.
                        </p>
                    @endif
                </div>
            </div>

            <p class="mt-6 text-center text-xs font-medium uppercase tracking-[0.22em] text-warm-600">Sistema de Registro de Asistencia</p>
        </div>
    </main>
</body>

</html>
