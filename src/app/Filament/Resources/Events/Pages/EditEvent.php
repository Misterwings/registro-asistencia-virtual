<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->modalHeading('Eliminar evento')
                ->modalSubmitActionLabel('Eliminar')
                ->successNotificationTitle('Evento eliminado'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['directed_by_id'] = auth()->id();
        $data['has_expiration'] = (bool) ($data['has_expiration'] ?? false);

        if (! $data['has_expiration']) {
            $data['expiration_date'] = null;
        }

        return $data;
    }
}
