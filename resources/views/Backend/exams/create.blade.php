@extends('Backend.master')

@section('content')
<div class="container" style="max-width:600px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-plus-circle me-2" style="color:var(--card-accent);"></i> Add Exam
        </h2>
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.5rem;">Track an upcoming exam or deadline.</p>

        <form action="{{ route('exams.store') }}" method="POST" class="form-glass">
            @csrf
            <div class="mb-3">
                <label class="form-label">Exam Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Midterm Exam" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" name="exam_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Time (optional)</label>
                    <input type="time" name="time" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Location / Room</label>
                <input type="text" name="location" class="form-control" placeholder="e.g., Room 301">
            </div>
            <div class="mb-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="1">Low</option>
                    <option value="2">Medium-Low</option>
                    <option value="3" selected>Medium</option>
                    <option value="4">High</option>
                    <option value="5">Critical</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Topics to cover, materials to bring..."></textarea>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('exams.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Add Exam</button>
            </div>
        </form>
    </div>
</div>
@endsection
