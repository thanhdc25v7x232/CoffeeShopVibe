<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AiConversation;
use App\Models\PDOFactory;
use App\Services\AiAssistantService;

class AiAssistantController extends Controller
{
    protected $conversationModel;
    protected $assistantService;

    public function __construct()
    {
        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];
        $pdo = (new PDOFactory())->create($config);
        $this->conversationModel = new AiConversation($pdo);
        $this->assistantService = new AiAssistantService($pdo);
    }

    /**
     * Xác định khóa phiên trò chuyện: khách đăng nhập dùng kh_ma, khách vãng lai dùng token ngẫu nhiên
     * lưu trong session (không yêu cầu đăng nhập vì AI chỉ tư vấn thông tin công khai).
     */
    private function resolveSession(): array
    {
        if (AUTHGUARD()->isCustomerLoggedIn()) {
            $customer = AUTHGUARD()->customer();
            return ['kh_' . $customer->kh_ma, (int)$customer->kh_ma];
        }

        if (empty($_SESSION['ai_guest_key'])) {
            $_SESSION['ai_guest_key'] = bin2hex(random_bytes(16));
        }

        return ['guest_' . $_SESSION['ai_guest_key'], null];
    }

    public function history()
    {
        header('Content-Type: application/json');

        [$sessionKey] = $this->resolveSession();

        $messages = array_map(function ($row) {
            return [
                'role' => $row['tl_vaitro'],
                'content' => $row['tl_noidung'],
            ];
        }, $this->conversationModel->getHistory($sessionKey, 30));

        echo json_encode(['success' => true, 'messages' => $messages]);
    }

    public function send()
    {
        header('Content-Type: application/json');

        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF không hợp lệ.']);
            return;
        }

        $noiDung = trim($_POST['noi_dung'] ?? '');
        if ($noiDung === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nội dung câu hỏi trống.']);
            return;
        }

        [$sessionKey, $customerId] = $this->resolveSession();

        $reply = $this->assistantService->ask($sessionKey, $customerId, $noiDung);

        echo json_encode(['success' => true, 'reply' => $reply]);
    }
}
