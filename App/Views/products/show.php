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

            <p class="text-muted"><?= nl2br(htmlspecialchars($product['sp_mota'] ?? '')) ?></p>

            <div class="d-flex align-items-center gap-2 mt-4">
                <input type="number" id="quantity" class="form-control" value="1" min="1" style="width: 100px;">
                <button type="button"
                        class="btn btn-success btn-lg flex-grow-1 add-to-cart-btn"
                        data-product-id="<?= $product['sp_ma'] ?>">
                    <i class="fa-solid fa-cart-plus me-2"></i>
                    Thêm vào giỏ hàng
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
