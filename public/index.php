<?php

require_once __DIR__ . '/../bootstrap.php';

// Chỉ hiển thị lỗi chi tiết khi APP_DEBUG=true (mặc định true để không phá vỡ môi trường dev hiện tại).
// Khi demo/deploy thật, đặt APP_DEBUG=false trong .env để tránh lộ đường dẫn/truy vấn khi có lỗi.
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
if ($appDebug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
}
error_reporting(E_ALL);

define('APPNAME', 'Vibe Coffee Shop');

// Khu vực admin dùng RIÊNG 1 tên cookie session (khác với khách hàng). Nếu dùng chung
// PHPSESSID, đăng nhập admin ở 1 tab sẽ ghi đè cookie của tab khách hàng đang mở cùng
// trình duyệt (cookie thuộc về domain, không thuộc về tab) -> tab khách hàng đột nhiên
// mất đăng nhập dù không làm gì cả. Tách riêng để 2 vai trò độc lập hoàn toàn.
if (str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/admin')) {
    session_name('ADMIN_SESSID');
}

session_start();

// Khởi tạo router
$router = new \Bramus\Router\Router();

// Khai báo route: xem routes/web.php (dùng chung biến $router ở trên)
require_once __DIR__ . '/../routes/web.php';

// --- CHẠY ROUTER (Luôn phải ở cuối cùng) ---
$router->run();
