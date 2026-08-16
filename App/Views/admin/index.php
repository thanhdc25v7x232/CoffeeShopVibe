<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fa-solid fa-chart-line me-2"></i>
                Dashboard Quản Trị
            </h2>
        </div>
    </div>

    <?php if (isset($messages['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($messages['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Card Quản Lý Cửa Hàng -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-store fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Quản Lý Cửa Hàng</h5>
                    <p class="card-text text-muted">Quản lý thông tin các cửa hàng</p>
                    <a href="/admin/stores" class="btn btn-success">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Quản Lý Sản Phẩm -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-box fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Quản Lý Sản Phẩm</h5>
                    <p class="card-text text-muted">Thêm, sửa, xóa sản phẩm</p>
                    <a href="/admin/products/create" class="btn btn-success">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Quản Lý Đơn Hàng -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-shopping-cart fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Quản Lý Đơn Hàng</h5>
                    <p class="card-text text-muted">Xem và quản lý đơn hàng</p>
                    <a href="/admin/orders" class="btn btn-success">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Thống Kê -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-chart-bar fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Thống Kê</h5>
                    <p class="card-text text-muted">Xem thống kê doanh thu</p>
                    <a href="/admin/statistics" class="btn btn-success">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Quản Lý Khuyến Mãi -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-tags fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Quản Lý Khuyến Mãi</h5>
                    <p class="card-text text-muted">Tạo và quản lý chương trình khuyến mãi</p>
                    <a href="/admin/promotions" class="btn btn-success">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Quản Lý Khách Hàng -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-users fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Quản Lý Khách Hàng</h5>
                    <p class="card-text text-muted">Xem tài khoản đăng ký và lịch sử mua hàng</p>
                    <a href="/admin/customers" class="btn btn-success">
                        <i class="fa-solid fa-arrow-right me-1"></i>
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-info-circle me-2"></i>
                        Thông Tin Hệ Thống
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Chào mừng bạn đến với trang quản trị!</strong>
                    </p>
                    <p class="text-muted mb-0">
                        Sử dụng menu phía trên để quản lý các chức năng của hệ thống.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
