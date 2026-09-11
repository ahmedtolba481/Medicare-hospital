<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete_an_empty_department(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post(route('admin.departments.store'), ['name' => 'New department', 'description' => 'Specialist care', 'id' => 99999, 'image' => 'forged-path'])
            ->assertRedirect(route('admin.departments.index'))->assertSessionHas('status');
        $department = Department::sole();
        $this->assertSame('New department', $department->name);
        $this->assertNull($department->image);
        $this->get(route('admin.departments.index'))->assertOk()->assertSeeText('New department');
        $this->get(route('admin.departments.edit', $department))->assertOk()->assertSeeText('Specialist care');
        $this->put(route('admin.departments.update', $department), ['name' => 'Updated department', 'description' => 'Updated care'])
            ->assertRedirect(route('admin.departments.index'));
        $this->assertSame('Updated department', $department->fresh()->name);
        $this->assertSame('Updated care', $department->fresh()->description);
        $this->get(route('departments.show', $department))->assertOk()->assertSeeText('Updated department');
        $this->delete(route('admin.departments.destroy', $department))->assertRedirect(route('admin.departments.index'));
        $this->assertModelMissing($department);
    }

    public function test_department_deletion_never_cascades_into_doctor_or_patient_history(): void
    {
        $visit = Appointment::factory()->create();
        $department = $visit->doctor->department;
        $this->actingAs(User::factory()->create(['role' => 'admin']))->deleteJson(route('admin.departments.destroy', $department))
            ->assertUnprocessable()->assertJsonValidationErrors('department');
        $this->assertModelExists($department);
        $this->assertModelExists($visit->doctor);
        $this->assertModelExists($visit);
        $this->assertModelExists($visit->patient);
    }

    public function test_department_validation_and_unique_name_exclusion(): void
    {
        $department = Department::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson(route('admin.departments.store'), ['name' => $department->name])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson(route('admin.departments.store'), ['name' => '', 'description' => []])->assertUnprocessable()->assertJsonValidationErrors(['name', 'description']);
        $this->postJson(route('admin.departments.store'), ['name' => str_repeat('a', 256), 'description' => str_repeat('b', 5001)])->assertUnprocessable()->assertJsonValidationErrors(['name', 'description']);
        $this->put(route('admin.departments.update', $department), ['name' => $department->name, 'description' => null])->assertRedirect();
        $this->assertNull($department->fresh()->description);
        $this->assertDatabaseCount('departments', 1);
    }

    public function test_department_content_is_escaped_and_deletion_failure_is_explained(): void
    {
        $visit = Appointment::factory()->create();
        $department = $visit->doctor->department;
        $department->update(['description' => '<script>alert(1)</script>']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.departments.index'))
            ->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->followingRedirects()->from(route('admin.departments.index'))->delete(route('admin.departments.destroy', $department))
            ->assertSeeText('Reassign all doctors before deleting this department.');
        $this->assertModelExists($department);
    }
}
