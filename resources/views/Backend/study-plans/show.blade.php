@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>{{ $planData['title'] ?? 'Study Plan' }}</h2>
            <p>Generated {{ $studyPlan->created_at->diffForHumans() }} &bull; {{ $studyPlan->hours_per_day }} hours/day</p>
        </div>
        <a href="{{ route('study-plans.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> All Plans</a>
    </div>

    @if(isset($planData['overview']))
        <div class="glass-card mb-4">
            <p style="color:#374151;font-size:0.9rem;line-height:1.6;">{{ $planData['overview'] }}</p>
        </div>
    @endif

    @if(isset($planData['weekly_schedule']))
        @foreach($planData['weekly_schedule'] as $day)
            <div class="glass-card mb-3">
                <h5 style="color:#1e1b4b;font-weight:700;margin-bottom:1rem;">
                    <i class="bi bi-calendar-day me-2" style="color:#6366f1;"></i> {{ $day['day'] }}
                </h5>
                @if(isset($day['sessions']))
                    @foreach($day['sessions'] as $session)
                        <div class="d-flex align-items-center gap-3 p-3 mb-2 rounded-3" style="background:rgba(99,102,241,0.04);border-left:3px solid {{ $session['priority'] === 'high' ? '#ef4444' : ($session['priority'] === 'medium' ? '#f59e0b' : '#10b981') }};">
                            <div style="min-width:130px;">
                                <span style="font-size:0.75rem;font-weight:600;color:#6366f1;">{{ $session['time'] }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div style="color:#1e1b4b;font-weight:600;font-size:0.88rem;">{{ $session['subject'] }}</div>
                                <div style="color:#6b7280;font-size:0.82rem;">{{ $session['topic'] }}</div>
                                <div style="color:#9ca3af;font-size:0.78rem;">{{ $session['activity'] }}</div>
                            </div>
                            <span style="font-size:0.65rem;padding:0.15rem 0.5rem;border-radius:20px;
                                background:{{ $session['priority'] === 'high' ? '#fef2f2' : ($session['priority'] === 'medium' ? '#fffbeb' : '#ecfdf5') }};
                                color:{{ $session['priority'] === 'high' ? '#dc2626' : ($session['priority'] === 'medium' ? '#d97706' : '#059669') }};
                                font-weight:600;text-transform:uppercase;">
                                {{ $session['priority'] }}
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    @endif

    @if(isset($planData['tips']))
        <div class="glass-card mb-4">
            <h5 style="color:#1e1b4b;font-weight:700;margin-bottom:1rem;"><i class="bi bi-lightbulb me-2" style="color:#f59e0b;"></i> Study Tips</h5>
            <ul style="color:#374151;font-size:0.88rem;line-height:1.8;">
                @foreach($planData['tips'] as $tip)
                    <li>{{ $tip }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($planData['daily_goal']))
        <div class="glass-card" style="border-left:4px solid #6366f1;">
            <p style="color:#1e1b4b;font-weight:600;margin:0;"><i class="bi bi-bullseye me-2" style="color:#6366f1;"></i> {{ $planData['daily_goal'] }}</p>
        </div>
    @endif
</div>
@endsection
