<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAdminDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $doctor = $this->route('doctor');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($doctor instanceof Doctor ? $doctor->user_id : null)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => $doctor instanceof Doctor ? ['prohibited'] : ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'specialization' => ['required', 'string', 'max:255'],
            'experience' => ['required', 'integer', 'min:0', 'max:80'],
            'education' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
