<?php

if (!function_exists('PDO')) {
    function PDO(): PDO
    {
        global $PDO;
        return $PDO;
    }
}

if (!function_exists('AUTHGUARD')) {
    function AUTHGUARD(): App\SessionGuard
    {
        global $AUTHGUARD;
        return $AUTHGUARD;
    }
}

if (!function_exists('dd')) {
    function dd($var)
    {
        var_dump($var);
        exit();
    }
}

if (!function_exists('redirect')) {
    // Chuyển hướng đến một trang khác
    function redirect($location, array $data = [])
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }

        header('Location: ' . $location, true, 302);
        exit();
    }
}

if (!function_exists('session_get_once')) {
    // Đọc và xóa một biến trong $_SESSION
    function session_get_once($name, $default = null)
    {
        $value = $default;
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];
            unset($_SESSION[$name]);
        }
        return $value;
    }
}
if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
}

if (!function_exists('abort_csrf')) {
    function abort_csrf()
    {
        http_response_code(403);
        echo 'Token CSRF không hợp lệ.';
        exit;
    }
}

if (!function_exists('send_http_cache_headers')) {
    /**
     * Gửi header ETag/Last-Modified và trả 304 Not Modified nếu client đã có bản mới nhất.
     * Trả về true nếu đã gửi 304 (nơi gọi nên dừng xử lý/render), false nếu cần render bình thường.
     */
    function send_http_cache_headers(string $etagSeed, ?int $lastModifiedTimestamp = null): bool
    {
        $etag = '"' . md5($etagSeed) . '"';
        $lastModifiedTimestamp = $lastModifiedTimestamp ?? time();

        header('Cache-Control: private, must-revalidate');
        header('ETag: ' . $etag);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModifiedTimestamp) . ' GMT');

        $ifNoneMatch = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';

        $etagMatches = $ifNoneMatch !== '' && $ifNoneMatch === $etag;
        $notModifiedSince = $ifModifiedSince !== '' && strtotime($ifModifiedSince) >= $lastModifiedTimestamp;

        if ($etagMatches || $notModifiedSince) {
            http_response_code(304);
            return true;
        }

        return false;
    }
}

if (!function_exists('send_no_cache_headers')) {
    /**
     * Chặn trình duyệt/CDN (kể cả Cloudflare Tunnel) lưu cache trang đã xác thực.
     * Gọi ngay sau khi xác nhận người dùng đủ quyền, để tránh tình trạng bấm nút
     * Back hoặc bị cache lại vẫn thấy nội dung trang admin sau khi đã đăng xuất.
     */
    function send_no_cache_headers(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

if (!function_exists('normalize_text')) {
    function normalize_text(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII;', $text);
        } else {
            $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
        }
        $text = preg_replace('/[^a-z0-9 ]+/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}

if (!function_exists('get_image_map')) {
    function get_image_map(string $publicPath): array
    {
        $map = [];
        $imgRoot = $publicPath . DIRECTORY_SEPARATOR . 'img';
        if (!is_dir($imgRoot)) {
            return $map;
        }

        $folders = array_filter(scandir($imgRoot), function ($d) use ($imgRoot) {
            return $d !== '.' && $d !== '..' && is_dir($imgRoot . DIRECTORY_SEPARATOR . $d);
        });

        foreach ($folders as $folder) {
            $folderPath = $imgRoot . DIRECTORY_SEPARATOR . $folder;
            $files = array_values(array_filter(scandir($folderPath), function ($f) use ($folderPath) {
                return $f !== '.' && $f !== '..' && is_file($folderPath . DIRECTORY_SEPARATOR . $f);
            }));

            foreach ($files as $file) {
                $key = normalize_text(pathinfo($file, PATHINFO_FILENAME));
                // store mapping: normalized filename -> actual filename
                $map[$folder][$key] = $file;
            }
        }

        return $map;
    }
}

if (!function_exists('product_image_url')) {
    function product_image_url(array $product, string $categoryName = ''): string
    {
        $publicPath = realpath(__DIR__ . '/../public');
        if (!$publicPath) {
            return '/img/unnamed.png';
        }

        // Prefer uploads if present
        $imageName = trim($product['sp_hinh'] ?? $product['image'] ?? '');
        if ($imageName !== '') {
            $uploadPath = $publicPath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $imageName;
            if (file_exists($uploadPath)) {
                return '/uploads/' . rawurlencode($imageName);
            }
        }

        $categoryName = normalize_text($categoryName ?: ($product['l_ten'] ?? ''));
        $productName = normalize_text($product['sp_ten'] ?? $product['name'] ?? '');

        // prefer a static config map if present
        $mapFile = dirname(__DIR__) . '/config/image_map.php';
        if (file_exists($mapFile)) {
            try {
                $configMap = include $mapFile;
                if (is_array($configMap)) {
                    $productKey = normalize_text($productName);
                    $categoryKey = normalize_text($categoryName);

                    if ($productKey !== '' && isset($configMap[$productKey])) {
                        return $configMap[$productKey];
                    }

                    foreach ($configMap as $key => $path) {
                        $mapKey = normalize_text($key);
                        if ($mapKey === '') {
                            continue;
                        }
                        if ($productKey !== '' && strpos($productKey, $mapKey) !== false) {
                            return $path;
                        }
                        if ($productKey !== '' && strpos($mapKey, $productKey) !== false) {
                            return $path;
                        }
                        if ($categoryKey !== '' && strpos($categoryKey, $mapKey) !== false) {
                            return $path;
                        }
                        if ($categoryKey !== '' && strpos($mapKey, $categoryKey) !== false) {
                            return $path;
                        }
                    }
                }
            } catch (Throwable $e) {
                // ignore invalid map
            }
        }

        // determine candidate folder from category or product name
        $categoryFolders = [
            'tra sua' => ['tra sua', 'hong tra', 'matcha', 'nhan sen', 'socola', 'o long'],
            'ca phe' => ['ca phe', 'cafe', 'coffee', 'cappuccino', 'capuccino', 'latte', 'espresso', 'den', 'da', 'ca phe sua', 'kem'],
            'banh' => ['banh', 'banh chuoi', 'chuoi', 'banh flan', 'tiramisu', 'panna cotta', 'croissant', 'sung bo'],
            'tra trai cay' => ['tra cam', 'tra trai cay', 'fruit tea', 'tra hoa qua', 'tra xanh', 'tra cam que']
        ];

        $folder = null;
        foreach ($categoryFolders as $dir => $keywords) {
            foreach ($keywords as $keyword) {
                $k = normalize_text($keyword);
                if ($k !== '' && (strpos($categoryName, $k) !== false || strpos($productName, $k) !== false)) {
                    $folder = $dir;
                    break 2;
                }
            }
        }

        $imgRoot = $publicPath . DIRECTORY_SEPARATOR . 'img';
        if ($folder === null) {
            $available = array_filter(scandir($imgRoot), function ($d) use ($imgRoot) {
                return $d !== '.' && $d !== '..' && is_dir($imgRoot . DIRECTORY_SEPARATOR . $d);
            });
            foreach ($available as $d) {
                $dn = normalize_text($d);
                if ($dn !== '' && strpos($productName, $dn) !== false) {
                    $folder = $d;
                    break;
                }
            }
        }

        if ($folder === null) {
            $folder = 'banh';
        }

        $map = get_image_map($publicPath);
        if (!empty($map[$folder])) {
            foreach ($map[$folder] as $key => $file) {
                if ($key === '') {
                    continue;
                }
                if ($productName !== '' && strpos($productName, $key) !== false) {
                    return '/img/' . implode('/', array_map('rawurlencode', [$folder, $file]));
                }
                if ($folder === 'banh' && strpos($key, $productName) !== false) {
                    return '/img/' . implode('/', array_map('rawurlencode', [$folder, $file]));
                }
            }

            if (!empty($map[$folder])) {
                $firstFile = reset($map[$folder]);
                return '/img/' . implode('/', array_map('rawurlencode', [$folder, $firstFile]));
            }
        }

        return '/img/unnamed.png';
    }
}
