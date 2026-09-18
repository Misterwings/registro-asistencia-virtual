<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Event;
use App\Models\Headquarter;
use App\Models\Position;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Evento')
                    ->options(fn () => Event::query()
                        ->where('directed_by_id', auth()->id())
                        ->orderByDesc('date')
                        ->pluck('topic', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('first_names')
                    ->label('Nombres')
                    ->required(),
                TextInput::make('last_names')
                    ->label('Apellidos')
                    ->required(),
                TextInput::make('id_number')
                    ->label('Identificación')
                    ->required(),
                Select::make('position_id')
                    ->label('Cargo')
                    ->options(fn () => Position::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('headquarter_id')
                    ->label('Sede')
                    ->options(fn () => Headquarter::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Textarea::make('signature')
                    ->columnSpanFull(),
                DateTimePicker::make('registered_at')
                    ->required(),
            ]);
    }
}
