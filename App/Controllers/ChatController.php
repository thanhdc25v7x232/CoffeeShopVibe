<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChatMessage;
use App\Models\PDOFactory;

class ChatController extends Controller
{
    protected $chatModel;

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
        $this->chatModel = new ChatMessage($pdo);
    }

    /**
     * JSON toàn bộ tin nhắn của khách hàng đang đăng nhập (dùng cho widget chat + polling).
     */
    public function messages()
    {
        header('Content-Type: application/json');

        if (!AUTHGUARD()->isCustomerLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'messages' => []]);
            return;
        }

        $customer = AUTHGUARD()->customer();
        $this->chatModel->markReadByCustomer($customer->kh_ma);

        echo json_encode([
            'success' => true,
            'messages' => $this->chatModel->listByCustomer($customer->kh_ma),
        ]);
    }

    /**
     * Khách hàng gửi tin nhắn tới shop.
     */
    public function send()
    {
        header('Content-Type: application/json');

        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF không hợp lệ.']);
            return;
        }

        if (!AUTHGUARD()->isCustomerLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
            return;
        }

        $noiDung = trim($_POST['noi_dung'] ?? '');
        if ($noiDung === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nội dung tin nhắn trống.']);
            return;
        }

        $customer = AUTHGUARD()->customer();
        $this->chatModel->send($customer->kh_ma, 'customer', $noiDung);

        echo json_encode(['success' => true]);
    }
}
