@extends('layouts.doctor', ['title' => 'My working schedule'])

@section('doctor-content')
    <section class="card border-0 shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">Weekly working hours</h2>
        <p class="text-secondary">Times use {{ config('app.timezone') }}. Entries must allow at least 30 minutes and must not overlap. Upcoming pending and confirmed appointments must remain within your working hours.</p>
        @forelse ($schedules as $schedule)
            <div class="border rounded-3 p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span><strong>{{ $schedule->day }}</strong> · {{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}</span>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('doctor.schedule.edit', $schedule) }}">Edit</a>
                </div>
                <details class="mt-2">
                    <summary class="text-danger">Delete this entry</summary>
                    <form class="mt-2" method="POST" action="{{ route('doctor.schedule.destroy', $schedule) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">Confirm deletion</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-secondary mb-0">No working hours have been added yet.</p>
        @endforelse
    </section>
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Add working hours</h2>
        <x-doctor.schedule-form />
    </section>
@endsection
