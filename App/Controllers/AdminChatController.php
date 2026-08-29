<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\PDOFactory;

class AdminChatController extends Controller
{
    protected $chatModel;
    protected $customerModel;

    public function __construct()
    {
        if (!AUTHGUARD()->isAdminLoggedIn()) {
            redirect('/admin/login');
        }
        send_no_cache_headers();

        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];
        $pdo = (new PDOFactory())->create($config);
        $this->chatModel = new ChatMessage($pdo);
        $this->customerModel = new Customer($pdo);

        $this->setLayout('layouts/admin_master');
    }

    /**
     * Danh sách hội thoại: mỗi khách hàng từng nhắn tin 1 dòng, kèm tin nhắn cuối + số tin chưa đọc.
     */
    public function index()
    {
        $this->view('admin/chat', [
            'title' => 'Quản lý trò chuyện - ' . APPNAME,
            'conversations' => $this->chatModel->listConversationsForAdmin(),
        ]);
    }

    /**
     * Xem + trả lời hội thoại với một khách hàng cụ thể.
     */
    public function conversation()
    {
        $customerId = (int)($_GET['customer_id'] ?? 0);
        if ($customerId <= 0) {
            redirect('/admin/chat');
        }

        $customer = $this->customerModel->findById($customerId);
        if (!$customer) {
            redirect('/admin/chat');
        }

        $this->chatModel->markReadByAdmin($customerId);

        $this->view('admin/chat_conversation', [
            'title' => 'Trò chuyện với ' . $customer->kh_ten . ' - ' . APPNAME,
            'customer' => $customer,
            'messages' => $this->chatModel->listByCustomer($customerId),
        ]);
    }

    public function send()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $noiDung = trim($_POST['noi_dung'] ?? '');

        if ($customerId <= 0 || $noiDung === '') {
            redirect('/admin/chat');
        }

        $this->chatModel->send($customerId, 'admin', $noiDung);
        redirect('/admin/chat/conversation?customer_id=' . $customerId);
    }
}
