<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\Admin;
use App\Models\PDOFactory;

class AdminRegisterController extends Controller
{
    protected $adminModel;

    public function __construct()
    {
        // Kiểm tra nếu đã đăng nhập admin thì redirect
        if (AUTHGUARD()->isAdminLoggedIn()) {
            redirect('/admin');
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
        $this->adminModel = new Admin($pdo);
        
        $this->setLayout('layouts/admin_master');
    }

    /**
     * Hiển thị trang đăng ký admin
     */
    public function create()
    {
        $data = [
            'title' => 'Đăng ký Admin - ' . APPNAME,
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors'),
            'messages' => session_get_once('messages'),
        ];

        $this->view('auth/admin_register', $data);
    }

    /**
     * Xử lý đăng ký admin
     */
    public function store()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $this->saveFormValues($_POST, ['password', 'password_confirmation']);

        $data = $this->filterAdminData($_POST);
        
        // Tạo instance mới của Admin để validate
        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];
        $pdo = (new PDOFactory())->create($config);
        $newAdmin = new Admin($pdo);
        
        $model_errors = $newAdmin->validate($data);
        
        if (empty($model_errors)) {
            $newAdmin->fill($data)->save();

            $messages = ['success' => 'Đăng ký thành công! Vui lòng đăng nhập.'];
            redirect('/admin/login', ['messages' => $messages]);
        }

        // Dữ liệu không hợp lệ...
        redirect('/admin/register', ['errors' => $model_errors]);
    }

    protected function filterAdminData(array $data)
    {
        return [
            'username' => trim($data['username'] ?? ''),
            'password' => $data['password'] ?? null,
            'password_confirmation' => $data['password_confirmation'] ?? null
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
