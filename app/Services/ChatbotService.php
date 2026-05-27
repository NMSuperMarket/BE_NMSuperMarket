<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $apiUrl;
    protected $apiKey;
    protected $provider;

    public function __construct()
    {
        $this->provider = env('AI_PROVIDER', 'groq');
        
        if ($this->provider === 'groq') {
            $this->apiKey = env('GROQ_API_KEY');
            $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        } else {
            // OpenRouter fallback
            $this->apiKey = env('OPENROUTER_API_KEY');
            $this->apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
        }
    }

    public function getProductContext()
    {
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        $products = DB::table('products')
            ->where('is_active', true)
            ->orderBy('sold_count', 'desc')
            ->limit(30)
            ->select('id', 'name', 'price', 'sale_price')
            ->get();

        return json_encode([
            'categories' => $categories,
            'products' => $products
        ], JSON_UNESCAPED_UNICODE);
    }

    public function chat(string $message, array $history = [])
    {
        $productContext = $this->getProductContext();

        $systemInstruction = "Bạn là trợ lý mua hàng thông minh của NMSuperMarket - siêu thị tiện lợi.
Nhiệm vụ: giúp khách tìm sản phẩm, đề xuất sản phẩm phù hợp, trả lời câu hỏi về sản phẩm.
Thông tin sản phẩm hiện có: {$productContext}

Quy tắc trả lời:
- Ngắn gọn, thân thiện, dùng tiếng Việt.
- Khi đề xuất sản phẩm, trả về đúng product_id từ danh sách trên.
- Nếu hỏi về sản phẩm không có trong danh sách, xin lỗi và gợi ý sản phẩm tương tự.
- Không tự bịa thông tin giá cả.

Trình bày câu trả lời của bạn BẮT BUỘC dưới dạng JSON object có cấu trúc như sau:
{
    \"reply\": \"câu trả lời văn bản của bạn\",
    \"suggested_product_ids\": [id1, id2]
}";

        $messages = [];
        $messages[] = [
            'role' => 'system',
            'content' => $systemInstruction
        ];
        
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $msg['content']
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $payload = [
            'model' => $this->provider === 'groq' ? 'llama-3.1-8b-instant' : 'google/gemma-7b-it:free',
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.2
        ];

        try {
            if (empty($this->apiKey)) {
                Log::error('Chatbot: API_KEY chưa được cấu hình trong .env');
                return [
                    'reply' => 'Chatbot chưa được cấu hình. Vui lòng liên hệ quản trị viên.',
                    'suggested_products' => []
                ];
            }

            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    ]
                ])
                ->withToken($this->apiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['choices'][0]['message']['content'])) {
                    $jsonText = $data['choices'][0]['message']['content'];
                    
                    // Cleanup possible markdown wrapping
                    $jsonText = preg_replace('/```(?:json)?(.*?)```/s', '$1', trim($jsonText));
                    $jsonText = trim($jsonText);
                    $decoded = json_decode($jsonText, true);

                    if (!$decoded) {
                        Log::error('Chatbot: JSON decode thất bại. Raw: ' . $jsonText);
                        return [
                            'reply' => 'Xin lỗi, tôi không thể xử lý yêu cầu lúc này.',
                            'suggested_products' => []
                        ];
                    }

                    $suggestedIds = $decoded['suggested_product_ids'] ?? [];
                    $products = [];
                    if (!empty($suggestedIds)) {
                        $products = DB::table('products')->whereIn('id', $suggestedIds)->get();
                    }

                    return [
                        'reply' => $decoded['reply'] ?? 'Xin lỗi, tôi chưa hiểu ý bạn.',
                        'suggested_products' => $products
                    ];
                }
            }
            
            Log::error('AI API Error: HTTP ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Chatbot exception: ' . $e->getMessage());
        }

        return [
            'reply' => 'Hệ thống đang bận, vui lòng thử lại sau.',
            'suggested_products' => []
        ];
    }
}
