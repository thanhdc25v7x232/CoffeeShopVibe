<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Đăng ký Admin
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

                    <form method="POST" action="/admin/register">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fa-solid fa-user me-1"></i>
                                Tên đăng nhập <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" 
                                   name="username" 
                                   value="<?= htmlspecialchars($old['username'] ?? '') ?>" 
                                   required 
                                   autofocus
                                   placeholder="Nhập tên đăng nhập (tối thiểu 3 ký tự)"
                                   minlength="3">
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['username']) ?>
                                </div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Tên đăng nhập phải có ít nhất 3 ký tự và không được trùng với tài khoản khác.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fa-solid fa-lock me-1"></i>
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                                   minlength="6">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['password']) ?>
                                </div>
                            <?php endif; ?>
                            <small class="form-text text-muted">
                                Mật khẩu phải có ít nhất 6 ký tự.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fa-solid fa-lock me-1"></i>
                                Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
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

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-user-plus me-2"></i>
                                Đăng ký
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-2">
                            Đã có tài khoản? 
                            <a href="/admin/login" class="text-success text-decoration-none fw-bold">
                                Đăng nhập ngay
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