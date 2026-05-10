<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request, ChatbotService $chatbot)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        $userId = auth()->id();
        // Use a session key that includes the user ID – each user has their own history
        $historyKey = 'chat_history_' . $userId;
        $history = session()->get($historyKey, []);

        $answer = $chatbot->chat($request->message, $history);

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
}
