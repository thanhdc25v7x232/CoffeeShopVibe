<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PDOFactory;

class ProductController extends Controller
{
    protected $productModel;
    protected $promotionModel;
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const ALLOWED_IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_IMAGE_SIZE = 2097152; // 2MB

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
        $this->promotionModel = new Promotion($pdo);
        $this->setLayout('layouts/admin_master');
    }

    /**
     * Trang frontend: danh sách tất cả sản phẩm (có lọc theo loại + phân trang)
     */
    public function index()
    {
        $this->setLayout('layouts/master');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $categoryId = isset($_GET['filter_category']) ? (int)$_GET['filter_category'] : null;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $categories = $this->productModel->getCategories();
        $products = $this->productModel->getProductsPaginated($limit, $offset, $categoryId);
        $totalProducts = $this->productModel->countTotal($categoryId);
        $totalPages = ceil($totalProducts / $limit);

        $this->view('products/index', [
            'title' => 'Sản phẩm - ' . APPNAME,
            'categories' => $categories,
            'products' => $products,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'currentCategory' => $categoryId
        ]);
    }

    /**
     * API nhẹ cho JS phía frontend polling: trả về mã phiên bản dữ liệu sản phẩm/khuyến mãi
     * hiện tại, để phát hiện khi admin vừa thêm/sửa/xóa mà không cần tải lại trang thủ công (F5).
     */
    public function version()
    {
        header('Content-Type: application/json');
        echo json_encode(['version' => $this->productModel->getDataVersion()]);
    }

    /**
     * Trang frontend: chi tiết một sản phẩm
     */
    public function show($id)
    {
        $this->setLayout('layouts/master');

        $product = $this->productModel->getProductById((int)$id);

        if (!$product) {
            header('Location: /san-pham');
            exit;
        }

        $relatedProducts = !empty($product['l_ma'])
            ? $this->productModel->getRelatedProducts($product['l_ma'], $product['sp_ma'])
            : [];

        $this->view('products/show', [
            'title' => $product['sp_ten'] . ' - ' . APPNAME,
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

    // Sửa hàm create cũ
    public function create()
    {
        $this->requireAdmin();

        // 1. Xử lý Lọc & Phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $categoryId = isset($_GET['filter_category']) ? (int)$_GET['filter_category'] : null;
        $limit = 20; // 20 sản phẩm/trang
        $offset = ($page - 1) * $limit;

        // 2. Gọi Model lấy dữ liệu
        $categories = $this->productModel->getCategories();
        $products = $this->productModel->getProductsPaginated($limit, $offset, $categoryId);
        $totalProducts = $this->productModel->countTotal($categoryId);
        $totalPages = ceil($totalProducts / $limit);

        // 3. Truyền hết sang View
        $this->view('products/create', [
            'categories' => $categories,
            'promotions' => $this->promotionModel->getActive(),
            'products' => $products,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'currentCategory' => $categoryId,
            'errors' => session_get_once('errors', []),
            'messages' => session_get_once('messages', [])
        ]);
    }

    public function update()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
                abort_csrf();
            }


            // Xử lý upload ảnh (Tóm tắt)
            $imageName = null;

            // Basic validation
            $errors = [];
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $currentProduct = $productId > 0 ? $this->productModel->findRawById($productId) : null;
            $name = trim($_POST['name'] ?? '');
            $price = $_POST['price'] ?? null;
            $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;

            if (!$currentProduct) {
                $errors['product'] = 'Sản phẩm không tồn tại.';
            }
            if ($name === '') {
                $errors['name'] = 'Tên sản phẩm không được để trống.';
            }
            if ($price === null || !is_numeric($price) || $price < 0) {
                $errors['price'] = 'Giá không hợp lệ.';
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = $this->uploadImage($_FILES['image'], $errors);
            }

            if (!empty($errors)) {
                if ($imageName) {
                    $this->deleteUploadedImage($imageName);
                }
                redirect('/admin/products/create', ['errors' => $errors, 'form' => $_POST]);
            }

            $promotion_id = isset($_POST['promotion_id']) && $_POST['promotion_id'] !== '' ? (int)$_POST['promotion_id'] : null;

            $data = [
                'id' => $productId, // Lấy ID từ hidden field
                'name' => $name,
                'price' => $price,
                'description' => $_POST['description'],
                'category_id' => $category_id,
                'promotion_id' => $promotion_id,
                'image' => $imageName // Nếu null, model sẽ bỏ qua cập nhật ảnh
            ];

            if ($this->productModel->updateProduct($data)) {
                if ($imageName) {
                    $this->deleteUploadedImage($currentProduct['sp_hinh'] ?? null);
                }
                redirect('/admin/products/create', ['messages' => ['success' => 'Đã cập nhật sản phẩm.']]);
            } elseif ($imageName) {
                $this->deleteUploadedImage($imageName);
            }
            redirect('/admin/products/create', ['errors' => ['product' => 'Không thể cập nhật sản phẩm.']]);
        }
    }

    // Thêm hàm delete
    public function delete()
    {
        $this->requireAdmin();

        if (isset($_POST['product_id'])) {
            if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
                abort_csrf();
            }
            $product = $this->productModel->findRawById((int)$_POST['product_id']);
            if ($this->productModel->deleteProduct($_POST['product_id']) && $product) {
                $this->deleteUploadedImage($product['sp_hinh'] ?? null);
                redirect('/admin/products/create', ['messages' => ['success' => 'Đã xóa sản phẩm.']]);
            }
        }
        redirect('/admin/products/create', ['errors' => ['product' => 'Không thể xóa sản phẩm.']]);
    }

    public function deleteImage()
    {
        $this->requireAdmin();

        if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
            abort_csrf();
        }

        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $product = $productId > 0 ? $this->productModel->findRawById($productId) : null;

        if ($product && $this->productModel->clearProductImage($productId)) {
            $this->deleteUploadedImage($product['sp_hinh'] ?? null);
            redirect('/admin/products/create', ['messages' => ['success' => 'Đã xóa ảnh sản phẩm.']]);
        }

        redirect('/admin/products/create', ['errors' => ['image' => 'Không thể xóa ảnh sản phẩm.']]);
    }

    // 2. Xử lý lưu dữ liệu (ĐÃ CẬP NHẬT)
    public function store()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['_csrf'] ?? '')) {
                abort_csrf();
            }

            $imageName = null; // Mặc định là null

            // --- GOM DỮ LIỆU (Đã thêm category_id) ---
            // Basic validation
            $errors = [];
            $name = trim($_POST['name'] ?? '');
            $price = $_POST['price'] ?? null;
            $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;

            if ($name === '') {
                $errors['name'] = 'Tên sản phẩm không được để trống.';
            }
            if ($price === null || !is_numeric($price) || $price < 0) {
                $errors['price'] = 'Giá không hợp lệ.';
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = $this->uploadImage($_FILES['image'], $errors);
            }

            if (!empty($errors)) {
                if ($imageName) {
                    $this->deleteUploadedImage($imageName);
                }
                redirect('/admin/products/create', ['errors' => $errors, 'form' => $_POST]);
            }

            $promotion_id = isset($_POST['promotion_id']) && $_POST['promotion_id'] !== '' ? (int)$_POST['promotion_id'] : null;

            $data = [
                'name'        => $name,
                'price'       => $price,
                'description' => $_POST['description'],
                'image'       => $imageName,
                'category_id' => $category_id,
                'promotion_id' => $promotion_id
            ];

            // Gọi Model để lưu
            if ($this->productModel->addProduct($data)) {
                // Thành công -> Quay về trang quản lý sản phẩm (giống update)
                redirect('/admin/products/create', ['messages' => ['success' => 'Đã thêm sản phẩm mới.']]);
            } else {
                if ($imageName) {
                    $this->deleteUploadedImage($imageName);
                }
                redirect('/admin/products/create', ['errors' => ['product' => 'Không thể lưu sản phẩm vào cơ sở dữ liệu.']]);
            }
        }
    }

    private function requireAdmin()
    {
        if (!AUTHGUARD()->isAdminLoggedIn()) {
            redirect('/admin/login');
        }
        send_no_cache_headers();
    }

    private function uploadImage(array $file, array &$errors)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors['image'] = 'Không thể tải ảnh lên. Vui lòng thử lại.';
            return null;
        }

        if (($file['size'] ?? 0) > self::MAX_IMAGE_SIZE) {
            $errors['image'] = 'Ảnh không được vượt quá 2MB.';
            return null;
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            $errors['image'] = 'Chỉ chấp nhận ảnh JPG, PNG, GIF hoặc WEBP.';
            return null;
        }

        $mimeType = is_file($file['tmp_name'] ?? '') ? (mime_content_type($file['tmp_name']) ?: '') : '';
        if (!in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES, true)) {
            $errors['image'] = 'Tập tin tải lên không phải là ảnh hợp lệ.';
            return null;
        }

        $uploadDir = $this->uploadsDirectory();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $imageName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $errors['image'] = 'Không thể lưu file ảnh vào thư mục public/uploads.';
            return null;
        }

        return $imageName;
    }

    private function deleteUploadedImage($imageName)
    {
        $imageName = trim((string)$imageName);
        if ($imageName === '' || basename($imageName) !== $imageName) {
            return;
        }

        $uploadDir = $this->uploadsDirectory();
        $realUploadDir = realpath($uploadDir);
        $realPath = realpath($uploadDir . DIRECTORY_SEPARATOR . $imageName);

        if (
            $realUploadDir
            && $realPath
            && str_starts_with($realPath, $realUploadDir . DIRECTORY_SEPARATOR)
            && is_file($realPath)
        ) {
            unlink($realPath);
        }
    }

    private function uploadsDirectory()
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    }
}
