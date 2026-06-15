@extends('Backend.master')

@section('content')
<div class="container" style="max-width:580px;">
    <div class="glass-card p-4" data-aos="fade-up">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-plus-circle me-2" style="color:#6366f1;"></i> Create New Subject
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Add a new subject to organize your study materials.</p>

        <form method="POST" action="{{ route('subjects.store') }}" class="form-glass">
            @csrf

            <div class="mb-3">
                <label class="form-label">Subject Name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g., Data Structures">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Subject Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g., CS301">
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Semester</label>
                <input type="text" name="semester" class="form-control @error('semester') is-invalid @enderror" value="{{ old('semester') }}" placeholder="e.g., 3rd Sem">
                @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('subjects.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-check-lg"></i> Create Subject</button>
            </div>
        </form>
    </div>
</div>
@endsection
