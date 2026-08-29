# Project Niên Luận Cơ Sở (CT271E)

Học kỳ 3, Năm học 2025-2026

**Tên dự án**: Website Coffee Shop VIBE

**MSSV**: DC25V7X232

**Họ tên SV**: Tăng Tố Thanh

**Lớp học phần**: CT271E - Niên Luận cơ sở

## Cài đặt & chạy thử

### Yêu cầu

- PHP >= 8.0 (có extension `pdo_pgsql`)
- PostgreSQL (có extension `pgcrypto`, dùng để hash mật khẩu mẫu)
- Composer
- `php`, `psql`, `composer` đã thêm vào biến môi trường PATH (cách kiểm tra: mở cmd, gõ `php -v` — nếu báo "not recognized" xem mục [Xử lý sự cố](#xử-lý-sự-cố-thường-gặp) bên dưới trước khi tiếp tục)

### Các bước

1. Cài thư viện PHP:
   ```cmd
   composer install
   ```

2. Tạo file `.env` ở thư mục gốc (cùng cấp `composer.json`):
   ```
   DB_HOST="localhost"
   DB_PORT="5432"
   DB_NAME="ct271e_project"
   DB_USER="postgres"
   DB_PASS="postgres"
   APP_DEBUG=true
   WS_PORT="8081"
   WS_ALLOWED_ORIGINS="localhost,127.0.0.1"
   ```
   - `APP_DEBUG=true` hiển thị lỗi chi tiết (PHP `display_errors`), phù hợp lúc phát triển. Khi demo/deploy thật, đặt `APP_DEBUG=false` để tránh lộ đường dẫn, câu truy vấn hoặc thông tin hệ thống khi có lỗi.
   - `WS_PORT`/`WS_ALLOWED_ORIGINS` cấu hình cho WebSocket server chat (Bước 5). `WS_ALLOWED_ORIGINS` là danh sách trắng các **tên miền** (không kèm scheme/cổng) được phép mở kết nối WebSocket — mọi tên miền khác sẽ bị chặn ngay từ bắt tay. Khi deploy công khai (kể cả qua Cloudflare Tunnel, xem mục bên dưới), phải thêm đúng tên miền công khai vào đây thì chat mới real-time được, ví dụ `WS_ALLOWED_ORIGINS="localhost,127.0.0.1,vibe-coffee.example.com"`.

3. Tạo database rồi import schema và dữ liệu mẫu (theo đúng thứ tự), mỗi lệnh `psql` sẽ hỏi mật khẩu PostgreSQL:
   ```cmd
   psql -U postgres -c "CREATE DATABASE ct271e_project"
   psql -U postgres -d ct271e_project -f ct271e_project.sql
   psql -U postgres -d ct271e_project -f seed_data.sql
   ```

4. Chạy server (dùng PHP built-in server, trỏ document root vào `public/`), giữ nguyên cửa sổ cmd này (không đóng):
   ```cmd
   php -S localhost:8000 -t public
   ```
   Thấy dòng `PHP ... Development Server ... started` là chạy thành công. Truy cập http://localhost:8000

5. Mở thêm một cửa sổ cmd khác (giữ nguyên cửa sổ Bước 4), `cd` vào thư mục dự án rồi chạy WebSocket server cho chat (real-time khách hàng ↔ admin):
   ```cmd
   php websocket_server.php
   ```
   Không bắt buộc phải chạy để dùng các tính năng khác — nếu daemon này không chạy, form chat vẫn hoạt động bình thường qua POST (không có cập nhật tức thời).

Đăng nhập thử bằng [tài khoản demo](#tài-khoản-demo-sau-khi-import-seed_datasql) ở cuối file sau khi hoàn tất.

### Chạy HTTPS/HTTP2 cục bộ để demo (Caddy)

Repo có sẵn [Caddyfile](Caddyfile) để demo trang qua HTTPS + HTTP/2 mà không cần domain hay VPS. Có 2 khối cấu hình giống nhau bên trong: `localhost:8443` (HTTPS, chứng chỉ nội bộ tự ký, dùng để mở trực tiếp bằng trình duyệt ở mục này) và `:8444` (HTTP thuần, dùng riêng cho Cloudflare Tunnel ở mục kế tiếp).

1. Cài Caddy — cách nhanh nhất trên Windows là dùng winget:
   ```cmd
   winget install CaddyServer.Caddy --accept-package-agreements --accept-source-agreements
   ```
   (hoặc tải thủ công tại https://caddyserver.com/docs/install). **Đóng cửa sổ cmd đang mở và mở cửa sổ cmd mới** sau khi cài — winget cập nhật PATH nhưng cửa sổ đang mở lúc cài không tự nhận.
2. Chạy server PHP (Bước 4) và WebSocket server (Bước 5) ở trên, giữ nguyên 2 cửa sổ đó.
3. Ở một cửa sổ terminal mới, `cd` vào thư mục dự án rồi chạy:
   ```cmd
   caddy run
   ```
   Lần đầu chạy, Caddy tự cài chứng chỉ CA nội bộ vào Windows để trình duyệt tin tưởng chứng chỉ cục bộ — không cần thao tác gì thêm, log sẽ hiện `certificate installed properly in windows trusts`.
4. Truy cập https://localhost:8443 — đây là bản HTTPS/HTTP2, được Caddy reverse-proxy vào server PHP ở cổng 8000. Caddy cũng gắn `Cache-Control` dài hạn cho `css/img/uploads` tĩnh, và proxy riêng path `/ws` sang WebSocket server ở cổng 8081 (chat vẫn real-time qua HTTPS, trình duyệt không chặn mixed-content).

### Triển khai công khai (có địa chỉ truy cập từ Internet) bằng Cloudflare Tunnel

Đây là cách nhanh nhất để có một URL công khai (đáp ứng tiêu chí "Triển khai website với VPS, Cloudflare Tunnel,…") mà không cần thuê VPS hay có domain riêng.

**Cách 1 — Quick Tunnel (nhanh nhất, không cần tài khoản, dùng để demo tạm thời):**

1. Cài `cloudflared`:
   - Windows: `winget install --id Cloudflare.cloudflared` (hoặc tải file `.exe` tại https://github.com/cloudflare/cloudflared/releases)
2. Chạy server PHP như bước 4 ở trên (`php -S localhost:8000 -t public`), giữ nguyên cửa sổ đó.
3. Ở một cửa sổ terminal khác, chạy:
   ```
   cloudflared tunnel --url http://localhost:8000
   ```
4. `cloudflared` in ra một URL dạng `https://xxxx-xxxx-xxxx.trycloudflare.com` — đây là địa chỉ công khai, ai cũng truy cập được, tự động có HTTPS do Cloudflare cấp. **Lưu ý:** URL này chỉ tồn tại khi lệnh trên còn chạy và sẽ đổi khác mỗi lần chạy lại.

**Cách 2 — Named Tunnel (URL cố định, cần tài khoản Cloudflare miễn phí + một domain):**

1. Đăng nhập: `cloudflared tunnel login` (mở trình duyệt để xác thực với tài khoản Cloudflare).
2. Tạo tunnel: `cloudflared tunnel create ct271e-vibe-coffee`.
3. Trỏ một subdomain của domain đã thêm vào Cloudflare tới tunnel: `cloudflared tunnel route dns ct271e-vibe-coffee vibe-coffee.<domain-của-bạn>`.
4. Tạo file cấu hình `config.yml` (thường ở `~/.cloudflared/`):
   ```yaml
   tunnel: ct271e-vibe-coffee
   credentials-file: /duong/dan/toi/<tunnel-id>.json
   ingress:
     - hostname: vibe-coffee.<domain-của-bạn>
       service: http://localhost:8000
     - service: http_status:404
   ```
5. Chạy: `cloudflared tunnel run ct271e-vibe-coffee`. URL `https://vibe-coffee.<domain-của-bạn>` sẽ cố định, dùng lại được nhiều lần.

Cả hai cách đều chỉ cần server PHP đang chạy cục bộ (`php -S localhost:8000 -t public`), không bắt buộc phải chạy Caddy song song — `cloudflared` tự lo phần HTTPS ở phía Cloudflare.

**Cách 3 — Tunnel qua Caddy (khuyến nghị, để chat real-time hoạt động qua Internet):**

Nếu trỏ tunnel thẳng vào cổng 8000 như Cách 1/2 ở trên, tính năng chat vẫn hoạt động nhưng **không real-time** (tự rơi về gửi qua form POST, vì cổng 8000 không có route `/ws`). Lý do: phía client (`App/Views/layouts/master.php`, `admin/chat.php`, `admin/chat_conversation.php`) tự phát hiện trang đang chạy qua HTTPS thì mới dùng `wss://location.host/ws?role=...`, và chỉ Caddy (không phải PHP built-in server) mới có route `/ws` proxy sang WebSocket daemon ở cổng 8081 (khai báo trong [Caddyfile](Caddyfile)).

[Caddyfile](Caddyfile) có sẵn 2 khối cấu hình giống nhau: `localhost:8443` (HTTPS, dùng chứng chỉ nội bộ tự ký `tls internal`, phù hợp mở trực tiếp bằng trình duyệt) và `:8444` (HTTP thuần, không TLS). **Dùng cổng `8444` cho tunnel**, không dùng `8443` — đã kiểm thử thực tế: trên Windows, `cloudflared tunnel --url https://localhost:8443 --no-tls-verify` không bỏ qua được xác thực chứng chỉ tự ký một cách đáng tin cậy (cloudflared tự cảnh báo "does not support loading the system root certificate pool on Windows"), khiến tunnel trả về HTTP 200 nhưng nội dung trang **rỗng** — lỗi khó nhận ra vì không báo lỗi rõ ràng. Cổng `8444` không có TLS nên tránh hoàn toàn vấn đề này, trong khi vẫn giữ nguyên route `/ws` proxy real-time.

1. Chạy đủ 3 tiến trình, mỗi tiến trình một cửa sổ terminal riêng, giữ nguyên cả 3:
   ```cmd
   php -S localhost:8000 -t public
   php websocket_server.php
   caddy run
   ```
2. Ở cửa sổ terminal thứ 4, chạy tunnel trỏ vào cổng `8444` của Caddy (**không dùng** `https://localhost:8443`):
   ```cmd
   cloudflared tunnel --url http://localhost:8444
   ```
   `cloudflared` in ra URL công khai dạng `https://xxxx-xxxx-xxxx.trycloudflare.com` — **giữ lại đúng domain này** cho bước 3.
3. **Bắt buộc, nếu không chat sẽ không real-time:** thêm domain vừa lấy được ở bước 2 vào `WS_ALLOWED_ORIGINS` trong `.env` (chỉ thêm domain, không kèm `https://`), rồi khởi động lại `websocket_server.php` (dừng cửa sổ đang chạy ở bước 1 bằng `Ctrl+C`, chạy lại) để nạp giá trị `.env` mới:
   ```
   WS_ALLOWED_ORIGINS="localhost,127.0.0.1,xxxx-xxxx-xxxx.trycloudflare.com"
   ```
   Lý do: `App/WebSocket/ChatServer.php` dùng thư viện Ratchet với `OriginCheck` — chặn mọi kết nối WebSocket có header `Origin` không nằm trong danh sách này, trả về lỗi 403 ngay từ bắt tay. Lỗi này **im lặng**: trang vẫn tải bình thường, chat vẫn gửi được tin nhắn (JS tự rơi về gửi qua POST khi WebSocket thất bại) nhưng không có cập nhật tức thời, không có thông báo lỗi nào hiện ra để nhận biết.

   > **Domain tunnel đổi mới mỗi lần chạy lại `cloudflared tunnel --url ...`** (Quick Tunnel không tài khoản, không cố định). Mỗi lần chạy lại tunnel, phải lặp lại bước 3 với domain mới thì chat real-time mới hoạt động qua domain đó. Muốn có domain cố định, dùng Named Tunnel (Cách 2) thay vì Quick Tunnel.
4. Truy cập URL công khai ở bước 2 — Cloudflare tự cấp HTTPS ở phía edge dù origin là HTTP thuần, nên trang vẫn tải qua HTTPS bình thường, khung chat khách hàng và trang `/admin/chat` đều nhận tin nhắn tức thời hai chiều qua WebSocket thật. Nếu trang trắng trơn hoặc chat vẫn không real-time, xem mục [Xử lý sự cố](#xử-lý-sự-cố-thường-gặp) ngay bên dưới.

### Xử lý sự cố thường gặp

**Lệnh `php`/`psql`/`composer`/`caddy`/`cloudflared` báo "not recognized as an internal or external command"**
→ Chương trình đó chưa có trong biến môi trường PATH. Hai cách xử lý:
  - Nhanh: gõ đường dẫn đầy đủ tới file `.exe` thay vì gõ tên lệnh, ví dụ `"C:\đường\dẫn\tới\php.exe" -v`. Tìm đường dẫn thật bằng cách mở nơi cài chương trình đó, hoặc (nếu cài qua winget) xem log lúc cài.
  - Lâu dài: thêm thư mục chứa file `.exe` đó vào PATH (System Properties → Environment Variables → Path → New), rồi **mở cửa sổ cmd mới** — cửa sổ đang mở lúc sửa PATH không tự nhận giá trị mới.

**Cần khởi động lại hoặc dừng một server đang chạy (PHP, WebSocket, Caddy)**
1. Ở cửa sổ cmd đang chạy server đó, nhấn `Ctrl + C` để dừng (gõ `Y` nếu được hỏi `Terminate batch job (Y/N)?`), rồi chạy lại đúng lệnh của server đó.
2. Nếu đã lỡ đóng cửa sổ cmd (server có thể vẫn chạy ngầm, chiếm cổng), tìm và dừng thủ công — thay `<cổng>` bằng `8000` (PHP), `8081` (WebSocket) hoặc `8443`/`8444` (Caddy):
   ```cmd
   netstat -ano | findstr :<cổng>
   ```
   Lấy số cuối dòng (PID) rồi dừng tiến trình:
   ```cmd
   taskkill /PID <PID> /F
   ```
   Sau đó chạy lại lệnh khởi động server như bình thường.

**Truy cập URL Cloudflare Tunnel (Cách 3) nhưng trang trắng trơn, không hiện nội dung, dù `cloudflared` báo tạo tunnel thành công**
→ Gần như chắc chắn đang trỏ tunnel nhầm vào `https://localhost:8443` thay vì `http://localhost:8444`. Dừng tunnel (`Ctrl+C`) và chạy lại đúng lệnh `cloudflared tunnel --url http://localhost:8444` (không kèm `--no-tls-verify`, không dùng cổng 8443) — xem chi tiết nguyên nhân ở Cách 3 bước 2 phía trên.

**Trang tải được qua Cloudflare Tunnel nhưng chat khách hàng ↔ admin vẫn không real-time**
→ Thiếu bước 3 ở Cách 3: domain tunnel chưa có trong `WS_ALLOWED_ORIGINS` của `.env`, hoặc đã sửa `.env` nhưng quên khởi động lại `websocket_server.php` (file `.env` chỉ được đọc lúc daemon khởi động, không tự nạp lại). Làm lại đúng bước 3 ở Cách 3 với domain tunnel hiện tại.

### Tài khoản demo (sau khi import `seed_data.sql`)

- Khách hàng: `a@example.com` / `password` (hoặc `b@example.com` / `password`)
- Quản trị viên: `admin` / `adminpass` (đăng nhập tại `/admin/login`)

