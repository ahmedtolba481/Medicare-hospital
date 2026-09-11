@extends('layouts.patient', ['title' => 'My messages'])

@section('patient-content')
    <section class="card app-panel p-4 mb-4">
        <h2 class="h4 mb-3">Message the care team</h2>
        <p class="muted-copy">We will respond using the contact details in your profile. For emergencies, call your local emergency number.</p>
        <form method="POST" action="{{ route('patient.messages.store') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="subject">Subject (optional)</label>
                <input id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ is_scalar(old('subject')) ? old('subject') : '' }}" maxlength="255" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                @error('subject')<div class="invalid-feedback" id="subject-error">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="message">Message</label>
                <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" rows="5" maxlength="5000" required @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ is_scalar(old('message')) ? old('message') : '' }}</textarea>
                @error('message')<div class="invalid-feedback" id="message-error">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary" type="submit">Send message</button>
        </form>
    </section>
    <section aria-labelledby="sent-messages">
        <h2 id="sent-messages" class="h4 mb-3">Sent messages</h2>
        @forelse ($messages as $message)
            <article class="card app-panel p-4 mb-3 text-break">
                <h3 class="h5">{{ $message->subject ?: 'Message to the care team' }}</h3>
                <p class="small text-secondary">Sent {{ $message->created_at->format('M j, Y, g:i A') }} · {{ $message->status === 'read' ? 'Read by the care team' : 'Awaiting review' }}</p>
                <p class="mb-0">{{ $message->message }}</p>
            </article>
        @empty
            <x-dashboard.empty-state title="No messages" message="You have not sent any messages from your patient account yet." icon="inbox" />
        @endforelse
        {{ $messages->links('pagination::bootstrap-5') }}
    </section>
@endsection
