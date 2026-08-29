<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Review;
use App\Models\PDOFactory;

class ReviewController extends Controller
{
    protected $reviewModel;

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
        $this->reviewModel = new Review($pdo);
    }

    /**
     * Khách hàng gửi (hoặc sửa) đánh giá cho một sản phẩm. Đánh giá luôn về trạng thái
     * "pending", chờ admin duyệt trước khi hiển thị công khai.
     */
    public function store($id)
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        if (!AUTHGUARD()->isCustomerLoggedIn()) {
            redirect('/login');
        }

        $productId = (int)$id;
        $sao = isset($_POST['sao']) ? (int)$_POST['sao'] : 0;
        $noiDung = trim($_POST['noi_dung'] ?? '');

        if ($sao < 1 || $sao > 5) {
            redirect('/san-pham/' . $productId, ['errors' => ['review' => 'Vui lòng chọn số sao từ 1 đến 5.']]);
        }

        $customer = AUTHGUARD()->customer();
        $this->reviewModel->upsert($customer->kh_ma, $productId, $sao, $noiDung);

        redirect('/san-pham/' . $productId, ['messages' => ['success' => 'Cảm ơn bạn đã đánh giá! Đánh giá đang chờ duyệt.']]);
    }
}
