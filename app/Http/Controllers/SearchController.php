<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return redirect()->back()->with('error', 'Please enter at least 2 characters to search.');
        }

        $userId = auth()->id();
        $escapedQuery = '%' . addcslashes($query, '%_') . '%';

        $subjects = Subject::where('user_id', $userId)
            ->where(function ($q) use ($escapedQuery) {
                $q->where('name', 'LIKE', $escapedQuery)
                  ->orWhere('code', 'LIKE', $escapedQuery)
                  ->orWhere('semester', 'LIKE', $escapedQuery);
            })->get();

        $documents = Document::where('user_id', $userId)
            ->where('original_name', 'LIKE', $escapedQuery)
            ->get();

        $chunks = DocumentChunk::whereHas('document', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('content', 'LIKE', $escapedQuery)
          ->with('document')
          ->limit(20)
          ->get();

        $mcqs = Mcq::where('user_id', $userId)
            ->where('question', 'LIKE', $escapedQuery)
            ->get();

        $trueFalse = TrueFalseQuestion::where('user_id', $userId)
            ->where('statement', 'LIKE', $escapedQuery)
            ->get();

        $shortAnswers = ShortAnswer::where('user_id', $userId)
            ->where('question', 'LIKE', $escapedQuery)
            ->get();

        $fillBlanks = FillBlank::where('user_id', $userId)
            ->where('sentence_with_blanks', 'LIKE', $escapedQuery)
            ->get();

        $matching = MatchingQuestion::where('user_id', $userId)
            ->where('left_items', 'LIKE', $escapedQuery)
            ->get();

        $flashcards = Flashcard::where('user_id', $userId)
            ->where(function ($q) use ($escapedQuery) {
                $q->where('front', 'LIKE', $escapedQuery)
                  ->orWhere('back', 'LIKE', $escapedQuery);
            })->get();

        return view('Backend.search-results', compact(
            'query', 'subjects', 'documents', 'chunks',
            'mcqs', 'trueFalse', 'shortAnswers', 'fillBlanks',
            'matching', 'flashcards'
        ));
    }
}
