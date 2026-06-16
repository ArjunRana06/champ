@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Search Results</h2>
            <p>Showing results for "<strong>{{ $query }}</strong>"</p>
        </div>
        <form action="{{ route('search') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search again..."
                   style="background:white;border:1.5px solid #e5e7eb;border-radius:40px;padding:0.5rem 1.2rem;font-family:'Inter',sans-serif;min-width:250px;">
            <button class="btn-soft" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    @php
        $total = $subjects->count() + $documents->count() + $mcqs->count() + $trueFalse->count()
               + $shortAnswers->count() + $fillBlanks->count() + $matching->count() + $flashcards->count()
               + $chunks->count();
    @endphp

    <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:1.5rem;">{{ $total }} result{{ $total !== 1 ? 's' : '' }} found</p>

    @if($total === 0)
        <div class="glass-card text-center py-5">
            <i class="bi bi-search" style="font-size:3rem;color:#c7d2fe;"></i>
            <h5 class="mt-3" style="color:var(--text-primary);">No results found</h5>
            <p style="color:var(--text-secondary);">Try different keywords or check your spelling.</p>
        </div>
    @endif

    {{-- Subjects --}}
    @if($subjects->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-bookmark-star-fill me-2" style="color:var(--card-accent);"></i> Subjects ({{ $subjects->count() }})</h5>
        <div class="table-responsive">
            <table class="glass-table">
                <thead><tr><th>Name</th><th>Code</th><th>Semester</th><th></th></tr></thead>
                <tbody>
                    @foreach($subjects as $sub)
                    <tr>
                        <td>{{ $sub->name }}</td>
                        <td>{{ $sub->code ?? '—' }}</td>
                        <td>{{ $sub->semester ?? '—' }}</td>
                        <td><a href="{{ route('subjects.edit', $sub) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;"><i class="bi bi-eye"></i> View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Documents --}}
    @if($documents->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-file-earmark-text-fill me-2" style="color:#a855f7;"></i> Documents ({{ $documents->count() }})</h5>
        <div class="table-responsive">
            <table class="glass-table">
                <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($documents as $doc)
                    <tr>
                        <td>{{ $doc->original_name }}</td>
                        <td>{{ $doc->subject?->name ?? 'Uncategorized' }}</td>
                        <td><span class="stat-badge up">{{ ucfirst($doc->status) }}</span></td>
                        <td><a href="/documents/{{ $doc->id }}/preview" class="btn-soft py-1 px-2" style="font-size:0.75rem;" target="_blank"><i class="bi bi-eye"></i> View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Content Chunks --}}
    @if($chunks->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-text-paragraph me-2" style="color:#f59e0b;"></i> Content Matches ({{ $chunks->count() }})</h5>
        @foreach($chunks as $chunk)
        <div class="p-3 mb-2" style="background:rgba(99,102,241,0.04);border-radius:1rem;">
            <small style="color:var(--card-accent);font-weight:600;">{{ $chunk->document?->original_name ?? 'Unknown' }}</small>
            <p class="mt-1 mb-0" style="color:var(--text-primary);font-size:0.85rem;">
                {{ Str::limit(strip_tags($chunk->content), 300) }}
            </p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- MCQs --}}
    @if($mcqs->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-ui-radios me-2" style="color:var(--card-accent);"></i> Multiple Choice Questions ({{ $mcqs->count() }})</h5>
        @foreach($mcqs as $mcq)
        <div class="p-3 mb-2" style="border-bottom:1px solid #f1f5f9;">
            <p style="color:var(--text-primary);font-weight:500;margin-bottom:0.3rem;">{{ $mcq->question }}</p>
            <small style="color:var(--text-muted);">Difficulty: {{ $mcq->difficulty }}</small>
        </div>
        @endforeach
    </div>
    @endif

    {{-- True/False --}}
    @if($trueFalse->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-check2-circle me-2" style="color:#10b981;"></i> True/False ({{ $trueFalse->count() }})</h5>
        @foreach($trueFalse as $tf)
        <div class="p-3 mb-2" style="border-bottom:1px solid #f1f5f9;">
            <p style="color:var(--text-primary);margin-bottom:0;">{{ $tf->statement }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Short Answers --}}
    @if($shortAnswers->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-pencil-square me-2" style="color:#f59e0b;"></i> Short Answers ({{ $shortAnswers->count() }})</h5>
        @foreach($shortAnswers as $sa)
        <div class="p-3 mb-2" style="border-bottom:1px solid #f1f5f9;">
            <p style="color:var(--text-primary);margin-bottom:0;">{{ $sa->question }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Fill Blanks --}}
    @if($fillBlanks->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-input-cursor-text me-2" style="color:#ec4899;"></i> Fill in the Blanks ({{ $fillBlanks->count() }})</h5>
        @foreach($fillBlanks as $fb)
        <div class="p-3 mb-2" style="border-bottom:1px solid #f1f5f9;">
            <p style="color:var(--text-primary);margin-bottom:0;">{{ $fb->sentence_with_blanks }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Matching --}}
    @if($matching->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-arrow-left-right me-2" style="color:#0ea5e9;"></i> Matching Questions ({{ $matching->count() }})</h5>
        @foreach($matching as $mq)
        <div class="p-3 mb-2" style="border-bottom:1px solid #f1f5f9;">
            <p style="color:var(--text-primary);margin-bottom:0;">{{ is_array($mq->left_items) ? implode(', ', array_slice($mq->left_items, 0, 3)) : $mq->left_items }}...</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Flashcards --}}
    @if($flashcards->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-card-text me-2" style="color:#10b981;"></i> Flashcards ({{ $flashcards->count() }})</h5>
        @foreach($flashcards as $fc)
        <div class="p-3 mb-2" style="border-bottom:1px solid #f1f5f9;">
            <p style="color:var(--text-primary);font-weight:500;margin-bottom:0.2rem;">Front: {{ $fc->front }}</p>
            <small style="color:var(--text-secondary);">Back: {{ Str::limit($fc->back, 100) }}</small>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
