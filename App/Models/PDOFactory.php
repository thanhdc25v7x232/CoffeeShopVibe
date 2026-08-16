<?php

namespace App\Models;

use PDO;
use PDOException;

class PDOFactory
{
    public function create(array $config)
    {
        try {
            // Chuỗi kết nối cho PostgreSQL
            $dsn = "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}";
            
            $conn = new PDO($dsn, $config['db_user'], $config['db_pass']);
            
            // Cấu hình báo lỗi: RẤT QUAN TRỌNG để debug và bảo mật (try-catch bên ngoài)
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            return $conn;
        } catch (PDOException $e) {
            // Không bao giờ echo lỗi trực tiếp ra màn hình ở production
            error_log($e->getMessage()); 
            die("Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.");
        }
    }
}