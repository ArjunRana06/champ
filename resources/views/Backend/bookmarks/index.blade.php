@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>My Bookmarks</h2>
            <p>Saved questions and resources for quick review</p>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    @php
        $typeLabels = [
            'App\Models\Mcq' => 'MCQ',
            'App\Models\TrueFalseQuestion' => 'True/False',
            'App\Models\ShortAnswer' => 'Short Answer',
            'App\Models\FillBlank' => 'Fill in the Blank',
            'App\Models\MatchingQuestion' => 'Matching',
            'App\Models\Flashcard' => 'Flashcard',
        ];
    @endphp

    @forelse($bookmarks as $type => $items)
        <div class="glass-card mb-4">
            <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-bookmark-fill me-2" style="color:#f59e0b;"></i>
                {{ $typeLabels[$type] ?? str_replace('App\Models\\', '', $type) }}
                <span class="stat-badge up ms-2">{{ $items->count() }}</span>
            </h5>
            @foreach($items as $bookmark)
                <div class="d-flex justify-content-between align-items-start py-2" style="border-bottom:1px solid #f1f5f9;">
                    <div style="color:var(--text-primary);font-size:0.85rem;">
                        @php $bookmarkable = $bookmark->bookmarkable; @endphp
                        @if($bookmarkable)
                            @if($type === 'App\Models\Mcq')
                                {{ $bookmarkable->question }}
                            @elseif($type === 'App\Models\TrueFalseQuestion')
                                {{ $bookmarkable->statement }}
                            @elseif($type === 'App\Models\ShortAnswer')
                                {{ $bookmarkable->question }}
                            @elseif($type === 'App\Models\FillBlank')
                                {{ $bookmarkable->sentence_with_blanks }}
                            @elseif($type === 'App\Models\MatchingQuestion')
                                Matching: {{ implode(', ', array_slice($bookmarkable->left_items ?? [], 0, 3)) }}
                            @elseif($type === 'App\Models\Flashcard')
                                {{ $bookmarkable->front }}
                            @endif
                        @else
                            <span style="color:var(--text-muted);">Resource no longer available</span>
                        @endif
                    </div>
                    <form action="{{ route('bookmarks.destroy', $bookmark->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn-soft danger py-1 px-2" style="font-size:0.75rem;" onclick="return confirm('Remove bookmark?')"><i class="bi bi-bookmark-x"></i></button>
                    </form>
                </div>
            @endforeach
        </div>
    @empty
        <div class="glass-card text-center py-5">
            <i class="bi bi-bookmark" style="font-size:3rem;color:#c7d2fe;"></i>
            <p class="mt-3" style="color:var(--text-secondary);">No bookmarks yet. You can bookmark questions while studying.</p>
        </div>
    @endforelse
</div>
@endsection
