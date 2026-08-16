<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\Customer;
use App\Models\PDOFactory;

class RegisterController extends Controller
{
    protected $customerModel;
    public function __construct()
    {
        if (AUTHGUARD()->isCustomerLoggedIn()) {
            redirect('/');
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
    }

    public function create()
    {
        $data = [
            'title' => 'Đăng ký - ' . APPNAME,
            'old' => $this->getSavedFormValues(),
            'errors' => session_get_once('errors')
        ];

        $this->view('auth/register', $data);
    }

    public function store()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $this->saveFormValues($_POST, ['password', 'password_confirmation']);

        $data = $this->filterUserData($_POST);

        // Tạo instance mới của Customer để validate
        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];
        $pdo = (new PDOFactory())->create($config);
        $newCustomer = new Customer($pdo);
        
        $model_errors = $newCustomer->validate($data);

        if (empty($model_errors)) {
            $newCustomer->fill($data)->save();

            $messages = ['success' => 'Đăng ký thành công! Vui lòng đăng nhập.'];
            redirect('/login', ['messages' => $messages]);
        }

        // Dữ liệu không hợp lệ...
        redirect('/register', ['errors' => $model_errors]);
    }

    protected function filterUserData(array $data)
    {
        return [
            'name' => trim($data['name'] ?? ''),
            'email' => filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'phone' => trim($data['phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
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
