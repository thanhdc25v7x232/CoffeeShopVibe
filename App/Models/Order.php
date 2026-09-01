<?php

namespace App\Models;

use PDO;
use Throwable;

class Order
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Tạo đơn hàng mới kèm chi tiết đơn hàng từ giỏ hàng (transaction)
    public function create(array $data, array $cartItems): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO DON_HANG (KH_MA, DH_TENKH, DH_SDT, DH_DIACHI, DH_HTNHAN, CH_MA, DH_TONGTIEN, DH_TRANGTHAI, DH_PTTT, DH_TT_TRANGTHAI)
                VALUES (:kh_ma, :tenkh, :sdt, :diachi, :htnhan, :ch_ma, :tongtien, 'pending', :pttt, 'unpaid')
                RETURNING DH_MA
            ");
            $stmt->execute([
                'kh_ma' => $data['customer_id'],
                'tenkh' => $data['name'],
                'sdt' => $data['phone'],
                'diachi' => $data['address'],
                'htnhan' => $data['method'],
                'ch_ma' => $data['store_id'],
                'tongtien' => $data['total'],
                'pttt' => $data['payment_method'],
            ]);
            $orderId = (int)$stmt->fetchColumn();

            $itemStmt = $this->pdo->prepare("
                INSERT INTO CHI_TIET_DON_HANG (DH_MA, SP_MA, CTDH_SOLUONG, CTDH_GIA, CTDH_THANHTIEN)
                VALUES (:dh_ma, :sp_ma, :soluong, :gia, :thanhtien)
            ");
            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    'dh_ma' => $orderId,
                    'sp_ma' => $item['id'],
                    'soluong' => $item['quantity'],
                    'gia' => $item['price'],
                    'thanhtien' => $item['price'] * $item['quantity'],
                ]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Lấy thông tin một đơn hàng (kèm tên cửa hàng nếu có)
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, c.CH_TEN
            FROM DON_HANG d
            LEFT JOIN CUA_HANG c ON d.CH_MA = c.CH_MA
            WHERE d.DH_MA = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Lấy chi tiết các sản phẩm trong một đơn hàng
    public function getItemsByOrderId(int $id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ct.*, s.SP_TEN, s.SP_HINH
            FROM CHI_TIET_DON_HANG ct
            LEFT JOIN SAN_PHAM s ON ct.SP_MA = s.SP_MA
            WHERE ct.DH_MA = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy lịch sử đơn hàng của một khách hàng ("Đơn hàng của tôi")
    /**
     * Mã băm đại diện cho trạng thái toàn bộ đơn hàng của MỘT khách hàng cụ thể.
     * Đổi giá trị khi admin cập nhật trạng thái bất kỳ đơn nào của khách này (hoặc có đơn mới).
     * Dùng cho JS phía trang "Đơn hàng của tôi" polling, không lộ dữ liệu khách khác.
     */
    public function getCustomerOrdersVersion(int $customerId): string
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(STRING_AGG(DH_MA || ':' || DH_TRANGTHAI, ',' ORDER BY DH_MA), '') AS state
            FROM DON_HANG
            WHERE KH_MA = :customer_id
        ");
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return md5((string)$stmt->fetchColumn());
    }

    public function findByCustomer(int $customerId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, c.CH_TEN
            FROM DON_HANG d
            LEFT JOIN CUA_HANG c ON d.CH_MA = c.CH_MA
            WHERE d.KH_MA = :customer_id
            ORDER BY d.DH_NGAYTAO DESC
        ");
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Danh sách đơn hàng cho admin (có phân trang)
    public function getAllPaginated(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, c.CH_TEN
            FROM DON_HANG d
            LEFT JOIN CUA_HANG c ON d.CH_MA = c.CH_MA
            ORDER BY d.DH_NGAYTAO DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countTotal(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM DON_HANG")->fetchColumn();
    }

    /**
     * Tín hiệu nhẹ cho JS admin polling: mã đơn hàng lớn nhất hiện có + số đơn đang chờ xử lý.
     * Client so sánh max_order_id với giá trị lúc tải trang để biết có đơn mới hay chưa.
     */
    public function getLatestOrderSignal(): array
    {
        $row = $this->pdo->query("
            SELECT
                COALESCE(MAX(DH_MA), 0) AS max_order_id,
                COUNT(*) FILTER (WHERE DH_TRANGTHAI = 'pending') AS pending_count
            FROM DON_HANG
        ")->fetch();

        return [
            'max_order_id' => (int)$row['max_order_id'],
            'pending_count' => (int)$row['pending_count'],
        ];
    }

    // Cập nhật trạng thái đơn hàng.
    // Khi chuyển sang "completed" lần đầu, trừ luôn tồn kho các sản phẩm trong đơn (trong cùng transaction).
    public function updateStatus(int $id, string $status): bool
    {
        $current = $this->findById($id);
        if (!$current) {
            return false;
        }

        if ($status === 'completed' && $current['dh_trangthai'] !== 'completed') {
            $this->pdo->beginTransaction();
            try {
                $stmt = $this->pdo->prepare("UPDATE DON_HANG SET DH_TRANGTHAI = :status WHERE DH_MA = :id");
                $stmt->execute(['status' => $status, 'id' => $id]);

                $items = $this->pdo->prepare("SELECT SP_MA, CTDH_SOLUONG FROM CHI_TIET_DON_HANG WHERE DH_MA = :id");
                $items->execute(['id' => $id]);

                $deduct = $this->pdo->prepare("
                    UPDATE SAN_PHAM SET SP_TONKHO = GREATEST(SP_TONKHO - :qty, 0) WHERE SP_MA = :sp_ma
                ");
                foreach ($items->fetchAll() as $item) {
                    $deduct->execute(['qty' => $item['ctdh_soluong'], 'sp_ma' => $item['sp_ma']]);
                }

                $this->pdo->commit();
                return true;
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        $stmt = $this->pdo->prepare("UPDATE DON_HANG SET DH_TRANGTHAI = :status WHERE DH_MA = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Admin xác nhận thủ công một đơn (thường là COD) đã được thu tiền.
    public function markPaid(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE DON_HANG SET DH_TT_TRANGTHAI = 'paid' WHERE DH_MA = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Khách tự báo "đã chuyển khoản" cho thanh toán QR thủ công (MoMo cá nhân, chuyển khoản).
    // Chỉ chuyển từ 'unpaid', không đè lên 'paid' nếu admin đã xác nhận trước đó.
    public function markWaitingConfirmation(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE DON_HANG SET DH_TT_TRANGTHAI = 'waiting_confirmation'
            WHERE DH_MA = :id AND DH_TT_TRANGTHAI = 'unpaid'
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Số liệu thống kê cho dashboard admin
    public function getStatistics(): array
    {
        $totalOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM DON_HANG")->fetchColumn();
        $totalRevenue = (float)$this->pdo->query(
            "SELECT COALESCE(SUM(DH_TONGTIEN), 0) FROM DON_HANG WHERE DH_TRANGTHAI != 'cancelled'"
        )->fetchColumn();
        $totalProducts = (int)$this->pdo->query("SELECT COUNT(*) FROM SAN_PHAM")->fetchColumn();
        $totalCustomers = (int)$this->pdo->query("SELECT COUNT(*) FROM KHACH_HANG")->fetchColumn();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers,
        ];
    }
}
