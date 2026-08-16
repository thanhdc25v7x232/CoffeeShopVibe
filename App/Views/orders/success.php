<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow text-center">
                <div class="card-body p-5">
                    <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
                    <h3 class="mb-2">Đặt hàng thành công!</h3>
                    <p class="text-muted">
                        Mã đơn hàng của bạn là
                        <strong class="text-success">#<?= $order['dh_ma'] ?></strong>.
                        Vibe sẽ liên hệ với bạn sớm nhất để xác nhận.
                    </p>

                    <ul class="list-group list-group-flush text-start mt-4">
                        <?php foreach ($items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= htmlspecialchars($item['sp_ten']) ?> x<?= $item['ctdh_soluong'] ?></span>
                                <strong><?= number_format($item['ctdh_thanhtien'], 0, ',', '.') ?> VNĐ</strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                        <h5 class="mb-0">Tổng cộng:</h5>
                        <h4 class="text-danger fw-bold mb-0"><?= number_format($order['dh_tongtien'], 0, ',', '.') ?> VNĐ</h4>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="/" class="btn btn-success btn-lg">
                            <i class="fa-solid fa-arrow-left me-2"></i>
                            Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
