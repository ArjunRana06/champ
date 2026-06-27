<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ChatController;

class ExplainAnswerController extends Controller
{
    public function explain(Request $request, ChatbotService $chatbot)
    {
        return app(ChatController::class)->explainAnswer($request, $chatbot);
    }
}
