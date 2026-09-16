@extends('layouts.' . Auth::user()->role . '_layout')

@section('title', 'Notifications')

@section(Auth::user()->role)

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Notifications</h3>

        @if($notifications->where('read_at', null)->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-3 shadow-sm p-3 p-md-4">
        @forelse($notifications as $notification)
            <div class="d-flex justify-content-between align-items-start border-bottom py-3">
                <div>
                    <div class="fw-semibold {{ $notification->read_at ? 'text-secondary' : 'text-primary' }}">
                        {{ $notification->data['message'] ?? 'Notification' }}
                    </div>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>

                @if(! $notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm p-0">Mark as read</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-secondary mb-0">No notifications yet.</p>
        @endforelse
    </div>

@endsection
