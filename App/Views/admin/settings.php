<div class="container mt-4 mb-5">
    <h2 class="mb-4">
        <i class="fa-solid fa-gear me-2"></i>
        Cài đặt chung
    </h2>

    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(is_array($errors) ? implode(' ', $errors) : $errors) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="/admin/settings/save">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Phí giao hàng (VNĐ)</label>
                    <input type="number" name="phi_giao_hang" class="form-control" min="0" step="1000"
                           value="<?= htmlspecialchars($settings['phi_giao_hang'] ?? '0') ?>">
                    <small class="text-muted">Áp dụng cho đơn hàng chọn "Giao tận nơi" ở trang thanh toán.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Thông báo trang chủ</label>
                    <textarea name="thong_bao" class="form-control" rows="2"
                              placeholder="VD: Vibe đang khuyến mãi 20% toàn bộ trà sữa..."><?= htmlspecialchars($settings['thong_bao'] ?? '') ?></textarea>
                    <small class="text-muted">Để trống để không hiển thị thanh thông báo trên trang chủ.</small>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu cài đặt
                </button>
            </form>
        </div>
    </div>
</div>
