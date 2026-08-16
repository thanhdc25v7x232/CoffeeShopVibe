<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-users me-2"></i>
                Quản lý khách hàng
            </h2>
            <p class="text-muted mb-0">
                Có <?= number_format($totalCustomers) ?> tài khoản phù hợp.
            </p>
        </div>

        <form method="GET" action="/admin/customers" class="d-flex gap-2" role="search">
            <input
                type="search"
                name="q"
                class="form-control"
                value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Tên, email hoặc số điện thoại"
                aria-label="Tìm khách hàng"
            >
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="d-none d-md-inline">Tìm</span>
            </button>
            <?php if ($keyword !== ''): ?>
                <a href="/admin/customers" class="btn btn-outline-secondary">Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã KH</th>
                            <th>Khách hàng</th>
                            <th>Liên hệ</th>
                            <th class="text-center">Đơn hàng</th>
                            <th class="text-end">Đã chi tiêu</th>
                            <th>Ngày đăng ký</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    Không tìm thấy khách hàng phù hợp.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>#<?= (int)$customer['kh_ma'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($customer['kh_ten'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($customer['kh_diachi'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($customer['kh_diachi'], ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($customer['kh_email'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($customer['kh_email'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($customer['kh_sdt'] ?: 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </td>
                                <td class="text-center"><?= number_format((int)$customer['total_orders']) ?></td>
                                <td class="text-end text-danger fw-semibold">
                                    <?= number_format((float)$customer['total_spent'], 0, ',', '.') ?>đ
                                </td>
                                <td>
                                    <?= !empty($customer['kh_ngaytao'])
                                        ? date('d/m/Y H:i', strtotime($customer['kh_ngaytao']))
                                        : '—' ?>
                                </td>
                                <td class="text-center">
                                    <a
                                        href="/admin/customer-detail?id=<?= (int)$customer['kh_ma'] ?>"
                                        class="btn btn-sm btn-outline-success"
                                    >
                                        <i class="fa-solid fa-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Phân trang khách hàng">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php
                            $query = http_build_query(array_filter([
                                'q' => $keyword,
                                'page' => $i,
                            ], static fn($value) => $value !== ''));
                            ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="/admin/customers?<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
