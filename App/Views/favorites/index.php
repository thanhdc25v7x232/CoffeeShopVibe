<div class="container mt-4 mb-5">
    <h3 class="text-success mb-4">
        <i class="fa-solid fa-heart me-2"></i>
        Sản phẩm yêu thích
    </h3>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fa-regular fa-heart fa-3x mb-3 d-block text-muted"></i>
            <h5>Bạn chưa có sản phẩm yêu thích nào</h5>
            <a href="/san-pham" class="btn btn-success mt-2">Khám phá sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="/san-pham/<?= $product['sp_ma'] ?>" class="text-decoration-none text-dark position-relative">
                            <?php if (!empty($product['km_phantram'])): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">-<?= $product['km_phantram'] ?>%</span>
                            <?php endif; ?>
                            <button type="button" class="btn btn-light btn-sm rounded-circle position-absolute top-0 start-0 m-2 favorite-btn" data-product-id="<?= $product['sp_ma'] ?>" data-favorited="1" title="Bỏ yêu thích">
                                <i class="fa-solid fa-heart text-danger"></i>
                            </button>
                            <img src="<?= product_image_url($product) ?>"
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($product['sp_ten']) ?>"
                                 style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($product['sp_ten']) ?></h6>
                                <p class="card-text text-danger fw-bold mb-0">
                                    <?= number_format($product['gia_hien_thi'], 0, ',', '.') ?> VNĐ
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    // Bỏ yêu thích tại trang này -> ẩn luôn card khỏi danh sách sau khi server xác nhận
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.favorite-btn');
        if (!button) return;
        setTimeout(function () {
            if (button.getAttribute('data-favorited') === '0') {
                var card = button.closest('.col-6, .col-md-3');
                if (card) card.remove();
            }
        }, 300);
    });
})();
</script>
