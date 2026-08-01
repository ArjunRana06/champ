<?php

namespace App\Traits;

use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Auth;

trait HasRelevantChunks
{
    public function getRelevantChunks($subjectId, $documentId, $topic = null, $limit = 20): array
    {
        $userId = Auth::id();

        $query = DocumentChunk::whereHas('document', function ($q) use ($userId, $subjectId, $documentId) {
            $q->where('user_id', $userId)->where('status', 'completed');
            if ($subjectId) $q->where('subject_id', $subjectId);
            if ($documentId) $q->where('id', $documentId);
        });

        if ($topic) {
            $keywords = preg_split('/[\s,;:.!?()]+/u', $topic);
            foreach ($keywords as $keyword) {
                if (mb_strlen(trim($keyword)) > 2) {
                    $query->where('content', 'LIKE', '%' . addcslashes(trim($keyword), '%_') . '%');
                }
            }
        }

        $chunks = $query->limit($limit)->get();

        if ($chunks->isEmpty()) {
            $query = DocumentChunk::whereHas('document', function ($q) use ($userId, $subjectId) {
                $q->where('user_id', $userId)->where('status', 'completed');
                if ($subjectId) $q->where('subject_id', $subjectId);
            })->limit($limit);
            $chunks = $query->get();
        }

        return $chunks->pluck('content')->toArray();
    }
}
