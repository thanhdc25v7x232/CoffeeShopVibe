<div class="container mt-4">
    <h2 class="mb-4">Quản Lý Sản Phẩm</h2>
    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($messages['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0" id="formTitle">Thêm Món Mới</h5>
                </div>
                <div class="card-body">
                    <form id="productForm" action="/admin/products/store" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <input type="hidden" name="product_id" id="product_id">

                        <div class="mb-3">
                            <label class="form-label">Tên món (*)</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Loại sản phẩm</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Chọn loại --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['l_ma'] ?>"><?= htmlspecialchars($category['l_ten']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Khuyến mãi áp dụng</label>
                            <select class="form-select" id="promotion_id" name="promotion_id">
                                <option value="">-- Không áp dụng --</option>
                                <?php foreach ($promotions as $promotion): ?>
                                    <option value="<?= $promotion['km_ma'] ?>">
                                        <?= htmlspecialchars($promotion['km_ten']) ?> (-<?= $promotion['km_phantram'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giá bán</label>
                            <input type="number" class="form-control" id="price" name="price" required min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted" id="imageNote" style="display:none">Bỏ qua nếu không muốn thay đổi ảnh</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btnSubmit">Lưu sản phẩm</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Hủy / Nhập mới</button>
                                <i> (*) Các trường bắt buộc nhập</i>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách món</h5>
                    
                    <form method="GET" action="" class="d-flex">
                        <select name="filter_category" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['l_ma'] ?>" <?= ($currentCategory == $cat['l_ma']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['l_ten']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Ảnh</th>
                                <th>Tên món</th>
                                <th>Loại</th>
                                <th>Giá</th>
                                <th width="160">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <img src="<?= product_image_url($p, $p['l_ten'] ?? '') ?>" width="40" height="40" style="object-fit: cover; border-radius: 4px;" onerror="this.onerror=null;this.src='/img/unnamed.png';">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['sp_ten']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($p['l_ten'] ?? 'Chưa phân loại') ?></td>
                                <td class="text-danger"><?= number_format($p['sp_gia']) ?>đ</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick='fillForm(<?= json_encode($p) ?>)'>
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <?php if (!empty($p['sp_hinh'])): ?>
                                        <form action="/admin/products/delete-image" method="POST" style="display:inline-block;"
                                              onsubmit="return confirm('Bạn chắc chắn muốn xóa ảnh của sản phẩm này?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $p['sp_ma'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa riêng ảnh">
                                                <i class="fa fa-image"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                                                        <form action="/admin/products/delete" method="POST" style="display:inline-block;" 
                                                                                    onsubmit="return confirm('Bạn chắc chắn sẽ xóa sản phẩm này chứ?');">
                                                                                <?= csrf_field() ?>
                                                                                <input type="hidden" name="product_id" value="<?= $p['sp_ma'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="card-footer bg-white">
                    <nav>
                        <ul class="pagination justify-content-center mb-0">
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&filter_category=<?= $currentCategory ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fillForm(product) {
        // 1. Điền dữ liệu vào form
        document.getElementById('product_id').value = product.sp_ma;
        document.getElementById('name').value = product.sp_ten;
        document.getElementById('price').value = product.sp_gia;
        document.getElementById('description').value = product.sp_mota;
        document.getElementById('category_id').value = product.l_ma;
        document.getElementById('promotion_id').value = product.km_ma ?? '';

        // 2. Thay đổi trạng thái Form thành "Cập nhật"
        document.getElementById('formTitle').innerText = 'Cập nhật món: ' + product.sp_ten;
        document.getElementById('btnSubmit').innerText = 'Cập nhật';
        document.getElementById('btnSubmit').classList.remove('btn-primary');
        document.getElementById('btnSubmit').classList.add('btn-warning');
        
        // 3. Đổi Action của Form sang route Update
        document.getElementById('productForm').action = '/admin/products/update';

        // 4. Ảnh không bắt buộc khi sửa
        document.getElementById('image').required = false;
        document.getElementById('imageNote').style.display = 'block';

        // 5. Cuộn lên đầu trang (nếu đang ở dưới)
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        // Reset về trạng thái "Thêm mới"
        document.getElementById('productForm').reset();
        document.getElementById('product_id').value = '';
        
        document.getElementById('formTitle').innerText = 'Thêm Món Mới';
        document.getElementById('btnSubmit').innerText = 'Lưu sản phẩm';
        document.getElementById('btnSubmit').classList.add('btn-primary');
        document.getElementById('btnSubmit').classList.remove('btn-warning');

        document.getElementById('productForm').action = '/admin/products/store';
        document.getElementById('image').required = true;
        document.getElementById('imageNote').style.display = 'none';
    }
</script>
