@extends('layouts.admin', ['title' => 'Contact messages'])
@section('admin-content')
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Contact inbox</h2>
        @if ($messages->isEmpty())
            <p>No contact messages have been received.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Received</th><th scope="col">Sender</th><th scope="col">Subject</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($messages as $contactMessage)
                            <tr>
                                <td class="text-nowrap">{{ $contactMessage->created_at->format('M j, Y') }}</td>
                                <td class="text-break">{{ $contactMessage->name }}<br><span class="small">{{ $contactMessage->email }}</span></td>
                                <td class="text-break"><a href="{{ route('admin.messages.show', $contactMessage) }}">{{ $contactMessage->subject }}</a></td>
                                <td><span class="badge {{ $contactMessage->status === 'unread' ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ ucfirst($contactMessage->status) }}</span></td>
                                <td><form method="POST" action="{{ route('admin.messages.update', $contactMessage) }}">@csrf @method('PATCH')<button class="btn btn-outline-primary btn-sm" name="status" value="{{ $contactMessage->status === 'unread' ? 'read' : 'unread' }}" type="submit">Mark {{ $contactMessage->status === 'unread' ? 'read' : 'unread' }}</button></form></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $messages->links('pagination::bootstrap-5') }}
    </section>
@endsection
