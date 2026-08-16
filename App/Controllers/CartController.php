<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\PDOFactory;

class CartController extends Controller
{
    protected $productModel;

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
        $this->productModel = new Product($pdo);
    }

    // Khởi tạo giỏ hàng trong session nếu chưa có
    protected function initCart()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add()
    {
        $this->initCart();

        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        if (!isset($_POST['id'])) {
            header('Location: /');
            exit;
        }

        $productId = (int)$_POST['id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        // Lấy thông tin sản phẩm từ database
        $product = $this->productModel->getProductById($productId);

        if (!$product) {
            header('Location: /');
            exit;
        }

        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        if (isset($_SESSION['cart'][$productId])) {
            // Nếu đã có thì tăng số lượng
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            // Nếu chưa có thì thêm mới
            $_SESSION['cart'][$productId] = [
                'id' => $product['sp_ma'],
                'name' => $product['sp_ten'],
                'price' => $product['gia_hien_thi'],
                'image' => $product['sp_hinh'],
                'quantity' => $quantity
            ];
        }

        // Redirect về trang giỏ hàng hoặc trang trước đó
        $redirect = $_POST['redirect'] ?? '/cart';
        header('Location: ' . $redirect);
        exit;
    }

    // Hiển thị giỏ hàng
    public function index()
    {
        $this->initCart();

        $cartItems = $_SESSION['cart'] ?? [];
        $total = 0;
        $totalItems = 0;

        // Tính tổng tiền và tổng số lượng
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
            $totalItems += $item['quantity'];
        }

        $this->view('cart/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'totalItems' => $totalItems
        ]);
    }

    // Cập nhật số lượng sản phẩm
    public function update()
    {
        $this->initCart();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
                abort_csrf();
            }
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            if (isset($_SESSION['cart'][$productId])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                } else {
                    // Nếu số lượng <= 0 thì xóa sản phẩm
                    unset($_SESSION['cart'][$productId]);
                }
            }
        }

        header('Location: /cart');
        exit;
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function remove()
    {
        $this->initCart();

        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        if (isset($_POST['id'])) {
            $productId = (int)$_POST['id'];
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
        }

        header('Location: /cart');
        exit;
    }

    // Xóa toàn bộ giỏ hàng
    public function clear()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $_SESSION['cart'] = [];
        header('Location: /cart');
        exit;
    }

    // Lấy số lượng sản phẩm trong giỏ hàng (API endpoint)
    public function count()
    {
        $this->initCart();
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        echo json_encode(['count' => $count]);
        exit;
    }

    // Thêm sản phẩm vào giỏ hàng qua AJAX
    public function addAjax()
    {
        $this->initCart();
        header('Content-Type: application/json');

        $productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $quantity  = isset($_GET['quantity']) ? max(1, (int) $_GET['quantity']) : 1;

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã sản phẩm']);
            exit;
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            exit;
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id'       => $product['sp_ma'],
                'name'     => $product['sp_ten'],
                'price'    => $product['gia_hien_thi'],
                'image'    => $product['sp_hinh'],
                'quantity' => $quantity,
            ];
        }

        $cartCount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['quantity'];
        }

        echo json_encode(['success' => true, 'cartCount' => $cartCount]);
        exit;
    }
}
