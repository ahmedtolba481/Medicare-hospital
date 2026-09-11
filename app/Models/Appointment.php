<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/* Represents a booking between a patient and a doctor. */

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected $fillable = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'reason', 'status', 'notes'];

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && Carbon::parse($this->appointment_date->toDateString().' '.$this->appointment_time)->isFuture();
    }

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** @return BelongsTo<Doctor, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
