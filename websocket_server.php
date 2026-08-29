<?php

// WebSocket server cho chat khách hàng <-> admin (real-time).
// Chạy song song với "php -S" bằng lệnh: php websocket_server.php
// Xem README.md mục "Chạy WebSocket server cho chat".

// Ratchet/ReactPHP chưa cập nhật hết cho các deprecation của PHP 8.4+ (tham số nullable ngầm định...);
// ẩn E_DEPRECATED để log daemon gọn, không phải lỗi thật.
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/bootstrap.php';

use App\WebSocket\ChatServer;
use Ratchet\Http\HttpServer;
use Ratchet\Http\OriginCheck;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$port = (int)($_ENV['WS_PORT'] ?? 8081);

// Ratchet\Http\OriginCheck chỉ so khớp theo hostname (bỏ qua scheme/cổng), nên chỉ liệt kê tên miền.
$allowedOrigins = array_filter(array_map(
    'trim',
    explode(',', $_ENV['WS_ALLOWED_ORIGINS'] ?? 'localhost,127.0.0.1')
));

$chatServer = new ChatServer($PDO);
$wsServer = new WsServer($chatServer);
$httpServer = new HttpServer(new OriginCheck($wsServer, $allowedOrigins));

$ioServer = IoServer::factory($httpServer, $port, '0.0.0.0');

// Dùng fwrite(STDOUT, ...) thay vì echo: echo/print đánh dấu "headers already sent" ngay cả trong
// CLI SAPI, khiến session_id()/session_start() trong ChatServer::onOpen() thất bại âm thầm cho MỌI
// kết nối về sau (không phải lỗi 1 lần, mà là trạng thái toàn tiến trình một khi đã echo lần đầu).
fwrite(STDOUT, "WebSocket chat server dang lang nghe tren cong {$port}...\n");
fwrite(STDOUT, "Origin duoc phep: " . implode(', ', $allowedOrigins) . "\n");

$ioServer->run();
