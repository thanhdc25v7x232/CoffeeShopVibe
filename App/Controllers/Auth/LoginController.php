<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\Customer;
use App\Models\Admin;
use App\Models\PDOFactory;

class LoginController extends Controller
{

    protected $customerModel;
    protected $adminModel;

    public function __construct()
    {
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
        $this->adminModel = new Admin($pdo);
    }

    public function create()
    {
        if (AUTHGUARD()->isCustomerLoggedIn()) {
            redirect('/');
        }

        $data = [
            'title' => 'Đăng nhập - ' . APPNAME,
            'messages' => session_get_once('messages'),
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors')
        ];

        $this->view('auth/login', $data);
    }

    public function store()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $user_credentials = $this->filterUserCredentials($_POST);

        $errors = [];
        $customer = $this->customerModel->findByEmail($user_credentials['email']);

        if (!$customer) {
            // Khách hàng không tồn tại
            $errors['email'] = 'Email hoặc mật khẩu không đúng.';
        } else if ($customer->kh_khoa) {
            $errors['email'] = 'Tài khoản này đã bị khóa. Vui lòng liên hệ Vibe để được hỗ trợ.';
        } else if (AUTHGUARD()->login($customer, $user_credentials)) {
            // Đăng nhập thành công
            redirect('/', ['messages' => ['success' => 'Đăng nhập thành công!']]);
        } else {
            // Sai mật khẩu...
            $errors['password'] = 'Email hoặc mật khẩu không đúng.';
        }

        // Đăng nhập không thành công: lưu giá trị trong form, trừ password
        $this->saveFormValues($_POST, ['password']);
        redirect('/login', ['errors' => $errors]);
    }

    public function destroy()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        AUTHGUARD()->logout();
        redirect('/');
    }

    /**
     * Hiển thị trang đăng nhập admin
     */
    public function index()
    {
        // Kiểm tra nếu đã đăng nhập admin thì redirect
        if (AUTHGUARD()->isAdminLoggedIn()) {
            redirect('/admin');
        }

        // Set layout cho admin
        $this->setLayout('layouts/admin_master');

        $data = [
            'title' => 'Đăng nhập Admin - ' . APPNAME,
            'messages' => session_get_once('messages'),
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors')
        ];

        $this->view('auth/admin_login', $data);
    }

    /**
     * Xử lý đăng nhập admin
     */
    public function authenticate()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $credentials = $this->filterAdminCredentials($_POST);

        $errors = [];
        $admin = $this->adminModel->findByUsername($credentials['username']);
        
        if (!$admin) {
            // Admin không tồn tại
            $errors['username'] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        } else if (AUTHGUARD()->loginAdmin($admin, $credentials)) {
            // Đăng nhập thành công
            redirect('/admin', ['messages' => ['success' => 'Đăng nhập thành công!']]);
        } else {
            // Sai mật khẩu...
            $errors['password'] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        }

        // Đăng nhập không thành công: lưu giá trị trong form, trừ password
        $this->saveFormValues($_POST, ['password']);
        redirect('/admin/login', ['errors' => $errors]);
    }

    protected function filterUserCredentials(array $data)
    {
        return [
            'email' => filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'password' => $data['password'] ?? null
        ];
    }

    protected function filterAdminCredentials(array $data)
    {
        return [
            'username' => trim($data['username'] ?? ''),
            'password' => $data['password'] ?? null
        ];
    }

    // Helper method để lấy giá trị form đã lưu
    protected function getSavedFormValues()
    {
        return session_get_once('form', []);
    }

    // Helper method để lưu giá trị form
    protected function saveFormValues(array $data, array $except = [])
    {
        $form = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $except, true)) {
                $form[$key] = $value;
            }
        }
        $_SESSION['form'] = $form;
    }
}