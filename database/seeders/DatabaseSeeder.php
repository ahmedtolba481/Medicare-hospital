<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /* Demo records are keyed by stable emails/names so reseeding is safe. */
        $this->call(DepartmentSeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@medicare.test'],
            ['name' => 'MediCare Administrator', 'password' => 'Admin@12345', 'role' => 'admin']
        );

        $departments = Department::query()->pluck('id', 'name');
        $doctorDetails = [
            ['name' => 'Dr. Amelia Carter', 'email' => 'doctor.carter@medicare.test', 'department' => 'Cardiology', 'specialization' => 'Cardiologist'],
            ['name' => 'Dr. Benjamin Lee', 'email' => 'doctor.lee@medicare.test', 'department' => 'Neurology', 'specialization' => 'Neurologist'],
            ['name' => 'Dr. Clara Morgan', 'email' => 'doctor.morgan@medicare.test', 'department' => 'Pediatrics', 'specialization' => 'Pediatrician'],
        ];

        $doctors = [];
        foreach ($doctorDetails as $details) {
            $user = User::updateOrCreate(
                ['email' => $details['email']],
                ['name' => $details['name'], 'password' => 'Doctor@12345', 'role' => 'doctor']
            );

            $doctors[] = Doctor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $departments[$details['department']],
                    'specialization' => $details['specialization'],
                    'bio' => 'Experienced '.$details['specialization'].' serving MediCare patients.',
                    'experience' => 8,
                    'education' => 'MediCare Medical University',
                ]
            );
        }

        $patients = [];
        foreach (range(1, 5) as $number) {
            $patients[] = User::updateOrCreate(
                ['email' => "patient{$number}@medicare.test"],
                ['name' => "Demo Patient {$number}", 'password' => 'Patient@12345', 'role' => 'patient']
            );
        }

        $scheduleDays = ['Saturday', 'Sunday', 'Monday'];
        foreach ($doctors as $doctor) {
            foreach ($scheduleDays as $day) {
                DoctorSchedule::updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day' => $day],
                    ['start_time' => '09:00:00', 'end_time' => '17:00:00']
                );
            }
        }

        $appointmentData = [
            ['patient' => 0, 'doctor' => 0, 'date' => '2026-09-12', 'time' => '09:00:00', 'status' => 'pending'],
            ['patient' => 1, 'doctor' => 1, 'date' => '2026-09-13', 'time' => '10:00:00', 'status' => 'confirmed'],
            ['patient' => 2, 'doctor' => 2, 'date' => '2026-09-14', 'time' => '11:00:00', 'status' => 'completed'],
            ['patient' => 3, 'doctor' => 0, 'date' => '2026-09-15', 'time' => '13:00:00', 'status' => 'cancelled'],
            ['patient' => 4, 'doctor' => 1, 'date' => '2026-09-16', 'time' => '14:00:00', 'status' => 'rejected'],
        ];

        foreach ($appointmentData as $data) {
            Appointment::updateOrCreate(
                ['doctor_id' => $doctors[$data['doctor']]->id, 'appointment_date' => $data['date'], 'appointment_time' => $data['time']],
                ['patient_id' => $patients[$data['patient']]->id, 'reason' => 'Routine consultation', 'status' => $data['status']]
            );
        }

        foreach (range(1, 3) as $number) {
            ContactMessage::updateOrCreate(
                ['email' => "visitor{$number}@example.test", 'subject' => 'General hospital enquiry'],
                ['name' => "Demo Visitor {$number}", 'phone' => '555-010'.$number, 'message' => 'Please contact me with more information.', 'status' => $number === 3 ? 'read' : 'unread']
            );
        }
    }
}
