<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="fa-solid fa-comments me-2"></i>
            Trò chuyện với <?= htmlspecialchars($customer->kh_ten) ?>
        </h2>
        <a href="/admin/chat" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Danh sách hội thoại
        </a>
    </div>

    <div class="card shadow-sm">
        <div id="chat-box" class="card-body" style="height: 420px; overflow-y: auto;">
            <?php foreach ($messages as $m): ?>
                <div class="mb-2 d-flex <?= $m['tn_nguoigui'] === 'admin' ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="px-2 py-1 rounded small <?= $m['tn_nguoigui'] === 'admin' ? 'bg-success text-white' : 'bg-light border' ?>" style="max-width: 70%; word-break: break-word;">
                        <?= nl2br(htmlspecialchars($m['tn_noidung'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer">
            <form id="reply-form" method="POST" action="/admin/chat/send" class="d-flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="customer_id" value="<?= $customer->kh_ma ?>">
                <input type="text" id="reply-input" name="noi_dung" class="form-control" placeholder="Nhập tin nhắn trả lời..." required autocomplete="off">
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-paper-plane"></i> Gửi
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var customerId = <?= (int)$customer->kh_ma ?>;
    var box = document.getElementById('chat-box');
    var form = document.getElementById('reply-form');
    var input = document.getElementById('reply-input');
    var ws = null;

    box.scrollTop = box.scrollHeight;

    function appendMessage(m) {
        var row = document.createElement('div');
        row.className = 'mb-2 d-flex ' + (m.tn_nguoigui === 'admin' ? 'justify-content-end' : 'justify-content-start');
        var bubble = document.createElement('div');
        bubble.className = 'px-2 py-1 rounded small ' + (m.tn_nguoigui === 'admin' ? 'bg-success text-white' : 'bg-light border');
        bubble.style.maxWidth = '70%';
        bubble.style.wordBreak = 'break-word';
        bubble.textContent = m.tn_noidung;
        row.appendChild(bubble);
        box.appendChild(row);
        box.scrollTop = box.scrollHeight;
    }

    function wsUrl() {
        if (location.protocol === 'https:') {
            return 'wss://' + location.host + '/ws?role=admin';
        }
        return 'ws://' + location.hostname + ':<?= (int)($_ENV['WS_PORT'] ?? 8081) ?>/?role=admin';
    }

    function connect() {
        try {
            ws = new WebSocket(wsUrl());
        } catch (e) {
            console.error('[admin-chat] Không thể tạo kết nối WebSocket:', e);
            return;
        }
        ws.onopen = function () {
            console.log('[admin-chat] Đã kết nối WebSocket (' + wsUrl() + ')');
        };
        ws.onmessage = function (event) {
            var data = JSON.parse(event.data);
            // Chỉ hiện tin nhắn thuộc đúng hội thoại đang xem, tránh lộ nhầm hội thoại khác.
            if (parseInt(data.kh_ma, 10) === customerId) {
                appendMessage(data);
            }
        };
        ws.onerror = function (event) {
            console.error('[admin-chat] Lỗi kết nối WebSocket — sẽ không nhận tin nhắn real-time. Kiểm tra: đã chạy "php websocket_server.php" chưa?', event);
        };
        ws.onclose = function () {
            setTimeout(connect, 5000);
        };
    }
    connect();

    form.addEventListener('submit', function (e) {
        var text = input.value.trim();
        if (!text) return;

        if (ws && ws.readyState === WebSocket.OPEN) {
            e.preventDefault();
            ws.send(JSON.stringify({ noi_dung: text, customer_id: customerId }));
            input.value = '';
            return;
        }

        // Không có WebSocket -> để form submit POST như cũ (dự phòng).
    });
})();
</script>
