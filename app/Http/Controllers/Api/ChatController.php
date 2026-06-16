<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'persona' => 'nullable|in:default,strict,friendly,socratic,simplifier',
        ]);

        $userId = auth()->id();
        $historyKey = 'chat_history_' . $userId;
        $personaKey = 'chat_persona_' . $userId;
        $history = session()->get($historyKey, []);

        $persona = $request->persona ?? session()->get($personaKey, 'default');
        session()->put($personaKey, $persona);

        $answer = $chatbot->chat($request->message, $history, $persona);

        // Append new exchange
        $history[] = ['role' => 'user', 'content' => $request->message];
        $history[] = ['role' => 'assistant', 'content' => $answer];
        // Keep only last 20 messages (10 exchanges)
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
        session()->put($historyKey, $history);

        return response()->json(['response' => $answer]);
    }

    public function showChatPage()
    {
        return view('Backend.Ai.chat');
    }

    public function explainAnswer(Request $request, ChatbotService $chatbot)
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'user_answer' => 'required|string|max:5000',
            'correct_answer' => 'nullable|string|max:5000',
            'question_type' => 'nullable|string|max:50',
        ]);

        $message = "I answered this question: \"{$request->question}\"\n";
        $message .= "My answer was: \"{$request->user_answer}\"\n";
        if ($request->correct_answer) {
            $message .= "The correct answer is: \"{$request->correct_answer}\"\n";
        }
        $message .= "\nPlease explain why my answer is " . ($request->correct_answer ? 'wrong and explain the correct answer' : 'correct or incorrect') . ". Be detailed and educational.";

        $explanation = $chatbot->chat($message, [], 'friendly');

        return response()->json(['explanation' => $explanation]);
    }
}
