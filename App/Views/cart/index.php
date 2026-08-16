<div class="container mt-4 mb-5">
    <h2 class="mb-4">
        <i class="fa-solid fa-cart-shopping me-2"></i>
        Giỏ hàng của bạn
    </h2>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fa-solid fa-cart-plus fa-3x mb-3 text-muted"></i>
            <h4>Giỏ hàng của bạn đang trống</h4>
            <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm!</p>
            <a href="/" class="btn btn-success mt-3">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10%;">Ảnh</th>
                                        <th style="width: 30%;">Tên sản phẩm</th>
                                        <th style="width: 15%;">Đơn giá</th>
                                        <th style="width: 20%;">Số lượng</th>
                                        <th style="width: 15%;">Thành tiền</th>
                                        <th style="width: 10%;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= product_image_url($item, $item['category_name'] ?? '') ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     class="img-thumbnail"
                                                     style="width: 80px; height: 80px; object-fit: cover;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="text-danger fw-bold">
                                                    <?= number_format($item['price'], 0, ',', '.') ?> VNĐ
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="/cart/update" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                                    <div class="input-group" style="width: 120px;">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                                onclick="decreaseQuantity(<?= $item['id'] ?>)">-</button>
                                                        <input type="number" 
                                                               name="quantity" 
                                                               value="<?= $item['quantity'] ?>" 
                                                               min="1" 
                                                               class="form-control form-control-sm text-center"
                                                               onchange="updateQuantity(<?= $item['id'] ?>, this.value)">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                                onclick="increaseQuantity(<?= $item['id'] ?>)">+</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <span class="text-danger fw-bold">
                                                    <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VNĐ
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="/cart/remove" class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <form method="POST" action="/cart/clear" class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fa-solid fa-trash-can me-2"></i>
                                    Xóa toàn bộ giỏ hàng
                                </button>
                            </form>
                            <a href="/" class="btn btn-outline-success ms-2">
                                <i class="fa-solid fa-arrow-left me-2"></i>
                                Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-receipt me-2"></i>
                            Tóm tắt đơn hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tổng số lượng:</span>
                            <strong><?= $totalItems ?> sản phẩm</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tạm tính:</span>
                            <strong class="text-danger">
                                <?= number_format($total, 0, ',', '.') ?> VNĐ
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Phí vận chuyển:</span>
                            <strong>Miễn phí</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Tổng cộng:</h5>
                            <h4 class="text-danger fw-bold">
                                <?= number_format($total, 0, ',', '.') ?> VNĐ
                            </h4>
                        </div>
                        <button class="btn btn-success w-100 btn-lg" onclick="checkout()">
                            <i class="fa-solid fa-credit-card me-2"></i>
                            Thanh toán
                        </button>
                        <p class="text-muted small text-center mt-3 mb-0">
                            <i class="fa-solid fa-shield-halved me-1"></i>
                            Thanh toán an toàn và bảo mật
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function increaseQuantity(productId) {
    const input = document.querySelector(`input[name="quantity"][onchange*="${productId}"]`);
    const currentValue = parseInt(input.value);
    input.value = currentValue + 1;
    updateQuantity(productId, input.value);
}

function decreaseQuantity(productId) {
    const input = document.querySelector(`input[name="quantity"][onchange*="${productId}"]`);
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        updateQuantity(productId, input.value);
    }
}

    function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        quantity = 1;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/cart/update';
    
    const productIdInput = document.createElement('input');
    productIdInput.type = 'hidden';
    productIdInput.name = 'product_id';
    productIdInput.value = productId;
    
    const quantityInput = document.createElement('input');
    quantityInput.type = 'hidden';
    quantityInput.name = 'quantity';
    quantityInput.value = quantity;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_csrf';
    csrfInput.value = (typeof CSRF_TOKEN !== 'undefined') ? CSRF_TOKEN : '';
    
    form.appendChild(productIdInput);
    form.appendChild(quantityInput);
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
}

function checkout() {
    window.location.href = '/checkout';
}
</script>

