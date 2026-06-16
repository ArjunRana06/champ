<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use Illuminate\Http\Request;

class SharedQuestionBankController extends Controller
{
    public function index()
    {
        $types = ['mcqs', 'true_false', 'short_answers', 'fill_blanks', 'matching', 'flashcards'];
        $shared = [];
        $myQuestions = [];
        foreach ($types as $type) {
            $model = $this->getModel($type);
            $shared[$type] = $model::where('is_public', true)->with('user', 'subject')->latest()->take(10)->get();
            $myQuestions[$type] = $model::where('user_id', auth()->id())->with('subject')->latest()->get();
        }
        return view('Backend.shared-questions.index', compact('shared', 'types', 'myQuestions'));
    }

    public function toggleVisibility(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $model = $this->getModel($request->type);
        $item = $model::where('id', $request->id)->where('user_id', auth()->id())->firstOrFail();
        $item->update(['is_public' => !$item->is_public]);

        return back()->with('success', 'Visibility updated.');
    }

    private function getModel(string $type): string
    {
        return match ($type) {
            'mcqs' => Mcq::class,
            'true_false' => TrueFalseQuestion::class,
            'short_answers' => ShortAnswer::class,
            'fill_blanks' => FillBlank::class,
            'matching' => MatchingQuestion::class,
            'flashcards' => Flashcard::class,
            default => throw new \InvalidArgumentException("Invalid question type: {$type}"),
        };
    }
}
