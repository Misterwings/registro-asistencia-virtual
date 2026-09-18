<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Headquarter;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function export(Request $request)
    {
        if (! $request->has('event_id') || ! $request->has('format')) {
            $events = Event::where('directed_by_id', auth()->id())
                ->orderBy('date', 'desc')
                ->get();
            $headquarters = Headquarter::orderBy('name')->get();

            return view('reports.form', compact('events', 'headquarters'));
        }

        if (! $request->has('headquarter_ids') && $request->filled('headquarter_id')) {
            $request->merge([
                'headquarter_ids' => [$request->query('headquarter_id')],
            ]);
        }

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'format' => 'required|in:xlsx,pdf',
            'headquarter_ids' => 'nullable|array',
            'headquarter_ids.*' => 'integer|distinct|exists:headquarters,id',
        ]);

        $event = Event::where('id', $request->query('event_id'))
            ->where('directed_by_id', auth()->id())
            ->firstOrFail();
        $headquarterIds = collect($request->query('headquarter_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $headquarterIds = $headquarterIds !== [] ? $headquarterIds : null;

        if ($request->query('format') === 'xlsx') {
            return $this->reportService->exportXlsx($event, $headquarterIds);
        }

        return $this->reportService->exportPdf($event, $headquarterIds);
    }

    public function exportCsv(Request $request)
    {
        $events = Event::where('directed_by_id', auth()->id())->get();

        if ($events->isEmpty()) {
            return back()->with('error', 'No tienes eventos con asistencias para exportar.');
        }

        $callback = function () use ($events) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Evento',
                'Nombre Completo',
                'Número de Identificación',
                'Cargo',
                'Sede',
                'Fecha de Registro',
            ]);

            foreach ($events as $event) {
                foreach ($event->attendances as $attendance) {
                    fputcsv($handle, [
                        $event->topic,
                        $attendance->full_name,
                        $attendance->id_number,
                        $attendance->position_label ?? '',
                        $attendance->headquarter_label ?? '',
                        $attendance->registered_at->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            fclose($handle);
        };

        $filename = 'asistencias-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
