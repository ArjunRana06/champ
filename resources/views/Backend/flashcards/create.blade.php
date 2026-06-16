@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-card-text me-2" style="color:var(--card-accent);"></i> Generate Flashcards
        </h2>
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.5rem;">Create flashcards (Q&A pairs) from your study materials using AI.</p>

        <form action="{{ route('flashcards.generate') }}" method="POST" class="form-glass">
            @csrf

            <div class="mb-3">
                <label class="form-label">Select Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- All Subjects --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Specific Topic</label>
                <input type="text" name="topic" class="form-control" placeholder="e.g., Key Terms, Dates, Formulas">
                <small style="color:var(--text-muted);font-size:0.75rem;">Leave empty to use general content from the selected subject.</small>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Number of Flashcards</label>
                    <input type="number" name="count" class="form-control" value="5" min="1" max="20" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Difficulty Level</label>
                    <select name="difficulty" class="form-select">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('flashcards.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-magic"></i> Generate</button>
            </div>
        </form>
    </div>
</div>
@endsection
