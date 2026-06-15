@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>My Subjects</h2>
            <p>Organize your study materials by subjects</p>
        </div>
        <a href="{{ route('subjects.create') }}" class="dark-btn">
            <i class="bi bi-plus-circle"></i> Add Subject
        </a>
    </div>

    <div class="row g-4">
        @forelse($subjects as $subject)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                <div class="glass-card h-100" style="cursor:default;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 style="color:#1e1b4b;font-weight:700;margin:0;font-size:1.1rem;">{{ $subject->name }}</h5>
                            <div class="d-flex gap-2 mt-2">
                                @if($subject->code)
                                    <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;background:#eef2ff;color:#6366f1;">{{ $subject->code }}</span>
                                @endif
                                @if($subject->semester)
                                    <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;background:#ecfeff;color:#0891b2;">{{ $subject->semester }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn-soft py-1 px-2" style="font-size:0.8rem;" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('subjects.edit', $subject) }}"><i class="bi bi-pencil"></i> Edit</a></li>
                                <li><button class="dropdown-item" style="color:#dc2626;" onclick="confirmDelete({{ $subject->id }})"><i class="bi bi-trash"></i> Delete</button></li>
                            </ul>
                        </div>
                    </div>
                    <div style="color:#6b7280;font-size:0.88rem;">
                        <i class="bi bi-files me-1" style="color:#6366f1;"></i> {{ $subject->documents->count() }} documents
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-journal-bookmark-fill" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:#6b7280;">No subjects yet. Create your first subject to organize uploads.</p>
                    <a href="{{ route('subjects.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Create Subject</a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this subject? All associated documents will also be deleted.')) {
            const form = document.getElementById('deleteForm');
            form.action = '/subjects/' + id;
            form.submit();
        }
    }
</script>
@endsection
