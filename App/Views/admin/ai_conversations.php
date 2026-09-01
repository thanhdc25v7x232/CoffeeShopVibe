<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fa-solid fa-robot me-2"></i>
        Trợ lý AI — Lịch sử hội thoại
    </h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Khách</th>
                            <th>Tin nhắn cuối</th>
                            <th class="text-center">Số tin nhắn</th>
                            <th>Thời gian</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">Chưa có hội thoại nào với trợ lý AI.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($session['kh_ten'])): ?>
                                        <?= htmlspecialchars($session['kh_ten']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Khách vãng lai</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-truncate" style="max-width: 380px;">
                                    <?= htmlspecialchars($session['last_message']) ?>
                                </td>
                                <td class="text-center"><?= (int)$session['message_count'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($session['last_time'])) ?></td>
                                <td class="text-center">
                                    <a href="/admin/ai-conversations/detail?session=<?= urlencode($session['phien']) ?>"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-eye"></i> Xem
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
