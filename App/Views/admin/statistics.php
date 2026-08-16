<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-chart-bar me-2"></i>
        Thống Kê
    </h2>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-receipt fa-3x text-success mb-3"></i>
                    <h3 class="mb-0"><?= number_format($stats['total_orders']) ?></h3>
                    <p class="text-muted mb-0">Tổng số đơn hàng</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-sack-dollar fa-3x text-success mb-3"></i>
                    <h3 class="mb-0"><?= number_format($stats['total_revenue'], 0, ',', '.') ?>đ</h3>
                    <p class="text-muted mb-0">Tổng doanh thu</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-box fa-3x text-success mb-3"></i>
                    <h3 class="mb-0"><?= number_format($stats['total_products']) ?></h3>
                    <p class="text-muted mb-0">Tổng số sản phẩm</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fa-solid fa-users fa-3x text-success mb-3"></i>
                    <h3 class="mb-0"><?= number_format($stats['total_customers']) ?></h3>
                    <p class="text-muted mb-0">Tổng số khách hàng</p>
                </div>
            </div>
        </div>
    </div>
</div>
