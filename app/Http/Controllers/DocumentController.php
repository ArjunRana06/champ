<?php
// app/Http/Controllers/DocumentController.php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDocumentJob;

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
            'document' => 'required|file|mimes:pdf,docx,ppt,pptx,txt,jpg,png|max:20480',
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
}
