-- Migration: trợ lý AI (RAG) — lưu lịch sử hội thoại giữa khách và chatbot.
-- Chạy trên DB đã có sẵn (không xóa dữ liệu hiện tại). Đã gộp vào ct271e_project.sql cho lần cài mới.

CREATE TABLE IF NOT EXISTS TRO_LY_AI (
    TL_MA SERIAL PRIMARY KEY,
    TL_PHIEN VARCHAR(64) NOT NULL, -- khách đăng nhập: 'kh_<id>'; khách vãng lai: token ngẫu nhiên theo session
    KH_MA INT REFERENCES KHACH_HANG(KH_MA) ON DELETE SET NULL,
    TL_VAITRO VARCHAR(20) NOT NULL, -- 'user' | 'assistant'
    TL_NOIDUNG TEXT NOT NULL,
    TL_INTERACTION_ID VARCHAR(128), -- id trả về từ Gemini Interactions API (dòng 'assistant'), dùng làm previous_interaction_id cho lượt kế tiếp
    TL_NGAYTAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE TRO_LY_AI ADD COLUMN IF NOT EXISTS TL_INTERACTION_ID VARCHAR(128);

CREATE INDEX IF NOT EXISTS idx_tro_ly_ai_phien ON TRO_LY_AI(TL_PHIEN, TL_NGAYTAO);
