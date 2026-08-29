<?php

namespace App\Models;

use PDO;

class Favorite
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Bật/tắt yêu thích, trả về true nếu sau thao tác là "đã yêu thích"
    public function toggle(int $khMa, int $spMa): bool
    {
        if ($this->isFavorited($khMa, $spMa)) {
            $stmt = $this->pdo->prepare("DELETE FROM YEU_THICH WHERE KH_MA = :kh_ma AND SP_MA = :sp_ma");
            $stmt->execute(['kh_ma' => $khMa, 'sp_ma' => $spMa]);
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO YEU_THICH (KH_MA, SP_MA)
            VALUES (:kh_ma, :sp_ma)
            ON CONFLICT (KH_MA, SP_MA) DO NOTHING
        ");
        $stmt->execute(['kh_ma' => $khMa, 'sp_ma' => $spMa]);
        return true;
    }

    public function isFavorited(int $khMa, int $spMa): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM YEU_THICH WHERE KH_MA = :kh_ma AND SP_MA = :sp_ma");
        $stmt->execute(['kh_ma' => $khMa, 'sp_ma' => $spMa]);
        return (bool)$stmt->fetchColumn();
    }

    // Danh sách sản phẩm yêu thích của một khách hàng, kèm giá khuyến mãi hiện hành
    public function listByCustomer(int $khMa): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, p.SP_TONKHO, km.KM_PHANTRAM, yt.YT_NGAYTAO
            FROM YEU_THICH yt
            JOIN SAN_PHAM p ON yt.SP_MA = p.SP_MA
            LEFT JOIN KHUYEN_MAI km ON p.KM_MA = km.KM_MA
                AND km.KM_TRANGTHAI = true
                AND CURRENT_DATE BETWEEN km.KM_NGAYBATDAU AND km.KM_NGAYKETTHUC
            WHERE yt.KH_MA = :kh_ma
            ORDER BY yt.YT_NGAYTAO DESC
        ");
        $stmt->execute(['kh_ma' => $khMa]);

        return array_map(function (array $row) {
            $row['km_phantram'] = isset($row['km_phantram']) ? (int)$row['km_phantram'] : null;
            $price = (float)$row['sp_gia'];
            $row['gia_hien_thi'] = $row['km_phantram']
                ? round($price * (1 - $row['km_phantram'] / 100), 2)
                : $price;
            return $row;
        }, $stmt->fetchAll());
    }
}
