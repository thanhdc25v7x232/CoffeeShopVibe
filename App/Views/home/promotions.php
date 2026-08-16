<div class="container mt-4 mb-5">
    <h2 class="text-success mb-4">
        <i class="fa-solid fa-tags me-2"></i>
        Khuyến mãi đang áp dụng
    </h2>

    <?php if (empty($promotions)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fa-solid fa-tags fa-3x mb-3 text-muted"></i>
            <h5>Hiện chưa có chương trình khuyến mãi nào</h5>
            <p class="text-muted mb-0">Vui lòng quay lại sau!</p>
        </div>
    <?php else: ?>
        <?php foreach ($promotions as $promotion): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?= htmlspecialchars($promotion['km_ten']) ?></h5>
                    <span class="badge bg-danger fs-6">-<?= $promotion['km_phantram'] ?>%</span>
                </div>
                <div class="card-body">
                    <p><?= htmlspecialchars($promotion['km_mota'] ?? '') ?></p>
                    <p class="text-muted small">
                        Áp dụng từ <?= date('d/m/Y', strtotime($promotion['km_ngaybatdau'])) ?>
                        đến <?= date('d/m/Y', strtotime($promotion['km_ngayketthuc'])) ?>
                    </p>

                    <?php if (!empty($promotion['products'])): ?>
                        <div class="row mt-3">
                            <?php foreach ($promotion['products'] as $product): ?>
                                <div class="col-6 col-md-3 mb-3">
                                    <a href="/san-pham/<?= $product['sp_ma'] ?>" class="text-decoration-none text-dark">
                                        <div class="card h-100">
                                            <img src="<?= product_image_url($product) ?>" class="card-img-top"
                                                 style="height: 140px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                                            <div class="card-body p-2">
                                                <p class="mb-0 small"><?= htmlspecialchars($product['sp_ten']) ?></p>
                                                <strong class="text-danger small">
                                                    <?= number_format($product['sp_gia'] * (1 - $promotion['km_phantram'] / 100), 0, ',', '.') ?> VNĐ
                                                </strong>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
(function () {
    var initialVersion = null;
    function checkProductsUpdated() {
        fetch('/api/products-version')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (initialVersion === null) {
                    initialVersion = data.version;
                    return;
                }
                if (data.version !== initialVersion) {
                    location.reload();
                }
            })
            .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
    }
    // Tự phát hiện khi admin vừa thêm/sửa/xóa sản phẩm hoặc khuyến mãi, tự tải lại trang
    setInterval(checkProductsUpdated, 8000);
})();
</script>
