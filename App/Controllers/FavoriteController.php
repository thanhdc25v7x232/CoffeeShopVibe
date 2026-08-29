<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Favorite;
use App\Models\PDOFactory;

class FavoriteController extends Controller
{
    protected $favoriteModel;

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
        $this->favoriteModel = new Favorite($pdo);
    }

    /**
     * Trang "Sản phẩm yêu thích" của khách hàng đang đăng nhập.
     */
    public function index()
    {
        if (!AUTHGUARD()->isCustomerLoggedIn()) {
            redirect('/login');
        }

        $customer = AUTHGUARD()->customer();

        $this->view('favorites/index', [
            'title' => 'Sản phẩm yêu thích - ' . APPNAME,
            'products' => $this->favoriteModel->listByCustomer($customer->kh_ma),
        ]);
    }

    /**
     * Bật/tắt yêu thích một sản phẩm (gọi qua AJAX từ nút trái tim trên card sản phẩm).
     */
    public function toggle()
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

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ.']);
            return;
        }

        $customer = AUTHGUARD()->customer();
        $favorited = $this->favoriteModel->toggle($customer->kh_ma, $productId);

        echo json_encode(['success' => true, 'favorited' => $favorited]);
    }
}
