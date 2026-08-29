<?php

namespace App\Models;

use PDO;

class ChatMessage
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function send(int $khMa, string $sender, string $noiDung): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO TIN_NHAN (KH_MA, TN_NGUOIGUI, TN_NOIDUNG)
            VALUES (:kh_ma, :sender, :noi_dung)
        ");
        return $stmt->execute(['kh_ma' => $khMa, 'sender' => $sender, 'noi_dung' => $noiDung]);
    }

    public function listByCustomer(int $khMa): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM TIN_NHAN WHERE KH_MA = :kh_ma ORDER BY TN_NGAYTAO ASC
        ");
        $stmt->execute(['kh_ma' => $khMa]);
        return $stmt->fetchAll();
    }

    // Đánh dấu đã đọc các tin nhắn của phía admin gửi (khách vừa xem lại đoạn chat)
    public function markReadByCustomer(int $khMa): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE TIN_NHAN SET TN_DADOC = true
            WHERE KH_MA = :kh_ma AND TN_NGUOIGUI = 'admin' AND TN_DADOC = false
        ");
        $stmt->execute(['kh_ma' => $khMa]);
    }

    // Đánh dấu đã đọc các tin nhắn của khách gửi (admin vừa xem đoạn chat với khách này)
    public function markReadByAdmin(int $khMa): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE TIN_NHAN SET TN_DADOC = true
            WHERE KH_MA = :kh_ma AND TN_NGUOIGUI = 'customer' AND TN_DADOC = false
        ");
        $stmt->execute(['kh_ma' => $khMa]);
    }

    // Danh sách hội thoại cho admin: mỗi khách hàng đã từng nhắn 1 dòng, kèm tin nhắn cuối + số tin chưa đọc
    public function listConversationsForAdmin(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                kh.KH_MA,
                kh.KH_TEN,
                last_msg.TN_NOIDUNG AS last_message,
                last_msg.TN_NGUOIGUI AS last_sender,
                last_msg.TN_NGAYTAO AS last_time,
                COALESCE(unread.so_luong, 0) AS unread_count
            FROM (SELECT DISTINCT KH_MA FROM TIN_NHAN) t
            JOIN KHACH_HANG kh ON kh.KH_MA = t.KH_MA
            JOIN LATERAL (
                SELECT TN_NOIDUNG, TN_NGUOIGUI, TN_NGAYTAO
                FROM TIN_NHAN
                WHERE KH_MA = t.KH_MA
                ORDER BY TN_NGAYTAO DESC
                LIMIT 1
            ) last_msg ON true
            LEFT JOIN (
                SELECT KH_MA, COUNT(*) AS so_luong
                FROM TIN_NHAN
                WHERE TN_NGUOIGUI = 'customer' AND TN_DADOC = false
                GROUP BY KH_MA
            ) unread ON unread.KH_MA = kh.KH_MA
            ORDER BY last_msg.TN_NGAYTAO DESC
        ");
        return $stmt->fetchAll();
    }
}
