<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'doctor';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'day' => ['required', Rule::in(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /** @return list<Closure> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (Carbon::parse($this->input('start_time'))->diffInMinutes(Carbon::parse($this->input('end_time'))) < 30) {
                $validator->errors()->add('end_time', 'Working hours must allow at least one 30-minute appointment.');
            }
        }];
    }
}
