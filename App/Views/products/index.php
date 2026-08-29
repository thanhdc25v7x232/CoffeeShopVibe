<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-success mb-0">Tất cả sản phẩm</h3>

        <form method="GET" action="/san-pham" class="d-flex">
            <select name="filter_category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả loại --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['l_ma'] ?>" <?= ($currentCategory == $cat['l_ma']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['l_ten']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center">
            <i class="fa fa-box-open fa-3x mb-3 d-block"></i>
            <h5>Chưa có sản phẩm nào</h5>
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
                            <button type="button" class="btn btn-light btn-sm rounded-circle position-absolute top-0 start-0 m-2 favorite-btn" data-product-id="<?= $product['sp_ma'] ?>" title="Yêu thích">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            <img src="<?= product_image_url($product, $product['l_ten'] ?? '') ?>"
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($product['sp_ten']) ?>"
                                 style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">

                            <div class="card-body pb-0">
                                <h5 class="card-title"><?= htmlspecialchars($product['sp_ten']) ?></h5>

                                <p class="card-text text-danger font-weight-bold mb-0">
                                    <?php if (!empty($product['km_phantram'])): ?>
                                        <span class="text-muted text-decoration-line-through small"><?= number_format($product['sp_gia'], 0, ',', '.') ?> VNĐ</span>
                                        <br>
                                    <?php endif; ?>
                                    <?= number_format($product['gia_hien_thi'], 0, ',', '.') ?> VNĐ
                                </p>
                            </div>
                        </a>

                        <div class="card-body pt-2">
                            <a href="#"
                               class="btn btn-success w-100 add-to-cart-btn"
                               data-product-id="<?= $product['sp_ma'] ?>"
                               data-product-name="<?= htmlspecialchars($product['sp_ten']) ?>">
                                <i class="fa fa-shopping-cart"></i> Thêm vào giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&filter_category=<?= $currentCategory ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
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

document.addEventListener('click', function(e) {
    const button = e.target.closest('.add-to-cart-btn');
    if (!button || button.disabled) return;
    e.preventDefault();

    const productId = button.getAttribute('data-product-id');
    if (!productId) return;

    const originalText = button.innerHTML;
    button.disabled = true;

    fetch('/cart/add-ajax?id=' + productId, {
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
