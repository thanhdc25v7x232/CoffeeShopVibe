<?php

namespace App\Models;

use PDO;

class Promotion
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Lấy danh sách (có phân trang)
    public function getAllPaginated($limit, $offset)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM KHUYEN_MAI ORDER BY KM_NGAYTAO DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countTotal()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM KHUYEN_MAI")->fetchColumn();
    }

    // 2. Lấy các khuyến mãi đang còn hiệu lực (cho trang công khai)
    public function getActive()
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM KHUYEN_MAI
             WHERE KM_TRANGTHAI = true AND CURRENT_DATE BETWEEN KM_NGAYBATDAU AND KM_NGAYKETTHUC
             ORDER BY KM_NGAYKETTHUC ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM KHUYEN_MAI WHERE KM_MA = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // 3. Thêm mới
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO KHUYEN_MAI (KM_TEN, KM_MOTA, KM_PHANTRAM, KM_NGAYBATDAU, KM_NGAYKETTHUC, KM_TRANGTHAI)
            VALUES (:ten, :mota, :phantram, :batdau, :ketthuc, :trangthai)
        ");
        return $this->bindAndExecute($stmt, $data);
    }

    // 4. Cập nhật
    public function update($data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE KHUYEN_MAI
            SET KM_TEN = :ten, KM_MOTA = :mota, KM_PHANTRAM = :phantram,
                KM_NGAYBATDAU = :batdau, KM_NGAYKETTHUC = :ketthuc, KM_TRANGTHAI = :trangthai
            WHERE KM_MA = :id
        ");
        $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
        return $this->bindAndExecute($stmt, $data);
    }

    // 5. Xóa
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM KHUYEN_MAI WHERE KM_MA = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Sản phẩm đang gắn với một khuyến mãi (để hiển thị trang công khai)
    public function getProductsForPromotion($promotionId)
    {
        $stmt = $this->pdo->prepare("SELECT SP_MA, SP_TEN, SP_GIA, SP_HINH FROM SAN_PHAM WHERE KM_MA = :id");
        $stmt->bindValue(':id', $promotionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function bindAndExecute($stmt, $data)
    {
        $stmt->bindValue(':ten', $data['name']);
        $stmt->bindValue(':mota', $data['description']);
        $stmt->bindValue(':phantram', $data['percent'], PDO::PARAM_INT);
        $stmt->bindValue(':batdau', $data['start_date']);
        $stmt->bindValue(':ketthuc', $data['end_date']);
        $stmt->bindValue(':trangthai', $data['status'], PDO::PARAM_BOOL);
        return $stmt->execute();
    }
}
