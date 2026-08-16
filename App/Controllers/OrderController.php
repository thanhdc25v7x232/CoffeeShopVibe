<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Models\PDOFactory;

class OrderController extends Controller
{
    protected $orderModel;
    protected $storeModel;

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
        $this->orderModel = new Order($pdo);
        $this->storeModel = new Store($pdo);
    }

    /**
     * Hiển thị form đặt hàng (dùng giỏ hàng trong session)
     */
    public function checkout()
    {
        if (empty($_SESSION['cart'])) {
            redirect('/cart');
        }

        $cartItems = $_SESSION['cart'];
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $customer = AUTHGUARD()->customer();

        $this->view('orders/checkout', [
            'title' => 'Đặt hàng - ' . APPNAME,
            'cartItems' => $cartItems,
            'total' => $total,
            'stores' => $this->storeModel->getAllActiveStores(),
            'wards' => $this->storeModel->getActiveWards(),
            'customer' => $customer,
            'old' => session_get_once('form', []),
            'errors' => session_get_once('errors'),
        ]);
    }

    /**
     * Xử lý đặt hàng
     */
    public function store()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        if (empty($_SESSION['cart'])) {
            redirect('/cart');
        }

        $cartItems = $_SESSION['cart'];
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $method = $_POST['method'] ?? '';

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Vui lòng nhập họ tên người nhận.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        }

        $address = null;
        $storeId = null;
        $methodLabel = null;

        if ($method === 'delivery') {
            $methodLabel = 'Giao tận nơi';
            $ward = trim($_POST['ward'] ?? '');
            $street = trim($_POST['street'] ?? '');

            if ($street === '') {
                $errors['street'] = 'Vui lòng nhập địa chỉ cụ thể.';
            }

            // Kiểm tra khu vực giao hàng: xã/phường phải có cửa hàng đang phục vụ
            $activeWards = $this->storeModel->getActiveWards();
            if ($ward === '' || !in_array($ward, $activeWards, true)) {
                $errors['ward'] = 'Khu vực này hiện chưa được Vibe hỗ trợ giao hàng.';
            } else {
                $address = $street . ', ' . $ward;
            }
        } elseif ($method === 'pickup') {
            $methodLabel = 'Nhận tại quán';
            $storeId = isset($_POST['store_id']) ? (int)$_POST['store_id'] : 0;

            $store = null;
            foreach ($this->storeModel->getAllActiveStores() as $s) {
                if ((int)$s['ch_ma'] === $storeId) {
                    $store = $s;
                    break;
                }
            }

            if (!$store) {
                $errors['store_id'] = 'Vui lòng chọn một cửa hàng hợp lệ.';
            } else {
                $address = $store['ch_diachi'];
            }
        } else {
            $errors['method'] = 'Vui lòng chọn hình thức nhận hàng.';
        }

        if (!empty($errors)) {
            redirect('/checkout', ['errors' => $errors, 'form' => $_POST]);
        }

        $customer = AUTHGUARD()->customer();

        $orderId = $this->orderModel->create([
            'customer_id' => $customer ? $customer->kh_ma : null,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'method' => $methodLabel,
            'store_id' => $storeId,
            'total' => $total,
        ], $cartItems);

        unset($_SESSION['cart']);

        redirect('/dat-hang/thanh-cong', ['order_id' => $orderId]);
    }

    /**
     * Trang xác nhận đặt hàng thành công
     */
    public function success()
    {
        $orderId = session_get_once('order_id');

        if (!$orderId) {
            redirect('/');
        }

        $this->view('orders/success', [
            'title' => 'Đặt hàng thành công - ' . APPNAME,
            'order' => $this->orderModel->findById($orderId),
            'items' => $this->orderModel->getItemsByOrderId($orderId),
        ]);
    }
}
