<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentJob;
use App\Services\NotificationService;
use App\Services\SummarizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        if ($request->ajax() || $request->query('ajax') == 1) {
            $documents = auth()->user()->documents()->with('subject')->latest()->get();

            return response()->json(['documents' => $documents]);
        }
        $subjects = auth()->user()->subjects;

        return view('Backend.Uploads.index', compact('subjects'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:51200',
            'subject_id' => ['nullable', Rule::exists('subjects', 'id')->where('user_id', auth()->id())],
        ]);
        $file = $request->file('document');

        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'text/csv', 'text/rtf',
            'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
            'application/octet-stream',
            'application/zip',
        ];

        $mime = $file->getMimeType();
        if (! in_array($mime, $allowedMimes, true)) {
            return response()->json([
                'error' => 'This file type is not supported. Supported formats: PDF, Word (.doc/.docx), PowerPoint (.ppt/.pptx), Excel (.xls/.xlsx), CSV, TXT, RTF, and images (JPG, PNG, GIF, BMP, WEBP).',
                'message' => 'This file type is not supported. Supported formats: PDF, Word (.doc/.docx), PowerPoint (.ppt/.pptx), Excel (.xls/.xlsx), CSV, TXT, RTF, and images (JPG, PNG, GIF, BMP, WEBP).',
            ], 422);
        }

        $path = $file->store('documents', 'public');

        $document = auth()->user()->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'subject_id' => $request->subject_id,
            'status' => 'pending',
        ]);

        ProcessDocumentJob::dispatch($document);

        $this->notificationService->notifyDocumentUploaded(auth()->id(), $document->original_name);

        return response()->json(['success' => true, 'document' => $document]);
    }

    public function preview($id)
    {
        $document = auth()->user()->documents()->findOrFail($id);
        $path = Storage::disk('public')->path($document->file_path);

        if (! file_exists($path)) {
            abort(404);
        }

        $activeTypes = [
            'text/html',
            'application/xhtml+xml',
            'image/svg+xml',
            'text/xml',
            'application/xml',
        ];
        $isActive = in_array($document->mime_type, $activeTypes, true);
        $mime = $isActive ? 'application/octet-stream' : $document->mime_type;

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($isActive ? 'attachment' : 'inline').'; filename="'.addslashes($document->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    public function destroy($id)
    {
        $document = auth()->user()->documents()->findOrFail($id);
        $name = $document->original_name;

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        $this->notificationService->notifyDocumentDeleted(auth()->id(), $name);

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

            $this->notificationService->notifyDocumentSummarized(auth()->id(), $document->original_name);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['success' => true, 'summary' => nl2br(e($summary->summary))]);
            }

            return back()->with('success', 'Summary generated successfully!');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Summarization failed: '.$e->getMessage());
        }
    }

    public function status($id)
    {
        $document = auth()->user()->documents()->findOrFail($id);

        return response()->json([
            'id' => $document->id,
            'status' => $document->status,
            'progress' => $document->processing_progress ?? 0,
            'message' => $document->processing_message ?? '',
            'total_chunks' => $document->total_chunks ?? 0,
        ]);
    }

    public function retry($id)
    {
        $document = auth()->user()->documents()->findOrFail($id);

        if ($document->status === 'processing' || $document->status === 'pending') {
            return response()->json(['error' => 'Document is already being processed.'], 400);
        }

        $document->update([
            'status' => 'pending',
            'processing_progress' => 0,
            'processing_message' => 'Retrying...',
        ]);

        ProcessDocumentJob::dispatch($document);

        $this->notificationService->notifyDocumentRetried(auth()->id(), $document->original_name);

        return response()->json(['success' => true, 'message' => 'Document re-queued for processing.']);
    }
}
