<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/* Generates doctor profiles with valid user and department relationships. */

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'doctor']),
            'department_id' => Department::factory(),
            'specialization' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'experience' => fake()->numberBetween(2, 25),
            'education' => fake()->sentence(),
            'image' => null,
        ];
    }
}
