<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DoctorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_list_all_database_departments_and_link_to_their_details(): void
    {
        $departments = Department::factory()->count(4)->sequence(
            ['name' => 'Cardiology'], ['name' => 'Dentistry'],
            ['name' => 'Neurology'], ['name' => 'Pediatrics'],
        )->create();

        $response = $this->get(route('departments.index'));

        foreach ($departments as $department) {
            $response->assertSeeText([$department->name, $department->description])
                ->assertSee('href="'.route('departments.show', $department).'"', false);
        }

        $this->assertGuest();
    }

    public function test_department_details_show_only_doctors_in_that_department(): void
    {
        $department = Department::factory()->create(['description' => 'Specialist heart care.']);
        $doctor = Doctor::factory()->for($department)->create(['experience' => 12]);
        $otherDoctor = Doctor::factory()->create();

        $this->get(route('departments.show', $department))
            ->assertSeeText([$department->name, 'Specialist heart care.', $doctor->user->name, '12 years in practice'])
            ->assertSee('href="'.route('doctors.show', $doctor).'"', false)
            ->assertSee('href="'.route('doctors.index', ['department' => $department->id]).'"', false)
            ->assertDontSeeText($otherDoctor->user->name);
    }

    public function test_department_without_doctors_has_a_helpful_empty_state(): void
    {
        $department = Department::factory()->create(['description' => null]);

        $this->get(route('departments.show', $department))
            ->assertSeeText(['Contact our care team to learn more about this department.', 'No doctor profiles are currently listed']);
    }

    #[TestWith(['departments.show', 'department'])]
    #[TestWith(['doctors.show', 'doctor'])]
    public function test_missing_profiles_return_not_found(string $routeName, string $parameter): void
    {
        $this->get(route($routeName, [$parameter => 999999]))->assertNotFound();
    }

    public function test_empty_directories_display_helpful_messages(): void
    {
        $this->get(route('departments.index'))->assertSeeText('Department information will be available soon.');
        $this->get(route('doctors.index'))->assertSeeText('No doctors found');
    }

    public function test_doctor_cards_display_database_information_and_profile_links(): void
    {
        $department = Department::factory()->create(['name' => 'Cardiology']);
        $doctor = Doctor::factory()->for($department)->create([
            'specialization' => 'Heart rhythm specialist',
            'experience' => 7,
        ]);
        $otherDoctor = Doctor::factory()->for($department)->create();

        $this->get(route('doctors.index'))
            ->assertSeeText([$doctor->user->name, $otherDoctor->user->name, 'Heart rhythm specialist', 'Cardiology', '7 years in practice'])
            ->assertSee('href="'.route('doctors.show', $doctor).'"', false);
    }

    #[TestWith(['amelia'])]
    #[TestWith(['Rhythm'])]
    #[TestWith(['cardiology'])]
    public function test_search_matches_name_specialization_or_department(string $search): void
    {
        $department = Department::factory()->create(['name' => 'Cardiology']);
        $doctor = Doctor::factory()->for($department)
            ->for(User::factory()->state(['name' => 'Dr. Amelia Carter', 'role' => 'doctor']))
            ->create(['specialization' => 'Heart rhythm specialist']);
        $otherDoctor = Doctor::factory()->for(Department::factory()->state(['name' => 'Neurology']))
            ->for(User::factory()->state(['name' => 'Dr. Benjamin Lee', 'role' => 'doctor']))
            ->create(['specialization' => 'Neurologist']);

        $this->get(route('doctors.index', ['search' => $search]))
            ->assertSeeText($doctor->user->name)
            ->assertDontSeeText($otherDoctor->user->name)
            ->assertSee('value="'.$search.'"', false);
    }

    public function test_department_filter_excludes_other_departments(): void
    {
        $department = Department::factory()->create();
        $doctor = Doctor::factory()->for($department)->create();
        $otherDoctor = Doctor::factory()->create();

        $this->get(route('doctors.index', ['department' => $department->id]))
            ->assertSeeText($doctor->user->name)
            ->assertDontSeeText($otherDoctor->user->name)
            ->assertSee('value="'.$department->id.'" selected', false);
    }

    public function test_search_and_department_filter_are_applied_together(): void
    {
        $department = Department::factory()->create(['name' => 'Cardiology']);
        $matchingDoctor = Doctor::factory()->for($department)
            ->for(User::factory()->state(['name' => 'Dr. Alex Carter', 'role' => 'doctor']))
            ->create(['specialization' => 'Heart rhythm specialist']);
        $wrongDepartment = Doctor::factory()->for(Department::factory()->state(['name' => 'Neurology']))
            ->for(User::factory()->state(['name' => 'Dr. Alex Lee', 'role' => 'doctor']))
            ->create(['specialization' => 'Heart rhythm specialist']);
        $wrongSearch = Doctor::factory()->for($department)
            ->for(User::factory()->state(['name' => 'Dr. Taylor Morgan', 'role' => 'doctor']))
            ->create(['specialization' => 'General cardiologist']);

        $this->get(route('doctors.index', ['search' => 'Alex', 'department' => $department->id]))
            ->assertSeeText($matchingDoctor->user->name)
            ->assertDontSeeText([$wrongDepartment->user->name, $wrongSearch->user->name]);
    }

    #[TestWith(['no-such-specialist'])]
    #[TestWith(['%'])]
    #[TestWith(['_'])]
    #[TestWith(["' OR 1=1 --"])]
    public function test_search_treats_input_as_text_and_can_return_no_matches(string $search): void
    {
        Doctor::factory()->for(User::factory()->state(['name' => 'Dr. Carter', 'role' => 'doctor']))
            ->for(Department::factory()->state(['name' => 'Cardiology']))
            ->create(['specialization' => 'Cardiologist']);

        $this->get(route('doctors.index', ['search' => $search]))->assertSeeText('No doctors found');
    }

    #[TestWith(['search', ['invalid'], 'The search field must be a string.'])]
    #[TestWith(['department', ['invalid'], 'The department field must be an integer.'])]
    #[TestWith(['department', 'invalid', 'The department field must be an integer.'])]
    #[TestWith(['department', 999999, 'The selected department is invalid.'])]
    public function test_invalid_filters_redirect_to_a_clean_directory_with_feedback(string $field, mixed $value, string $error): void
    {
        $this->followingRedirects()->get(route('doctors.index', [$field => $value]))
            ->assertSeeText(['Please check your search filters.', $error]);
    }

    public function test_search_rejects_overlong_queries(): void
    {
        $this->get(route('doctors.index', ['search' => str_repeat('a', 101)]))
            ->assertRedirect(route('doctors.index'))
            ->assertSessionHasErrors(['search' => 'The search field must not be greater than 100 characters.']);
    }

    public function test_doctor_details_display_information_schedules_and_booking_link(): void
    {
        $doctor = Doctor::factory()->create([
            'specialization' => 'Pediatric specialist',
            'experience' => 9,
            'education' => 'MD, Example University',
            'bio' => 'Supporting children and their families.',
        ]);
        DoctorSchedule::factory()->for($doctor)->create(['day' => 'Monday', 'start_time' => '13:00:00', 'end_time' => '16:00:00']);
        DoctorSchedule::factory()->for($doctor)->create(['day' => 'Monday', 'start_time' => '09:00:00', 'end_time' => '12:00:00']);

        $this->get(route('doctors.show', $doctor))
            ->assertSeeText([$doctor->user->name, $doctor->department->name, 'Pediatric specialist',
                '9 years in practice', 'MD, Example University', 'Supporting children and their families.',
                'Book Appointment', 'confirm availability'])
            ->assertSeeTextInOrder(['9:00 AM', '12:00 PM', '1:00 PM', '4:00 PM'])
            ->assertSee('href="'.route('patient.appointments.create', ['doctor_id' => $doctor->id]).'"', false)
            ->assertSee('href="'.route('departments.show', $doctor->department).'"', false);

        $this->assertDatabaseEmpty('appointments');
    }

    public function test_doctor_without_optional_information_displays_fallbacks(): void
    {
        $doctor = Doctor::factory()->create(['bio' => null, 'education' => null, 'image' => null]);

        $this->get(route('doctors.show', $doctor))
            ->assertSeeText(['Contact our care team to learn more about this doctor.',
                'Please contact our care team for details.', 'Please contact MediCare for availability.'])
            ->assertDontSee('<img', false);
    }

    #[TestWith(['doctors/carter.jpg'])]
    #[TestWith(['https://images.example.test/carter.jpg'])]
    public function test_doctor_images_appear_in_cards_and_details(string $image): void
    {
        $doctor = Doctor::factory()->create(['image' => $image]);
        $expectedUrl = str_starts_with($image, 'https://') ? $image : Storage::disk('public')->url($image);

        foreach ([route('doctors.index'), route('doctors.show', $doctor)] as $url) {
            $this->get($url)->assertSee('src="'.$expectedUrl.'"', false)
                ->assertSee('alt="'.e($doctor->user->name).'"', false);
        }
    }

    public function test_directory_and_details_escape_database_content_and_search_input(): void
    {
        $unsafeText = '<script>alert("unsafe")</script>';
        $department = Department::factory()->create(['name' => $unsafeText, 'description' => $unsafeText]);
        $doctor = Doctor::factory()->for($department)
            ->for(User::factory()->state(['name' => $unsafeText, 'role' => 'doctor']))
            ->create(['specialization' => $unsafeText, 'bio' => $unsafeText, 'education' => $unsafeText]);

        foreach ([route('doctors.index', ['search' => $unsafeText]), route('doctors.show', $doctor), route('departments.show', $department)] as $url) {
            $this->get($url)->assertSee($unsafeText)->assertDontSee($unsafeText, false);
        }
    }
}
