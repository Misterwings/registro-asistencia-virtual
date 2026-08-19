<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="font-medium text-gray-500">Evento</span>
            <p class="text-gray-800 font-medium">{{ $record->event->topic }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Nombre</span>
            <p class="text-gray-800 font-medium">{{ $record->full_name }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Identificación</span>
            <p class="text-gray-800">{{ $record->id_number }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Cargo</span>
            <p class="text-gray-800">{{ $record->position_label }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Sede</span>
            <p class="text-gray-800">{{ $record->headquarter_label }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Registrado</span>
            <p class="text-gray-800">{{ $record->registered_at?->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <span class="font-medium text-gray-500 text-sm flex items-center gap-1.5 mb-3">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Firma
        </span>
        <div class="inline-block bg-warm-50 border border-warm-200 rounded-xl p-4 transition-shadow hover:shadow-sm">
            <img src="{{ $record->signature }}" alt="Firma de {{ $record->full_name }}" class="max-h-32 w-auto">
        </div>
    </div>
</div>
