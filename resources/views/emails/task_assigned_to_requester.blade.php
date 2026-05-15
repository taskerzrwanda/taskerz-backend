@component('emails.layouts.base', ['title' => 'Tasker assigned'])
    <h1>Your tasker is on the way</h1>
    <p>Hi {{ $taskRequest->full_name }}, good news — we've matched you with a tasker for your "{{ $taskRequest->subTask?->name ?? 'task' }}" request.</p>

    @if($tasker)
        <div class="panel">
            <div class="panel-row">
                <div class="panel-label">Tasker</div>
                <div class="panel-value">{{ $tasker->name }}</div>
            </div>
            @if($tasker->profession)
                <div class="panel-row">
                    <div class="panel-label">Profession</div>
                    <div class="panel-value">{{ $tasker->profession }}</div>
                </div>
            @endif
            @if($tasker->phone)
                <div class="panel-row">
                    <div class="panel-label">Contact</div>
                    <div class="panel-value"><a href="tel:{{ $tasker->phone }}">{{ $tasker->phone }}</a></div>
                </div>
            @endif
            @if($tasker->rating)
                <div class="panel-row">
                    <div class="panel-label">Rating</div>
                    <div class="panel-value">⭐ {{ number_format((float) $tasker->rating, 1) }} / 5</div>
                </div>
            @endif
        </div>
    @endif

    <p>They'll reach out shortly to confirm timing and any final details.</p>
@endcomponent
