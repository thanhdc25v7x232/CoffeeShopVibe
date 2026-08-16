<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PDOFactory;

class AccountController extends Controller
{
    protected $customerModel;
    protected $orderModel;

    public function __construct()
    {
        // Phải đăng nhập mới được xem/sửa thông tin tài khoản
        if (!AUTHGUARD()->isCustomerLoggedIn()) {
            redirect('/login');
        }

        // Khởi tạo kết nối DB
        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];
        $pdo = (new PDOFactory())->create($config);
        $this->customerModel = new Customer($pdo);
        $this->orderModel = new Order($pdo);
    }

    /**
     * Hiển thị trang thông tin tài khoản
     */
    public function show()
    {
        $data = [
            'title' => 'Thông tin tài khoản - ' . APPNAME,
            'customer' => AUTHGUARD()->customer(),
            'messages' => session_get_once('messages'),
            'old' => session_get_once('form', []),
            'errors' => session_get_once('errors')
        ];

        $this->view('account/show', $data);
    }

    /**
     * Xử lý cập nhật thông tin tài khoản
     */
    public function update()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $customer = $this->customerModel->findById($_SESSION['customer_id']);
        $data = $this->filterAccountData($_POST);

        $errors = $customer->validate($data);

        if (empty($errors)) {
            $customer->fill($data)->save();
            redirect('/account', ['messages' => ['success' => 'Cập nhật thông tin thành công!']]);
        }

        redirect('/account', ['errors' => $errors, 'form' => $data]);
    }

    /**
     * Hiển thị lịch sử đơn hàng của khách hàng đang đăng nhập
     */
    public function orders()
    {
        $customer = AUTHGUARD()->customer();

        $this->view('account/orders', [
            'title' => 'Đơn hàng của tôi - ' . APPNAME,
            'orders' => $this->orderModel->findByCustomer($customer->kh_ma),
        ]);
    }

    /**
     * API nhẹ cho JS trang "Đơn hàng của tôi" polling: phát hiện khi admin vừa đổi trạng thái
     * đơn hàng của khách đang đăng nhập, để tự làm mới mà không cần bấm F5.
     * Chỉ trả về dữ liệu của chính khách hàng đang đăng nhập (đã có auth check ở constructor).
     */
    public function ordersVersion()
    {
        header('Content-Type: application/json');
        $customer = AUTHGUARD()->customer();
        echo json_encode(['version' => $this->orderModel->getCustomerOrdersVersion($customer->kh_ma)]);
    }

    protected function filterAccountData(array $data): array
    {
        return [
            'name' => trim($data['name'] ?? ''),
            'email' => filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'phone' => trim($data['phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
        ];
    }
}
