@props(['schedule' => null])

<form method="POST" action="{{ $schedule ? route('doctor.schedule.update', $schedule) : route('doctor.schedule.store') }}" novalidate>
    @csrf
    @if ($schedule) @method('PATCH') @endif
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="day">Day</label>
            <select class="form-select @error('day') is-invalid @enderror" id="day" name="day" required>
                <option value="">Choose a day</option>
                @foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                    <option value="{{ $day }}" @selected(old('day', $schedule?->day) === $day)>{{ $day }}</option>
                @endforeach
            </select>
            @error('day')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @foreach (['start_time' => 'Start time', 'end_time' => 'End time'] as $field => $label)
            @php($value = old($field, $schedule ? substr($schedule->{$field}, 0, 5) : ''))
            <div class="col-md-4">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <input class="form-control @error($field) is-invalid @enderror" type="time" id="{{ $field }}" name="{{ $field }}" value="{{ is_scalar($value) ? $value : '' }}" required>
                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endforeach
        <div class="col-12"><button class="btn btn-primary" type="submit">Save working hours</button></div>
    </div>
</form>
