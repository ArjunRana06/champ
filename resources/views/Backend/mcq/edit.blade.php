@extends('Backend.master')

@section('content')
<div class="container" style="max-width:750px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-pencil-square me-2" style="color:#6366f1;"></i> Edit MCQ
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Edit the question and answer options.</p>

        <form action="{{ route('mcqs.update', $mcq) }}" method="POST" class="form-glass">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $mcq->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Question</label>
                <textarea name="question" class="form-control" rows="3" required>{{ old('question', $mcq->question) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Options (at least 2)</label>
                <div id="options-container">
                    @foreach(old('options', $mcq->options ?? ['', '', '', '']) as $i => $option)
                    <div class="input-group mb-2">
                        <span class="input-group-text" style="background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:0.75rem 0 0 0.75rem;">{{ chr(65 + $i) }}</span>
                        <input type="text" name="options[]" class="form-control" value="{{ $option }}" required style="border-radius:0 0.75rem 0.75rem 0;">
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn-soft mt-1" onclick="addOption()"><i class="bi bi-plus"></i> Add Option</button>
            </div>

            <div class="mb-3">
                <label class="form-label">Correct Answer</label>
                <select name="correct_answer" class="form-select" required>
                    @foreach(old('options', $mcq->options ?? ['A', 'B', 'C', 'D']) as $i => $option)
                        <option value="{{ $option }}" {{ $mcq->correct_answer === $option ? 'selected' : '' }}>{{ chr(65 + $i) }}: {{ Str::limit($option, 40) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Explanation</label>
                <textarea name="explanation" class="form-control" rows="2">{{ old('explanation', $mcq->explanation) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="easy" {{ $mcq->difficulty === 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ $mcq->difficulty === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ $mcq->difficulty === 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('mcqs.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update MCQ</button>
            </div>
        </form>
    </div>
</div>

<script>
function addOption() {
    const container = document.getElementById('options-container');
    const count = container.children.length;
    const letter = String.fromCharCode(65 + count);
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <span class="input-group-text" style="background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:0.75rem 0 0 0.75rem;">${letter}</span>
        <input type="text" name="options[]" class="form-control" required style="border-radius:0 0.75rem 0.75rem 0;">
    `;
    container.appendChild(div);
}
</script>
@endsection
