<?php

namespace App\Core;

class Controller
{
    // Mặc định sử dụng layout của User (master.php)
    protected $layout = 'layouts/master';

    /**
     * Hàm này cho phép đổi layout từ bên trong Controller con
     * Ví dụ: $this->setLayout('layouts/admin_master');
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
    }

    protected function view($path, $data = [])
    {
        // 1. Giải nén dữ liệu
        extract($data);

        // 2. Lấy nội dung file view con
        $viewFile = __DIR__ . '/../Views/' . $path . '.php';

        if (!file_exists($viewFile)) {
            die("Lỗi: View '{$path}' không tồn tại!");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // 3. Gọi layout (Dựa vào biến $this->layout)
        // Đường dẫn file layout
        $layoutPath = __DIR__ . '/../Views/' . $this->layout . '.php';
        
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            // Fallback: nếu không tìm thấy layout thì in nội dung trần
            echo $content;
        }
    }
}