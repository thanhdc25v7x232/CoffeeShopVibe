<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Review;
use App\Models\PDOFactory;

class AdminReviewController extends Controller
{
    protected $reviewModel;

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
        $this->reviewModel = new Review($pdo);

        $this->setLayout('layouts/admin_master');
    }

    /**
     * Danh sách đánh giá sản phẩm cho admin duyệt/quản lý, lọc theo trạng thái.
     */
    public function index()
    {
        $status = $_GET['status'] ?? '';
        $status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : null;

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $totalReviews = $this->reviewModel->countForAdmin($status);
        $totalPages = max(1, (int)ceil($totalReviews / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('admin/reviews', [
            'title' => 'Quản lý đánh giá - ' . APPNAME,
            'reviews' => $this->reviewModel->getAllForAdminPaginated($limit, ($page - 1) * $limit, $status),
            'status' => $status,
            'totalReviews' => $totalReviews,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'messages' => session_get_once('messages'),
        ]);
    }

    public function updateStatus()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $id = (int)($_POST['review_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id <= 0 || !in_array($status, ['approved', 'rejected'], true)) {
            redirect('/admin/reviews', ['errors' => ['Thiếu thông tin cần thiết.']]);
        }

        $this->reviewModel->updateStatus($id, $status);
        redirect('/admin/reviews', ['messages' => ['success' => 'Đã cập nhật trạng thái đánh giá.']]);
    }

    public function delete()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $id = (int)($_POST['review_id'] ?? 0);
        if ($id > 0) {
            $this->reviewModel->delete($id);
        }

        redirect('/admin/reviews', ['messages' => ['success' => 'Đã xóa đánh giá.']]);
    }
}
