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
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
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

        $total = $subjects->count() + $documents->count() + $chunks->count()
            + $mcqs->count() + $trueFalse->count() + $shortAnswers->count()
            + $fillBlanks->count() + $matching->count() + $flashcards->count();

        $this->recordHistory($query, $total);

        $recentSearches = SearchHistory::forUser($userId)
            ->where('query', '!=', $query)
            ->orderBy('searched_at', 'desc')
            ->limit(6)
            ->get();

        return view('Backend.search-results', compact(
            'query', 'subjects', 'documents', 'chunks',
            'mcqs', 'trueFalse', 'shortAnswers', 'fillBlanks',
            'matching', 'flashcards', 'recentSearches'
        ));
    }

    public function history(Request $request)
    {
        $userId = auth()->id();
        $term = trim((string) $request->get('q', ''));

        $query = SearchHistory::forUser($userId);

        if ($term !== '') {
            $query->where('query', 'LIKE', '%' . addcslashes($term, '%_') . '%');
        }

        $history = $query
            ->orderBy('searched_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'query' => $item->query,
                'result_count' => $item->result_count,
                'searched_at' => $item->searched_at?->diffForHumans() ?? 'just now',
            ]);

        return response()->json(['history' => $history]);
    }

    public function destroy($id)
    {
        SearchHistory::where('id', $id)->where('user_id', auth()->id())->delete();

        return response()->json(['success' => true]);
    }

    public function clear(Request $request)
    {
        SearchHistory::where('user_id', auth()->id())->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Search history cleared.');
    }

    private function recordHistory($query, $resultCount)
    {
        $userId = auth()->id();

        SearchHistory::updateOrCreate(
            ['user_id' => $userId, 'query' => $query],
            [
                'result_count' => $resultCount,
                'searched_at' => now(),
            ]
        );

        $histories = SearchHistory::forUser($userId)
            ->orderBy('searched_at', 'desc')
            ->get();

        if ($histories->count() > 50) {
            $histories->slice(50)->each->delete();
        }
    }
}
