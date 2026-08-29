<?php

namespace App;

use App\Models\Customer;
use App\Models\Admin;
use App\Models\PDOFactory;

class SessionGuard
{
    protected $customer;
    protected $admin;

    public function login(Customer $customer, array $credentials)
    {
        $verified = password_verify($credentials['password'], $customer->kh_matkhau);
        if ($verified) {
            // Cấp session ID mới sau khi xác thực để chống session fixation
            session_regenerate_id(true);
            // Một session chỉ đại diện cho MỘT vai trò tại một thời điểm: đăng nhập khách hàng
            // phải xóa phiên admin cũ (nếu có) trong cùng trình duyệt, tránh nhầm lẫn ở những nơi
            // phân biệt vai trò theo session như WebSocket chat (App\WebSocket\ChatServer).
            unset($_SESSION['admin_id']);
            $this->admin = null;
            $_SESSION['customer_id'] = $customer->kh_ma;
        }
        return $verified;
    }

    public function loginAdmin(Admin $admin, array $credentials)
    {
        $verified = password_verify($credentials['password'], $admin->qtv_matkhau);
        if ($verified) {
            // Cấp session ID mới sau khi xác thực để chống session fixation
            session_regenerate_id(true);
            // Tương tự login(): xóa phiên khách hàng cũ (nếu có) để không lẫn 2 vai trò.
            unset($_SESSION['customer_id']);
            $this->customer = null;
            $_SESSION['admin_id'] = $admin->qtv_ma;
        }
        return $verified;
    }

    public function customer()
    {
        if (!$this->customer && $this->isCustomerLoggedIn()) {
            $config = [
                'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
                'db_port' => $_ENV['DB_PORT'] ?? '5432',
                'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
                'db_user' => $_ENV['DB_USER'] ?? 'postgres',
                'db_pass' => $_ENV['DB_PASS'] ?? 'password',
            ];
            $pdo = (new PDOFactory())->create($config);
            $this->customer = (new Customer($pdo))->findById($_SESSION['customer_id']);
        }
        return $this->customer;
    }

    public function admin()
    {
        if (!$this->admin && $this->isAdminLoggedIn()) {
            $config = [
                'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
                'db_port' => $_ENV['DB_PORT'] ?? '5432',
                'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
                'db_user' => $_ENV['DB_USER'] ?? 'postgres',
                'db_pass' => $_ENV['DB_PASS'] ?? 'password',
            ];
            $pdo = (new PDOFactory())->create($config);
            $this->admin = (new Admin($pdo))->findById($_SESSION['admin_id']);
        }
        return $this->admin;
    }

    public function user()
    {
        return $this->customer();
    }

    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->customer = null;
        $this->admin = null;
        session_unset();
        session_destroy();
    }

    public function logoutAdmin()
    {
        $this->admin = null;
        unset($_SESSION['admin_id']);
    }

    public function isCustomerLoggedIn()
    {
        return isset($_SESSION['customer_id']);
    }

    public function isAdminLoggedIn()
    {
        return isset($_SESSION['admin_id']);
    }

    public function isUserLoggedIn()
    {
        return $this->isCustomerLoggedIn();
    }
}
