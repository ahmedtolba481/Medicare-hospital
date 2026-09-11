<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/* Creates the five departments used by the shared MediCare demo data. */

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Cardiology' => 'Diagnosis and treatment for heart and circulatory conditions.',
            'Neurology' => 'Specialist care for conditions affecting the brain, spine, and nervous system.',
            'Pediatrics' => 'Comprehensive medical care for infants, children, and adolescents.',
            'Orthopedics' => 'Treatment for bone, joint, muscle, and sports-related conditions.',
            'Dentistry' => 'Preventive, restorative, and urgent dental care for all ages.',
        ];

        foreach ($departments as $name => $description) {
            Department::updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }
}
