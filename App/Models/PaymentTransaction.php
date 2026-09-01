<?php

namespace App\Models;

use PDO;

class PaymentTransaction
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Tạo bản ghi giao dịch khi khởi tạo thanh toán với cổng bên ngoài (MoMo/VNPAY).
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO GIAO_DICH_THANH_TOAN (DH_MA, GD_NHACC, GD_PTTT, GD_SOTIEN, GD_MA_YEUCAU, GD_TRANGTHAI)
            VALUES (:dh_ma, :nhacc, :pttt, :sotien, :ma_yeucau, 'pending')
            RETURNING GD_MA
        ");
        $stmt->execute([
            'dh_ma' => $data['order_id'],
            'nhacc' => $data['provider'],
            'pttt' => $data['method'] ?? null,
            'sotien' => $data['amount'],
            'ma_yeucau' => $data['request_id'],
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function findByOrderId(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM GIAO_DICH_THANH_TOAN WHERE DH_MA = :dh_ma ORDER BY GD_NGAYTAO DESC
        ");
        $stmt->bindValue(':dh_ma', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByProviderRequestId(string $provider, string $requestId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM GIAO_DICH_THANH_TOAN WHERE GD_NHACC = :nhacc AND GD_MA_YEUCAU = :ma_yeucau
        ");
        $stmt->execute(['nhacc' => $provider, 'ma_yeucau' => $requestId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Cập nhật kết quả sau khi cổng thanh toán phản hồi (callback/IPN).
    public function updateResult(int $id, string $status, array $result = []): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE GIAO_DICH_THANH_TOAN
            SET GD_TRANGTHAI = :status,
                GD_MA_GIAODICH = :ma_giaodich,
                GD_MA_PHANHOI = :ma_phanhoi,
                GD_PHANHOI_THO = :phanhoi_tho,
                GD_NGAYTT = CASE WHEN :status2 = 'success' THEN CURRENT_TIMESTAMP ELSE GD_NGAYTT END
            WHERE GD_MA = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'status2' => $status,
            'ma_giaodich' => $result['transaction_id'] ?? null,
            'ma_phanhoi' => $result['response_code'] ?? null,
            'phanhoi_tho' => $result['raw'] ?? null,
            'id' => $id,
        ]);
    }
}
