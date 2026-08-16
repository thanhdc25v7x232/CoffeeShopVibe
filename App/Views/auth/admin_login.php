<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-shield-halved me-2"></i>
                        Đăng nhập Admin
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($messages['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($messages['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/admin/login">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fa-solid fa-user me-1"></i>
                                Tên đăng nhập
                            </label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" 
                                   name="username" 
                                   value="<?= htmlspecialchars($old['username'] ?? '') ?>" 
                                   required 
                                   autofocus>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['username']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fa-solid fa-lock me-1"></i>
                                Mật khẩu
                            </label>
                            <input type="password" 
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-sign-in-alt me-2"></i>
                                Đăng nhập
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-2">
                            Chưa có tài khoản? 
                            <a href="/admin/register" class="text-success text-decoration-none fw-bold">
                                Đăng ký ngay
                            </a>
                        </p>
                        <a href="/" class="text-muted text-decoration-none">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
