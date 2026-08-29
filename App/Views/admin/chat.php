<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-comments me-2"></i>
        Quản lý trò chuyện
    </h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Khách hàng</th>
                            <th>Tin nhắn cuối</th>
                            <th>Thời gian</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="conversation-list">
                        <?php if (empty($conversations)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Chưa có hội thoại nào.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($conversations as $conv): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($conv['kh_ten']) ?></strong></td>
                                <td class="text-truncate" style="max-width: 320px;">
                                    <?= $conv['last_sender'] === 'admin' ? '<span class="text-muted">Bạn: </span>' : '' ?>
                                    <?= htmlspecialchars($conv['last_message']) ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($conv['last_time'])) ?></td>
                                <td class="text-center">
                                    <?php if ((int)$conv['unread_count'] > 0): ?>
                                        <span class="badge bg-danger"><?= (int)$conv['unread_count'] ?> tin mới</span>
                                    <?php else: ?>
                                        <span class="text-muted small">Đã đọc</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="/admin/chat/conversation?customer_id=<?= $conv['kh_ma'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-message"></i> Xem
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function wsUrl() {
        if (location.protocol === 'https:') {
            return 'wss://' + location.host + '/ws?role=admin';
        }
        return 'ws://' + location.hostname + ':<?= (int)($_ENV['WS_PORT'] ?? 8081) ?>/?role=admin';
    }

    function connect() {
        var ws;
        try {
            ws = new WebSocket(wsUrl());
        } catch (e) {
            console.error('[admin-chat] Không thể tạo kết nối WebSocket:', e);
            return;
        }
        ws.onopen = function () {
            console.log('[admin-chat] Đã kết nối WebSocket (' + wsUrl() + ')');
        };
        // Có tin nhắn mới bất kỳ (từ khách hoặc admin khác) -> tải lại danh sách hội thoại ngay
        ws.onmessage = function () {
            location.reload();
        };
        ws.onerror = function (event) {
            console.error('[admin-chat] Lỗi kết nối WebSocket — danh sách sẽ không tự cập nhật. Kiểm tra: đã chạy "php websocket_server.php" chưa?', event);
        };
        ws.onclose = function () {
            setTimeout(connect, 5000);
        };
    }
    connect();
})();
</script>
