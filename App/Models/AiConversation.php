<?php

namespace App\Models;

use PDO;

class AiConversation
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lấy N tin nhắn gần nhất của một phiên, trả về theo thứ tự thời gian tăng dần
    public function getHistory(string $sessionKey, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare("
            SELECT TL_VAITRO, TL_NOIDUNG, TL_NGAYTAO
            FROM TRO_LY_AI
            WHERE TL_PHIEN = :phien
            ORDER BY TL_MA DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':phien', $sessionKey);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_reverse($stmt->fetchAll());
    }

    /**
     * Id lượt hội thoại gần nhất mà Gemini trả về cho phiên này — dùng làm
     * previous_interaction_id để Gemini tự giữ ngữ cảnh phía server, không cần gửi lại lịch sử.
     */
    public function getLastInteractionId(string $sessionKey): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT TL_INTERACTION_ID
            FROM TRO_LY_AI
            WHERE TL_PHIEN = :phien AND TL_INTERACTION_ID IS NOT NULL
            ORDER BY TL_MA DESC
            LIMIT 1
        ");
        $stmt->bindValue(':phien', $sessionKey);
        $stmt->execute();
        $value = $stmt->fetchColumn();

        return $value !== false ? $value : null;
    }

    /**
     * Danh sách phiên hội thoại cho trang admin theo dõi trợ lý AI: mỗi phiên 1 dòng,
     * kèm tin nhắn cuối, thời gian, số tin nhắn và tên khách (nếu đã đăng nhập lúc chat).
     */
    public function listSessionsForAdmin(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                t.TL_PHIEN AS phien,
                k.KH_TEN AS kh_ten,
                COUNT(*) AS message_count,
                MAX(t.TL_NGAYTAO) AS last_time,
                (ARRAY_AGG(t.TL_NOIDUNG ORDER BY t.TL_MA DESC))[1] AS last_message
            FROM TRO_LY_AI t
            LEFT JOIN KHACH_HANG k ON k.KH_MA = t.KH_MA
            GROUP BY t.TL_PHIEN, k.KH_TEN
            ORDER BY MAX(t.TL_NGAYTAO) DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function append(string $sessionKey, ?int $customerId, string $role, string $content, ?string $interactionId = null): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO TRO_LY_AI (TL_PHIEN, KH_MA, TL_VAITRO, TL_NOIDUNG, TL_INTERACTION_ID)
            VALUES (:phien, :kh_ma, :vaitro, :noidung, :interaction_id)
        ");
        $stmt->bindValue(':phien', $sessionKey);
        $stmt->bindValue(':kh_ma', $customerId, $customerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':vaitro', $role);
        $stmt->bindValue(':noidung', $content);
        $stmt->bindValue(':interaction_id', $interactionId, $interactionId === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
    }
}
