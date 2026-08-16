<?php

define('ROOTDIR', __DIR__ . DIRECTORY_SEPARATOR);

// require_once __DIR__ . '/vendor/autoload.php';

require_once ROOTDIR . 'vendor/autoload.php';

// Nạp biến môi trường từ file .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // Nếu không có file .env thì bỏ qua (hoặc báo lỗi nếu muốn)
    echo "Lưu ý: Chưa có file .env, sử dụng cấu hình mặc định.";
}

try {
  $PDO = (new App\Models\PDOFactory())->create([
    'db_host' => $_ENV['DB_HOST'] ?? 'localhost',
    'db_port' => $_ENV['DB_PORT'] ?? '5432',
    'db_name' => $_ENV['DB_NAME'] ?? 'ct271e_project',
    'db_user' => $_ENV['DB_USER'] ?? 'postgres',
    'db_pass' => $_ENV['DB_PASS'] ?? 'password',
  ]);
} catch (Exception $ex) {
  echo 'Không thể kết nối đến PostgreSQL,
		kiểm tra lại username/password đến PostgreSQL.<br>';
  dd($ex);
}

$AUTHGUARD = new App\SessionGuard();