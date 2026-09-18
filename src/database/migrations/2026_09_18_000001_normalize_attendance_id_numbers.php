<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $normalizedByKey = [];
        $updates = [];

        DB::table('attendances')
            ->select(['id', 'event_id', 'id_number'])
            ->orderBy('id')
            ->chunk(1000, function ($attendances) use (&$normalizedByKey, &$updates): void {
                foreach ($attendances as $attendance) {
                    $normalized = $this->normalize($attendance->id_number);

                    if ($normalized === '' || ! preg_match('/\A[0-9]+\z/', $normalized)) {
                        throw new RuntimeException(
                            "La asistencia {$attendance->id} tiene una identificación que no puede normalizarse."
                        );
                    }

                    $key = $attendance->event_id . ':' . $normalized;

                    if (isset($normalizedByKey[$key]) && $normalizedByKey[$key] !== $attendance->id) {
                        throw new RuntimeException(
                            "Las asistencias {$normalizedByKey[$key]} y {$attendance->id} quedarían duplicadas " .
                            "en el evento {$attendance->event_id} después de normalizar la identificación."
                        );
                    }

                    $normalizedByKey[$key] = $attendance->id;

                    if ($normalized !== $attendance->id_number) {
                        $updates[$attendance->id] = $normalized;
                    }
                }
            });

        foreach ($updates as $id => $idNumber) {
            DB::table('attendances')
                ->where('id', $id)
                ->update(['id_number' => $idNumber]);
        }
    }

    public function down(): void
    {
        // La normalización elimina separadores y no puede revertirse sin conocer el formato original.
    }

    private function normalize(?string $value): string
    {
        $value = trim((string) $value);

        return preg_replace('/(?<=[0-9])[.\-\s]+(?=[0-9])/', '', $value) ?? $value;
    }
};
