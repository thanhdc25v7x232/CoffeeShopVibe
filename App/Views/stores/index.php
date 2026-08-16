<div class="container mt-4">
    <h2 class="mb-4">Quản Lý Cửa Hàng</h2>
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0" id="formTitle">Thêm Cửa Hàng Mới</h5>
                </div>
                <div class="card-body">
                    <form id="storeForm" action="/admin/stores/save" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="store_id" id="store_id">

                        <input type="hidden" name="province_name" id="province_name">
                        <input type="hidden" name="ward_name" id="ward_name">

                        <div class="mb-3">
                            <label class="form-label">Tên cửa hàng (*)</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="VD: Vibe Coffee Cần Thơ">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tỉnh / Thành phố</label>
                            <select class="form-select" id="province" name="province_code" required>
                                <option value="">-- Chọn Tỉnh/Thành --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phường / Xã</label>
                            <select class="form-select" id="ward" name="ward_code" required disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số nhà, Đường</label>
                            <input type="text" class="form-control" id="street" name="street" required placeholder="VD: 123 Đường 3/2">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone" required placeholder="0901xxxxxx">
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Giờ mở cửa</label>
                                <input type="time" class="form-control" id="open_time" name="open_time" value="07:00" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Giờ đóng cửa</label>
                                <input type="time" class="form-control" id="close_time" name="close_time" value="22:00" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Đang hoạt động</option>
                                <option value="0">Tạm ngưng / Đóng cửa</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="btnSubmit">Lưu cửa hàng</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">Hủy / Nhập mới</button>
                            <i> (*) Các trường bắt buộc nhập</i>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Danh sách cửa hàng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Tên & Địa chỉ</th>
                                <th width="100">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stores as $store): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($store['ch_ten']) ?></strong>

                                        <?php if ($store['ch_trangthai'] == 1 || $store['ch_trangthai'] === true || $store['ch_trangthai'] === 't'): ?>
                                            <span class="badge bg-success" style="font-size: 0.7rem;">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger" style="font-size: 0.7rem;">Tạm ngưng</span>
                                        <?php endif; ?>

                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-phone"></i> <?= htmlspecialchars($store['ch_sdt']) ?> |
                                            <i class="fa fa-clock"></i> <?= substr($store['gio_mo_cua'], 0, 5) ?> - <?= substr($store['gio_dong_cua'], 0, 5) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($store['ch_diachi']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick='fillStoreForm(<?= json_encode($store) ?>)'>
                                            <i class="fa fa-edit"></i>
                                        </button>

                                        <form action="/admin/stores/delete" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="store_id" value="<?= $store['ch_ma'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
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
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    // API đơn vị hành chính Việt Nam theo mô hình 2 cấp (Tỉnh/Thành phố -> Xã/Phường),
    // áp dụng từ 01/07/2025 sau khi bỏ cấp Quận/Huyện.
    const API_HOST = "https://tinhthanhpho.com/api/v1/new-provinces";

    function addressLabel(val) {
        return (val.type ? val.type + " " : "") + val.name;
    }

    $(document).ready(function() {
        // 1. Load Tỉnh/Thành phố khi vào trang
        $.getJSON(API_HOST + "?limit=100", function(res) {
            if (res.success) {
                $.each(res.data, function(key, val) {
                    $("#province").append(`<option value="${val.code}">${addressLabel(val)}</option>`);
                });
            }
        });

        // 2. Sự kiện chọn Tỉnh/Thành phố -> tải Xã/Phường trực thuộc
        $("#province").change(function() {
            let code = $(this).val();
            $("#province_name").val($("#province option:selected").text());

            $("#ward").html('<option value="">-- Chọn Phường/Xã --</option>').prop("disabled", true);

            if (code) {
                $.getJSON(API_HOST + "/" + code + "/wards?limit=1000", function(res) {
                    if (res.success) {
                        $.each(res.data, function(key, val) {
                            $("#ward").append(`<option value="${val.code}">${addressLabel(val)}</option>`);
                        });
                        $("#ward").prop("disabled", false);
                    }
                });
            }
        });

        // 3. Sự kiện chọn Xã/Phường
        $("#ward").change(function() {
            $("#ward_name").val($("#ward option:selected").text());
        });
    });

    // 5. Hàm điền dữ liệu khi bấm SỬA
    function fillStoreForm(store) {
        resetForm(); // Xóa trắng trước

        // Điền thông tin cơ bản
        $("#store_id").val(store.ch_ma);
        $("#name").val(store.ch_ten);
        $("#street").val(store.sonha_duong);
        $("#phone").val(store.ch_sdt);
        let status = (store.ch_trangthai == 1 || store.ch_trangthai === true || store.ch_trangthai === 't') ? "1" : "0";
        $("#status").val(status);
        $("#open_time").val(store.gio_mo_cua.substring(0, 5));
        $("#close_time").val(store.gio_dong_cua.substring(0, 5));

        // Đổi giao diện nút bấm
        $("#formTitle").text('Cập nhật: ' + store.ch_ten);
        $("#btnSubmit").text('Cập nhật').removeClass('btn-success').addClass('btn-warning');

        // Logic Cascade: Chọn Tỉnh/Thành -> chờ tải Xã/Phường -> Chọn Xã/Phường
        $("#province").val(store.matinh).change();

        setTimeout(function() {
            $("#ward").val(store.maxa).change();
        }, 500);

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function resetForm() {
        $("#storeForm")[0].reset();
        $("#store_id").val('');
        $("#ward").prop("disabled", true);
        $("#formTitle").text('Thêm Cửa Hàng Mới');
        $("#btnSubmit").text('Lưu Thông Tin').addClass('btn-success').removeClass('btn-warning');
        $("#open_time").val("07:00");
        $("#close_time").val("22:00");
        $("#status").val("1");
    }
</script>