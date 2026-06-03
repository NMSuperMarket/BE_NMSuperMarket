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
    // hàm này dùng để get danh mục và sản phẩm
    public function __construct()

    {
        // khởi tạo API key và API url từ file .env
        $this->provider = env('AI_PROVIDER', 'groq');
        // nếu provider là groq thì lấy API key và API url từ file .env
        if ($this->provider === 'groq') {
            $this->apiKey = env('GROQ_API_KEY');
            $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        } else {
            // nếu provider là openrouter thì lấy API key và API url từ file .env
            $this->apiKey = env('OPENROUTER_API_KEY');
            $this->apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
        }
    }

    // hàm này dùng để get danh mục và sản phẩm
    public function getProductContext()
    {
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        $products = DB::table('products')
            ->where('is_active', true)
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
Nhiệm vụ: giúp khách tìm sản phẩm, đề xuất sản phẩm phù hợp, tư vấn và trả lời câu hỏi về sản phẩm, đặc biệt là tư vấn công thức nấu ăn và đề xuất các nguyên liệu cần mua từ danh sách sản phẩm của siêu thị.
Thông tin danh mục và sản phẩm hiện có tại siêu thị: {$productContext}

Quy tắc trả lời và hành vi:
1. Thân thiện, chu đáo, dùng tiếng Việt tự nhiên và xưng hô lịch sự.
2. Từ chối câu hỏi ngoài lề (Anti-Jailbreak): Nếu khách hàng hỏi về các chủ đề hoàn toàn ngoài phạm vi của siêu thị, sản phẩm, mua sắm hoặc nấu ăn (như viết mã lập trình, giải toán học, thảo luận chính trị, v.v.), hãy lịch sự từ chối và hướng khách hàng quay lại các chủ đề liên quan đến siêu thị.
3. Khi khách hàng hỏi về một món ăn, thực đơn, công thức nấu ăn (Ví dụ: 'tôi muốn nấu canh chua', 'chiên cá thì mua gì', 'tối nay ăn gì'):
   - Hãy liệt kê các nguyên liệu cần thiết để thực hiện món ăn đó.
   - Tìm kiếm và đối chiếu xem các nguyên liệu đó có sẵn trong danh sách sản phẩm của siêu thị hay không.
   - Gợi ý chi tiết các sản phẩm đang bán tại siêu thị có thể dùng làm nguyên liệu (Ví dụ: 'Cá hồi Na Uy phi lê' hoặc 'Tôm sú biển tươi' cho món canh chua, 'Rau muống VietGAP', 'Hạt nêm Knorr', 'Nước mắm Nam Ngư', 'Dầu ăn Simply'...).
   - Đưa giá cả của các sản phẩm đang bán (lấy từ giá 'price' hoặc giá khuyến mãi 'sale_price' nếu có) vào câu trả lời để khách hàng dễ tham khảo.
   - Đối với các nguyên liệu cần thiết nhưng siêu thị KHÔNG CÓ SẴN (ví dụ: me chua, dọc mùng...), hãy nêu rõ là siêu thị hiện chưa kinh doanh mặt hàng này và gợi ý khách mua thêm ở ngoài hoặc dùng sản phẩm thay thế có sẵn tại siêu thị (nếu có).
4. Khi khách hàng hỏi về khuyến mãi, giảm giá: Hãy quét danh sách sản phẩm được cung cấp, tìm các sản phẩm có `sale_price` khác null và nhỏ hơn `price` để ưu tiên đề xuất cho khách hàng.
5. Giải đáp các câu hỏi thường gặp về siêu thị (FAQ):
   - Giờ mở cửa: Từ 06:00 đến 23:00 hàng ngày.
   - Thời gian giao hàng: Giao nhanh trong 30-60 phút ở nội thành. Miễn phí vận chuyển cho đơn hàng từ 300.000đ trở lên.
   - Phương thức thanh toán: Hỗ trợ thanh toán khi nhận hàng (COD) hoặc Chuyển khoản ngân hàng.
   - Hướng dẫn điều hướng: Giỏ hàng nằm ở góc trên bên phải website. Khách hàng có thể nhấn nút \"Thêm\" trực tiếp trên các thẻ sản phẩm do chatbot đề xuất để đưa vào giỏ hàng.
6. Ràng buộc không bịa đặt sản phẩm (Anti-Hallucination): Chỉ đề xuất và đưa giá của các sản phẩm thực tế có mặt 100% trong danh sách được cung cấp. Tuyệt đối không tự bịa ra sản phẩm hoặc giá cả không tồn tại.
7. Đưa tất cả các `id` của các sản phẩm bạn đề xuất ở trên vào trường `suggested_product_ids` trong kết quả JSON trả về. Tuyệt đối KHÔNG hiển thị mã ID của sản phẩm (ví dụ: không viết 'ID: 3', 'Mã sản phẩm: 3' hay số ID đứng một mình) trong nội dung văn bản trả lời (`reply`). Khách hàng chỉ cần nhìn thấy tên sản phẩm và giá tiền. ID sản phẩm chỉ được dùng cho hệ thống xử lý ngầm ở trường `suggested_product_ids`.
8. Trình bày câu trả lời của bạn BẮT BUỘC dưới dạng JSON object có cấu trúc như sau:
{
    \"reply\": \"câu trả lời văn bản chi tiết của bạn (hỗ trợ xuống dòng bằng \\n để trình bày đẹp mắt)\",
    \"suggested_product_ids\": [id1, id2, ...]
}";
// tạo mảng $messages để chứa tin nhắn hệ thống và lịch sử chat
        $messages = [];
        // thêm tin nhắn hệ thống vào mảng $messages
        $messages[] = [
            'role' => 'system',
            'content' => $systemInstruction
        ];
        // thêm lịch sử chat vào mảng $messages
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $msg['content']
            ];
        }
        // thêm tin nhắn của người dùng vào mảng $messages
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        // tạo payload để gửi đến API
        // sử dụng model llama-3.1-8b-instant nếu provider là groq, ngược lại sử dụng google/gemma-7b-it:free
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
            // gửi yêu cầu POST đến API của nhà cung cấp AI với payload đã chuẩn bị và xử lý phản hồi
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    ]
                ])
                ->withToken($this->apiKey) // sử dụng phương thức withToken để thêm header Authorization với giá trị Bearer token từ API key
                ->withHeaders(['Content-Type' => 'application/json']) // đảm bảo header Content-Type là application/json
                ->post($this->apiUrl, $payload);
// Note Suưử lyý traả phanr hoôiồi tuưừ API
            if ($response->successful()) {
                $data = $response->json(); // giải mã phản hồi JSON từ API thành mảng PHP
                if (isset($data['choices'][0]['message']['content'])) { 

                    $jsonText = $data['choices'][0]['message']['content'];

                    // Loại bỏ các dấu ```json...``` nếu có và trim kết quả nếu API trả về dưới dạng code block markdown
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
