<x-filament::page>
    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-warm-200 bg-white shadow-sm shadow-navy-900/5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-navy-900/10">
            <div class="border-b border-warm-200 bg-gradient-to-r from-navy-900 to-navy-700 px-6 py-5 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-gold-300">Detalles del evento</p>
                <h2 class="mt-1 font-serif text-2xl">{{ $this->record->topic }}</h2>
            </div>

            <dl class="grid gap-4 p-6 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl bg-warm-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Fecha</dt>
                    <dd class="mt-1 font-semibold text-navy-900">{{ \Carbon\Carbon::parse($this->record->date)->format('d/m/Y') }}</dd>
                </div>
                <div class="rounded-xl bg-warm-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Hora inicio</dt>
                    <dd class="mt-1 font-semibold text-navy-900">{{ \Carbon\Carbon::parse($this->record->start_time)->format('H:i') }}</dd>
                </div>
                <div class="rounded-xl bg-warm-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Hora final</dt>
                    <dd class="mt-1 font-semibold text-navy-900">{{ \Carbon\Carbon::parse($this->record->end_time)->format('H:i') }}</dd>
                </div>
                <div class="rounded-xl bg-warm-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Lugar</dt>
                    <dd class="mt-1 font-semibold text-navy-900">{{ $this->record->place }}</dd>
                </div>
                <div class="rounded-xl bg-warm-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Dirige</dt>
                    <dd class="mt-1 font-semibold text-navy-900">{{ $this->record->directedBy->name }} - {{ $this->record->directed_by_position }}</dd>
                </div>
                <div class="rounded-xl bg-red-50 p-4 ring-1 ring-red-100">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-red-700">Asistencias registradas</dt>
                    <dd class="mt-1 text-2xl font-bold text-red-700">{{ $this->record->attendances()->count() }}</dd>
                </div>
                <div class="rounded-xl bg-warm-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Vencimiento del enlace</dt>
                    <dd class="mt-1 font-semibold text-navy-900">
                        @if ($this->record->has_expiration && $this->record->expiration_date)
                            {{ $this->record->expiration_date->format('d/m/Y') }}
                        @else
                            Sin vencimiento
                        @endif
                    </dd>
                </div>
                <div class="rounded-xl bg-warm-50 p-4 sm:col-span-2 lg:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-warm-600">Motivo</dt>
                    <dd class="mt-1 font-semibold text-navy-900">{{ $this->record->reason }}</dd>
                </div>
            </dl>
        </div>

        <div class="overflow-hidden rounded-2xl border border-warm-200 bg-white shadow-sm shadow-navy-900/5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-navy-900/10">
            <div class="relative overflow-hidden bg-gradient-to-br from-warm-50 via-white to-gold-300/20 p-6 text-center sm:p-8">
                <div class="absolute left-1/2 top-8 h-52 w-52 -translate-x-1/2 rounded-full bg-red-500/10 blur-3xl"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-600">Acceso público</p>
                    <h2 class="mt-2 font-serif text-3xl text-navy-900">Código QR</h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-warm-700">Comparta este código para que los asistentes registren su asistencia desde cualquier dispositivo.</p>

                    <div class="mx-auto mt-7 inline-block rounded-[1.5rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-red-900/10 transition-transform duration-300 hover:scale-[1.03]">
                        {!! $this->getQrCodeSvg() !!}
                    </div>

                    <div class="mx-auto mt-6 max-w-xl">
                        <div class="flex items-center gap-2 rounded-2xl border border-warm-200 bg-white/90 px-4 py-3 text-sm shadow-sm transition-all duration-200 hover:border-gold-400/60 hover:shadow-md">
                            <svg class="h-4 w-4 shrink-0 text-warm-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <a href="{{ $this->getPublicUrl() }}" target="_blank" class="truncate font-semibold text-navy-700 underline decoration-gold-400/70 underline-offset-4 transition-colors hover:text-red-700">
                                {{ $this->getPublicUrl() }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
