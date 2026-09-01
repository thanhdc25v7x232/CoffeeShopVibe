<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-receipt me-2"></i>
        Chi Tiết Đơn Hàng #<?= $order['dh_ma'] ?>
    </h2>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Thông tin nhận hàng</div>
                <div class="card-body">
                    <p><strong>Tên khách:</strong> <?= htmlspecialchars($order['dh_tenkh']) ?></p>
                    <p><strong>Điện thoại:</strong> <?= htmlspecialchars($order['dh_sdt']) ?></p>
                    <p><strong>Hình thức nhận:</strong> <?= htmlspecialchars($order['dh_htnhan']) ?></p>
                    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['dh_diachi'] ?? '') ?></p>
                    <?php if (!empty($order['ch_ten'])): ?>
                        <p><strong>Cửa hàng:</strong> <?= htmlspecialchars($order['ch_ten']) ?></p>
                    <?php endif; ?>
                    <p class="mb-0"><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['dh_ngaytao'])) ?></p>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">Thanh toán</div>
                <div class="card-body">
                    <p>
                        <strong>Phương thức:</strong>
                        <?= htmlspecialchars($paymentMethodLabels[$order['dh_pttt']] ?? $order['dh_pttt'] ?? 'COD') ?>
                    </p>
                    <p class="mb-3">
                        <strong>Trạng thái:</strong>
                        <?php $paymentStatus = $order['dh_tt_trangthai'] ?? 'unpaid'; ?>
                        <?php if ($paymentStatus === 'paid'): ?>
                            <span class="badge bg-success">Đã thanh toán</span>
                        <?php elseif ($paymentStatus === 'waiting_confirmation'): ?>
                            <span class="badge bg-info text-dark">Chờ xác nhận (khách báo đã chuyển)</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                        <?php endif; ?>
                    </p>

                    <?php if (($order['dh_tt_trangthai'] ?? 'unpaid') !== 'paid'): ?>
                        <form method="POST" action="/admin/mark-order-paid" onsubmit="return confirm('Xác nhận đơn hàng này đã được thanh toán?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int)$order['dh_ma'] ?>">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fa-solid fa-check"></i> Xác nhận đã thanh toán
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (!empty($transactions)): ?>
                        <hr>
                        <p class="mb-2 text-muted small">Lịch sử giao dịch với cổng thanh toán:</p>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cổng</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($t['gd_nhacc']) ?></td>
                                            <td><?= number_format($t['gd_sotien'], 0, ',', '.') ?>đ</td>
                                            <td><?= htmlspecialchars($t['gd_trangthai']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($t['gd_ngaytao'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">Sản phẩm</div>
                <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['sp_ten'] ?? 'Sản phẩm đã bị xóa') ?></td>
                                <td><?= $item['ctdh_soluong'] ?></td>
                                <td><?= number_format($item['ctdh_gia'], 0, ',', '.') ?>đ</td>
                                <td><?= number_format($item['ctdh_thanhtien'], 0, ',', '.') ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <strong>Tổng cộng:</strong>
                    <span class="text-danger fw-bold fs-5"><?= number_format($order['dh_tongtien'], 0, ',', '.') ?>đ</span>
                </div>
            </div>
        </div>
    </div>

    <a href="/admin/orders" class="btn btn-outline-secondary mt-3">
        <i class="fa fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>
