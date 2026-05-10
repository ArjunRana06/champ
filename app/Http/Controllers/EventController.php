<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        $activities = Activity::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();
        // dd($activities);
        return view('Backend.Events.index', compact('activities'));
    }

    public function create()
    {
        return view('Backend.Events.create');
    }

    public function store(Request $request)
    {
        // === DEBUG: log everything that arrives ===
        Log::info('STORE CALLED', [
            'all_input' => $request->all(),
            'has_file' => $request->hasFile('file'),
            'files' => $_FILES ?? 'no files',
            'content_length' => strlen($request->input('content', '')),
        ]);

        // Validate
        $validator = validator($request->all(), [
            'content' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:204800', // 200MB
            'emotion' => 'nullable|string|max:50',
            'confidence' => 'nullable|numeric|min:0|max:1',
        ], [
            'file.max' => 'File size must not exceed 200MB.',
            'content.max' => 'Message cannot exceed 5000 characters.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        // Prevent empty submission
        if (empty($request->content) && !$request->hasFile('file')) {
            return back()->withErrors(['content' => 'Please enter a message or select a file.'])->withInput();
        }

        // === DEBUG: check file if present ===
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Log::info('File detected', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'error_message' => $this->getUploadErrorMessage($file->getError()),
                'mime' => $file->getMimeType(),
                'valid' => $file->isValid(),
            ]);

            if (!$file->isValid()) {
                return back()->withErrors(['file' => 'Upload failed: ' . $this->getUploadErrorMessage($file->getError())])->withInput();
            }
        } else {
            Log::info('No file in request');
        }

        // Auto‑detect type
        $type = 'text';
        if ($request->hasFile('file')) {
            $mime = $request->file('file')->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $type = 'video';
            } elseif (str_starts_with($mime, 'audio/')) {
                $type = 'voice';
            }
            Log::info('Detected type', ['type' => $type, 'mime' => $mime]);
        }

        $data = [
            'user_id' => auth()->id(),
            'type' => $type,
        ];

        if (in_array($type, ['text', 'emoji', 'emotion'])) {
            $data['content'] = ['text' => $request->input('content', '')];
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            // Check if storage disk is writable
            try {
                $disk = Storage::disk('public');
                $testPath = 'test_write_' . time() . '.txt';
                $disk->put($testPath, 'test');
                $disk->delete($testPath);
                Log::info('Storage disk is writable');
            } catch (\Exception $e) {
                Log::error('Storage disk not writable: ' . $e->getMessage());
                return back()->withErrors(['file' => 'Storage error. Please run "php artisan storage:link".'])->withInput();
            }

            $path = $file->store($type, 'public');
            if (!$path) {
                Log::error('Failed to store file');
                return back()->withErrors(['file' => 'Could not save file. Check disk permissions.'])->withInput();
            }

            $data['file_path'] = $path;
            Log::info('File stored', ['path' => $path, 'full_url' => Storage::url($path)]);

            if (empty($data['content'])) {
                $data['content'] = ['text' => "Uploaded $type: $originalName"];
            }
        }

        $activity = Activity::create($data);
        Log::info('Activity created', ['id' => $activity->id, 'type' => $activity->type]);

        if ($request->filled('emotion')) {
            $activity->emotions()->create([
                'emotion' => $request->emotion,
                'confidence_score' => $request->confidence,
            ]);
            Log::info('Emotion saved', ['emotion' => $request->emotion]);
        }

        return redirect()->route('events.index')->with('success', 'Memory added successfully.');
    }

    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload.';
            default:
                return 'Unknown upload error.';
        }
    }

    public function destroy(Activity $event)
    {
        // Manual authorization
        if (auth()->id() !== $event->user_id) abort(403);

        if ($event->file_path && Storage::disk('public')->exists($event->file_path)) {
            Storage::disk('public')->delete($event->file_path);
        }

        $event->emotions()->delete();
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Memory deleted.');
    }

    public function update(Request $request, Activity $event)
    {
        // Authorization: only the owner can edit
        if (auth()->id() !== $event->user_id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'content' => 'required_if:type,text,emoji|string|max:1000',
            'emotion' => 'nullable|string|max:50',
        ]);

        // Update the JSON `content` column correctly
        $currentContent = $event->content ?? [];
        $currentContent['text'] = $request->input('content');
        $event->content = $currentContent;
        $event->save();

        // Update or create emotion
        if ($request->has('emotion') && !empty($request->emotion)) {
            $event->emotions()->updateOrCreate(
                ['activity_id' => $event->id],
                ['emotion' => $request->input('emotion')]
            );
        } elseif ($request->has('emotion') && empty($request->emotion)) {
            // Remove emotion if user cleared it
            $event->emotions()->delete();
        }

        // Return the updated data
        return response()->json([
            'success' => true,
            'new_content' => $request->input('content'),
            'new_emotion' => $request->input('emotion'),
        ]);
    }

    public function filterByType($type)
    {
        $query = Activity::where('user_id', Auth::id())->orderBy('created_at', 'desc');

        if ($type === 'images') {
            $query->where('type', 'image');
        } elseif ($type === 'videos') {
            $query->where('type', 'video');
        } elseif ($type === 'chats') {
            $query->whereNotIn('type', ['image', 'video']);
        } else {
            return response()->json([]);
        }

        $activities = $query->get();

        // Return a partial HTML view (you'll create this)
        return view('Backend.Events.partials.filtered_items', compact('activities'))->render();
    }
}
