<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fa-solid fa-star me-2"></i>
                Quản lý đánh giá sản phẩm
            </h2>
            <p class="text-muted mb-0">Có <?= number_format($totalReviews) ?> đánh giá phù hợp.</p>
        </div>

        <div class="btn-group" role="group">
            <a href="/admin/reviews" class="btn btn-outline-success <?= $status === null ? 'active' : '' ?>">Tất cả</a>
            <a href="/admin/reviews?status=pending" class="btn btn-outline-warning <?= $status === 'pending' ? 'active' : '' ?>">Chờ duyệt</a>
            <a href="/admin/reviews?status=approved" class="btn btn-outline-success <?= $status === 'approved' ? 'active' : '' ?>">Đã duyệt</a>
            <a href="/admin/reviews?status=rejected" class="btn btn-outline-secondary <?= $status === 'rejected' ? 'active' : '' ?>">Đã từ chối</a>
        </div>
    </div>

    <?php if (!empty($messages['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Khách hàng</th>
                            <th>Sao</th>
                            <th>Nội dung</th>
                            <th>Trạng thái</th>
                            <th>Ngày</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Không có đánh giá nào.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?= htmlspecialchars($review['sp_ten']) ?></td>
                                <td><?= htmlspecialchars($review['kh_ten']) ?></td>
                                <td class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-<?= $i <= $review['dg_sao'] ? 'solid' : 'regular' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </td>
                                <td style="max-width: 260px;"><?= nl2br(htmlspecialchars($review['dg_noidung'] ?? '')) ?></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'pending' => ['Chờ duyệt', 'warning'],
                                        'approved' => ['Đã duyệt', 'success'],
                                        'rejected' => ['Đã từ chối', 'secondary'],
                                    ];
                                    [$label, $color] = $statusMap[$review['dg_trangthai']] ?? [$review['dg_trangthai'], 'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($review['dg_ngaytao'])) ?></td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <?php if ($review['dg_trangthai'] !== 'approved'): ?>
                                            <form method="POST" action="/admin/reviews/update-status" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="review_id" value="<?= $review['dg_ma'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($review['dg_trangthai'] !== 'rejected'): ?>
                                            <form method="POST" action="/admin/reviews/update-status" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="review_id" value="<?= $review['dg_ma'] ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Từ chối">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/admin/reviews/delete" class="d-inline" onsubmit="return confirm('Xóa đánh giá này?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="review_id" value="<?= $review['dg_ma'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Phân trang đánh giá">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php
                            $query = http_build_query(array_filter([
                                'status' => $status,
                                'page' => $i,
                            ], static fn($value) => $value !== null && $value !== ''));
                            ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="/admin/reviews?<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
