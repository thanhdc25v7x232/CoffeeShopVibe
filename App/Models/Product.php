<?php

namespace App\Models;

use PDO;

class Product
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Lấy danh sách sản phẩm mới nhất
    public function getLatestProducts($limit = 8)
    {
        // Sử dụng Prepare Statement để ngăn chặn SQL Injection
        // Lưu ý: PostgreSQL trả về tên cột thường là chữ thường khi fetchAll
        $stmt = $this->pdo->prepare("
            SELECT SP_MA, SP_TEN, SP_GIA, SP_HINH, SP_MOTA 
            FROM SAN_PHAM 
            ORDER BY SP_NGAYTAO DESC 
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // 2. Lấy danh sách Loại sản phẩm (Để hiển thị trong menu xổ xuống)
    public function getCategories()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM LOAI_SAN_PHAM ORDER BY L_MA ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 3. Thêm sản phẩm mới (Đã cập nhật thêm cột L_MA, KM_MA)
    public function addProduct($data)
    {
        // Câu lệnh SQL chèn dữ liệu, bao gồm cả khóa ngoại L_MA, KM_MA
        $sql = "INSERT INTO SAN_PHAM (SP_TEN, SP_GIA, SP_MOTA, SP_HINH, L_MA, KM_MA, SP_TONKHO)
                VALUES (:ten, :gia, :mota, :hinh, :loai, :khuyenmai, :tonkho)";

        $stmt = $this->pdo->prepare($sql);

        // Gán dữ liệu vào tham số
        $stmt->bindValue(':ten', $data['name']);
        $stmt->bindValue(':gia', $data['price']);
        $stmt->bindValue(':mota', $data['description']);
        $stmt->bindValue(':hinh', $data['image']);
        $stmt->bindValue(':tonkho', $data['stock'] ?? 0, PDO::PARAM_INT);

        // Xử lý logic: Nếu người dùng không chọn loại (hoặc rỗng) thì lưu là NULL
        // Lưu ý: Trong Controller ta dùng key là 'category_id'
        $loai = !empty($data['category_id']) ? $data['category_id'] : null;
        $stmt->bindValue(':loai', $loai);

        $khuyenMai = !empty($data['promotion_id']) ? $data['promotion_id'] : null;
        $stmt->bindValue(':khuyenmai', $khuyenMai);

        return $stmt->execute();
    }

    // Thêm vào trong class Product

    // Điều kiện JOIN khuyến mãi đang còn hiệu lực (dùng chung cho các query sản phẩm)
    private const ACTIVE_PROMOTION_JOIN = "LEFT JOIN KHUYEN_MAI km ON p.KM_MA = km.KM_MA
            AND km.KM_TRANGTHAI = true
            AND CURRENT_DATE BETWEEN km.KM_NGAYBATDAU AND km.KM_NGAYKETTHUC";

    // Thêm km_phantram / gia_hien_thi (giá sau khi giảm) vào một dòng sản phẩm
    private function withEffectivePrice(array $row): array
    {
        $row['km_phantram'] = isset($row['km_phantram']) ? (int)$row['km_phantram'] : null;
        $price = (float)$row['sp_gia'];
        $row['gia_hien_thi'] = $row['km_phantram']
            ? round($price * (1 - $row['km_phantram'] / 100), 2)
            : $price;
        return $row;
    }

    // 1. Lấy danh sách sản phẩm (Có lọc theo loại và Phân trang)
    public function getProductsPaginated($limit, $offset, $categoryId = null)
    {
        $sql = "SELECT p.*, l.L_TEN, km.KM_PHANTRAM
            FROM SAN_PHAM p
            LEFT JOIN LOAI_SAN_PHAM l ON p.L_MA = l.L_MA
            " . self::ACTIVE_PROMOTION_JOIN;

        // Nếu có lọc theo loại
        if ($categoryId) {
            $sql .= " WHERE p.L_MA = :category_id";
        }

        $sql .= " ORDER BY p.SP_NGAYTAO DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($categoryId) {
            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return array_map([$this, 'withEffectivePrice'], $stmt->fetchAll());
    }

    // 2. Đếm tổng số sản phẩm (Để tính số trang)
    public function countTotal($categoryId = null)
    {
        $sql = "SELECT COUNT(*) FROM SAN_PHAM";
        if ($categoryId) {
            $sql .= " WHERE L_MA = :category_id";
        }

        $stmt = $this->pdo->prepare($sql);
        if ($categoryId) {
            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // 3. Cập nhật sản phẩm
    public function updateProduct($data)
    {
        // Nếu có ảnh mới thì cập nhật cả ảnh, không thì giữ nguyên
        if (!empty($data['image'])) {
            $sql = "UPDATE SAN_PHAM SET SP_TEN=:ten, SP_GIA=:gia, SP_MOTA=:mota, SP_HINH=:hinh, L_MA=:loai, KM_MA=:khuyenmai, SP_TONKHO=:tonkho, SP_NGAYCAPNHAT=CURRENT_TIMESTAMP
                WHERE SP_MA=:id";
        } else {
            $sql = "UPDATE SAN_PHAM SET SP_TEN=:ten, SP_GIA=:gia, SP_MOTA=:mota, L_MA=:loai, KM_MA=:khuyenmai, SP_TONKHO=:tonkho, SP_NGAYCAPNHAT=CURRENT_TIMESTAMP
                WHERE SP_MA=:id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ten', $data['name']);
        $stmt->bindValue(':gia', $data['price']);
        $stmt->bindValue(':mota', $data['description']);
        $stmt->bindValue(':loai', $data['category_id']);
        $stmt->bindValue(':khuyenmai', !empty($data['promotion_id']) ? $data['promotion_id'] : null);
        $stmt->bindValue(':tonkho', $data['stock'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $data['id']);

        if (!empty($data['image'])) {
            $stmt->bindValue(':hinh', $data['image']);
        }

        return $stmt->execute();
    }

    public function findRawById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM SAN_PHAM WHERE SP_MA = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function clearProductImage($id)
    {
        $stmt = $this->pdo->prepare("UPDATE SAN_PHAM SET SP_HINH = NULL WHERE SP_MA = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // 4. Xóa sản phẩm
    public function deleteProduct($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM SAN_PHAM WHERE SP_MA = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    // 5. Lấy sản phẩm theo ID
    public function getProductById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, p.L_MA, p.KM_MA, p.SP_NGAYCAPNHAT, p.SP_TONKHO, km.KM_PHANTRAM
            FROM SAN_PHAM p
            " . self::ACTIVE_PROMOTION_JOIN . "
            WHERE p.SP_MA = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $this->withEffectivePrice($row) : $row;
    }

    // 6. Lấy tất cả sản phẩm theo loại (không giới hạn để có thể slider)
    public function getProductsByCategory($categoryId)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, p.L_MA, l.L_TEN, km.KM_PHANTRAM
            FROM SAN_PHAM p
            LEFT JOIN LOAI_SAN_PHAM l ON p.L_MA = l.L_MA
            " . self::ACTIVE_PROMOTION_JOIN . "
            WHERE p.L_MA = :category_id
            ORDER BY p.SP_NGAYTAO DESC
        ");
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([$this, 'withEffectivePrice'], $stmt->fetchAll());
    }

    // 7. Lấy tất cả sản phẩm mới nhất (không giới hạn để có thể slider)
    public function getAllLatestProducts()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, p.L_MA, l.L_TEN, km.KM_PHANTRAM
            FROM SAN_PHAM p
            LEFT JOIN LOAI_SAN_PHAM l ON p.L_MA = l.L_MA
            " . self::ACTIVE_PROMOTION_JOIN . "
            ORDER BY p.SP_NGAYTAO DESC
        ");
        $stmt->execute();
        return array_map([$this, 'withEffectivePrice'], $stmt->fetchAll());
    }

    // 8. Tìm kiếm sản phẩm theo tên (hỗ trợ gõ không dấu, vd: "ca phe" khớp "Cà Phê")
    public function searchProducts($keyword)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, km.KM_PHANTRAM
            FROM SAN_PHAM p
            " . self::ACTIVE_PROMOTION_JOIN . "
            ORDER BY p.SP_NGAYTAO DESC
        ");
        $stmt->execute();

        $normalizedKeyword = normalize_text($keyword);
        if ($normalizedKeyword === '') {
            return [];
        }

        $matched = array_filter($stmt->fetchAll(), function ($row) use ($normalizedKeyword) {
            return str_contains(normalize_text($row['sp_ten']), $normalizedKeyword);
        });

        return array_map([$this, 'withEffectivePrice'], array_values($matched));
    }
    // 9. Lấy sản phẩm theo loại với sắp xếp giá
    public function getProductsByCategoryWithSort($categoryId, $sortBy = 'default')
    {
        $sql = "
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, km.KM_PHANTRAM
            FROM SAN_PHAM p
            " . self::ACTIVE_PROMOTION_JOIN . "
            WHERE p.L_MA = :category_id
        ";

        // Xử lý sắp xếp
        switch ($sortBy) {
            case 'price_asc':
                $sql .= " ORDER BY p.SP_GIA ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.SP_GIA DESC";
                break;
            default:
                $sql .= " ORDER BY p.SP_NGAYTAO DESC";
                break;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([$this, 'withEffectivePrice'], $stmt->fetchAll());
    }

    // 11. Lấy sản phẩm cùng loại, dùng cho "sản phẩm liên quan"
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_GIA, p.SP_HINH, p.SP_MOTA, km.KM_PHANTRAM
            FROM SAN_PHAM p
            " . self::ACTIVE_PROMOTION_JOIN . "
            WHERE p.L_MA = :category_id AND p.SP_MA != :exclude_id
            ORDER BY p.SP_NGAYTAO DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map([$this, 'withEffectivePrice'], $stmt->fetchAll());
    }

    // 10. Lấy thông tin loại sản phẩm theo ID
    public function getCategoryById($categoryId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM LOAI_SAN_PHAM WHERE L_MA = :id");
        $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Mã băm đại diện cho toàn bộ dữ liệu sản phẩm + khuyến mãi hiện tại.
     * Đổi giá trị bất cứ khi nào có thêm/sửa/xóa sản phẩm hoặc khuyến mãi.
     * Dùng cho JS phía client polling để phát hiện dữ liệu vừa thay đổi ở trang admin.
     */
    public function getDataVersion(): string
    {
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM SAN_PHAM) AS sp_count,
                (SELECT COALESCE(MAX(SP_NGAYCAPNHAT), TIMESTAMP '1970-01-01') FROM SAN_PHAM) AS sp_last,
                (SELECT COUNT(*) FROM KHUYEN_MAI) AS km_count,
                (SELECT COALESCE(STRING_AGG(KM_MA || ':' || KM_PHANTRAM || ':' || KM_TRANGTHAI, ',' ORDER BY KM_MA), '') FROM KHUYEN_MAI) AS km_state
        ");
        $row = $stmt->fetch();
        return md5(implode('|', $row));
    }

    // Cập nhật nhanh tồn kho một sản phẩm (dùng ở trang báo cáo tồn kho admin)
    public function updateStock($id, $soLuong)
    {
        $stmt = $this->pdo->prepare("
            UPDATE SAN_PHAM SET SP_TONKHO = :tonkho, SP_NGAYCAPNHAT = CURRENT_TIMESTAMP WHERE SP_MA = :id
        ");
        $stmt->bindValue(':tonkho', max(0, (int)$soLuong), PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Báo cáo tồn kho cho admin: toàn bộ sản phẩm, tồn kho thấp lên trước
    public function getInventoryReport(int $lowStockThreshold = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.SP_MA, p.SP_TEN, p.SP_HINH, p.SP_TONKHO, l.L_TEN
            FROM SAN_PHAM p
            LEFT JOIN LOAI_SAN_PHAM l ON p.L_MA = l.L_MA
            ORDER BY p.SP_TONKHO ASC, p.SP_TEN ASC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map(function (array $row) use ($lowStockThreshold) {
            $row['low_stock'] = (int)$row['sp_tonkho'] <= $lowStockThreshold;
            return $row;
        }, $rows);
    }
}
