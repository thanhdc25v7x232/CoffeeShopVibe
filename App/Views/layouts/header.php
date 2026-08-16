<?php
$menuCategories = [];
try {
    $menuCategories = (new \App\Models\Product(PDO()))->getCategories();
} catch (Throwable $e) {
    $menuCategories = [];
}
?>

<header class="text-center text-light" style="margin:0; padding:0; position: relative; z-index: 1000;">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 px-md-4 py-2" style="
            background-color: rgb(247, 220, 239);
            border-bottom: 1px solid #ccc;
            width: 100%;
            position: relative;
            box-sizing: border-box;">

        <div class="d-flex align-items-center gap-2 gap-md-3 flex-grow-1" style="min-width: 220px;">
            <a href="/" class="d-flex align-items-center flex-shrink-0">
                <img src="/img/logo/logo2.jpg" alt="Logo Vibe" height="70">
            </a>
            <form action="/search" method="GET" class="input-group flex-grow-1" style="max-width: 320px; min-width: 140px;">
                <input type="text"
                    class="form-control"
                    name="q"
                    placeholder="Bạn muốn mua gì hôm nay?"
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    required>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <button type="button" class="btn btn-light btn-sm" title="Phương thức nhận hàng">
                <i class="fa-solid fa-motorcycle"></i>
                <span class="d-none d-lg-inline">Phương thức nhận hàng</span>
            </button>
            <a href="/cart" class="btn btn-success position-relative btn-sm" title="Giỏ hàng">
                <i class="fa-solid fa-cart-shopping"></i> <span class="d-none d-sm-inline">Giỏ hàng</span>
                <?php
                $cartCount = 0;
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $cartCount += $item['quantity'];
                    }
                }
                ?>
                <span id="cart-badge"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="<?= $cartCount > 0 ? '' : 'display:none;' ?>">
                    <?= $cartCount ?>
                </span>
            </a>
            <?php if (AUTHGUARD()->isCustomerLoggedIn()): ?>
                <?php $customer = AUTHGUARD()->customer(); ?>
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user me-2"></i>
                        <span class="d-none d-sm-inline"><?= htmlspecialchars($customer->kh_ten ?? 'Tài khoản') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <h6 class="dropdown-header">
                                <i class="fa-solid fa-user me-2"></i>
                                <?= htmlspecialchars($customer->kh_ten ?? '') ?>
                            </h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="/account">
                                <i class="fa-solid fa-user-circle me-2"></i>
                                Thông tin tài khoản
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/account/orders">
                                <i class="fa-solid fa-receipt me-2"></i>
                                Đơn hàng của tôi
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                                <form method="POST" action="/logout" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fa-solid fa-sign-out-alt me-2"></i>
                                        Đăng xuất
                                    </button>
                                </form>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="/login" class="btn btn-success btn-sm" title="Đăng nhập">
                    <i class="fa-solid fa-user me-2"></i>
                    <span class="d-none d-sm-inline">Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="navbar navbar-expand-md navbar-dark" style="background-color: #B12A82; padding: 0;">
        <div class="container-fluid justify-content-center position-relative">
            <button class="navbar-toggler my-2" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu"
                    aria-controls="mainMenu" aria-expanded="false" aria-label="Mở/đóng menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="mainMenu">
                <ul class="navbar-nav text-center py-1">
                    <li class="nav-item"><a href="/" class="btn btn-success m-1">TRANG CHỦ</a></li>
                    <li class="nav-item dropdown">
                        <button type="button" class="dropbtn btn btn-success dropdown-toggle m-1" data-bs-toggle="dropdown">
                            MENU
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach ($menuCategories as $category): ?>
                                <li>
                                    <a class="dropdown-item" href="/category/<?= $category['l_ma'] ?>">
                                        <?= htmlspecialchars($category['l_ten']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="/cua-hang" class="btn btn-success m-1">CỬA HÀNG</a></li>
                    <li class="nav-item"><a href="/khuyen-mai" class="btn btn-success m-1">KHUYẾN MÃI</a></li>
                    <li class="nav-item"><a href="/ve-vibe" class="btn btn-success m-1">VỀ VIBE</a></li>
                    <li class="nav-item"><a href="/lien-he" class="btn btn-success m-1">LIÊN HỆ</a></li>
                </ul>
            </div>
        </div>
    </nav>

</header>

<script>
(function () {
    function refreshCartBadge() {
        fetch('/cart/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var badge = document.getElementById('cart-badge');
                if (!badge) return;
                var count = data.count || 0;
                badge.textContent = count;
                badge.style.display = count > 0 ? '' : 'none';
            })
            .catch(function () { /* bỏ qua lỗi mạng tạm thời */ });
    }
    // Tự cập nhật số lượng giỏ hàng định kỳ, không cần tải lại trang (F5)
    setInterval(refreshCartBadge, 8000);
})();
</script>
