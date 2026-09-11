<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([null])]
    #[TestWith(['patient'])]
    #[TestWith(['doctor'])]
    public function test_all_admin_routes_reject_guests_and_non_admin_roles(?string $role): void
    {
        $visit = Appointment::factory()->create();
        $message = ContactMessage::factory()->create();
        if ($role !== null) {
            $this->actingAs(User::factory()->create(['role' => $role]));
        }
        $requests = [
            ['get', '/admin'], ['get', '/admin/doctors'], ['get', '/admin/doctors/create'],
            ['get', '/admin/doctors/'.$visit->doctor_id.'/edit'], ['post', '/admin/doctors'],
            ['put', '/admin/doctors/'.$visit->doctor_id], ['delete', '/admin/doctors/'.$visit->doctor_id],
            ['get', '/admin/departments'], ['get', '/admin/departments/create'],
            ['get', '/admin/departments/'.$visit->doctor->department_id.'/edit'], ['post', '/admin/departments'],
            ['put', '/admin/departments/'.$visit->doctor->department_id], ['delete', '/admin/departments/'.$visit->doctor->department_id],
            ['get', '/admin/patients'], ['get', '/admin/patients/'.$visit->patient_id],
            ['get', '/admin/appointments'], ['get', '/admin/appointments/'.$visit->id],
            ['get', '/admin/messages'], ['get', '/admin/messages/'.$message->id],
            ['patch', '/admin/messages/'.$message->id], ['delete', '/admin/messages/'.$message->id],
        ];
        foreach ($requests as [$method, $url]) {
            $response = $this->{$method}($url);
            if ($role === null) {
                $response->assertRedirect(route('login'));
            } else {
                $response->assertForbidden();
            }
        }
        $this->assertModelExists($visit);
        $this->assertModelExists($visit->doctor);
        $this->assertModelExists($message);
        $this->assertSame('unread', $message->fresh()->status);
    }

    public function test_dashboard_counts_and_patient_history_include_all_doctors_but_only_patient_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create();
        $first = Appointment::factory()->create(['patient_id' => $patient->id]);
        $second = Appointment::factory()->create(['patient_id' => $patient->id, 'status' => 'confirmed']);
        ContactMessage::factory()->create(['status' => 'unread']);
        ContactMessage::factory()->create(['status' => 'read']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertViewHas('statistics', [
            'Total patients' => 1, 'Total doctors' => 2, 'Total departments' => 2,
            'Total appointments' => 2, 'Pending appointments' => 1, 'Unread messages' => 1,
        ])->assertSeeText([$first->doctor->user->name, $second->doctor->user->name]);
        $this->get(route('admin.patients.index'))->assertOk()->assertViewHas('patients', fn ($patients): bool => $patients->total() === 1 && $patients->first()->appointments_count === 2);
        $this->get(route('admin.patients.show', $patient))->assertOk()->assertSeeText([$first->doctor->user->name, $second->doctor->user->name, $patient->email]);
        $this->get(route('admin.patients.show', $admin))->assertNotFound();
        $this->get(route('admin.patients.show', $first->doctor->user))->assertNotFound();
    }

    #[TestWith(['pending'])]
    #[TestWith(['confirmed'])]
    #[TestWith(['completed'])]
    #[TestWith(['cancelled'])]
    #[TestWith(['rejected'])]
    public function test_appointment_filters_combine_and_details_show_each_status(string $status): void
    {
        $doctor = Doctor::factory()->create();
        $visit = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => $status, 'appointment_date' => '2026-09-15', 'appointment_time' => '09:00', 'reason' => 'Consultation reason', 'notes' => 'Doctor notes']);
        Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => $status, 'appointment_date' => '2026-09-16']);
        Appointment::factory()->create(['status' => $status, 'appointment_date' => '2026-09-15']);
        Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => $status === 'pending' ? 'confirmed' : 'pending', 'appointment_date' => '2026-09-15', 'appointment_time' => '12:00']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.appointments.index', ['status' => $status, 'doctor_id' => $doctor->id, 'date' => '2026-09-15']))->assertOk()
            ->assertViewHas('appointments', fn ($visits): bool => $visits->total() === 1 && $visits->first()->id === $visit->id);
        $this->get(route('admin.appointments.index'))->assertOk()->assertViewHas('appointments', fn ($visits): bool => $visits->total() === 4);
        $this->get(route('admin.appointments.show', $visit))->assertOk()->assertSeeText([ucfirst($status), 'Consultation reason', 'Doctor notes', $visit->patient->email, $doctor->specialization]);
    }

    #[TestWith([['status' => 'unknown'], 'status'])]
    #[TestWith([['status' => ['pending']], 'status'])]
    #[TestWith([['doctor_id' => 999999], 'doctor_id'])]
    #[TestWith([['doctor_id' => '1 OR 1=1'], 'doctor_id'])]
    #[TestWith([['date' => '2026-02-30'], 'date'])]
    #[TestWith([['date' => ['2026-09-15']], 'date'])]
    public function test_invalid_appointment_filters_are_rejected(array $filters, string $field): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson(route('admin.appointments.index', $filters))->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public function test_filtered_pagination_preserves_filters(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        for ($day = 1; $day <= 16; $day++) {
            Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'appointment_date' => sprintf('2026-10-%02d', $day), 'status' => 'confirmed']);
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.appointments.index', ['status' => 'confirmed', 'doctor_id' => $doctor->id]))
            ->assertOk()->assertSee('status=confirmed')->assertSee('doctor_id='.$doctor->id)
            ->assertViewHas('appointments', fn ($visits): bool => $visits->count() === 15 && $visits->total() === 16);
        $this->get(route('admin.appointments.index', ['status' => 'confirmed', 'doctor_id' => $doctor->id, 'page' => 2]))
            ->assertViewHas('appointments', fn ($visits): bool => $visits->count() === 1);
    }

    public function test_messages_are_read_explicitly_and_deletion_only_removes_the_selected_message(): void
    {
        $message = ContactMessage::factory()->create(['message' => '<script>alert("x")</script>']);
        $other = ContactMessage::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.messages.index'))->assertOk()->assertSeeText($message->subject);
        $this->get(route('admin.messages.show', $message))->assertOk()->assertSee($message->message)->assertDontSee($message->message, false);
        $this->assertSame('unread', $message->fresh()->status);
        foreach (['read', 'unread'] as $status) {
            $this->patch(route('admin.messages.update', $message), ['status' => $status, 'message' => 'Forged', 'patient_id' => 99999])
                ->assertRedirect(route('admin.messages.show', $message))->assertSessionHas('status', 'Message marked as '.$status.'.');
            $this->assertSame($status, $message->fresh()->status);
            $this->assertSame($message->message, $message->fresh()->message);
        }
        $this->patchJson(route('admin.messages.update', $message), ['status' => 'deleted'])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertSame('unread', $message->fresh()->status);
        $this->delete(route('admin.messages.destroy', $message))->assertRedirect(route('admin.messages.index'))->assertSessionHas('status');
        $this->assertModelMissing($message);
        $this->assertModelExists($other);
        $this->get(route('admin.messages.show', $message))->assertNotFound();
    }

    public function test_empty_lists_and_forms_render(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['dashboard', 'doctors.index', 'doctors.create', 'departments.index', 'departments.create', 'patients.index', 'appointments.index', 'messages.index'] as $name) {
            $this->get(route('admin.'.$name))->assertOk();
        }
    }

    public function test_admin_mutations_require_csrf_tokens(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->app->instance('env', 'local');
        foreach ([['post', '/admin/doctors'], ['put', '/admin/doctors/1'], ['delete', '/admin/doctors/1'], ['post', '/admin/departments'], ['put', '/admin/departments/1'], ['delete', '/admin/departments/1'], ['patch', '/admin/messages/1'], ['delete', '/admin/messages/1']] as [$method, $url]) {
            $this->{$method}($url)->assertStatus(419);
        }
    }
}
