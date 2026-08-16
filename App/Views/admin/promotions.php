<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-tags me-2"></i>
        Quản Lý Khuyến Mãi
    </h2>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0" id="formTitle">Thêm Khuyến Mãi</h5>
                </div>
                <div class="card-body">
                    <form id="promotionForm" action="/admin/promotions/save" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="promotion_id" id="promotion_id">

                        <div class="mb-3">
                            <label class="form-label">Tên khuyến mãi (*)</label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phần trăm giảm (*)</label>
                            <input type="number" class="form-control <?= isset($errors['percent']) ? 'is-invalid' : '' ?>"
                                   id="percent" name="percent" min="1" max="100" value="<?= htmlspecialchars($old['percent'] ?? '') ?>" required>
                            <?php if (isset($errors['percent'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['percent']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Ngày bắt đầu (*)</label>
                                <input type="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>"
                                       id="start_date" name="start_date" value="<?= htmlspecialchars($old['start_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Ngày kết thúc (*)</label>
                                <input type="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>"
                                       id="end_date" name="end_date" value="<?= htmlspecialchars($old['end_date'] ?? '') ?>" required>
                            </div>
                            <?php if (isset($errors['date'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($errors['date']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                            <label class="form-check-label" for="status">Đang áp dụng</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btnSubmit">Lưu khuyến mãi</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Hủy / Nhập mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Danh sách khuyến mãi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Giảm</th>
                                <th>Thời gian</th>
                                <th width="110">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promotions as $promo): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($promo['km_ten']) ?></strong>
                                        <?php if ($promo['km_trangthai']): ?>
                                            <span class="badge bg-success" style="font-size: 0.7rem;">Đang áp dụng</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">Tạm tắt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>-<?= $promo['km_phantram'] ?>%</td>
                                    <td><?= date('d/m/Y', strtotime($promo['km_ngaybatdau'])) ?> - <?= date('d/m/Y', strtotime($promo['km_ngayketthuc'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick='fillForm(<?= json_encode($promo) ?>)'>
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form action="/admin/promotions/delete" method="POST" style="display:inline-block;" onsubmit="return confirm('Xóa khuyến mãi này?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="promotion_id" value="<?= $promo['km_ma'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function fillForm(promo) {
    document.getElementById('promotion_id').value = promo.km_ma;
    document.getElementById('name').value = promo.km_ten;
    document.getElementById('description').value = promo.km_mota ?? '';
    document.getElementById('percent').value = promo.km_phantram;
    document.getElementById('start_date').value = promo.km_ngaybatdau.substring(0, 10);
    document.getElementById('end_date').value = promo.km_ngayketthuc.substring(0, 10);
    document.getElementById('status').checked = (promo.km_trangthai === true || promo.km_trangthai === 't' || promo.km_trangthai == 1);

    document.getElementById('formTitle').innerText = 'Cập nhật: ' + promo.km_ten;
    document.getElementById('btnSubmit').innerText = 'Cập nhật';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('promotionForm').reset();
    document.getElementById('promotion_id').value = '';
    document.getElementById('formTitle').innerText = 'Thêm Khuyến Mãi';
    document.getElementById('btnSubmit').innerText = 'Lưu khuyến mãi';
}
</script>
