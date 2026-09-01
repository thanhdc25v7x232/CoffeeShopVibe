-- Migration: kiến trúc thanh toán trực tuyến (MoMo/VNPAY sau này) — tách trạng thái
-- thanh toán khỏi trạng thái đơn hàng và thêm bảng lưu giao dịch từng cổng thanh toán.
-- Chạy trên DB đã có sẵn (không xóa dữ liệu hiện tại). Đã gộp vào ct271e_project.sql cho lần cài mới.

ALTER TABLE DON_HANG ADD COLUMN IF NOT EXISTS DH_PTTT VARCHAR(20) DEFAULT 'cod'; -- cod | momo | vnpay | bank_transfer
ALTER TABLE DON_HANG ADD COLUMN IF NOT EXISTS DH_TT_TRANGTHAI VARCHAR(20) DEFAULT 'unpaid'; -- unpaid | pending | paid | failed | refunded

CREATE TABLE IF NOT EXISTS GIAO_DICH_THANH_TOAN (
    GD_MA SERIAL PRIMARY KEY,
    DH_MA INT NOT NULL REFERENCES DON_HANG(DH_MA) ON DELETE CASCADE,
    GD_NHACC VARCHAR(30) NOT NULL, -- momo | vnpay
    GD_PTTT VARCHAR(30), -- wallet | qr | atm | intcard ...
    GD_SOTIEN NUMERIC(15,0) NOT NULL,
    GD_MA_YEUCAU VARCHAR(100), -- requestId gửi cho cổng thanh toán, dùng chống gọi trùng
    GD_MA_GIAODICH VARCHAR(100), -- transactionId cổng thanh toán trả về
    GD_TRANGTHAI VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending | success | failed
    GD_MA_PHANHOI VARCHAR(50),
    GD_PHANHOI_THO TEXT, -- lưu nguyên JSON phản hồi để tra soát khi có tranh chấp
    GD_NGAYTAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    GD_NGAYTT TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_giao_dich_dh_ma ON GIAO_DICH_THANH_TOAN(DH_MA);
CREATE UNIQUE INDEX IF NOT EXISTS uq_giao_dich_nhacc_yeucau ON GIAO_DICH_THANH_TOAN(GD_NHACC, GD_MA_YEUCAU);
