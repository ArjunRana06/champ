<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ExplainAnswerController extends Controller
{
    public function explain(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'user_answer' => 'required|string|max:5000',
            'correct_answer' => 'nullable|string|max:5000',
        ]);

        $msg = "Question: \"{$request->question}\"\nMy answer: \"{$request->user_answer}\"";
        if ($request->correct_answer) {
            $msg .= "\nCorrect answer: \"{$request->correct_answer}\"";
        }
        $msg .= "\n\nPlease explain the correct answer and why my answer was " . ($request->correct_answer ? 'incorrect' : 'correct') . ". Be thorough and educational.";

        $explanation = $chatbot->chat($msg, [], 'friendly');

        return response()->json(['explanation' => $explanation]);
    }
}
