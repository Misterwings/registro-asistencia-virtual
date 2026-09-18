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
            'position_id' => $position->id,
            'headquarter_id' => $headquarter->id,
        ]));

        $response->assertRedirect();
        $attendance = Attendance::firstOrFail();

        $this->assertSame($position->id, $attendance->position_id);
        $this->assertNull($attendance->position_custom);
        $this->assertSame($headquarter->id, $attendance->headquarter_id);
        $this->assertNull($attendance->headquarter_custom);
        $this->assertSame('Persona de prueba', $attendance->full_name);
        $this->assertSame('Analista Contable', $attendance->position_label);
        $this->assertSame('Sede Norte', $attendance->headquarter_label);
    }

    public function test_custom_values_are_not_accepted_for_a_new_attendance(): void
    {
        $event = $this->createEvent();

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'position_id' => null,
            'headquarter_id' => null,
            'position' => 'Cargo Temporal',
            'headquarter' => 'Sede Temporal',
        ]));

        $response->assertSessionHasErrors(['position_id', 'headquarter_id']);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_names_and_surnames_are_required(): void
    {
        $event = $this->createEvent();

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'first_names' => '',
            'last_names' => '',
        ]));

        $response->assertSessionHasErrors(['first_names', 'last_names']);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_identification_is_normalized_before_registration(): void
    {
        $event = $this->createEvent();

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'id_number' => '1.149.303.038',
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id_number' => '1149303038',
        ]);
    }

    public function test_identification_with_letters_is_rejected(): void
    {
        $event = $this->createEvent();

        $response = $this->post(route('event.register', $event->slug), $this->attendancePayload([
            'id_number' => '1149303038A',
        ]));

        $response->assertSessionHasErrors('id_number');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_same_identification_cannot_register_twice_for_the_same_event(): void
    {
        $event = $this->createEvent();
        $payload = $this->attendancePayload(['id_number' => '1149303038']);

        $this->post(route('event.register', $event->slug), $payload)->assertRedirect();

        $response = $this->post(route('event.register', $event->slug), array_merge($payload, [
            'id_number' => '1 149 303 038',
        ]));

        $response->assertSessionHasErrors('id_number');
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_same_identification_can_register_for_a_different_event(): void
    {
        $firstEvent = $this->createEvent();
        $secondEvent = $this->createEvent(['slug' => 'evento-segundo-' . uniqid()]);
        $payload = $this->attendancePayload(['id_number' => '1149303038']);

        $this->post(route('event.register', $firstEvent->slug), $payload)->assertRedirect();
        $this->post(route('event.register', $secondEvent->slug), $payload)->assertRedirect();

        $this->assertDatabaseCount('attendances', 2);
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
        $position = Position::firstOrCreate(
            ['name' => 'Cargo de prueba'],
            ['is_active' => true]
        );
        $headquarter = Headquarter::firstOrCreate(
            ['name' => 'Sede de prueba'],
            ['is_active' => true]
        );

        return array_merge([
            'first_names' => 'Persona',
            'last_names' => 'de prueba',
            'id_number' => (string) random_int(1000000000, 1999999999),
            'position_id' => $position->id,
            'headquarter_id' => $headquarter->id,
            'signature' => 'data:image/png;base64,c2lnbmF0dXJl',
        ], $overrides);
    }
}
