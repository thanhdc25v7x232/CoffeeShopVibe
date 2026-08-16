<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Đăng ký tài khoản
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/register">
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
                                    value="<?= htmlspecialchars($old['name'] ?? '') ?>" 
                                    required 
                                    autofocus
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
                                    value="<?= htmlspecialchars($old['email'] ?? '') ?>" 
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
                                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($old['address'] ?? '') ?>"
                                    placeholder="Nhập địa chỉ">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fa-solid fa-lock me-2"></i>
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                    id="password" 
                                    name="password" 
                                    required
                                    placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)">
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fa-solid fa-lock me-2"></i>
                                    Xác nhận mật khẩu <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    required
                                    placeholder="Nhập lại mật khẩu">
                                <?php if (isset($errors['password_confirmation'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password_confirmation']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-user-plus me-2"></i>
                                Đăng ký
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0">
                                Đã có tài khoản? 
                                <a href="/login" class="text-success text-decoration-none fw-bold">
                                    Đăng nhập ngay
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
