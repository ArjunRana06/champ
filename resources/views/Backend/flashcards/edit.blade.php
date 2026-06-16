@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-pencil-square me-2" style="color:var(--card-accent);"></i> Edit Flashcard
        </h2>
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.5rem;">Edit the front and back of this flashcard.</p>

        <form action="{{ route('flashcards.update', $flashcard) }}" method="POST" class="form-glass">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $flashcard->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Front (question/term)</label>
                <textarea name="front" class="form-control" rows="2" required>{{ old('front', $flashcard->front) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Back (answer/definition)</label>
                <textarea name="back" class="form-control" rows="3" required>{{ old('back', $flashcard->back) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="easy" {{ $flashcard->difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $flashcard->difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $flashcard->difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('flashcards.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Flashcard</button>
            </div>
        </form>
    </div>
</div>
@endsection
