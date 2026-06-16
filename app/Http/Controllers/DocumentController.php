<?php
// app/Http/Controllers/DocumentController.php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Embedding;
use App\Models\Subject;
use App\Models\DocumentSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDocumentJob;
use App\Services\SummarizationService;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        // If the request expects JSON (either via header or query param)
        if ($request->ajax() || $request->query('ajax') == 1) {
            $documents = auth()->user()->documents()->with('subject')->latest()->get();
            return response()->json(['documents' => $documents]);
        }
        // Otherwise, return the normal view
        $subjects = auth()->user()->subjects;
        return view('Backend.Uploads.index', compact('subjects'));
    }

    public function upload(Request $request)
    {

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png,gif,bmp,webp,csv,rtf,odt|max:51200',
            'subject_id' => 'nullable|exists:subjects,id'
        ]);
        $file = $request->file('document');
        $path = $file->store('documents', 'public');

        $document = auth()->user()->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'subject_id' => $request->subject_id,
            'status' => 'pending'
        ]);

        // Dispatch job for processing (RAG pipeline)
        ProcessDocumentJob::dispatch($document);

        return response()->json(['success' => true, 'document' => $document]);
    }

    public function preview($id)
    {
        $document = auth()->user()->documents()->findOrFail($id);
        $path = storage_path('app/public/' . $document->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function destroy($id)
    {
        $document = auth()->user()->documents()->findOrFail($id);

        // Delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Cascade deletes chunks and embeddings via DB foreign keys
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function summarize($id, SummarizationService $summarizationService)
    {
        $document = auth()->user()->documents()->findOrFail($id);

        if ($document->status !== 'completed') {
            return response()->json(['error' => 'Document must be fully processed before summarization.'], 400);
        }

        try {
            $summary = $summarizationService->summarize($document);
            if (request()->ajax() || !request()->expectsJson()) {
                return response()->json(['success' => true, 'summary' => nl2br(e($summary->summary))]);
            }
            return back()->with('success', 'Summary generated successfully!');
        } catch (\Exception $e) {
            if (request()->ajax() || !request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Summarization failed: ' . $e->getMessage());
        }
    }
}
