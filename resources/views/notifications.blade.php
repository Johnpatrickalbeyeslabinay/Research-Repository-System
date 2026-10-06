@extends('layouts.' . Auth::user()->role . '_layout')

@section('title', 'Notifications')

@section(Auth::user()->role)

<div class="container-fluid px-0">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row
                justify-content-between align-items-start
                align-items-md-center gap-3 mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Notifications
            </h3>

            <p class="text-muted mb-0">
                Stay updated with your latest notifications.
            </p>
        </div>

        @if($notifications->where('read_at', null)->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf

                <button type="submit"
                    class="btn btn-outline-secondary btn-sm rounded-3 px-3">

                    <i class="bi bi-check2-all me-1"></i>
                    Mark all as read

                </button>
            </form>
        @endif

    </div>


    <!-- Notifications Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- Card Header -->
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">

            <div class="d-flex align-items-center gap-2">

                <div class="bg-primary bg-opacity-10
                            text-primary rounded-circle
                            d-flex align-items-center justify-content-center"
                    style="width:40px; height:40px;">

                    <i class="bi bi-bell"></i>

                </div>

                <div>
                    <h6 class="fw-bold mb-0">
                        Recent Notifications
                    </h6>

                    <small class="text-muted">
                        Your latest updates and activities
                    </small>
                </div>

            </div>

        </div>


        <!-- Notification List -->
        <div class="card-body p-0">

            @forelse($notifications as $notification)

                <div class="d-flex flex-column flex-md-row
                            justify-content-between
                            align-items-start gap-3
                            px-3 px-md-4 py-3
                            border-bottom">

                    <!-- Notification Content -->
                    <div class="d-flex gap-3 w-100">

                        <!-- Status Indicator -->
                        <div class="flex-shrink-0 pt-1">

                            @if(! $notification->read_at)

                                <span class="bg-primary rounded-circle
                                             d-block"
                                    style="width:10px; height:10px;">
                                </span>

                            @else

                                <span class="bg-secondary-subtle rounded-circle
                                             d-block"
                                    style="width:10px; height:10px;">
                                </span>

                            @endif

                        </div>


                        <!-- Message -->
                        <div class="flex-grow-1">

                            <div class="fw-semibold
                                {{ $notification->read_at
                                    ? 'text-secondary'
                                    : 'text-primary' }}">

                                {{ $notification->data['message'] ?? 'Notification' }}

                            </div>

                            <div class="small text-muted mt-1">

                                <i class="bi bi-clock me-1"></i>

                                {{ $notification->created_at->diffForHumans() }}

                            </div>

                        </div>

                    </div>


                    <!-- Mark as Read -->
                    @if(! $notification->read_at)

                        <form action="{{ route('notifications.read', $notification->id) }}"
                            method="POST"
                            class="flex-shrink-0">

                            @csrf

                            <button type="submit"
                                class="btn btn-link btn-sm
                                       text-decoration-none
                                       p-0">

                                Mark as read

                            </button>

                        </form>

                    @endif

                </div>

            @empty

                <!-- Empty State -->
                <div class="text-center py-5 px-3">

                    <div class="bg-light rounded-circle
                                d-inline-flex align-items-center
                                justify-content-center mb-3"
                        style="width:64px; height:64px;">

                        <i class="bi bi-bell-slash fs-3 text-secondary"></i>

                    </div>

                    <h6 class="fw-bold mb-1">
                        No notifications yet
                    </h6>

                    <p class="text-muted small mb-0">
                        You're all caught up. New notifications will appear here.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection
