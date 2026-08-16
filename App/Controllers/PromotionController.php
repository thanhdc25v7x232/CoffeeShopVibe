<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Promotion;
use App\Models\PDOFactory;

class PromotionController extends Controller
{
    protected $promotionModel;

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
        $this->promotionModel = new Promotion($pdo);
        $this->setLayout('layouts/admin_master');
    }

    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $this->view('admin/promotions', [
            'title' => 'Quản Lý Khuyến Mãi - ' . APPNAME,
            'promotions' => $this->promotionModel->getAllPaginated($limit, $offset),
            'totalPages' => ceil($this->promotionModel->countTotal() / $limit),
            'currentPage' => $page,
            'errors' => session_get_once('errors'),
            'old' => session_get_once('form', []),
        ]);
    }

    // Xử lý chung cho cả Thêm và Sửa
    public function save()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $name = trim($_POST['name'] ?? '');
        $percent = (int)($_POST['percent'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Vui lòng nhập tên khuyến mãi.';
        }
        if ($percent < 1 || $percent > 100) {
            $errors['percent'] = 'Phần trăm giảm phải từ 1 đến 100.';
        }
        if ($startDate === '' || $endDate === '' || $startDate > $endDate) {
            $errors['date'] = 'Ngày bắt đầu/kết thúc không hợp lệ.';
        }

        if (!empty($errors)) {
            redirect('/admin/promotions', ['errors' => $errors, 'form' => $_POST]);
        }

        $data = [
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'percent' => $percent,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => isset($_POST['status']) ? 1 : 0,
        ];

        if (!empty($_POST['promotion_id'])) {
            $data['id'] = (int)$_POST['promotion_id'];
            $this->promotionModel->update($data);
        } else {
            $this->promotionModel->create($data);
        }

        redirect('/admin/promotions');
    }

    public function delete()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        if (!empty($_POST['promotion_id'])) {
            $this->promotionModel->delete((int)$_POST['promotion_id']);
        }

        redirect('/admin/promotions');
    }
}
