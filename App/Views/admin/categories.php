<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-tags me-2"></i>
        Quản lý danh mục sản phẩm
    </h2>

    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0" id="formTitle">Thêm danh mục mới</h5>
                </div>
                <div class="card-body">
                    <form id="categoryForm" action="/admin/categories/save" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="category_id" id="category_id">

                        <div class="mb-3">
                            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   placeholder="VD: Trà sữa" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btnSubmit">Lưu danh mục</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Hủy / Nhập mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Danh sách danh mục</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên danh mục</th>
                                <th class="text-center">Số sản phẩm</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Chưa có danh mục nào.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= htmlspecialchars($category['l_ten']) ?></td>
                                    <td class="text-center"><?= (int)$category['product_count'] ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning"
                                                onclick='fillForm(<?= json_encode(['id' => $category['l_ma'], 'name' => $category['l_ten']]) ?>)'>
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form action="/admin/categories/delete" method="POST" class="d-inline"
                                              onsubmit="return confirm('Xóa danh mục này? Sản phẩm thuộc danh mục sẽ chuyển về \'chưa phân loại\'.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="category_id" value="<?= (int)$category['l_ma'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
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
</div>

<script>
function fillForm(category) {
    document.getElementById('category_id').value = category.id;
    document.getElementById('name').value = category.name;
    document.getElementById('formTitle').textContent = 'Cập nhật: ' + category.name;
    const btn = document.getElementById('btnSubmit');
    btn.textContent = 'Cập nhật';
    btn.classList.remove('btn-success');
    btn.classList.add('btn-warning');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('formTitle').textContent = 'Thêm danh mục mới';
    const btn = document.getElementById('btnSubmit');
    btn.textContent = 'Lưu danh mục';
    btn.classList.remove('btn-warning');
    btn.classList.add('btn-success');
}
</script>
