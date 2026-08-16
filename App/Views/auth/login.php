<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                        Đăng nhập
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

                    <form method="POST" action="/login">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fa-solid fa-envelope me-2"></i>
                                Email
                            </label>
                            <input 
                                type="email" 
                                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>" 
                                required 
                                autofocus
                                placeholder="Nhập email của bạn">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fa-solid fa-lock me-2"></i>
                                Mật khẩu
                            </label>
                            <input 
                                type="password" 
                                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Nhập mật khẩu">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-sign-in-alt me-2"></i>
                                Đăng nhập
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0">
                                Chưa có tài khoản? 
                                <a href="/register" class="text-success text-decoration-none fw-bold">
                                    Đăng ký ngay
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
