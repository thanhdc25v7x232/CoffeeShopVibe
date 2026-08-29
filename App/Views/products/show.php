<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-success">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/san-pham" class="text-success">Sản phẩm</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['sp_ten']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="position-relative">
                <?php if (!empty($product['km_phantram'])): ?>
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2 fs-6">-<?= $product['km_phantram'] ?>%</span>
                <?php endif; ?>
                <button type="button"
                        class="btn btn-light btn-sm rounded-circle position-absolute top-0 start-0 m-2 favorite-btn"
                        data-product-id="<?= $product['sp_ma'] ?>"
                        data-favorited="<?= $isFavorited ? '1' : '0' ?>"
                        title="Yêu thích">
                    <i class="<?= $isFavorited ? 'fa-solid text-danger' : 'fa-regular' ?> fa-heart"></i>
                </button>
                <img src="<?= product_image_url($product) ?>"
                     class="img-fluid rounded shadow-sm w-100"
                     alt="<?= htmlspecialchars($product['sp_ten']) ?>"
                     style="height: 360px; object-fit: contain; background-color: #f8f9fa;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
            </div>
        </div>

        <div class="col-md-7">
            <h2 class="mb-3"><?= htmlspecialchars($product['sp_ten']) ?></h2>

            <div class="mb-3">
                <?php if (!empty($product['km_phantram'])): ?>
                    <span class="text-muted text-decoration-line-through me-2">
                        <?= number_format($product['sp_gia'], 0, ',', '.') ?> VNĐ
                    </span>
                <?php endif; ?>
                <span class="text-danger fw-bold fs-3">
                    <?= number_format($product['gia_hien_thi'], 0, ',', '.') ?> VNĐ
                </span>
            </div>

            <?php $tonKho = (int)($product['sp_tonkho'] ?? 0); ?>
            <div class="mb-3">
                <?php if ($tonKho <= 0): ?>
                    <span class="badge bg-secondary">Hết hàng</span>
                <?php elseif ($tonKho <= 5): ?>
                    <span class="badge bg-warning text-dark">Sắp hết hàng (còn <?= $tonKho ?>)</span>
                <?php else: ?>
                    <span class="badge bg-success">Còn hàng (<?= $tonKho ?>)</span>
                <?php endif; ?>
            </div>

            <p class="text-muted"><?= nl2br(htmlspecialchars($product['sp_mota'] ?? '')) ?></p>

            <div class="d-flex align-items-center gap-2 mt-4">
                <input type="number" id="quantity" class="form-control" value="1" min="1" style="width: 100px;" <?= $tonKho <= 0 ? 'disabled' : '' ?>>
                <button type="button"
                        class="btn btn-success btn-lg flex-grow-1 add-to-cart-btn"
                        data-product-id="<?= $product['sp_ma'] ?>"
                        <?= $tonKho <= 0 ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-cart-plus me-2"></i>
                    <?= $tonKho <= 0 ? 'Hết hàng' : 'Thêm vào giỏ hàng' ?>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($relatedProducts)): ?>
        <h4 class="text-success mt-5 mb-3">Sản phẩm liên quan</h4>
        <div class="row">
            <?php foreach ($relatedProducts as $related): ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="/san-pham/<?= $related['sp_ma'] ?>" class="text-decoration-none text-dark position-relative">
                            <?php if (!empty($related['km_phantram'])): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">-<?= $related['km_phantram'] ?>%</span>
                            <?php endif; ?>
                            <button type="button" class="btn btn-light btn-sm rounded-circle position-absolute top-0 start-0 m-2 favorite-btn" data-product-id="<?= $related['sp_ma'] ?>" title="Yêu thích">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            <img src="<?= product_image_url($related) ?>"
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($related['sp_ten']) ?>"
                                 style="height: 180px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($related['sp_ten']) ?></h6>
                                <p class="card-text text-danger fw-bold mb-0">
                                    <?= number_format($related['gia_hien_thi'], 0, ',', '.') ?> VNĐ
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h4 class="text-success mt-5 mb-3">Đánh giá sản phẩm</h4>
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="fs-3 fw-bold text-warning">
                    <?= number_format($reviewStats['avg'], 1) ?>
                    <i class="fa-solid fa-star"></i>
                </span>
                <span class="text-muted">(<?= $reviewStats['count'] ?> đánh giá)</span>
            </div>

            <?php if (empty($reviews)): ?>
                <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($review['kh_ten']) ?></strong>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($review['dg_ngaytao'])) ?></small>
                        </div>
                        <div class="text-warning mb-1">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-<?= $i <= $review['dg_sao'] ? 'solid' : 'regular' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['dg_noidung'] ?? '')) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <?php if (AUTHGUARD()->isCustomerLoggedIn()): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title"><?= $myReview ? 'Sửa đánh giá của bạn' : 'Viết đánh giá' ?></h6>
                        <?php if ($myReview && $myReview['dg_trangthai'] === 'pending'): ?>
                            <p class="text-warning small mb-2">
                                <i class="fa-solid fa-clock"></i> Đánh giá của bạn đang chờ duyệt.
                            </p>
                        <?php endif; ?>
                        <form method="POST" action="/san-pham/<?= $product['sp_ma'] ?>/danh-gia">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <select name="sao" class="form-select" required>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>" <?= (isset($myReview['dg_sao']) && (int)$myReview['dg_sao'] === $i) ? 'selected' : '' ?>>
                                            <?= $i ?> sao
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <textarea name="noi_dung" class="form-control" rows="3" placeholder="Cảm nhận của bạn về sản phẩm..."><?= htmlspecialchars($myReview['dg_noidung'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Gửi đánh giá</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-light border text-center">
                    <a href="/login">Đăng nhập</a> để viết đánh giá.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}

document.querySelector('.add-to-cart-btn')?.addEventListener('click', function () {
    const button = this;
    if (button.disabled) return;

    const productId = button.getAttribute('data-product-id');
    const quantity = Math.max(1, parseInt(document.getElementById('quantity').value) || 1);

    button.disabled = true;
    const originalText = button.innerHTML;

    fetch(`/cart/add-ajax?id=${productId}&quantity=${quantity}`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cartCount);
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm vào giỏ hàng'));
        }
        button.innerHTML = originalText;
        button.disabled = false;
    })
    .catch(() => {
        alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng');
        button.innerHTML = originalText;
        button.disabled = false;
    });
});
</script>

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
