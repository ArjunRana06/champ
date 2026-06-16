@extends('Backend.master')

@section('content')
<div class="container" style="max-width:650px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-play-circle me-2" style="color:#6366f1;"></i> Start a Quiz
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Practice with questions generated from your study materials.</p>

        <div class="row g-3 mb-4">
            <div class="col-4 text-center">
                <div class="glass-card py-2"><strong style="color:#6366f1;font-size:1.3rem;">{{ $stats['mcqs'] }}</strong><br><small style="color:#6b7280;">MCQs</small></div>
            </div>
            <div class="col-4 text-center">
                <div class="glass-card py-2"><strong style="color:#a855f7;font-size:1.3rem;">{{ $stats['true_false'] }}</strong><br><small style="color:#6b7280;">T/F</small></div>
            </div>
            <div class="col-4 text-center">
                <div class="glass-card py-2"><strong style="color:#f59e0b;font-size:1.3rem;">{{ $stats['mcqs'] + $stats['true_false'] }}</strong><br><small style="color:#6b7280;">Total Available</small></div>
            </div>
        </div>

        <form action="{{ route('quiz-attempts.start') }}" method="POST" class="form-glass">
            @csrf

            <div class="mb-3">
                <label class="form-label">Question Type</label>
                <select name="type" class="form-select" required>
                    <option value="mcq">Multiple Choice</option>
                    <option value="true-false">True / False</option>
                    <option value="mixed">Mixed (MCQ + T/F)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Subject (optional)</label>
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Number of Questions</label>
                    <input type="number" name="count" class="form-control" value="10" min="1" max="50" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Time Limit (minutes, 0 = no limit)</label>
                    <input type="number" name="time_limit" class="form-control" value="0" min="0" max="180">
                </div>
            </div>

            <div class="mb-3">
                <label class="d-flex align-items-center gap-2" style="cursor:pointer;">
                    <input type="checkbox" name="is_exam_mode" value="1" class="form-check-input">
                    <span style="font-size:0.85rem;font-weight:500;">Exam Mode — no backtracking, strict timer</span>
                </label>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('quiz-attempts.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-play-circle"></i> Start Quiz</button>
            </div>
        </form>
    </div>
</div>
@endsection
