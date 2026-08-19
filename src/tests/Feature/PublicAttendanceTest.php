<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Headquarter;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_values_are_saved_as_relationship_ids(): void
    {
        $event = $this->createEvent();
        $position = Position::create(['name' => 'Analista Contable', 'is_active' => true]);
        $headquarter = Headquarter::create(['name' => 'Sede Norte', 'is_active' => true]);

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'position' => '  analista   contable ',
            'headquarter' => 'sede norte',
        ]));

        $response->assertRedirect();
        $attendance = Attendance::firstOrFail();

        $this->assertSame($position->id, $attendance->position_id);
        $this->assertNull($attendance->position_custom);
        $this->assertSame($headquarter->id, $attendance->headquarter_id);
        $this->assertNull($attendance->headquarter_custom);
        $this->assertSame('Analista Contable', $attendance->position_label);
        $this->assertSame('Sede Norte', $attendance->headquarter_label);
    }

    public function test_custom_values_are_saved_on_the_attendance(): void
    {
        $event = $this->createEvent();

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'position' => 'Cargo Temporal',
            'headquarter' => 'Sede Temporal',
        ]));

        $response->assertRedirect();
        $attendance = Attendance::firstOrFail();

        $this->assertNull($attendance->position_id);
        $this->assertSame('Cargo Temporal', $attendance->position_custom);
        $this->assertNull($attendance->headquarter_id);
        $this->assertSame('Sede Temporal', $attendance->headquarter_custom);
        $this->assertSame('Cargo Temporal', $attendance->position_label);
        $this->assertSame('Sede Temporal', $attendance->headquarter_label);
    }

    public function test_blank_catalog_fields_are_stored_as_null(): void
    {
        $event = $this->createEvent();

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'position' => '   ',
            'headquarter' => '',
        ]));

        $response->assertRedirect();
        $attendance = Attendance::firstOrFail();

        $this->assertNull($attendance->position_id);
        $this->assertNull($attendance->position_custom);
        $this->assertNull($attendance->headquarter_id);
        $this->assertNull($attendance->headquarter_custom);
    }

    public function test_public_link_remains_available_on_its_expiration_date(): void
    {
        $event = $this->createEvent([
            'has_expiration' => true,
            'expiration_date' => '2026-08-19',
        ]);

        $this->travelTo(Carbon::create(2026, 8, 19, 23, 59, 59, 'America/Bogota'));

        $response = $this->get(route('event.show', $event->slug));

        $response->assertOk();
        $response->assertSee('Registrar Asistencia');
    }

    public function test_expired_public_link_cannot_be_viewed(): void
    {
        $event = $this->createEvent([
            'has_expiration' => true,
            'expiration_date' => '2026-08-19',
        ]);

        $this->travelTo(Carbon::create(2026, 8, 20, 0, 0, 0, 'America/Bogota'));

        $response = $this->get(route('event.show', $event->slug));

        $response->assertStatus(410);
        $response->assertSee('Enlace vencido');
        $response->assertDontSee('Registrar Asistencia');
    }

    public function test_expired_public_link_cannot_register_attendance(): void
    {
        $event = $this->createEvent([
            'has_expiration' => true,
            'expiration_date' => '2026-08-19',
        ]);

        $this->travelTo(Carbon::create(2026, 8, 20, 0, 0, 0, 'America/Bogota'));

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload());

        $response->assertStatus(410);
        $this->assertDatabaseCount('attendances', 0);
    }

    private function createEvent(array $overrides = []): Event
    {
        $director = User::factory()->create();

        return Event::create(array_merge([
            'date' => '2026-08-19',
            'topic' => 'Evento de prueba',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'place' => 'Sala principal',
            'reason' => 'Capacitacion',
            'directed_by_id' => $director->id,
            'directed_by_position' => 'Coordinador',
            'slug' => 'evento-prueba-'.uniqid(),
        ], $overrides));
    }

    private function attendancePayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Persona de prueba',
            'id_number' => uniqid('id-'),
            'position' => null,
            'headquarter' => null,
            'signature' => 'data:image/png;base64,c2lnbmF0dXJl',
        ], $overrides);
    }
}
