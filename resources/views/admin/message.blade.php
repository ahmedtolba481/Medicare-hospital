@extends('layouts.admin', ['title' => 'Contact message'])
@section('admin-content')
    <section class="card app-panel p-4">
        <h2 class="h4 text-break mb-3">{{ $contactMessage->subject }}</h2>
        <p><span class="badge {{ $contactMessage->status === 'unread' ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ ucfirst($contactMessage->status) }}</span> &middot; {{ $contactMessage->created_at->format('M j, Y g:i A') }}</p>
        <dl class="row">
            <dt class="col-sm-3">Name</dt><dd class="col-sm-9 text-break">{{ $contactMessage->name }}</dd>
            <dt class="col-sm-3">Email</dt><dd class="col-sm-9 text-break">{{ $contactMessage->email }}</dd>
            <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $contactMessage->phone ?: 'Not provided' }}</dd>
        </dl>
        <p class="text-break" style="white-space: pre-wrap">{{ $contactMessage->message }}</p>
        <form method="POST" action="{{ route('admin.messages.update', $contactMessage) }}">@csrf @method('PATCH')<button class="btn btn-outline-primary" name="status" value="{{ $contactMessage->status === 'unread' ? 'read' : 'unread' }}" type="submit">Mark {{ $contactMessage->status === 'unread' ? 'read' : 'unread' }}</button></form>
        <x-admin.delete-form :action="route('admin.messages.destroy', $contactMessage)" description="Permanently delete this contact message? It will also be removed from the sender's patient message history, if linked." />
        <a class="align-self-start mt-4" href="{{ route('admin.messages.index') }}">Back to messages</a>
    </section>
@endsection
