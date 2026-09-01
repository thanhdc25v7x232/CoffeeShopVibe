<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fa-solid fa-user-shield me-2"></i>
            Quản lý tài khoản admin
        </h2>
        <a href="/admin/register" class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i> Thêm admin
        </a>
    </div>

    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(is_array($errors) ? implode(' ', $errors) : $errors) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Tên đăng nhập</th>
                            <th>Ngày tạo</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>#<?= (int)$admin['qtv_ma'] ?></td>
                                <td>
                                    <?= htmlspecialchars($admin['qtv_tendn']) ?>
                                    <?php if ((int)$admin['qtv_ma'] === $currentAdminId): ?>
                                        <span class="badge bg-secondary">Bạn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($admin['qtv_ngaytao']) ? date('d/m/Y H:i', strtotime($admin['qtv_ngaytao'])) : '—' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int)$admin['qtv_ma'] !== $currentAdminId): ?>
                                        <form method="POST" action="/admin/admins/delete" class="d-inline"
                                              onsubmit="return confirm('Xóa tài khoản admin này?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="admin_id" value="<?= (int)$admin['qtv_ma'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
