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
        foreach (['Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'Dentistry'] as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['description' => $name.' services at MediCare Hospital.']
            );
        }
    }
}
