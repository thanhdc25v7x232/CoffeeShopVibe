<?php

namespace App\WebSocket;

use App\Models\ChatMessage;
use PDO;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

/**
 * Máy chủ WebSocket cho chat khách hàng <-> admin. Danh tính người dùng lấy từ session PHP
 * (cookie PHPSESSID gửi kèm lúc bắt tay WebSocket), không tin bất kỳ trường nào client tự khai.
 */
class ChatServer implements MessageComponentInterface
{
    protected $chatModel;

    /** @var SplObjectStorage tất cả kết nối đang mở */
    protected $clients;

    /** @var array<int, SplObjectStorage> kh_ma => tập kết nối (hỗ trợ nhiều tab của cùng khách) */
    protected $customerConns = [];

    /** @var SplObjectStorage tập kết nối admin đang mở */
    protected $adminConns;

    public function __construct(PDO $pdo)
    {
        $this->chatModel = new ChatMessage($pdo);
        $this->clients = new SplObjectStorage();
        $this->adminConns = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Client PHẢI khai báo mình đang mở kết nối cho trang nào (?role=customer|admin).
        // Đây KHÔNG phải xác thực (identity vẫn luôn lấy từ cookie session, không tin client) —
        // nó chỉ quyết định connection này thuộc nhóm broadcast nào. Lý do cần có: 1 trình
        // duyệt có thể mang CẢ 2 cookie cùng lúc (đã đăng nhập cả khách lẫn admin, nay cho
        // phép vì 2 cookie khác tên nhau) — nếu suy luận "role" chỉ từ việc cookie admin có
        // hợp lệ hay không, connection mở từ trang khách hàng sẽ bị xếp nhầm vào nhóm admin,
        // vừa khiến tin nhắn của chính khách bị hiểu sai vừa khiến khách nhận được broadcast
        // của các cuộc trò chuyện khác (rò rỉ hội thoại người khác) — đã tái hiện thực tế.
        $role = $this->resolveRoleFromQuery($conn);
        $identity = $this->resolveIdentityFromCookie($conn);

        if ($role === 'admin') {
            if (!$identity['admin_id']) {
                $this->log('onOpen: TU CHOI (role=admin nhung cookie admin khong hop le)');
                $conn->close();
                return;
            }
            $conn->khMa = null;
            $conn->isAdmin = true;
            $this->log('onOpen: OK - admin');
            $this->clients->attach($conn);
            $this->adminConns->attach($conn);
            return;
        }

        if ($role === 'customer') {
            if (!$identity['customer_id']) {
                $this->log('onOpen: TU CHOI (role=customer nhung cookie khach khong hop le)');
                $conn->close();
                return;
            }
            $conn->khMa = $identity['customer_id'];
            $conn->isAdmin = false;
            $this->log('onOpen: OK - khach kh_ma=' . $conn->khMa);
            $this->clients->attach($conn);
            if (!isset($this->customerConns[$conn->khMa])) {
                $this->customerConns[$conn->khMa] = new SplObjectStorage();
            }
            $this->customerConns[$conn->khMa]->attach($conn);
            return;
        }

        $this->log('onOpen: TU CHOI (thieu hoac sai tham so ?role=customer|admin)');
        $conn->close();
    }

    protected function resolveRoleFromQuery(ConnectionInterface $conn): ?string
    {
        parse_str($conn->httpRequest->getUri()->getQuery(), $params);
        $role = $params['role'] ?? null;
        return in_array($role, ['customer', 'admin'], true) ? $role : null;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        $noiDung = trim($data['noi_dung'] ?? '');
        if ($noiDung === '') {
            $this->log('onMessage: BO QUA (noi_dung rong hoac JSON khong hop le)');
            return;
        }

        // Phân loại theo HÌNH DẠNG payload (widget khách hàng không bao giờ gửi kèm
        // customer_id, chỉ trang trả lời của admin mới gửi), KHÔNG dựa vào việc connection
        // này "có phải admin không". Lý do: nếu một trình duyệt từng đăng nhập cả 2 vai
        // (giờ dùng 2 cookie riêng ADMIN_SESSID/PHPSESSID nên được phép cùng tồn tại), một
        // kết nối mở từ trang khách hàng vẫn có thể có $from->isAdmin=true (vì cookie admin
        // cũ vẫn còn hợp lệ) dù tin nhắn thực chất đến từ khung chat khách hàng — ưu tiên
        // theo isAdmin sẽ hiểu nhầm và âm thầm loại bỏ tin nhắn (đã xác minh thực tế qua log).
        $hasCustomerId = array_key_exists('customer_id', (array)$data);

        if ($hasCustomerId) {
            // Tin nhắn admin trả lời cho 1 khách cụ thể -> yêu cầu connection này thực sự là admin.
            if (empty($from->isAdmin)) {
                $this->log('onMessage: TU CHOI (gui kem customer_id nhung connection khong phai admin)');
                return;
            }
            $customerId = (int)$data['customer_id'];
            if ($customerId <= 0) {
                return;
            }
            $this->log('onMessage: admin -> khach kh_ma=' . $customerId . ' : ' . $noiDung);
            $this->chatModel->send($customerId, 'admin', $noiDung);
            $payload = [
                'tn_nguoigui' => 'admin',
                'tn_noidung' => $noiDung,
                'kh_ma' => $customerId,
            ];
            $this->broadcastToCustomer($customerId, $payload);
            $this->broadcastToAdmins($payload);
            return;
        }

        // Không kèm customer_id -> tin nhắn của chính khách hàng đang mở connection này.
        if (!empty($from->khMa)) {
            $customerId = (int)$from->khMa;
            $this->log('onMessage: khach kh_ma=' . $customerId . ' : ' . $noiDung);
            $this->chatModel->send($customerId, 'customer', $noiDung);
            $payload = [
                'tn_nguoigui' => 'customer',
                'tn_noidung' => $noiDung,
                'kh_ma' => $customerId,
            ];
            $this->broadcastToCustomer($customerId, $payload);
            $this->broadcastToAdmins($payload);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->log('onClose: ' . (!empty($conn->isAdmin) ? 'admin' : ('khach kh_ma=' . ($conn->khMa ?? '?'))) . ' ngat ket noi');
        $this->clients->detach($conn);
        $this->adminConns->detach($conn);
        if (!empty($conn->khMa) && isset($this->customerConns[$conn->khMa])) {
            $this->customerConns[$conn->khMa]->detach($conn);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->log('onError: ' . $e->getMessage());
        $conn->close();
    }

    // Dùng fwrite(STDOUT, ...) thay vì echo (xem giải thích trong websocket_server.php).
    protected function log(string $message): void
    {
        fwrite(STDOUT, '[' . date('H:i:s') . '] ' . $message . "\n");
    }

    protected function broadcastToCustomer(int $customerId, array $payload): void
    {
        if (!isset($this->customerConns[$customerId])) {
            return;
        }
        $json = json_encode($payload);
        foreach ($this->customerConns[$customerId] as $conn) {
            $conn->send($json);
        }
    }

    protected function broadcastToAdmins(array $payload): void
    {
        $json = json_encode($payload);
        foreach ($this->adminConns as $conn) {
            $conn->send($json);
        }
    }

    /**
     * Đọc cookie session lúc bắt tay WebSocket, rồi đọc TRỰC TIẾP file session tương ứng
     * trên đĩa để lấy customer_id/admin_id.
     *
     * Khách hàng và admin dùng 2 TÊN COOKIE session khác nhau (xem public/index.php:
     * PHPSESSID cho khách, ADMIN_SESSID cho admin) — cố tình tách riêng để đăng nhập admin
     * ở 1 tab không ghi đè cookie khách hàng đang mở ở tab khác trong CÙNG trình duyệt (đã
     * tái hiện thực tế: cookie là theo domain chứ không theo tab, dùng chung 1 tên cookie
     * khiến tab kia bất ngờ "biến" thành vai trò khác, tưởng như tự bị đăng xuất).
     *
     * QUAN TRỌNG: không được dùng session_id()/session_start()/session_write_close() ở đây.
     * Daemon này là 1 tiến trình sống lâu dài, xử lý session của RẤT NHIỀU người dùng khác
     * nhau tuần tự trong cùng 1 process. session_start() nạp dữ liệu vào biến toàn cục
     * $_SESSION nhưng KHÔNG xóa các khóa còn sót lại từ lần session_start() trước đó (của
     * một người dùng khác) — dẫn đến $_SESSION bị trộn lẫn dữ liệu giữa nhiều người, rồi
     * session_write_close() ghi đè nhầm dữ liệu đó vào file session của người khác (đã xác
     * minh thực tế: sess_<khách> bị dính thêm admin_id của người khác). Đọc file trực tiếp,
     * chỉ đọc (read-only), tránh hoàn toàn vấn đề này, đồng thời không kích hoạt session GC.
     */
    protected function resolveIdentityFromCookie(ConnectionInterface $conn): array
    {
        $result = ['customer_id' => null, 'admin_id' => null];

        $cookieHeader = $conn->httpRequest->getHeaderLine('Cookie');
        if ($cookieHeader === '') {
            return $result;
        }

        $customerSid = $this->extractCookieValue($cookieHeader, 'PHPSESSID');
        if ($customerSid) {
            $data = $this->readSessionFile($customerSid);
            if (!empty($data['customer_id'])) {
                $result['customer_id'] = (int)$data['customer_id'];
            }
        }

        $adminSid = $this->extractCookieValue($cookieHeader, 'ADMIN_SESSID');
        if ($adminSid) {
            $data = $this->readSessionFile($adminSid);
            if (!empty($data['admin_id'])) {
                $result['admin_id'] = (int)$data['admin_id'];
            }
        }

        return $result;
    }

    protected function extractCookieValue(string $cookieHeader, string $name): ?string
    {
        foreach (explode(';', $cookieHeader) as $pair) {
            $pair = trim($pair);
            if (str_starts_with($pair, $name . '=')) {
                $value = substr($pair, strlen($name) + 1);
                // Chỉ chấp nhận ký tự hợp lệ trong session ID (chặn path traversal khi ghép tên file).
                return preg_match('/^[a-zA-Z0-9,-]+$/', $value) ? $value : null;
            }
        }
        return null;
    }

    /**
     * Đọc và giải mã (chỉ đọc, không mutate $_SESSION) nội dung file session PHP mặc định
     * (session.save_handler=files, session.serialize_handler=php).
     */
    protected function readSessionFile(string $sessionId): array
    {
        $savePath = session_save_path();
        $savePath = $savePath ? explode(';', $savePath) : [];
        $dir = end($savePath) ?: sys_get_temp_dir();

        $file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
        if (!is_file($file)) {
            return [];
        }

        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') {
            return [];
        }

        try {
            return $this->parseSessionData($raw);
        } catch (\Throwable $e) {
            // Dữ liệu bất thường/không parse được -> coi như không xác định được danh tính.
            return [];
        }
    }

    protected function parseSessionData(string $raw): array
    {
        $result = [];
        $pos = 0;
        $len = strlen($raw);

        while ($pos < $len) {
            $pipePos = strpos($raw, '|', $pos);
            if ($pipePos === false) {
                break;
            }
            $name = substr($raw, $pos, $pipePos - $pos);
            $pos = $pipePos + 1;
            $result[$name] = $this->parseSerializedValue($raw, $pos);
        }

        return $result;
    }

    /**
     * Parse 1 giá trị theo định dạng serialize() chuẩn của PHP, bắt đầu từ $pos, và di chuyển
     * $pos tới ngay sau giá trị đó. Chỉ hỗ trợ các kiểu $_SESSION của app này thực sự dùng tới
     * (null, bool, int, float, string, array) — gặp kiểu khác (object...) sẽ ném exception để
     * dừng parse an toàn thay vì đọc sai.
     */
    protected function parseSerializedValue(string $raw, int &$pos)
    {
        $type = $raw[$pos] ?? '';

        switch ($type) {
            case 'N': // N;
                $pos += 2;
                return null;

            case 'b': // b:0; hoặc b:1;
                $value = ($raw[$pos + 2] ?? '0') === '1';
                $pos += 4;
                return $value;

            case 'i': // i:123;
                $semi = strpos($raw, ';', $pos);
                $value = (int)substr($raw, $pos + 2, $semi - $pos - 2);
                $pos = $semi + 1;
                return $value;

            case 'd': // d:1.5;
                $semi = strpos($raw, ';', $pos);
                $value = (float)substr($raw, $pos + 2, $semi - $pos - 2);
                $pos = $semi + 1;
                return $value;

            case 's': // s:5:"hello";
                $colon = strpos($raw, ':', $pos + 2);
                $strLen = (int)substr($raw, $pos + 2, $colon - $pos - 2);
                $start = $colon + 2; // bỏ qua ':"'
                $value = substr($raw, $start, $strLen);
                $pos = $start + $strLen + 2; // bỏ qua '";'
                return $value;

            case 'a': // a:2:{...}
                $colon = strpos($raw, ':', $pos + 2);
                $count = (int)substr($raw, $pos + 2, $colon - $pos - 2);
                $pos = $colon + 2; // bỏ qua ':{'
                $array = [];
                for ($i = 0; $i < $count; $i++) {
                    $key = $this->parseSerializedValue($raw, $pos);
                    $value = $this->parseSerializedValue($raw, $pos);
                    $array[$key] = $value;
                }
                $pos += 1; // bỏ qua '}'
                return $array;

            default:
                throw new \RuntimeException('Kieu du lieu session khong ho tro: ' . $type);
        }
    }
}
