<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required|in:user,model',
            'history.*.content' => 'required|string'
        ]);

        $message = $request->input('message');
        $history = $request->input('history', []);

        $response = $this->chatbotService->chat($message, $history);

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Thành công'
        ]);
    }
}
