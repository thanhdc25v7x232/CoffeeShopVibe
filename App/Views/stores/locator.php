<div class="container mt-4">
    <div class="row g-3">
        <!-- Sidebar lọc tỉnh/thành -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Tìm cửa hàng gần bạn</h5>
                </div>
                <div class="card-body">
                    <!-- Dropdown cho mobile -->
                    <label class="form-label d-lg-none">Chọn Tỉnh/Thành</label>
                    <select id="provinceSelect" class="form-select mb-3 d-lg-none">
                        <option value="all" selected>Tất cả</option>
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?= htmlspecialchars($province) ?>"><?= htmlspecialchars($province) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Danh sách dạng list group cho desktop -->
                    <div id="provinceList" class="list-group d-none d-lg-block">
                        <button class="list-group-item list-group-item-action active" data-province="all">
                            Tất cả
                        </button>
                        <?php foreach ($provinces as $province): ?>
                            <button class="list-group-item list-group-item-action" data-province="<?= htmlspecialchars($province) ?>">
                                <?= htmlspecialchars($province) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <small class="text-muted d-block mt-3">
                        Chọn Tỉnh/Thành để lọc danh sách cửa hàng ở bên phải.
                    </small>
                </div>
            </div>
        </div>

        <!-- Nội dung danh sách cửa hàng -->
        <div class="col-lg-9">
            <h2 class="mb-3">Danh sách cửa hàng</h2>

            <?php if (empty($stores)): ?>
                <div class="alert alert-warning">Hiện chưa có cửa hàng hoạt động.</div>
            <?php else: ?>
                <div class="row" id="storeList">
                    <?php foreach ($stores as $store): ?>
                        <?php
                            $province = isset($store['ch_thanhpho']) ? (string)$store['ch_thanhpho'] : '';
                            $name = isset($store['ch_ten']) ? (string)$store['ch_ten'] : '';
                            $address = isset($store['ch_diachi']) ? (string)$store['ch_diachi'] : '';
                            $phone = isset($store['ch_sdt']) ? (string)$store['ch_sdt'] : '';
                            $openTime = isset($store['gio_mo_cua']) ? (string)$store['gio_mo_cua'] : '';
                            $closeTime = isset($store['gio_dong_cua']) ? (string)$store['gio_dong_cua'] : '';
                            $isActive = ($store['ch_trangthai'] == 1 || $store['ch_trangthai'] === true || $store['ch_trangthai'] === 't');
                        ?>
                        <div class="col-md-6 mb-3 store-card" data-province="<?= htmlspecialchars($province) ?>">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($name) ?></h5>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Tạm ngưng</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted mb-1">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($address) ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fa fa-phone"></i>
                                        <?= htmlspecialchars($phone) ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fa fa-clock"></i>
                                        <?php
                                            $openDisplay = $openTime !== '' ? htmlspecialchars(substr($openTime, 0, 5)) : '-';
                                            $closeDisplay = $closeTime !== '' ? htmlspecialchars(substr($closeTime, 0, 5)) : '-';
                                        ?>
                                        <?= $openDisplay ?> - <?= $closeDisplay ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const provinceButtons = document.querySelectorAll('#provinceList [data-province]');
        const provinceSelect = document.getElementById('provinceSelect');
        const storeCards = document.querySelectorAll('.store-card');

        const applyFilter = (province) => {
            const normalized = province.toLowerCase();
            storeCards.forEach(card => {
                const cardProvince = (card.dataset.province || '').toLowerCase();
                const match = normalized === 'all' || cardProvince === normalized;
                card.style.display = match ? '' : 'none';
            });
        };

        provinceButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                provinceButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const province = btn.getAttribute('data-province');
                provinceSelect.value = province;
                applyFilter(province);
            });
        });

        if (provinceSelect) {
            provinceSelect.addEventListener('change', (e) => {
                const province = e.target.value;
                applyFilter(province);
                // đồng bộ trạng thái list-group trên desktop
                provinceButtons.forEach(b => {
                    b.classList.toggle('active', b.getAttribute('data-province') === province);
                });
            });
        }
    });
</script>

<script>
(function () {
    var initialVersion = null;
    function checkStoresUpdated() {
        fetch('/api/stores-version')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (initialVersion === null) {
                    initialVersion = data.version;
                    return;
                }
                if (data.version !== initialVersion) {
                    location.reload();
                }
            })
            .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
    }
    // Tự phát hiện khi admin vừa thêm/sửa/xóa cửa hàng, tự tải lại trang
    setInterval(checkStoresUpdated, 8000);
})();
</script>

