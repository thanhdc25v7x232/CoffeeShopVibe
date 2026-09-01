<?php

namespace App\Models;

use PDO;

class Setting
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->pdo->prepare("SELECT CD_GIATRI FROM CAI_DAT WHERE CD_KHOA = :key");
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value !== false && $value !== null ? $value : $default;
    }

    // Trả về tất cả cài đặt dạng mảng [khóa => giá trị], dùng để đổ vào form cài đặt.
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT CD_KHOA, CD_GIATRI FROM CAI_DAT");
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['cd_khoa']] = $row['cd_giatri'];
        }
        return $result;
    }

    public function set(string $key, string $value): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO CAI_DAT (CD_KHOA, CD_GIATRI, CD_NGAYCAPNHAT)
            VALUES (:key, :value, CURRENT_TIMESTAMP)
            ON CONFLICT (CD_KHOA) DO UPDATE SET CD_GIATRI = :value2, CD_NGAYCAPNHAT = CURRENT_TIMESTAMP
        ");
        return $stmt->execute(['key' => $key, 'value' => $value, 'value2' => $value]);
    }
}
