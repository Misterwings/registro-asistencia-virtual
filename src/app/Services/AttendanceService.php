<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Headquarter;
use App\Models\Position;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function register(array $data, Event $event): Attendance
    {
        $position = $this->normalizeValue($data['position'] ?? null);
        $headquarter = $this->normalizeValue($data['headquarter'] ?? null);

        $positionId = $this->resolveCatalogId(Position::class, $position);
        $headquarterId = $this->resolveCatalogId(Headquarter::class, $headquarter);

        unset($data['position'], $data['headquarter']);

        $data['position_id'] = $positionId;
        $data['position_custom'] = $positionId ? null : $position;
        $data['headquarter_id'] = $headquarterId;
        $data['headquarter_custom'] = $headquarterId ? null : $headquarter;
        $data['event_id'] = $event->id;
        $data['registered_at'] = now();

        return Attendance::create($data);
    }

    private function resolveCatalogId(string $modelClass, ?string $value): ?int
    {
        $canonicalValue = $this->canonicalValue($value);

        if ($canonicalValue === null) {
            return null;
        }

        $record = $modelClass::query()
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->first(fn ($record) => $this->canonicalValue($record->name) === $canonicalValue);

        return $record?->id;
    }

    private function canonicalValue(?string $value): ?string
    {
        $value = $this->normalizeValue($value);

        return $value === null ? null : mb_strtolower($value, 'UTF-8');
    }

    private function normalizeValue(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    public function isAlreadyRegistered(Event $event, string $idNumber): bool
    {
        return Attendance::where('event_id', $event->id)
            ->where('id_number', $idNumber)
            ->exists();
    }

    public function getAttendancesByEvent(Event $event): Collection
    {
        return $event->attendances()
            ->with(['position', 'headquarter'])
            ->orderBy('registered_at', 'desc')
            ->get();
    }

    public function getAttendancesByAdmin(): Collection
    {
        return Attendance::whereHas('event', function ($query) {
            $query->where('directed_by_id', auth()->id());
        })
            ->with(['event', 'position', 'headquarter'])
            ->orderBy('registered_at', 'desc')
            ->get();
    }
}
