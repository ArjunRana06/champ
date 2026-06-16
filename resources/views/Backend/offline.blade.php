@extends('Backend.master')

@section('content')
<div class="container text-center py-5">
    <i class="bi bi-wifi-off" style="font-size:4rem;color:#6366f1;"></i>
    <h2 style="color:#1e1b4b;font-weight:800;margin-top:1rem;">You're Offline</h2>
    <p style="color:#6b7280;">Some features may be unavailable. Connect to the internet to continue.</p>
    <a href="{{ route('dashboard') }}" class="dark-btn mt-3"><i class="bi bi-arrow-repeat"></i> Try Again</a>
</div>
@endsection
