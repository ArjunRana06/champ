@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-pencil-square me-2" style="color:#6366f1;"></i> Edit Matching Question
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Edit the matching pairs.</p>

        <form action="{{ route('matching.update', $matching) }}" method="POST" class="form-glass">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $matching->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Left Items (one per line)</label>
                <textarea name="left_items[]" class="form-control" rows="4" required>{{ old('left_items', is_array($matching->left_items) ? implode("\n", $matching->left_items) : '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Right Items (one per line)</label>
                <textarea name="right_items[]" class="form-control" rows="4" required>{{ old('right_items', is_array($matching->right_items) ? implode("\n", $matching->right_items) : '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Correct Pairs (JSON format: {"left":"right", ...})</label>
                <textarea name="correct_pairs" class="form-control" rows="3" required>{{ old('correct_pairs', is_array($matching->correct_pairs) ? json_encode($matching->correct_pairs, JSON_PRETTY_PRINT) : $matching->correct_pairs) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="easy" {{ $matching->difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $matching->difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $matching->difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('matching.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Question</button>
            </div>
        </form>
    </div>
</div>
@endsection
