@extends('Backend.master')

@section('content')
<div class="container" style="max-width:700px;">
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:1rem;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:1rem;">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:1rem;">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-calendar-week me-2" style="color:var(--card-accent);"></i> Generate Study Plan
        </h2>
        <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1.5rem;">AI will create a personalized weekly study schedule based on your subjects and exam dates.</p>

        <form action="{{ route('study-plans.generate') }}" method="POST" class="form-glass">
            @csrf

            <div class="mb-3">
                <label class="form-label">Subjects to Include</label>
                <div class="mb-2">
                    @forelse($subjects as $subject)
                        <label class="d-inline-flex align-items-center gap-2 me-3 mb-2" style="cursor:pointer;">
                            <input type="checkbox" name="subjects[]" value="{{ $subject->name }}" class="form-check-input" @checked(in_array($subject->name, old('subjects', $subjects->pluck('name')->all())))>
                            <span style="font-size:0.85rem;">{{ $subject->name }}</span>
                        </label>
                    @empty
                        <p style="color:var(--text-muted);font-size:0.85rem;">No subjects yet.</p>
                    @endforelse
                </div>
                <div class="input-group">
                    <input type="text" name="subjects[]" class="form-control" placeholder="Or type a custom subject" value="{{ old('subjects', []) ? '' : '' }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Exam Dates (optional)</label>
                <div id="exam-dates">
                    @forelse($subjects as $subject)
                        <div class="input-group mb-2">
                            <span class="input-group-text" style="background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:0.75rem 0 0 0.75rem;">{{ $subject->name }}</span>
                            <input type="date" name="exam_dates[{{ $subject->name }}]" class="form-control" style="border-radius:0 0.75rem 0.75rem 0;" value="{{ old('exam_dates.' . $subject->name) }}">
                        </div>
                    @empty
                    @endforelse
                </div>
                <small style="color:var(--text-muted);font-size:0.75rem;">Set exam dates to prioritize subjects with upcoming exams.</small>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Hours per Day</label>
                    <input type="number" name="hours_per_day" class="form-control" value="{{ old('hours_per_day', 3) }}" min="1" max="16" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Focus Area</label>
                    <input type="text" name="focus" class="form-control" placeholder="e.g., Exam preparation, revision" value="{{ old('focus') }}">
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('study-plans.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-magic"></i> Generate Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
