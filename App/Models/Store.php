<?php

namespace App\Models;

use PDO;

class Store
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Lấy danh sách (Có phân trang)
    public function getStoresPaginated($limit, $offset)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM CUA_HANG ORDER BY CH_NGAYTAO DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 2. Đếm tổng số
    public function countTotal()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM CUA_HANG")->fetchColumn();
    }

    // 3. Thêm mới
    public function addStore($data)
    {
        $sql = "INSERT INTO CUA_HANG (CH_TEN, CH_DIACHI, CH_XAPHUONG, CH_THANHPHO, MATINH, MAXA, SONHA_DUONG, CH_SDT, CH_TRANGTHAI, GIO_MO_CUA, GIO_DONG_CUA)
                VALUES (:ten, :diachi, :xaphuong, :thanhpho, :matinh, :maxa, :sonha, :sdt, :trangthai, :giomo, :giodong)";

        $stmt = $this->pdo->prepare($sql);
        $this->bindParams($stmt, $data);
        return $stmt->execute();
    }

    // 4. Cập nhật
    public function updateStore($data)
    {
        $sql = "UPDATE CUA_HANG
            SET CH_TEN=:ten, CH_DIACHI=:diachi, CH_XAPHUONG=:xaphuong, CH_THANHPHO=:thanhpho,
                MATINH=:matinh, MAXA=:maxa, SONHA_DUONG=:sonha,
                CH_SDT=:sdt, CH_TRANGTHAI=:trangthai, GIO_MO_CUA=:giomo, GIO_DONG_CUA=:giodong
            WHERE CH_MA=:id";

        $stmt = $this->pdo->prepare($sql);
        $this->bindParams($stmt, $data);
        $stmt->bindValue(':id', $data['id']);
        return $stmt->execute();
    }
    // 5. Xóa
    public function deleteStore($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM CUA_HANG WHERE CH_MA = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    // 6. Lấy tất cả cửa hàng đang hoạt động (cho người dùng xem)
    public function getAllActiveStores()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM CUA_HANG WHERE CH_TRANGTHAI = true ORDER BY CH_TEN ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mã băm đại diện cho toàn bộ dữ liệu cửa hàng hiện tại.
     * Dùng cho JS phía trang "Cửa hàng" polling để phát hiện admin vừa thêm/sửa/xóa cửa hàng.
     */
    public function getDataVersion(): string
    {
        $stmt = $this->pdo->query("
            SELECT COALESCE(STRING_AGG(
                CH_MA || ':' || CH_TEN || ':' || CH_DIACHI || ':' || CH_TRANGTHAI
                || ':' || COALESCE(GIO_MO_CUA, '') || ':' || COALESCE(GIO_DONG_CUA, ''),
                ',' ORDER BY CH_MA
            ), '') AS state
            FROM CUA_HANG
        ");
        return md5((string)$stmt->fetchColumn());
    }

    // 7. Lấy danh sách xã/phường đang được phục vụ (để kiểm tra khu vực giao hàng)
    public function getActiveWards()
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT CH_XAPHUONG FROM CUA_HANG WHERE CH_TRANGTHAI = true AND CH_XAPHUONG IS NOT NULL ORDER BY CH_XAPHUONG ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Hàm phụ trợ để gán tham số cho gọn
    private function bindParams($stmt, $data)
    {
        $stmt->bindValue(':ten', $data['name']);
        $stmt->bindValue(':diachi', $data['full_address']);
        $stmt->bindValue(':xaphuong', $data['ward_name']);
        $stmt->bindValue(':thanhpho', $data['province_name']);
        $stmt->bindValue(':matinh', $data['province_code']);
        $stmt->bindValue(':maxa', $data['ward_code']);
        $stmt->bindValue(':sonha', $data['street']);
        $stmt->bindValue(':sdt', $data['phone']);
        $stmt->bindValue(':trangthai', $data['status'], PDO::PARAM_BOOL);
        $stmt->bindValue(':giomo', $data['open_time']);
        $stmt->bindValue(':giodong', $data['close_time']);
    }
}
