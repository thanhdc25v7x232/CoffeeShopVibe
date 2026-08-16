# Project Niên Luận Cơ Sở (CT271E)

Học kỳ 3, Năm học 2025-2026

**Tên dự án**: Website Coffee Shop VIBE

**MSSV**:DC25V7X232

**Họ tên SV**:Tăng Tố Thanh

**Lớp học phần**: CT271E - Niên Luận cơ sở

## Cài đặt & chạy thử

### Yêu cầu

- PHP >= 8.0 (có extension `pdo_pgsql`)
- PostgreSQL (có extension `pgcrypto`, dùng để hash mật khẩu mẫu)
- Composer

### Các bước

1. Cài thư viện PHP:
   ```
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
   ```
   `APP_DEBUG=true` hiển thị lỗi chi tiết (PHP `display_errors`), phù hợp lúc phát triển. Khi demo/deploy thật, đặt `APP_DEBUG=false` để tránh lộ đường dẫn, câu truy vấn hoặc thông tin hệ thống khi có lỗi.

3. Tạo database rồi import schema và dữ liệu mẫu (theo đúng thứ tự):
   ```
   psql -U postgres -c "CREATE DATABASE ct271e_project"
   psql -U postgres -d ct271e_project -f ct271e_project.sql
   psql -U postgres -d ct271e_project -f seed_data.sql
   ```
  4. Chạy server (dùng PHP built-in server, trỏ document root vào `public/`):
   ```
   php -S localhost:8000 -t public
   ```
   Sau đó truy cập http://localhost:8000

### Chạy HTTPS/HTTP2 cục bộ để demo (Caddy)

Repo có sẵn [Caddyfile](Caddyfile) để demo trang qua HTTPS + HTTP/2 mà không cần domain hay VPS:

1. Cài Caddy: https://caddyserver.com/docs/install
2. Chạy server PHP như bước 4 ở trên (`php -S localhost:8000 -t public`), giữ nguyên cửa sổ đó.
3. Ở một cửa sổ terminal khác, tại thư mục gốc dự án, chạy:
   ```
   caddy run
   ```
   Lần đầu chạy Caddy sẽ xin quyền cài chứng chỉ CA nội bộ (`tls internal`) để trình duyệt tin tưởng chứng chỉ cục bộ.
4. Truy cập https://localhost:8443 — đây là bản HTTPS/HTTP2, được Caddy reverse-proxy vào server PHP ở cổng 8000. Caddy cũng gắn `Cache-Control` dài hạn cho `css/img/uploads` tĩnh.

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

### Tài khoản demo (sau khi import `seed_data.sql`)

- Khách hàng: `a@example.com` / `password` (hoặc `b@example.com` / `password`)
- Quản trị viên: `admin` / `adminpass` (đăng nhập tại `/admin/login`)

