<div class="container mt-4">
    <div class="mb-4">
        <h2 class="mb-1">
            <i class="fa-solid fa-boxes-stacked me-2"></i>
            Báo cáo tồn kho
        </h2>
        <p class="text-muted mb-0">Sản phẩm tồn kho thấp (≤ 10) được xếp lên đầu.</p>
    </div>

    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Loại</th>
                            <th>Tồn kho</th>
                            <th width="220">Cập nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Chưa có sản phẩm nào.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($products as $p): ?>
                            <tr class="<?= $p['low_stock'] ? 'table-warning' : '' ?>">
                                <td>
                                    <img src="<?= product_image_url($p, $p['l_ten'] ?? '') ?>" width="40" height="40" style="object-fit: cover; border-radius: 4px;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                                </td>
                                <td><strong><?= htmlspecialchars($p['sp_ten']) ?></strong></td>
                                <td><?= htmlspecialchars($p['l_ten'] ?? 'Chưa phân loại') ?></td>
                                <td>
                                    <span class="badge <?= $p['low_stock'] ? 'bg-danger' : 'bg-success' ?> fs-6">
                                        <?= (int)$p['sp_tonkho'] ?>
                                    </span>
                                    <?php if ($p['low_stock']): ?>
                                        <span class="text-danger small ms-1"><i class="fa-solid fa-triangle-exclamation"></i> Sắp hết</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="/admin/inventory/update-stock" class="d-flex gap-1">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= $p['sp_ma'] ?>">
                                        <input type="number" name="stock" class="form-control form-control-sm" value="<?= (int)$p['sp_tonkho'] ?>" min="0" style="width: 100px;">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-floppy-disk"></i> Lưu
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
