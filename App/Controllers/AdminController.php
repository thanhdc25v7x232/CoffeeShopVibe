<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Admin;
use App\Models\AiConversation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\PDOFactory;
use App\Models\Setting;

class AdminController extends Controller
{
    protected $pdo;
    protected $orderModel;
    protected $customerModel;
    protected $productModel;
    protected $adminModel;
    protected $paymentTransactionModel;
    protected $aiConversationModel;
    protected $settingModel;

    public function __construct()
    {
        // Kiểm tra đăng nhập admin
        if (!AUTHGUARD()->isAdminLoggedIn()) {
            redirect('/admin/login');
        }
        send_no_cache_headers();

        // Khởi tạo kết nối DB
        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];
        $this->pdo = (new PDOFactory())->create($config);
        $this->orderModel = new Order($this->pdo);
        $this->customerModel = new Customer($this->pdo);
        $this->productModel = new Product($this->pdo);
        $this->adminModel = new Admin($this->pdo);
        $this->paymentTransactionModel = new PaymentTransaction($this->pdo);
        $this->aiConversationModel = new AiConversation($this->pdo);
        $this->settingModel = new Setting($this->pdo);

        $this->setLayout('layouts/admin_master');
    }

    /**
     * Hiển thị trang dashboard admin
     */
    public function index()
    {
        $data = [
            'title' => 'Trang Quản Trị - ' . APPNAME,
            'messages' => session_get_once('messages'),
        ];

        $this->view('admin/index', $data);
    }

    /**
     * Đăng xuất admin
     */
    public function logout()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        AUTHGUARD()->logoutAdmin();
        redirect('/admin/login', ['messages' => ['success' => 'Đăng xuất thành công!']]);
    }

    /**
     * Hiển thị danh sách đơn hàng
     */
    public function orders()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->getAllPaginated($limit, $offset);
        $totalOrders = $this->orderModel->countTotal();
        $orderSignal = $this->orderModel->getLatestOrderSignal();

        $data = [
            'title' => 'Quản Lý Đơn Hàng - ' . APPNAME,
            'orders' => $orders,
            'totalPages' => ceil($totalOrders / $limit),
            'currentPage' => $page,
            'messages' => session_get_once('messages'),
            'initialMaxOrderId' => $orderSignal['max_order_id'],
        ];

        $this->view('admin/orders', $data);
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function orderDetail()
    {
        $orderId = $_GET['id'] ?? null;

        if (!$orderId) {
            redirect('/admin/orders');
        }

        $order = $this->orderModel->findById((int)$orderId);

        if (!$order) {
            redirect('/admin/orders');
        }

        $data = [
            'title' => 'Chi Tiết Đơn Hàng - ' . APPNAME,
            'order' => $order,
            'items' => $this->orderModel->getItemsByOrderId((int)$orderId),
            'transactions' => $this->paymentTransactionModel->findByOrderId((int)$orderId),
            'paymentMethodLabels' => \App\Services\Payments\PaymentGatewayFactory::labels(),
        ];

        $this->view('admin/order_detail', $data);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $orderId = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$orderId || !$status) {
            redirect('/admin/orders', ['errors' => ['Thiếu thông tin cần thiết.']]);
        }

        $this->orderModel->updateStatus((int)$orderId, $status);

        redirect('/admin/orders', ['messages' => ['success' => 'Cập nhật trạng thái đơn hàng thành công!']]);
    }

    /**
     * Xác nhận thủ công một đơn hàng (thường là COD) đã được thu tiền.
     */
    public function markOrderPaid()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            redirect('/admin/orders');
        }

        $this->orderModel->markPaid($orderId);

        redirect('/admin/order-detail?id=' . $orderId, ['messages' => ['success' => 'Đã xác nhận đơn hàng đã thanh toán.']]);
    }

    /**
     * API nhẹ cho JS trang quản lý đơn hàng polling: phát hiện đơn hàng mới vừa được khách đặt,
     * để tự làm mới danh sách mà admin không cần bấm F5.
     */
    public function ordersNewCheck()
    {
        header('Content-Type: application/json');
        echo json_encode($this->orderModel->getLatestOrderSignal());
    }

    /**
     * Hiển thị thống kê
     */
    public function statistics()
    {
        $data = [
            'title' => 'Thống Kê - ' . APPNAME,
            'stats' => $this->orderModel->getStatistics(),
        ];

        $this->view('admin/statistics', $data);
    }

    /**
     * Danh sách tài khoản khách hàng đã đăng ký.
     */
    public function customers()
    {
        $keyword = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $totalCustomers = $this->customerModel->countForAdmin($keyword);
        $totalPages = max(1, (int)ceil($totalCustomers / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->view('admin/customers', [
            'title' => 'Quản lý khách hàng - ' . APPNAME,
            'customers' => $this->customerModel->getAdminPaginated(
                $limit,
                ($page - 1) * $limit,
                $keyword
            ),
            'keyword' => $keyword,
            'totalCustomers' => $totalCustomers,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ]);
    }

    /**
     * Thông tin tài khoản và lịch sử đơn hàng của một khách hàng.
     */
    public function customerDetail()
    {
        $customerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$customerId) {
            redirect('/admin/customers');
        }

        $customer = $this->customerModel->findAdminDetail((int)$customerId);
        if (!$customer) {
            http_response_code(404);
            $this->view('errors/404', [
                'title' => 'Không tìm thấy khách hàng - ' . APPNAME,
            ]);
            return;
        }

        $this->view('admin/customer_detail', [
            'title' => 'Chi tiết khách hàng - ' . APPNAME,
            'customer' => $customer,
            'orders' => $this->orderModel->findByCustomer((int)$customerId),
            'messages' => session_get_once('messages'),
            'errors' => session_get_once('errors'),
        ]);
    }

    /**
     * Khóa/mở khóa tài khoản khách hàng — khách bị khóa không đăng nhập được nữa.
     */
    public function toggleCustomerLock()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $locked = ($_POST['locked'] ?? '') === '1';

        if ($customerId <= 0) {
            redirect('/admin/customers');
        }

        $this->customerModel->setLocked($customerId, $locked);

        redirect('/admin/customer-detail?id=' . $customerId, [
            'messages' => ['success' => $locked ? 'Đã khóa tài khoản khách hàng.' : 'Đã mở khóa tài khoản khách hàng.'],
        ]);
    }

    /**
     * Báo cáo tồn kho: toàn bộ sản phẩm, sản phẩm tồn kho thấp lên trước.
     */
    public function inventory()
    {
        $this->view('admin/inventory', [
            'title' => 'Tồn kho sản phẩm - ' . APPNAME,
            'products' => $this->productModel->getInventoryReport(),
            'messages' => session_get_once('messages'),
        ]);
    }

    /**
     * Cập nhật nhanh số lượng tồn kho của một sản phẩm.
     */
    public function updateStock()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $stock = $_POST['stock'] ?? null;

        if ($productId <= 0 || $stock === null || !is_numeric($stock) || $stock < 0) {
            redirect('/admin/inventory', ['errors' => ['Số lượng tồn kho không hợp lệ.']]);
        }

        $this->productModel->updateStock($productId, (int)$stock);
        redirect('/admin/inventory', ['messages' => ['success' => 'Đã cập nhật tồn kho.']]);
    }

    /**
     * Danh sách tài khoản admin.
     */
    public function admins()
    {
        $this->view('admin/admins', [
            'title' => 'Quản lý admin - ' . APPNAME,
            'admins' => $this->adminModel->getAll(),
            'currentAdminId' => (int)AUTHGUARD()->admin()->qtv_ma,
            'messages' => session_get_once('messages'),
            'errors' => session_get_once('errors'),
        ]);
    }

    /**
     * Xóa một tài khoản admin. Không cho tự xóa chính mình và không cho xóa admin cuối cùng,
     * để tránh khóa luôn lối vào trang quản trị.
     */
    public function deleteAdmin()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $adminId = (int)($_POST['admin_id'] ?? 0);
        $currentAdminId = (int)AUTHGUARD()->admin()->qtv_ma;

        if ($adminId === $currentAdminId) {
            redirect('/admin/admins', ['errors' => ['Không thể tự xóa tài khoản đang đăng nhập.']]);
        }

        if ($this->adminModel->countAll() <= 1) {
            redirect('/admin/admins', ['errors' => ['Không thể xóa admin cuối cùng.']]);
        }

        $this->adminModel->delete($adminId);
        redirect('/admin/admins', ['messages' => ['success' => 'Đã xóa tài khoản admin.']]);
    }

    /**
     * Danh sách phiên hội thoại với trợ lý AI (theo dõi/kiểm tra chất lượng trả lời).
     */
    public function aiConversations()
    {
        $this->view('admin/ai_conversations', [
            'title' => 'Trợ lý AI - ' . APPNAME,
            'sessions' => $this->aiConversationModel->listSessionsForAdmin(),
        ]);
    }

    public function aiConversationDetail()
    {
        $session = trim($_GET['session'] ?? '');
        if ($session === '') {
            redirect('/admin/ai-conversations');
        }

        $this->view('admin/ai_conversation_detail', [
            'title' => 'Chi tiết hội thoại AI - ' . APPNAME,
            'session' => $session,
            'messages' => $this->aiConversationModel->getHistory($session, 200),
        ]);
    }

    /**
     * Cài đặt chung của site: phí giao hàng, thông báo trang chủ.
     */
    public function settings()
    {
        $this->view('admin/settings', [
            'title' => 'Cài đặt chung - ' . APPNAME,
            'settings' => $this->settingModel->getAll(),
            'messages' => session_get_once('messages'),
            'errors' => session_get_once('errors'),
        ]);
    }

    public function saveSettings()
    {
        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $phiGiaoHang = $_POST['phi_giao_hang'] ?? '0';
        if (!is_numeric($phiGiaoHang) || $phiGiaoHang < 0) {
            redirect('/admin/settings', ['errors' => ['Phí giao hàng không hợp lệ.']]);
        }

        $this->settingModel->set('phi_giao_hang', (string)(int)$phiGiaoHang);
        $this->settingModel->set('thong_bao', trim($_POST['thong_bao'] ?? ''));

        redirect('/admin/settings', ['messages' => ['success' => 'Đã lưu cài đặt.']]);
    }
}
