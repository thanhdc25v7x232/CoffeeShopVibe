<?php

namespace App\Models;

use PDO;

class Review
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Thêm mới hoặc sửa đánh giá của một khách hàng cho một sản phẩm (mỗi khách 1 đánh giá/sản phẩm).
    // Sửa lại đánh giá sẽ đưa về trạng thái "pending" để admin duyệt lại.
    public function upsert(int $khMa, int $spMa, int $sao, string $noiDung): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO DANH_GIA (KH_MA, SP_MA, DG_SAO, DG_NOIDUNG, DG_TRANGTHAI)
            VALUES (:kh_ma, :sp_ma, :sao, :noi_dung, 'pending')
            ON CONFLICT (KH_MA, SP_MA) DO UPDATE
                SET DG_SAO = EXCLUDED.DG_SAO,
                    DG_NOIDUNG = EXCLUDED.DG_NOIDUNG,
                    DG_TRANGTHAI = 'pending'
        ");
        return $stmt->execute([
            'kh_ma' => $khMa,
            'sp_ma' => $spMa,
            'sao' => $sao,
            'noi_dung' => $noiDung,
        ]);
    }

    public function findByCustomerAndProduct(int $khMa, int $spMa): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM DANH_GIA WHERE KH_MA = :kh_ma AND SP_MA = :sp_ma");
        $stmt->execute(['kh_ma' => $khMa, 'sp_ma' => $spMa]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getApprovedByProduct(int $spMa): array
    {
        $stmt = $this->pdo->prepare("
            SELECT dg.*, kh.KH_TEN
            FROM DANH_GIA dg
            JOIN KHACH_HANG kh ON dg.KH_MA = kh.KH_MA
            WHERE dg.SP_MA = :sp_ma AND dg.DG_TRANGTHAI = 'approved'
            ORDER BY dg.DG_NGAYTAO DESC
        ");
        $stmt->execute(['sp_ma' => $spMa]);
        return $stmt->fetchAll();
    }

    public function getStatsForProduct(int $spMa): array
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(AVG(DG_SAO), 0) AS avg_sao, COUNT(*) AS so_luong
            FROM DANH_GIA
            WHERE SP_MA = :sp_ma AND DG_TRANGTHAI = 'approved'
        ");
        $stmt->execute(['sp_ma' => $spMa]);
        $row = $stmt->fetch();

        return [
            'avg' => round((float)$row['avg_sao'], 1),
            'count' => (int)$row['so_luong'],
        ];
    }

    // Danh sách đánh giá cho admin quản lý (có thể lọc theo trạng thái), có phân trang
    public function getAllForAdminPaginated(int $limit, int $offset, ?string $status = null): array
    {
        $sql = "
            SELECT dg.*, kh.KH_TEN, sp.SP_TEN
            FROM DANH_GIA dg
            JOIN KHACH_HANG kh ON dg.KH_MA = kh.KH_MA
            JOIN SAN_PHAM sp ON dg.SP_MA = sp.SP_MA
        ";
        if ($status) {
            $sql .= " WHERE dg.DG_TRANGTHAI = :status";
        }
        $sql .= " ORDER BY dg.DG_NGAYTAO DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        if ($status) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countForAdmin(?string $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM DANH_GIA";
        if ($status) {
            $sql .= " WHERE DG_TRANGTHAI = :status";
        }
        $stmt = $this->pdo->prepare($sql);
        if ($status) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE DANH_GIA SET DG_TRANGTHAI = :status WHERE DG_MA = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM DANH_GIA WHERE DG_MA = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
