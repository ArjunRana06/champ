@extends('Backend.master')

@section('content')
<div class="container text-center py-5">
    <i class="bi bi-wifi-off" style="font-size:4rem;color:var(--card-accent);"></i>
    <h2 style="color:var(--text-primary);font-weight:800;margin-top:1rem;">You're Offline</h2>
    <p style="color:var(--text-secondary);">Some features may be unavailable. Connect to the internet to continue.</p>
    <a href="{{ route('dashboard') }}" class="dark-btn mt-3"><i class="bi bi-arrow-repeat"></i> Try Again</a>
</div>
@endsection
