@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Shared Question Banks</h2>
            <p>Browse and share questions with peers</p>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4" style="border-left:4px solid #059669;padding:0.75rem 1rem;">
            <span style="color:#059669;font-weight:500;">{{ session('success') }}</span>
        </div>
    @endif

    @php
        function questionText($item) {
            return $item->question ?? $item->statement ?? $item->front ?? $item->sentence_with_blanks ?? (is_array($item->left_items) ? ($item->left_items[0] ?? 'Matching question') : 'Question #'.$item->id);
        }
    @endphp

    @php
        $hasPublic = collect($shared)->flatten()->isNotEmpty();
    @endphp

    @if($hasPublic)
    @foreach($types as $type)
        @php $items = $shared[$type] ?? collect(); @endphp
        @if($items->count())
        <div class="glass-card mb-4">
            <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-share me-2" style="color:var(--card-accent);"></i> {{ ucfirst(str_replace('_', ' ', $type)) }}</h5>
            <div class="d-flex flex-column gap-2">
                @foreach($items as $item)
                <div class="d-flex align-items-center gap-3 py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                    <div style="flex:1;font-size:0.88rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit(strip_tags(questionText($item)), 80) }}</div>
                    <small style="color:var(--text-muted);">by {{ $item->user?->name ?? 'Unknown' }}</small>
                    <span class="stat-badge up" style="font-size:0.65rem;">{{ $item->subject?->name ?? 'General' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach
    @else
    <div class="glass-card text-center py-5">
        <i class="bi bi-inbox" style="font-size:3rem;color:#c7d2fe;"></i>
        <p class="mt-3" style="color:var(--text-secondary);">No public questions shared yet.</p>
        <p style="color:var(--text-muted);font-size:0.85rem;">You can make your questions public from the section below.</p>
    </div>
    @endif

    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-person me-2" style="color:var(--card-accent);"></i> Your Questions</h5>
        @php $hasOwn = false; @endphp
        @foreach($types as $type)
            @php $items = $myQuestions[$type] ?? collect(); @endphp
            @if($items->count())
                @php $hasOwn = true; @endphp
                <h6 style="color:var(--text-secondary);font-weight:600;font-size:0.8rem;margin:1rem 0 0.5rem;text-transform:uppercase;letter-spacing:0.05em;">{{ ucfirst(str_replace('_', ' ', $type)) }}</h6>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($items as $item)
                    <div class="d-flex align-items-center gap-3 py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.88rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit(strip_tags(questionText($item)), 80) }}</div>
                            <small style="color:var(--text-muted);">{{ $item->subject?->name ?? 'General' }}</small>
                        </div>
                        <form action="{{ route('shared-questions.toggle') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <button type="submit" class="btn-soft py-1 px-2" style="font-size:0.7rem;{{ $item->is_public ? 'color:#059669;' : 'color:var(--text-muted);' }}">
                                <i class="bi {{ $item->is_public ? 'bi-unlock-fill' : 'bi-lock-fill' }}"></i>
                                {{ $item->is_public ? 'Public' : 'Private' }}
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        @endforeach
        @if(!$hasOwn)
        <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
            <i class="bi bi-pencil" style="font-size:1.5rem;display:block;margin-bottom:0.3rem;"></i>
            You haven't created any questions yet.
        </div>
        @endif
    </div>
</div>
@endsection
