<?php
$statusLabels = [
    'pending' => ['Chờ xác nhận', 'warning'],
    'confirmed' => ['Đã xác nhận', 'primary'],
    'completed' => ['Hoàn thành', 'success'],
    'cancelled' => ['Đã hủy', 'secondary'],
];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fa-solid fa-user me-2"></i>
            Chi tiết khách hàng
        </h2>
        <a href="/admin/customers" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Danh sách khách hàng
        </a>
    </div>

    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(is_array($errors) ? implode(' ', $errors) : $errors) ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <strong>Thông tin tài khoản #<?= (int)$customer['kh_ma'] ?></strong>
                    <?php $locked = filter_var($customer['kh_khoa'] ?? false, FILTER_VALIDATE_BOOLEAN); ?>
                    <form method="POST" action="/admin/customer-toggle-lock"
                          onsubmit="return confirm('<?= $locked ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này? Khách sẽ không đăng nhập được nữa.' ?>');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="customer_id" value="<?= (int)$customer['kh_ma'] ?>">
                        <input type="hidden" name="locked" value="<?= $locked ? '0' : '1' ?>">
                        <button type="submit" class="btn btn-sm <?= $locked ? 'btn-light' : 'btn-outline-light' ?>">
                            <?php if ($locked): ?>
                                <i class="fa-solid fa-lock-open"></i> Mở khóa
                            <?php else: ?>
                                <i class="fa-solid fa-lock"></i> Khóa tài khoản
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <?php if ($locked): ?>
                        <div class="alert alert-warning py-2 px-3 mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Tài khoản đang bị khóa — khách hàng không thể đăng nhập.
                        </div>
                    <?php endif; ?>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Họ và tên</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($customer['kh_ten'], ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-4">Email đăng nhập</dt>
                        <dd class="col-sm-8">
                            <a href="mailto:<?= htmlspecialchars($customer['kh_email'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($customer['kh_email'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </dd>

                        <dt class="col-sm-4">Số điện thoại</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars($customer['kh_sdt'] ?: 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4">Địa chỉ</dt>
                        <dd class="col-sm-8">
                            <?= nl2br(htmlspecialchars($customer['kh_diachi'] ?: 'Chưa cập nhật', ENT_QUOTES, 'UTF-8')) ?>
                        </dd>

                        <dt class="col-sm-4">Ngày đăng ký</dt>
                        <dd class="col-sm-8 mb-0">
                            <?= !empty($customer['kh_ngaytao'])
                                ? date('d/m/Y H:i', strtotime($customer['kh_ngaytao']))
                                : '—' ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="row g-3">
                <div class="col-6 col-lg-12">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-receipt fa-2x text-success mb-2"></i>
                            <h3 class="mb-0"><?= number_format((int)$customer['total_orders']) ?></h3>
                            <span class="text-muted">Tổng đơn hàng</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-12">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-wallet fa-2x text-danger mb-2"></i>
                            <h3 class="mb-0"><?= number_format((float)$customer['total_spent'], 0, ',', '.') ?>đ</h3>
                            <span class="text-muted">Chi tiêu không tính đơn hủy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fa-solid fa-clock-rotate-left me-2"></i>
                Lịch sử đơn hàng
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Hình thức nhận</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    Khách hàng chưa có đơn hàng nào.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($orders as $order): ?>
                            <?php
                            $status = $statusLabels[$order['dh_trangthai']]
                                ?? [ucfirst((string)$order['dh_trangthai']), 'secondary'];
                            ?>
                            <tr>
                                <td>#<?= (int)$order['dh_ma'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['dh_ngaytao'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['dh_htnhan'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($order['ch_ten'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($order['ch_ten'], ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-<?= $status[1] ?>">
                                        <?= htmlspecialchars($status[0], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    <?= number_format((float)$order['dh_tongtien'], 0, ',', '.') ?>đ
                                </td>
                                <td class="text-center">
                                    <a
                                        href="/admin/order-detail?id=<?= (int)$order['dh_ma'] ?>"
                                        class="btn btn-sm btn-outline-success"
                                    >
                                        <i class="fa-solid fa-eye"></i> Xem đơn
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
