<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PDOFactory;

class HomeController extends Controller
{
    protected $productModel;
    protected $promotionModel;

    public function __construct()
    {
        // 1. Lấy thông tin cấu hình từ file .env (hoặc mặc định)
        $config = [
            'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db_port' => $_ENV['DB_PORT'] ?? '5432',
            'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
            'db_user' => $_ENV['DB_USER'] ?? 'postgres',
            'db_pass' => $_ENV['DB_PASS'] ?? 'password',
        ];

        // 2. Tạo kết nối PDO
        $factory = new PDOFactory();
        $pdo = $factory->create($config);

        // 3. Khởi tạo Model với kết nối vừa tạo
        $this->productModel = new Product($pdo);
        $this->promotionModel = new Promotion($pdo);
    }

    public function index()
    {
        // Lấy tất cả sản phẩm mới nhất (cho dòng đầu tiên)
        $latestProducts = $this->productModel->getAllLatestProducts();

        // Lấy tất cả các loại sản phẩm
        $categories = $this->productModel->getCategories();

        // Lấy tất cả loại sản phẩm để đồng bộ với dữ liệu trong DB
        $displayCategories = $categories;

        // Lấy sản phẩm cho mỗi loại
        $categoryProducts = [];
        foreach ($displayCategories as $category) {
            $products = $this->productModel->getProductsByCategory($category['l_ma']);
            $categoryProducts[] = [
                'category' => $category,
                'products' => $products
            ];
        }

        $this->view('home/index', [
            'latestProducts' => $latestProducts,
            'categoryProducts' => $categoryProducts
        ]);
    }

    public function about()
    {
        $this->view('home/about');
    }

    public function search()
    {
        $keyword = $_GET['q'] ?? '';
        $keyword = trim($keyword);

        if (empty($keyword)) {
            header('Location: /');
            exit;
        }

        // Tìm kiếm sản phẩm
        $products = $this->productModel->searchProducts($keyword);

        $this->view('home/search', [
            'keyword' => $keyword,
            'products' => $products,
            'totalResults' => count($products)
        ]);
    }

        public function category($categoryId)
    {
        // Lấy thông tin loại sản phẩm
        $category = $this->productModel->getCategoryById($categoryId);
        
        if (!$category) {
            header('Location: /');
            exit;
        }

        // Xử lý sắp xếp giá từ query string
        $sortBy = $_GET['sort'] ?? 'default';
        if (!in_array($sortBy, ['default', 'price_asc', 'price_desc'])) {
            $sortBy = 'default';
        }

        // Lấy sản phẩm của loại này với sắp xếp
        $products = $this->productModel->getProductsByCategoryWithSort($categoryId, $sortBy);

        $this->view('home/category', [
            'category' => $category,
            'products' => $products,
            'totalResults' => count($products),
            'currentSort' => $sortBy
        ]);
    }

    // View liên hệ
    public function contact()
    {
        $this->view('home/contact');
    }

    // Trang công khai: danh sách khuyến mãi đang áp dụng
    public function promotions()
    {
        $promotions = $this->promotionModel->getActive();

        $promotionsWithProducts = array_map(function ($promotion) {
            $promotion['products'] = $this->promotionModel->getProductsForPromotion($promotion['km_ma']);
            return $promotion;
        }, $promotions);

        $this->view('home/promotions', [
            'title' => 'Khuyến mãi - ' . APPNAME,
            'promotions' => $promotionsWithProducts
        ]);
    }
}
