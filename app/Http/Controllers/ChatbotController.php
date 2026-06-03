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
        try {
            $request->validate([
                'message' => 'required|string|max:500',
                'history' => 'nullable|array|max:100',
                'history.*.role' => 'required|string',
                'history.*.content' => 'required|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed: ' . json_encode($e->errors()) . ' | Request data: ' . json_encode($request->all()));
            throw $e;
        }

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
