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

                    <?php
                    $paymentLabels = \App\Services\Payments\PaymentGatewayFactory::labels();
                    $paymentMethod = $paymentLabels[$order['dh_pttt']] ?? $order['dh_pttt'];
                    ?>
                    <p class="text-muted mb-0">
                        Thanh toán: <strong><?= htmlspecialchars($paymentMethod) ?></strong>
                        <?php if ($order['dh_tt_trangthai'] === 'paid'): ?>
                            <span class="badge bg-success">Đã thanh toán</span>
                        <?php elseif ($order['dh_tt_trangthai'] === 'waiting_confirmation'): ?>
                            <span class="badge bg-info text-dark">Chờ Vibe xác nhận</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                        <?php endif; ?>
                    </p>

                    <?php if ($manualPaymentInfo && $order['dh_tt_trangthai'] === 'unpaid'): ?>
                        <div class="card text-start mt-3">
                            <div class="card-header bg-white">
                                <i class="fa-solid fa-qrcode me-1"></i>
                                Thanh toán qua <?= htmlspecialchars($manualPaymentInfo->displayName()) ?>
                            </div>
                            <div class="card-body">
                                <?php if ($manualPaymentInfo->qrImagePath()): ?>
                                    <div class="text-center mb-3">
                                        <img src="<?= htmlspecialchars($manualPaymentInfo->qrImagePath()) ?>" alt="QR thanh toán" style="max-width: 220px;">
                                    </div>
                                <?php endif; ?>
                                <p class="mb-1">
                                    <?= htmlspecialchars($manualPaymentInfo->accountLabel()) ?>:
                                    <strong><?= htmlspecialchars($manualPaymentInfo->accountValue()) ?></strong>
                                </p>
                                <?php if ($manualPaymentInfo->accountOwnerName()): ?>
                                    <p class="mb-1">Chủ tài khoản: <strong><?= htmlspecialchars($manualPaymentInfo->accountOwnerName()) ?></strong></p>
                                <?php endif; ?>
                                <p class="mb-3">
                                    Nội dung chuyển khoản:
                                    <strong><?= htmlspecialchars($manualPaymentInfo->transferNote((int)$order['dh_ma'])) ?></strong>
                                </p>
                                <p class="text-muted small mb-3">
                                    Sau khi quét QR/chuyển khoản đúng số tiền và nội dung ở trên, bấm nút bên dưới để Vibe biết và kiểm tra xác nhận.
                                </p>
                                <form method="POST" action="/dat-hang/xac-nhan-da-chuyen">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= (int)$order['dh_ma'] ?>">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fa-solid fa-check me-1"></i>
                                        Tôi đã thanh toán
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($order['dh_tt_trangthai'] === 'waiting_confirmation'): ?>
                        <div class="alert alert-info text-start mt-3 mb-0">
                            <i class="fa-solid fa-clock me-1"></i>
                            Vibe đã ghi nhận, đang đối chiếu giao dịch. Đơn sẽ được xác nhận thanh toán sớm.
                        </div>
                    <?php endif; ?>

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
