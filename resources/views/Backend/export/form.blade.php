@extends('Backend.master')

@section('content')
<div class="container" style="max-width:700px;">
    <div class="glass-card p-4">
        <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;margin-bottom:0.25rem;">
            <i class="bi bi-download me-2" style="color:#6366f1;"></i> Export Questions
        </h2>
        <p style="color:#6b7280;font-size:0.88rem;margin-bottom:1.5rem;">Download your questions in various formats.</p>

        <div class="row g-3 mb-4">
            @foreach($counts as $key => $count)
            <div class="col-4 col-md-2 text-center">
                <div style="font-size:1.5rem;font-weight:700;color:#6366f1;">{{ $count }}</div>
                <small style="color:#6b7280;font-size:0.7rem;">{{ str_replace('_', ' ', ucfirst($key)) }}</small>
            </div>
            @endforeach
        </div>

        <form id="exportForm" method="POST" class="form-glass">
            @csrf
            <div class="mb-3">
                <label class="form-label">Question Type</label>
                <select name="type" class="form-select" required>
                    <option value="mcqs">Multiple Choice</option>
                    <option value="true_false">True / False</option>
                    <option value="short_answers">Short Answer</option>
                    <option value="fill_blanks">Fill in the Blank</option>
                    <option value="matching">Matching</option>
                    <option value="flashcards">Flashcards</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Format</label>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" formaction="{{ route('export.pdf') }}" class="dark-btn"><i class="bi bi-filetype-pdf"></i> PDF</button>
                    <button type="submit" formaction="{{ route('export.csv') }}" class="dark-btn dark-btn-outline"><i class="bi bi-filetype-csv"></i> CSV</button>
                    <button type="submit" formaction="{{ route('export.json') }}" class="dark-btn dark-btn-outline"><i class="bi bi-filetype-json"></i> JSON</button>
                    <button type="submit" formaction="{{ route('export.anki') }}" class="btn-soft"><i class="bi bi-card-list"></i> Anki (TSV)</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
