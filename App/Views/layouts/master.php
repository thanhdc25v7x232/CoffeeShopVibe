<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? "Coffee Shop Vibe" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="/css/style.css" rel="stylesheet">
    
</head>
<body>
    
    <?php include 'header.php'; ?>
    
    <div class="main-content" style="min-height: 600px;">
        <?= $content ?? '<p class="text-center">Chưa có nội dung</p>' ?> 
    </div>

    <?php include 'footer.php'; ?>

    <?php if (AUTHGUARD()->isCustomerLoggedIn()): ?>
        <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1050;">
            <div id="chat-panel" class="card shadow" style="display:none; flex-direction: column; width: min(320px, calc(100vw - 32px)); height: min(420px, calc(100vh - 140px)); position: fixed; bottom: 86px; right: 16px;">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
                    <span><i class="fa-solid fa-headset me-2"></i>Hỗ trợ khách hàng</span>
                    <button type="button" class="btn-close btn-close-white btn-sm" id="chat-close" aria-label="Đóng"></button>
                </div>
                <div id="chat-messages" class="p-2" style="flex: 1; overflow-y: auto;"></div>
                <div class="card-footer p-2">
                    <form id="chat-form" class="d-flex gap-1">
                        <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Nhập tin nhắn..." autocomplete="off">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <button type="button" id="chat-toggle" class="btn btn-success rounded-circle shadow position-relative" style="width:56px;height:56px;" title="Chat với shop">
                <i class="fa-solid fa-comment-dots fa-lg"></i>
                <span id="chat-unread-dot" class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="display:none; width:14px; height:14px; padding:0;"></span>
            </button>
        </div>
    <?php else: ?>
        <a href="/login" class="btn btn-success rounded-circle shadow d-flex align-items-center justify-content-center"
           style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; width:56px; height:56px;" title="Đăng nhập để chat với shop">
            <i class="fa-solid fa-comment-dots fa-lg"></i>
        </a>
    <?php endif; ?>

    <!-- Trợ lý AI: luôn hiển thị, không cần đăng nhập (chỉ tư vấn thông tin công khai) -->
    <div style="position: fixed; bottom: 20px; right: 90px; z-index: 1050;">
        <div id="ai-panel" class="card shadow" style="display:none; flex-direction: column; width: min(320px, calc(100vw - 32px)); height: min(420px, calc(100vh - 140px)); position: fixed; bottom: 86px; right: 16px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                <span><i class="fa-solid fa-robot me-2"></i>Trợ lý AI Vibe</span>
                <button type="button" class="btn-close btn-close-white btn-sm" id="ai-close" aria-label="Đóng"></button>
            </div>
            <div id="ai-messages" class="p-2" style="flex: 1; overflow-y: auto;"></div>
            <div class="card-footer p-2">
                <form id="ai-form" class="d-flex gap-1">
                    <input type="text" id="ai-input" class="form-control form-control-sm" placeholder="Hỏi về menu, giá, khuyến mãi..." autocomplete="off">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        <button type="button" id="ai-toggle" class="btn btn-primary rounded-circle shadow" style="width:56px;height:56px;" title="Hỏi trợ lý AI">
            <i class="fa-solid fa-robot fa-lg"></i>
        </button>
    </div>

    <script>
        const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
    </script>

    <script>
    (function () {
        var toggle = document.getElementById('ai-toggle');
        var panel = document.getElementById('ai-panel');
        var closeBtn = document.getElementById('ai-close');
        var form = document.getElementById('ai-form');
        var input = document.getElementById('ai-input');
        var box = document.getElementById('ai-messages');
        var isPanelOpen = false;
        var historyLoaded = false;

        function appendMessage(role, text) {
            var row = document.createElement('div');
            row.className = 'mb-2 d-flex ' + (role === 'user' ? 'justify-content-end' : 'justify-content-start');
            var bubble = document.createElement('div');
            bubble.className = 'px-2 py-1 rounded small ' + (role === 'user' ? 'bg-primary text-white' : 'bg-light border');
            bubble.style.maxWidth = '80%';
            bubble.style.wordBreak = 'break-word';
            bubble.textContent = text;
            row.appendChild(bubble);
            box.appendChild(row);
            box.scrollTop = box.scrollHeight;
            return row;
        }

        function loadHistory() {
            fetch('/ai/messages', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    box.innerHTML = '';
                    if (data.success && data.messages.length) {
                        data.messages.forEach(function (m) { appendMessage(m.role, m.content); });
                    } else {
                        appendMessage('assistant', 'Chào bạn! Mình là trợ lý AI của Vibe, bạn cần hỏi gì về menu, giá hay khuyến mãi không?');
                    }
                })
                .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
        }

        toggle.addEventListener('click', function () {
            isPanelOpen = panel.style.display !== 'flex';
            if (isPanelOpen) {
                // Cả 2 khung (chat & AI) neo cùng 1 vị trí trên mobile để không tràn màn hình,
                // nên chỉ mở 1 khung tại 1 thời điểm để tránh chồng lên nhau.
                var chatPanel = document.getElementById('chat-panel');
                if (chatPanel) chatPanel.style.display = 'none';
                panel.style.display = 'flex';
                if (!historyLoaded) {
                    historyLoaded = true;
                    loadHistory();
                }
            } else {
                panel.style.display = 'none';
            }
        });

        closeBtn.addEventListener('click', function () {
            isPanelOpen = false;
            panel.style.display = 'none';
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = input.value.trim();
            if (!text) return;
            input.value = '';
            input.disabled = true;

            appendMessage('user', text);
            var typingRow = appendMessage('assistant', 'Đang trả lời...');

            var body = new URLSearchParams();
            body.set('_csrf', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
            body.set('noi_dung', text);

            fetch('/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: body.toString()
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                typingRow.remove();
                appendMessage('assistant', data.success ? data.reply : (data.message || 'Có lỗi xảy ra, bạn thử lại nhé.'));
            })
            .catch(function () {
                typingRow.remove();
                appendMessage('assistant', 'Không thể kết nối tới trợ lý AI, bạn thử lại sau nhé.');
            })
            .finally(function () {
                input.disabled = false;
                input.focus();
            });
        });
    })();
    </script>

    <?php if (AUTHGUARD()->isCustomerLoggedIn()): ?>
    <script>
    (function () {
        var toggle = document.getElementById('chat-toggle');
        var panel = document.getElementById('chat-panel');
        var closeBtn = document.getElementById('chat-close');
        var form = document.getElementById('chat-form');
        var input = document.getElementById('chat-input');
        var box = document.getElementById('chat-messages');
        var unreadDot = document.getElementById('chat-unread-dot');
        var isPanelOpen = false;
        var ws = null;

        function appendMessage(m) {
            var row = document.createElement('div');
            row.className = 'mb-2 d-flex ' + (m.tn_nguoigui === 'customer' ? 'justify-content-end' : 'justify-content-start');
            var bubble = document.createElement('div');
            bubble.className = 'px-2 py-1 rounded small ' + (m.tn_nguoigui === 'customer' ? 'bg-success text-white' : 'bg-light border');
            bubble.style.maxWidth = '80%';
            bubble.style.wordBreak = 'break-word';
            bubble.textContent = m.tn_noidung;
            row.appendChild(bubble);
            box.appendChild(row);
            box.scrollTop = box.scrollHeight;
        }

        function renderMessages(messages) {
            box.innerHTML = '';
            messages.forEach(appendMessage);
        }

        function loadHistory() {
            fetch('/chat/messages', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) renderMessages(data.messages);
                })
                .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
        }

        function wsUrl() {
            if (location.protocol === 'https:') {
                return 'wss://' + location.host + '/ws?role=customer'; // qua Caddy reverse-proxy /ws
            }
            return 'ws://' + location.hostname + ':<?= (int)($_ENV['WS_PORT'] ?? 8081) ?>/?role=customer';
        }

        function connectWs() {
            try {
                ws = new WebSocket(wsUrl());
            } catch (e) {
                console.error('[chat] Không thể tạo kết nối WebSocket:', e);
                return;
            }
            ws.onopen = function () {
                console.log('[chat] Đã kết nối WebSocket (' + wsUrl() + ')');
            };
            ws.onmessage = function (event) {
                var data = JSON.parse(event.data);
                if (isPanelOpen) {
                    appendMessage(data);
                } else if (data.tn_nguoigui === 'admin') {
                    unreadDot.style.display = 'block';
                }
            };
            ws.onerror = function (event) {
                console.error('[chat] Lỗi kết nối WebSocket — tin nhắn sẽ gửi qua form dự phòng. Kiểm tra: đã chạy "php websocket_server.php" chưa? Domain hiện tại (' + location.hostname + ') có nằm trong WS_ALLOWED_ORIGINS không?', event);
            };
            ws.onclose = function (event) {
                console.warn('[chat] WebSocket đã đóng (code=' + event.code + ', reason="' + event.reason + '"). Đang thử kết nối lại sau 5s...');
                // Thử kết nối lại sau vài giây nếu daemon WebSocket tạm gián đoạn.
                setTimeout(connectWs, 5000);
            };
        }
        connectWs();

        toggle.addEventListener('click', function () {
            isPanelOpen = panel.style.display !== 'flex';
            if (isPanelOpen) {
                // Xem ghi chú tương tự ở khung AI: 2 khung neo cùng vị trí trên mobile.
                var aiPanel = document.getElementById('ai-panel');
                if (aiPanel) aiPanel.style.display = 'none';
                panel.style.display = 'flex';
                unreadDot.style.display = 'none';
                loadHistory();
            } else {
                panel.style.display = 'none';
            }
        });

        closeBtn.addEventListener('click', function () {
            isPanelOpen = false;
            panel.style.display = 'none';
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = input.value.trim();
            if (!text) return;
            input.value = '';

            if (ws && ws.readyState === WebSocket.OPEN) {
                // Gửi qua WebSocket: server sẽ lưu DB rồi phát ngược lại cho chính mình + admin,
                // nên không cần tự thêm bong bóng chat ở đây (tránh hiện trùng).
                console.log('[chat] Gửi qua WebSocket:', text);
                ws.send(JSON.stringify({ noi_dung: text }));
                return;
            }

            console.log('[chat] WebSocket chưa sẵn sàng (readyState=' + (ws ? ws.readyState : 'null') + '), gửi qua form POST dự phòng.');
            // Dự phòng khi WebSocket chưa kết nối được: gửi qua form POST như cũ.
            var body = new URLSearchParams();
            body.set('_csrf', typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '');
            body.set('noi_dung', text);

            fetch('/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: body.toString()
            })
            .then(function (res) { return res.json(); })
            .then(function () { loadHistory(); })
            .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>