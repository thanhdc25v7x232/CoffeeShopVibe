<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Store;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use Throwable;

class AiAssistantService
{
    private const MAX_QUESTION_LENGTH = 800;
    private const FALLBACK_MESSAGE = 'Xin lỗi, trợ lý AI đang gặp sự cố nên chưa thể trả lời ngay lúc này. Bạn vui lòng thử lại sau hoặc dùng khung "Chat với shop" để được nhân viên hỗ trợ trực tiếp.';
    private const GEMINI_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/interactions';

    protected AiConversation $conversationModel;
    protected Product $productModel;
    protected Promotion $promotionModel;
    protected Store $storeModel;

    public function __construct(PDO $pdo)
    {
        $this->conversationModel = new AiConversation($pdo);
        $this->productModel = new Product($pdo);
        $this->promotionModel = new Promotion($pdo);
        $this->storeModel = new Store($pdo);
    }

    public function ask(string $sessionKey, ?int $customerId, string $question): string
    {
        $question = trim($question);

        if ($question === '') {
            return 'Bạn muốn hỏi Vibe điều gì nè?';
        }

        if (mb_strlen($question) > self::MAX_QUESTION_LENGTH) {
            return 'Câu hỏi hơi dài, bạn rút gọn lại giúp mình nhé (tối đa ' . self::MAX_QUESTION_LENGTH . ' ký tự).';
        }

        $this->conversationModel->append($sessionKey, $customerId, 'user', $question);

        $apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        $interactionId = null;

        if ($apiKey === '') {
            error_log('[AiAssistantService] Thiếu GEMINI_API_KEY trong .env, không thể gọi Gemini API.');
            $reply = self::FALLBACK_MESSAGE;
        } else {
            try {
                $previousInteractionId = $this->conversationModel->getLastInteractionId($sessionKey);

                $client = new Client(['timeout' => 20]);
                $response = $client->post(self::GEMINI_ENDPOINT, [
                    'headers' => [
                        'x-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => array_filter([
                        'model' => $_ENV['GEMINI_MODEL'] ?? 'gemini-2.5-flash-lite',
                        'system_instruction' => $this->buildSystemPrompt($question),
                        'input' => $question,
                        'previous_interaction_id' => $previousInteractionId,
                    ], fn($value) => $value !== null && $value !== ''),
                ]);

                $data = json_decode((string)$response->getBody(), true);
                $interactionId = $data['id'] ?? null;
                $reply = $this->extractText($data);

                if ($reply === '') {
                    $reply = self::FALLBACK_MESSAGE;
                }
            } catch (GuzzleException $e) {
                error_log('[AiAssistantService] Lỗi API Gemini: ' . $e->getMessage());
                $reply = self::FALLBACK_MESSAGE;
            } catch (Throwable $e) {
                error_log('[AiAssistantService] Lỗi không xác định khi gọi Gemini API: ' . $e->getMessage());
                $reply = self::FALLBACK_MESSAGE;
            }
        }

        $this->conversationModel->append($sessionKey, $customerId, 'assistant', $reply, $interactionId);

        return $reply;
    }

    /**
     * Gộp text từ các step kiểu 'model_output' trong response Gemini Interactions API.
     */
    private function extractText(?array $data): string
    {
        if (!$data || empty($data['steps'])) {
            return '';
        }

        $parts = [];
        foreach ($data['steps'] as $step) {
            if (($step['type'] ?? '') !== 'model_output') {
                continue;
            }
            foreach ($step['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                    $parts[] = $block['text'];
                }
            }
        }

        return trim(implode("\n", $parts));
    }

    private function buildSystemPrompt(string $question): string
    {
        return "Bạn là trợ lý ảo của Vibe Coffee Shop, một quán cà phê/trà sữa. "
            . "Chỉ trả lời dựa trên DỮ LIỆU THỰC TẾ được cung cấp bên dưới (sản phẩm, giá, khuyến mãi, cửa hàng). "
            . "Nếu câu hỏi không liên quan đến quán hoặc dữ liệu không có thông tin để trả lời, hãy nói rõ là bạn không có thông tin đó, "
            . "đừng bịa ra sản phẩm/giá/khuyến mãi không có trong dữ liệu. "
            . "Trả lời ngắn gọn, thân thiện, bằng tiếng Việt, phù hợp hiển thị trong khung chat nhỏ (không dùng markdown phức tạp).\n\n"
            . $this->buildContext($question);
    }

    private function buildContext(string $question): string
    {
        $products = $this->productModel->searchForAssistant($question, 6);
        $productLines = empty($products)
            ? ['(Chưa có sản phẩm nào trong hệ thống)']
            : array_map(function ($p) {
                $stock = (int)$p['sp_tonkho'] > 0 ? 'còn hàng' : 'tạm hết hàng';
                return sprintf(
                    '- %s (%s): %s đ%s — %s [%s]',
                    $p['sp_ten'],
                    $p['l_ten'] ?? 'chưa phân loại',
                    number_format((float)$p['gia_hien_thi'], 0, ',', '.'),
                    $p['km_phantram'] ? sprintf(' (giảm %d%% từ %s đ)', $p['km_phantram'], number_format((float)$p['sp_gia'], 0, ',', '.')) : '',
                    $p['sp_mota'] ?: 'không có mô tả',
                    $stock
                );
            }, $products);

        $categories = $this->productModel->getCategories();
        $categoryLines = array_map(fn($c) => '- ' . $c['l_ten'], $categories);

        $promotions = $this->promotionModel->getActive();
        $promotionLines = empty($promotions)
            ? ['(Hiện không có khuyến mãi nào đang áp dụng)']
            : array_map(
                fn($p) => sprintf('- %s: giảm %d%% (đến hết %s) — %s', $p['km_ten'], $p['km_phantram'], $p['km_ngayketthuc'], $p['km_mota']),
                $promotions
            );

        $stores = $this->storeModel->getAllActiveStores();
        $storeLines = empty($stores)
            ? ['(Chưa có thông tin cửa hàng)']
            : array_map(
                fn($s) => sprintf('- %s: %s (giờ mở cửa %s - %s, SĐT %s)', $s['ch_ten'], $s['ch_diachi'], $s['gio_mo_cua'], $s['gio_dong_cua'], $s['ch_sdt']),
                $stores
            );

        return "SẢN PHẨM LIÊN QUAN ĐẾN CÂU HỎI:\n" . implode("\n", $productLines)
            . "\n\nDANH SÁCH LOẠI SẢN PHẨM:\n" . implode("\n", $categoryLines)
            . "\n\nKHUYẾN MÃI ĐANG ÁP DỤNG:\n" . implode("\n", $promotionLines)
            . "\n\nDANH SÁCH CỬA HÀNG:\n" . implode("\n", $storeLines);
    }
}
