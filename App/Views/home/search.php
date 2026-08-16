<div class="container mt-4">
    <div class="mb-4">
        <h3 class="text-success">
            Kết quả tìm kiếm cho: "<span class="text-dark"><?= htmlspecialchars($keyword) ?></span>"
        </h3>
        <p class="text-muted">Tìm thấy <?= $totalResults ?> sản phẩm</p>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center">
            <i class="fa fa-search fa-3x mb-3 d-block"></i>
            <h5>Không tìm thấy sản phẩm nào</h5>
            <p class="mb-0">Vui lòng thử lại với từ khóa khác</p>
        </div>
        <div class="text-center mt-4">
            <a href="/" class="btn btn-success">
                <i class="fa fa-home"></i> Quay về trang chủ
            </a>
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
    <?php endif; ?>
</div>

<script>
// Hàm cập nhật số lượng giỏ hàng trên header
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

// Xử lý sự kiện click nút "Thêm vào giỏ hàng"
document.addEventListener('click', function(e) {
    const button = e.target.closest('.add-to-cart-btn');
    if (!button) return;
    
    if (button.disabled) {
        e.preventDefault();
        return;
    }
    
    e.preventDefault();
    
    const productId = button.getAttribute('data-product-id');
    if (!productId) return;
    
    const originalText = button.innerHTML;
    button.disabled = true;
    
    fetch('/cart/add-ajax?id=' + productId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cartCount);
            button.innerHTML = originalText;
            button.disabled = false;
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm vào giỏ hàng'));
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
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