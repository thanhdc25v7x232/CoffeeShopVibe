<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="fa-solid fa-robot me-2"></i>
            Chi tiết hội thoại AI
        </h2>
        <a href="/admin/ai-conversations" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Danh sách hội thoại
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
            <?php foreach ($messages as $m): ?>
                <div class="mb-3 d-flex <?= $m['tl_vaitro'] === 'user' ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div>
                        <div class="small text-muted mb-1 <?= $m['tl_vaitro'] === 'user' ? 'text-end' : '' ?>">
                            <?= $m['tl_vaitro'] === 'user' ? 'Khách' : 'Trợ lý AI' ?> —
                            <?= date('d/m/Y H:i', strtotime($m['tl_ngaytao'])) ?>
                        </div>
                        <div class="px-3 py-2 rounded <?= $m['tl_vaitro'] === 'user' ? 'bg-success text-white' : 'bg-light border' ?>" style="max-width: 480px; word-break: break-word;">
                            <?= nl2br(htmlspecialchars($m['tl_noidung'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
