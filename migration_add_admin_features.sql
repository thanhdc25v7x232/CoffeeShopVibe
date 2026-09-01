-- Migration: khóa tài khoản khách hàng (admin) + bảng cài đặt chung (phí giao hàng, thông báo trang chủ).
-- Chạy trên DB đã có sẵn (không xóa dữ liệu hiện tại). Đã gộp vào ct271e_project.sql cho lần cài mới.

ALTER TABLE KHACH_HANG ADD COLUMN IF NOT EXISTS KH_KHOA BOOLEAN NOT NULL DEFAULT false;

CREATE TABLE IF NOT EXISTS CAI_DAT (
    CD_KHOA VARCHAR(50) PRIMARY KEY,
    CD_GIATRI TEXT,
    CD_NGAYCAPNHAT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO CAI_DAT (CD_KHOA, CD_GIATRI) VALUES
    ('phi_giao_hang', '0'),
    ('thong_bao', '')
ON CONFLICT (CD_KHOA) DO NOTHING;
