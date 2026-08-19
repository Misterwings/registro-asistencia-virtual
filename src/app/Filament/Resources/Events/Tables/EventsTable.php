<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('directed_by_id', auth()->id()))
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('topic')
                    ->label('Tema')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('start_time')
                    ->label('Hora de inicio')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Hora final')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('place')
                    ->label('Lugar')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('expiration_date')
                    ->label('Vence el')
                    ->date('d/m/Y')
                    ->placeholder('Sin vencimiento')
                    ->sortable(),
                TextColumn::make('attendances_count')
                    ->label('Asistencias')
                    ->counts('attendances')
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Enlace')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),
                EditAction::make()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar eventos seleccionados')
                        ->modalSubmitActionLabel('Eliminar')
                        ->successNotificationTitle('Eventos eliminados'),
                ])->label('Acciones masivas'),
            ]);
    }
}
