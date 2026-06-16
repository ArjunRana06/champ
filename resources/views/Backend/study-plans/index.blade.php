@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Study Plans</h2>
            <p>AI-generated study schedules from your subjects</p>
        </div>
        <a href="{{ route('study-plans.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate Plan</a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="row g-4">
        @forelse($plans as $plan)
            <div class="col-md-6" data-aos="fade-up">
                <div class="glass-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 style="color:#1e1b4b;font-weight:700;margin:0;">{{ $plan->title }}</h5>
                            <small style="color:#9ca3af;">{{ $plan->created_at->format('M d, Y') }} &bull; {{ $plan->hours_per_day }} hrs/day</small>
                        </div>
                        <form action="{{ route('study-plans.destroy', $plan) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn-soft danger py-1 px-2" style="font-size:0.75rem;" onclick="return confirm('Delete this plan?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @if($plan->subjects)
                        <div class="mb-2">
                            @foreach($plan->subjects as $sub)
                                <span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#eef2ff;color:#6366f1;margin-right:0.3rem;">{{ $sub }}</span>
                            @endforeach
                        </div>
                    @endif
                    <a href="{{ route('study-plans.show', $plan) }}" class="btn-soft w-100 justify-content-center mt-2"><i class="bi bi-eye"></i> View Plan</a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-calendar-week" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:#6b7280;">No study plans yet. Generate a personalized plan from your subjects.</p>
                    <a href="{{ route('study-plans.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate Plan</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($plans, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">{{ $plans->links() }}</div>
    @endif
</div>
@endsection
