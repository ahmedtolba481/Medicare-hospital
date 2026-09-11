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
        $patient = User::query()->where('email', 'new.patient@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($patient);
        $this->assertDatabaseHas('users', ['email' => 'new.patient@example.test', 'role' => 'patient']);
        $this->assertTrue(Hash::check('Password123!', $patient->password));
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

    public function test_invalid_login_returns_an_error_and_does_not_authenticate_the_user(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect-password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_patient_registration_rejects_a_duplicate_email_address(): void
    {
        User::factory()->create(['email' => 'existing.patient@example.test']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another Patient',
                'email' => 'existing.patient@example.test',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::query()->where('email', 'existing.patient@example.test')->count());
        $this->assertGuest();
    }

    public function test_every_role_is_limited_to_its_own_protected_area(): void
    {
        $protectedAreas = [
            'patient' => '/test-patient-area',
            'doctor' => '/test-doctor-area',
            'admin' => '/test-admin-area',
        ];

        foreach ($protectedAreas as $role => $path) {
            Route::middleware(['auth', 'role:'.$role])->get($path, fn () => response('ok'));

            $this->get($path)->assertRedirect(route('login'));
        }

        foreach (array_keys($protectedAreas) as $userRole) {
            $user = User::factory()->create(['role' => $userRole]);

            foreach ($protectedAreas as $areaRole => $areaPath) {
                $expectedStatus = $userRole === $areaRole ? 200 : 403;

                $this->actingAs($user)->get($areaPath)->assertStatus($expectedStatus);
            }
        }
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
