<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Store;
use App\Models\PDOFactory;
use App\Services\Payments\PaymentGatewayFactory;
use RuntimeException;

class OrderController extends Controller
{
    protected $orderModel;
    protected $storeModel;
    protected $settingModel;

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
        $this->settingModel = new Setting($pdo);
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
            'deliveryFee' => (int)$this->settingModel->get('phi_giao_hang', '0'),
            'stores' => $this->storeModel->getAllActiveStores(),
            'wards' => $this->storeModel->getActiveWards(),
            'paymentMethods' => PaymentGatewayFactory::labels(),
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
        $paymentMethod = $_POST['payment_method'] ?? 'cod';

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Vui lòng nhập họ tên người nhận.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        }

        $paymentGateway = PaymentGatewayFactory::make($paymentMethod);
        if (!array_key_exists($paymentMethod, PaymentGatewayFactory::labels()) || !$paymentGateway->isAvailable()) {
            $errors['payment_method'] = 'Phương thức thanh toán này hiện chưa khả dụng, vui lòng chọn phương thức khác.';
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

        // Phí giao hàng tính lại phía server (không lấy từ form) để khách không sửa được.
        if ($method === 'delivery') {
            $total += (int)$this->settingModel->get('phi_giao_hang', '0');
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
            'payment_method' => $paymentMethod,
        ], $cartItems);

        unset($_SESSION['cart']);

        // Không single-read như 'order_id' của redirect() bên dưới: giữ lại để endpoint
        // "Tôi đã thanh toán" xác minh đúng người vừa đặt đơn này mới được báo đã chuyển khoản.
        $_SESSION['last_order_id'] = $orderId;

        try {
            $payment = $paymentGateway->createPayment($this->orderModel->findById($orderId));
        } catch (RuntimeException $e) {
            // Cổng thanh toán từ chối sau khi đã tạo đơn (vd. mất kết nối) — đơn vẫn giữ 'unpaid',
            // khách quay lại trang thành công và có thể thử thanh toán lại sau.
            redirect('/dat-hang/thanh-cong', ['order_id' => $orderId]);
        }

        if (!empty($payment['redirect_url'])) {
            redirect($payment['redirect_url']);
        }

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

        $order = $this->orderModel->findById($orderId);

        $manualPaymentInfo = null;
        if ($order && in_array($order['dh_pttt'], ['momo', 'bank_transfer'], true)) {
            $gateway = PaymentGatewayFactory::make($order['dh_pttt']);
            if ($gateway instanceof \App\Services\Payments\ManualQrPaymentGateway) {
                $manualPaymentInfo = $gateway;
            }
        }

        $this->view('orders/success', [
            'title' => 'Đặt hàng thành công - ' . APPNAME,
            'order' => $order,
            'items' => $this->orderModel->getItemsByOrderId($orderId),
            'manualPaymentInfo' => $manualPaymentInfo,
        ]);
    }

    /**
     * Khách bấm "Tôi đã thanh toán" sau khi quét QR/chuyển khoản thủ công (MoMo cá nhân,
     * chuyển khoản ngân hàng) — chỉ đánh dấu chờ xác nhận, KHÔNG tự chuyển thành đã thanh toán.
     * Admin phải đối chiếu sao kê thực tế rồi mới xác nhận ở trang quản trị.
     */
    public function confirmManualPayment()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $orderId = (int)($_POST['order_id'] ?? 0);

        if ($orderId <= 0 || $orderId !== (int)($_SESSION['last_order_id'] ?? 0)) {
            redirect('/');
        }

        $this->orderModel->markWaitingConfirmation($orderId);

        redirect('/dat-hang/thanh-cong', ['order_id' => $orderId]);
    }
}
