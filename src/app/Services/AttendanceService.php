<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function register(array $data, Event $event): Attendance
    {
        $firstNames = $this->normalizeValue($data['first_names'] ?? null);
        $lastNames = $this->normalizeValue($data['last_names'] ?? null);

        $data['first_names'] = $firstNames;
        $data['last_names'] = $lastNames;
        $data['full_name'] = trim(implode(' ', array_filter([$firstNames, $lastNames])));
        $data['id_number'] = $this->normalizeIdNumber($data['id_number'] ?? null);
        $data['position_custom'] = null;
        $data['headquarter_custom'] = null;
        $data['event_id'] = $event->id;
        $data['registered_at'] = now();

        return Attendance::create($data);
    }

    private function normalizeValue(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    public function normalizeIdNumber(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/(?<=[0-9])[.\-\s]+(?=[0-9])/', '', $value) ?? $value;
    }

    public function isAlreadyRegistered(Event $event, string $idNumber): bool
    {
        return Attendance::where('event_id', $event->id)
            ->where('id_number', $this->normalizeIdNumber($idNumber))
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
