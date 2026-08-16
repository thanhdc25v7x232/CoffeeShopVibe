<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3 class="text-success mb-4">
                <i class="fa-solid fa-bag-shopping me-2"></i>
                Đặt hàng
            </h3>

            <div class="row">
                <div class="col-md-7 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Thông tin nhận hàng</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="/dat-hang">
                                <?= csrf_field() ?>

                                <div class="mb-3">
                                    <label class="form-label">Họ và tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars($old['name'] ?? $customer?->kh_ten ?? '') ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone"
                                           class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                           value="<?= htmlspecialchars($old['phone'] ?? $customer?->kh_sdt ?? '') ?>" required>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <?php $method = $old['method'] ?? 'delivery'; ?>
                                <div class="mb-3">
                                    <label class="form-label d-block">Hình thức nhận hàng <span class="text-danger">*</span></label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="method" id="method-delivery" value="delivery"
                                               onchange="toggleMethod()" <?= $method === 'delivery' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-success" for="method-delivery">
                                            <i class="fa-solid fa-motorcycle me-1"></i> Giao tận nơi
                                        </label>

                                        <input type="radio" class="btn-check" name="method" id="method-pickup" value="pickup"
                                               onchange="toggleMethod()" <?= $method === 'pickup' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-success" for="method-pickup">
                                            <i class="fa-solid fa-store me-1"></i> Nhận tại quán
                                        </label>
                                    </div>
                                    <?php if (isset($errors['method'])): ?>
                                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['method']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div id="delivery-fields">
                                    <div class="mb-3">
                                        <label class="form-label">Xã/Phường <span class="text-danger">*</span></label>
                                        <select name="ward" class="form-select <?= isset($errors['ward']) ? 'is-invalid' : '' ?>">
                                            <option value="">-- Chọn xã/phường --</option>
                                            <?php foreach ($wards as $ward): ?>
                                                <option value="<?= htmlspecialchars($ward) ?>" <?= ($old['ward'] ?? '') === $ward ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ward) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['ward'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['ward']) ?></div>
                                        <?php else: ?>
                                            <small class="text-muted">Vibe hiện chỉ giao hàng trong các khu vực trên.</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                        <input type="text" name="street"
                                               class="form-control <?= isset($errors['street']) ? 'is-invalid' : '' ?>"
                                               value="<?= htmlspecialchars($old['street'] ?? $customer?->kh_diachi ?? '') ?>"
                                               placeholder="Số nhà, tên đường, phường/xã...">
                                        <?php if (isset($errors['street'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['street']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div id="pickup-fields" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Chọn cửa hàng <span class="text-danger">*</span></label>
                                        <select name="store_id" class="form-select <?= isset($errors['store_id']) ? 'is-invalid' : '' ?>">
                                            <option value="">-- Chọn cửa hàng --</option>
                                            <?php foreach ($stores as $store): ?>
                                                <option value="<?= $store['ch_ma'] ?>" <?= (string)($old['store_id'] ?? '') === (string)$store['ch_ma'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($store['ch_ten']) ?> — <?= htmlspecialchars($store['ch_diachi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['store_id'])): ?>
                                            <div class="invalid-feedback"><?= htmlspecialchars($errors['store_id']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fa-solid fa-check me-2"></i>
                                        Xác nhận đặt hàng
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cartItems as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></span>
                                    <strong><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VNĐ</strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Tổng cộng:</h5>
                            <h4 class="text-danger fw-bold mb-0"><?= number_format($total, 0, ',', '.') ?> VNĐ</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMethod() {
    const isPickup = document.getElementById('method-pickup').checked;
    document.getElementById('pickup-fields').style.display = isPickup ? 'block' : 'none';
    document.getElementById('delivery-fields').style.display = isPickup ? 'none' : 'block';
}
document.addEventListener('DOMContentLoaded', toggleMethod);
</script>
