<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereHas('event', fn ($q) => $q->where('directed_by_id', auth()->id())))
            ->columns([
                TextColumn::make('event.topic')
                    ->label('Evento')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('id_number')
                    ->label('Identificación')
                    ->searchable(),
                TextColumn::make('position_label')
                    ->label('Cargo'),
                TextColumn::make('headquarter_label')
                    ->label('Sede'),
                TextColumn::make('registered_at')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                ImageColumn::make('signature')
                    ->label('Firma')
                    ->height(40)
                    ->width(80)
                    ->square(false),
            ])
            ->filters([])
            ->recordActions([
                Action::make('viewSignature')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalles de Asistencia')
                    ->modalWidth(Width::Medium)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (Attendance $record) {
                        return view('filament.resources.attendances.modal.signature', ['record' => $record]);
                    }),
            ])
            ->toolbarActions([
                Action::make('exportReport')
                    ->label('Descargar Reporte')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(route('reports.form'))
                    ->openUrlInNewTab(),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar asistencias seleccionadas')
                        ->modalSubmitActionLabel('Eliminar')
                        ->successNotificationTitle('Asistencias eliminadas'),
                ])->label('Acciones masivas'),
            ]);
    }
}
