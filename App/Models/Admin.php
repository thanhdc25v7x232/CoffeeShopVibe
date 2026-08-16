<?php

namespace App\Models;

use PDO;

class Admin
{
    protected $pdo;

    public $qtv_ma = -1;
    public $qtv_tendn;
    public $qtv_matkhau;
    public $qtv_ngaytao;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Tìm admin theo tên đăng nhập
    public function findByUsername(string $username): ?Admin
    {
        $stmt = $this->pdo->prepare("
            SELECT QTV_MA as qtv_ma, QTV_TENDN as qtv_tendn, 
                   QTV_MATKHAU as qtv_matkhau, QTV_NGAYTAO as qtv_ngaytao
            FROM QUAN_TRI_VIEN 
            WHERE QTV_TENDN = :username
        ");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $admin = new Admin($this->pdo);
            $admin->fillFromDbRow($row);
            return $admin;
        }
        
        return null;
    }

    // Tìm admin theo ID
    public function findById(int $id): ?Admin
    {
        $stmt = $this->pdo->prepare("
            SELECT QTV_MA as qtv_ma, QTV_TENDN as qtv_tendn, 
                   QTV_MATKHAU as qtv_matkhau, QTV_NGAYTAO as qtv_ngaytao
            FROM QUAN_TRI_VIEN 
            WHERE QTV_MA = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $admin = new Admin($this->pdo);
            $admin->fillFromDbRow($row);
            return $admin;
        }
        
        return null;
    }

    // Điền dữ liệu từ database row
    protected function fillFromDbRow(array $row)
    {
        $this->qtv_ma = $row['qtv_ma'];
        $this->qtv_tendn = $row['qtv_tendn'];
        $this->qtv_matkhau = $row['qtv_matkhau'];
        $this->qtv_ngaytao = $row['qtv_ngaytao'] ?? null;
    }

    // Lưu admin (thêm mới hoặc cập nhật)
    public function save(): bool
    {
        if ($this->qtv_ma >= 0) {
            // Cập nhật
            $stmt = $this->pdo->prepare("
                UPDATE QUAN_TRI_VIEN 
                SET QTV_TENDN = :tendn, QTV_MATKHAU = :matkhau
                WHERE QTV_MA = :id
            ");
            return $stmt->execute([
                'id' => $this->qtv_ma,
                'tendn' => $this->qtv_tendn,
                'matkhau' => $this->qtv_matkhau
            ]);
        } else {
            // Thêm mới - PostgreSQL sử dụng RETURNING
            $stmt = $this->pdo->prepare("
                INSERT INTO QUAN_TRI_VIEN (QTV_TENDN, QTV_MATKHAU)
                VALUES (:tendn, :matkhau)
                RETURNING QTV_MA
            ");
            $result = $stmt->execute([
                'tendn' => $this->qtv_tendn,
                'matkhau' => $this->qtv_matkhau
            ]);
            
            if ($result) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->qtv_ma = (int)$row['qtv_ma'];
            }
            
            return $result;
        }
    }

    // Điền dữ liệu từ form
    public function fill(array $data): Admin
    {
        $this->qtv_tendn = trim($data['username'] ?? '');
        
        // Mã hóa mật khẩu nếu có
        if (isset($data['password']) && !empty($data['password'])) {
            $this->qtv_matkhau = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this;
    }

    // Kiểm tra username đã tồn tại chưa
    private function isUsernameInUse(string $username, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM QUAN_TRI_VIEN WHERE QTV_TENDN = :username AND QTV_MA != :id");
            $stmt->execute(['username' => $username, 'id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM QUAN_TRI_VIEN WHERE QTV_TENDN = :username");
            $stmt->execute(['username' => $username]);
        }
        return $stmt->fetchColumn() > 0;
    }

    // Validate dữ liệu
    public function validate(array $data): array
    {
        $errors = [];

        // Validate username
        if (empty($data['username']) || strlen(trim($data['username'])) < 3) {
            $errors['username'] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
        } elseif ($this->isUsernameInUse($data['username'], $this->qtv_ma >= 0 ? $this->qtv_ma : null)) {
            $errors['username'] = 'Tên đăng nhập đã được sử dụng.';
        }

        // Validate mật khẩu (chỉ khi đăng ký hoặc đổi mật khẩu)
        if ($this->qtv_ma < 0 || isset($data['password'])) {
            if (empty($data['password']) || strlen($data['password']) < 6) {
                $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            } elseif (isset($data['password_confirmation']) && $data['password'] !== $data['password_confirmation']) {
                $errors['password_confirmation'] = 'Mật khẩu xác nhận không khớp.';
            }
        }

        return $errors;
    }
}
