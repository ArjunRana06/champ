@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-pencil-square me-2" style="color:var(--card-accent);"></i> Edit True/False Question
        </h2>
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.5rem;">Edit the statement and correct answer.</p>

        <form action="{{ route('true-false.update', $trueFalse) }}" method="POST" class="form-glass">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $trueFalse->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Statement</label>
                <textarea name="statement" class="form-control" rows="3" required>{{ old('statement', $trueFalse->statement) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Correct Answer</label>
                <select name="correct_answer" class="form-select" required>
                    <option value="1" {{ $trueFalse->correct_answer ? 'selected' : '' }}>True</option>
                    <option value="0" {{ !$trueFalse->correct_answer ? 'selected' : '' }}>False</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Explanation</label>
                <textarea name="explanation" class="form-control" rows="2">{{ old('explanation', $trueFalse->explanation) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="easy" {{ $trueFalse->difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $trueFalse->difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $trueFalse->difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('true-false.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Question</button>
            </div>
        </form>
    </div>
</div>
@endsection
