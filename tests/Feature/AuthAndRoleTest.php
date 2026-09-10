<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/* Verifies the shared authentication, role middleware, and booking constraint. */

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_register_and_is_authenticated(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New Patient',
            'email' => 'new.patient@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs(User::where('email', 'new.patient@example.test')->first());
        $this->assertDatabaseHas('users', ['email' => 'new.patient@example.test', 'role' => 'patient']);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'login@example.test', 'password' => Hash::make('Password123!')]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password123!'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_role_middleware_allows_matching_role_and_forbids_other_roles(): void
    {
        Route::middleware(['auth', 'role:admin'])->get('/test-admin-area', fn () => response('ok'));
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($admin)->get('/test-admin-area')->assertOk();
        $this->actingAs($patient)->get('/test-admin-area')->assertForbidden();
        $this->post(route('logout'));
        $this->get('/test-admin-area')->assertRedirect(route('login'));
    }

    public function test_duplicate_doctor_date_and_time_is_rejected_by_database(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $attributes = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-10',
            'appointment_time' => '09:00:00',
            'reason' => 'Consultation',
            'status' => 'pending',
        ];

        Appointment::create($attributes);

        $this->expectException(UniqueConstraintViolationException::class);
        Appointment::create($attributes);
    }
}
