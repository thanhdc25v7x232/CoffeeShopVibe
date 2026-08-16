<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-user-circle me-2"></i>
                        Thông tin tài khoản
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($messages['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>
                            <?= htmlspecialchars($messages['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/account">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fa-solid fa-user me-2"></i>
                                    Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                    id="name"
                                    name="name"
                                    value="<?= htmlspecialchars($old['name'] ?? $customer->kh_ten ?? '') ?>"
                                    required
                                    placeholder="Nhập họ và tên">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fa-solid fa-envelope me-2"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="email"
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($old['email'] ?? $customer->kh_email ?? '') ?>"
                                    required
                                    placeholder="Nhập email">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fa-solid fa-phone me-2"></i>
                                    Số điện thoại
                                </label>
                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?= htmlspecialchars($old['phone'] ?? $customer->kh_sdt ?? '') ?>"
                                    placeholder="Nhập số điện thoại">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">
                                    <i class="fa-solid fa-location-dot me-2"></i>
                                    Địa chỉ
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="address"
                                    name="address"
                                    value="<?= htmlspecialchars($old['address'] ?? $customer->kh_diachi ?? '') ?>"
                                    placeholder="Nhập địa chỉ">
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-floppy-disk me-2"></i>
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
