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
            [
                'name' => 'Dr. Amelia Carter',
                'email' => 'doctor.carter@medicare.test',
                'department' => 'Cardiology',
                'specialization' => 'Interventional Cardiologist',
                'bio' => 'Dr. Carter treats coronary artery disease, hypertension, and complex cardiovascular conditions.',
                'experience' => 14,
                'education' => 'MD, FACC - Johns Hopkins University School of Medicine',
            ],
            [
                'name' => 'Dr. Benjamin Lee',
                'email' => 'doctor.lee@medicare.test',
                'department' => 'Neurology',
                'specialization' => 'Neurologist',
                'bio' => 'Dr. Lee provides evidence-based care for headaches, stroke recovery, epilepsy, and movement disorders.',
                'experience' => 11,
                'education' => 'MD - University of Michigan Medical School',
            ],
            [
                'name' => 'Dr. Clara Morgan',
                'email' => 'doctor.morgan@medicare.test',
                'department' => 'Pediatrics',
                'specialization' => 'Pediatrician',
                'bio' => 'Dr. Morgan supports children and families through preventive visits, acute illnesses, and developmental care.',
                'experience' => 9,
                'education' => 'MD - Boston University Chobanian & Avedisian School of Medicine',
            ],
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
                    'bio' => $details['bio'],
                    'experience' => $details['experience'],
                    'education' => $details['education'],
                ]
            );
        }

        $patients = [];
        $patientDetails = [
            ['name' => 'Olivia Bennett', 'email' => 'patient1@medicare.test', 'phone' => '+1-555-0101', 'address' => '14 Maple Street, Springfield', 'date_of_birth' => '1988-04-18'],
            ['name' => 'Noah Williams', 'email' => 'patient2@medicare.test', 'phone' => '+1-555-0102', 'address' => '87 Cedar Avenue, Springfield', 'date_of_birth' => '1976-11-02'],
            ['name' => 'Emma Davis', 'email' => 'patient3@medicare.test', 'phone' => '+1-555-0103', 'address' => '220 Lakeview Road, Springfield', 'date_of_birth' => '1993-07-26'],
            ['name' => 'Liam Johnson', 'email' => 'patient4@medicare.test', 'phone' => '+1-555-0104', 'address' => '5 Willow Lane, Springfield', 'date_of_birth' => '1981-01-14'],
            ['name' => 'Sophia Martinez', 'email' => 'patient5@medicare.test', 'phone' => '+1-555-0105', 'address' => '66 Oak Boulevard, Springfield', 'date_of_birth' => '2000-09-30'],
        ];

        foreach ($patientDetails as $details) {
            $patients[] = User::updateOrCreate(
                ['email' => $details['email']],
                [...$details, 'password' => 'Patient@12345', 'role' => 'patient']
            );
        }

        $scheduleDetails = [
            [['day' => 'Sunday', 'start_time' => '09:00:00', 'end_time' => '16:00:00'], ['day' => 'Tuesday', 'start_time' => '09:00:00', 'end_time' => '16:00:00'], ['day' => 'Thursday', 'start_time' => '10:00:00', 'end_time' => '14:00:00']],
            [['day' => 'Saturday', 'start_time' => '10:00:00', 'end_time' => '17:00:00'], ['day' => 'Monday', 'start_time' => '10:00:00', 'end_time' => '17:00:00'], ['day' => 'Wednesday', 'start_time' => '09:00:00', 'end_time' => '15:00:00']],
            [['day' => 'Sunday', 'start_time' => '08:30:00', 'end_time' => '15:30:00'], ['day' => 'Tuesday', 'start_time' => '08:30:00', 'end_time' => '15:30:00'], ['day' => 'Thursday', 'start_time' => '08:30:00', 'end_time' => '13:00:00']],
        ];

        foreach ($doctors as $index => $doctor) {
            foreach ($scheduleDetails[$index] as $schedule) {
                DoctorSchedule::updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day' => $schedule['day']],
                    ['start_time' => $schedule['start_time'], 'end_time' => $schedule['end_time']]
                );
            }
        }

        $appointmentData = [
            ['patient' => 0, 'doctor' => 0, 'date' => '2026-09-13', 'time' => '09:00:00', 'reason' => 'Recurring chest discomfort during exercise', 'status' => 'pending', 'notes' => null],
            ['patient' => 1, 'doctor' => 1, 'date' => '2026-09-14', 'time' => '10:00:00', 'reason' => 'Follow-up for persistent migraines', 'status' => 'confirmed', 'notes' => 'Patient asked to bring their headache diary.'],
            ['patient' => 2, 'doctor' => 2, 'date' => '2026-09-15', 'time' => '11:00:00', 'reason' => 'Annual pediatric wellness visit', 'status' => 'completed', 'notes' => 'Vaccinations reviewed and growth chart updated.'],
            ['patient' => 3, 'doctor' => 0, 'date' => '2026-09-17', 'time' => '13:00:00', 'reason' => 'Blood pressure medication review', 'status' => 'cancelled', 'notes' => 'Cancelled by patient due to travel.'],
            ['patient' => 4, 'doctor' => 1, 'date' => '2026-09-16', 'time' => '14:00:00', 'reason' => 'Dizziness and balance concerns', 'status' => 'rejected', 'notes' => 'Referred to the next available neurology clinic slot.'],
        ];

        foreach ($appointmentData as $data) {
            Appointment::updateOrCreate(
                ['doctor_id' => $doctors[$data['doctor']]->id, 'appointment_date' => $data['date'], 'appointment_time' => $data['time']],
                ['patient_id' => $patients[$data['patient']]->id, 'reason' => $data['reason'], 'status' => $data['status'], 'notes' => $data['notes']]
            );
        }

        $contactMessages = [
            ['name' => 'Ava Thompson', 'email' => 'ava.thompson@example.test', 'phone' => '+1-555-0201', 'subject' => 'Cardiology consultation availability', 'message' => 'I would like to know the next available consultation with the cardiology team.', 'status' => 'unread'],
            ['name' => 'Ethan Brown', 'email' => 'ethan.brown@example.test', 'phone' => '+1-555-0202', 'subject' => 'Pediatric vaccination records', 'message' => 'Please let me know how to request a copy of my child’s vaccination record.', 'status' => 'unread'],
            ['name' => 'Mia Wilson', 'email' => 'mia.wilson@example.test', 'phone' => null, 'subject' => 'Hospital visiting hours', 'message' => 'Could you confirm the visiting hours for the inpatient ward this weekend?', 'status' => 'read'],
        ];

        foreach ($contactMessages as $message) {
            ContactMessage::updateOrCreate(
                ['email' => $message['email'], 'subject' => $message['subject']],
                $message
            );
        }
    }
}
