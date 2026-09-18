<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Headquarter;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_form_allows_multiple_headquarters(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        Headquarter::create(['name' => 'Sede Norte', 'is_active' => true]);

        $response = $this->actingAs($user)->get(route('reports.form'));

        $response->assertOk();
        $response->assertSee('name="headquarter_ids[]"', false);
        $response->assertSee('Seleccionar todas');
        $response->assertSee('Limpiar selección');
    }

    public function test_xlsx_report_receives_selected_headquarters(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent($user);
        $firstHeadquarter = Headquarter::create(['name' => 'Sede Norte', 'is_active' => true]);
        $secondHeadquarter = Headquarter::create(['name' => 'Sede Sur', 'is_active' => true]);

        $reportService = Mockery::mock(ReportService::class);
        $reportService->shouldReceive('exportXlsx')
            ->once()
            ->withArgs(function (Event $receivedEvent, ?array $headquarterIds) use ($event, $firstHeadquarter, $secondHeadquarter): bool {
                return $receivedEvent->is($event)
                    && $headquarterIds === [$firstHeadquarter->id, $secondHeadquarter->id];
            })
            ->andReturn(response('xlsx'));
        $this->app->instance(ReportService::class, $reportService);

        $response = $this->actingAs($user)->get(route('attendances.export', [
            'event_id' => $event->id,
            'format' => 'xlsx',
            'headquarter_ids' => [$firstHeadquarter->id, $secondHeadquarter->id],
        ]));

        $response->assertOk();
        $response->assertSee('xlsx');
    }

    public function test_empty_headquarter_selection_exports_all_headquarters(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent($user);

        $reportService = Mockery::mock(ReportService::class);
        $reportService->shouldReceive('exportPdf')
            ->once()
            ->withArgs(fn (Event $receivedEvent, ?array $headquarterIds): bool =>
                $receivedEvent->is($event) && $headquarterIds === null
            )
            ->andReturn(response('pdf'));
        $this->app->instance(ReportService::class, $reportService);

        $response = $this->actingAs($user)->get(route('attendances.export', [
            'event_id' => $event->id,
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $response->assertSee('pdf');
    }

    public function test_invalid_headquarter_selection_is_rejected(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent($user);

        $response = $this->actingAs($user)->get(route('attendances.export', [
            'event_id' => $event->id,
            'format' => 'xlsx',
            'headquarter_ids' => [999999],
        ]));

        $response->assertSessionHasErrors('headquarter_ids.0');
    }

    private function createEvent(User $user): Event
    {
        return Event::create([
            'date' => '2026-08-19',
            'topic' => 'Evento de reporte',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'place' => 'Sala principal',
            'reason' => 'Capacitacion',
            'directed_by_id' => $user->id,
            'directed_by_position' => 'Coordinador',
            'slug' => 'evento-reporte-' . uniqid(),
        ]);
    }
}
