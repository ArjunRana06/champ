@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-pencil-square me-2" style="color:#6366f1;"></i> Edit Short Answer Question
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Edit the question and expected answer.</p>

        <form action="{{ route('short-answers.update', $shortAnswer) }}" method="POST" class="form-glass">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $shortAnswer->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Question</label>
                <textarea name="question" class="form-control" rows="3" required>{{ old('question', $shortAnswer->question) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Expected Answer</label>
                <textarea name="expected_answer" class="form-control" rows="3" required>{{ old('expected_answer', $shortAnswer->expected_answer) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="easy" {{ $shortAnswer->difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $shortAnswer->difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $shortAnswer->difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('short-answers.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Question</button>
            </div>
        </form>
    </div>
</div>
@endsection
