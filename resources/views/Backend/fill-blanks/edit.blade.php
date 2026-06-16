@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-pencil-square me-2" style="color:#6366f1;"></i> Edit Fill-in-the-Blank
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Edit the sentence with blanks and correct answers.</p>

        <form action="{{ route('fill-blanks.update', $fillBlank) }}" method="POST" class="form-glass">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $fillBlank->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Sentence with Blanks (use <code>[blank]</code> for each blank)</label>
                <textarea name="sentence_with_blanks" class="form-control" rows="3" required>{{ old('sentence_with_blanks', $fillBlank->sentence_with_blanks) }}</textarea>
                <small style="color:#9ca3af;font-size:0.75rem;">Example: "The capital of France is [blank]."</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Correct Answers (one per line)</label>
                <textarea name="answers" class="form-control" rows="3" required>{{ old('answers', is_array($fillBlank->answers) ? implode("\n", $fillBlank->answers) : $fillBlank->answers) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="easy" {{ $fillBlank->difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $fillBlank->difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $fillBlank->difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('fill-blanks.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Question</button>
            </div>
        </form>
    </div>
</div>
@endsection
