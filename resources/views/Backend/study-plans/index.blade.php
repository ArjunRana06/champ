@extends('Backend.master')

@push('styles')
<style>
    .sp-progress { height: 8px; border-radius: 20px; background: var(--table-header-bg); overflow: hidden; }
    .sp-progress-fill {
        height: 100%; width: 0%; border-radius: 20px; background: linear-gradient(90deg, #6366f1, #a855f7);
        transition: width 0.5s ease;
    }
    .sp-stat { text-align: center; padding: 0.4rem 0; }
    .sp-stat .val { font-size: 1.5rem; font-weight: 800; color: var(--card-accent); line-height: 1.15; }
    .sp-stat .lbl { font-size: 0.72rem; color: var(--text-secondary); font-weight: 500; margin-top: 0.25rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2><i class="bi bi-calendar-week me-2" style="color:var(--card-accent);"></i>Study Plans</h2>
            <p>AI-generated study schedules from your subjects</p>
        </div>
        <a href="{{ route('study-plans.create') }}" class="dark-btn"><i class="bi bi-magic"></i> Generate Plan</a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card py-3"><div class="sp-stat"><div class="val">{{ $totalPlans }}</div><div class="lbl">Plans Created</div></div></div>
        </div>
        <div class="col-md-4">
            <div class="glass-card py-3"><div class="sp-stat"><div class="val" style="color:#10b981;">{{ $totalHoursDay }}</div><div class="lbl">Planned Hours / Day</div></div></div>
        </div>
        <div class="col-md-4">
            <div class="glass-card py-3"><div class="sp-stat"><div class="val" style="color:#a855f7;">{{ $uniqueSubjects }}</div><div class="lbl">Subjects Covered</div></div></div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($plans as $plan)
            @php
                $planData = json_decode($plan->plan_json, true);
                $sessionCount = collect($planData['weekly_schedule'] ?? [])->sum(fn($d) => count($d['sessions'] ?? []));
            @endphp
            <div class="col-md-6 col-xl-4" data-aos="fade-up">
                <div class="glass-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 style="color:var(--text-primary);font-weight:700;margin:0;font-size:1rem;">{{ $plan->title }}</h5>
                        <form action="{{ route('study-plans.destroy', $plan) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn-soft danger py-1 px-2" style="font-size:0.72rem;" onclick="return confirm('Delete this plan?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.9rem;">
                        <i class="bi bi-clock me-1"></i>{{ $plan->created_at->format('M d, Y') }}
                        &nbsp;·&nbsp; <i class="bi bi-hourglass-split me-1"></i>{{ $plan->hours_per_day }} hrs/day
                        @if($sessionCount) &nbsp;·&nbsp; <i class="bi bi-list-check me-1"></i>{{ $sessionCount }} sessions @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);">Progress</span>
                        <span class="sp-progress-pct" data-plan="{{ $plan->id }}" style="font-size:0.75rem;font-weight:700;color:var(--card-accent);">0%</span>
                    </div>
                    <div class="sp-progress mb-3">
                        <div class="sp-progress-fill" id="progress-{{ $plan->id }}"></div>
                    </div>

                    @if($plan->subjects)
                        <div class="mb-3 d-flex flex-wrap gap-1">
                            @foreach($plan->subjects as $sub)
                                <span style="font-size:0.68rem;padding:0.15rem 0.6rem;border-radius:20px;background:var(--badge-bg);color:var(--badge-color);">{{ $sub }}</span>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('study-plans.show', $plan) }}" class="btn-soft w-100 justify-content-center mt-auto"><i class="bi bi-eye"></i> Open Plan</a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-calendar-week" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:var(--text-secondary);">No study plans yet. Generate a personalized plan from your subjects.</p>
                    <a href="{{ route('study-plans.create') }}" class="dark-btn"><i class="bi bi-magic"></i> Generate Plan</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($plans, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">{{ $plans->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.sp-progress-fill').forEach(function (bar) {
    const id = bar.id.replace('progress-', '');
    try {
        const done = JSON.parse(localStorage.getItem('studyplan_' + id + '_done') || '{}');
        const total = Object.keys(done).length;
        const filled = Object.values(done).filter(Boolean).length;
        if (total > 0) {
            const pct = Math.round((filled / total) * 100);
            bar.style.width = pct + '%';
            const label = document.querySelector('.sp-progress-pct[data-plan="' + id + '"]');
            if (label) label.textContent = pct + '%';
        }
    } catch (e) {}
});
</script>
@endpush
@endsection
