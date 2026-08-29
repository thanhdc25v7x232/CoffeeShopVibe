<header class="text-center text-light" style="margin:0; padding:0; position: relative; z-index: 1000;">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 px-md-4 py-2" style="
            background-color: rgb(247, 220, 239);
            border-bottom: 1px solid #ccc;
            width: 100%;
            position: relative;
            box-sizing: border-box;">

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/admin" class="d-flex align-items-center gap-2 text-decoration-none" title="Về trang Dashboard">
                <img src="/img/logo/Logo2.jpg" alt="Logo Vibe" height="60">
                <h4 class="text-success display-6 mb-0">VIBE COFFEE </h4>
            </a>
            <i class="text-success display-7 d-none d-md-inline">Theo đuổi đam mê, thành công sẽ theo đuổi bạn</i>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-success">Xin chào, <?php
                $admin = AUTHGUARD()->admin();
                echo htmlspecialchars($admin ? $admin->qtv_tendn : 'Admin');
            ?></span>

            <form method="POST" action="/admin/logout" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm" title="Đăng xuất">
                    <i class="fa-solid fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Đăng xuất</span>
                </button>
            </form>
        </div>
    </div>
    <nav class="navbar navbar-expand-md navbar-dark" style="background-color: #B12A82; padding: 0;">
        <div class="container-fluid justify-content-center position-relative">
            <button class="navbar-toggler my-2" type="button" data-bs-toggle="collapse" data-bs-target="#adminMainMenu"
                    aria-controls="adminMainMenu" aria-expanded="false" aria-label="Mở/đóng menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="adminMainMenu">
                <ul class="navbar-nav text-center py-1">
                    <li class="nav-item"><a href="/admin" class="btn btn-success m-1">DASHBOARD</a></li>
                    <li class="nav-item"><a href="/admin/stores" class="btn btn-success m-1">QUẢN LÝ CỬA HÀNG</a></li>
                    <li class="nav-item"><a href="/admin/products/create" class="btn btn-success m-1">QUẢN LÝ SẢN PHẨM</a></li>
                    <li class="nav-item"><a href="/admin/promotions" class="btn btn-success m-1">QUẢN LÝ KHUYẾN MÃI</a></li>
                    <li class="nav-item"><a href="/admin/orders" class="btn btn-success m-1">QUẢN LÝ ĐƠN HÀNG</a></li>
                    <li class="nav-item"><a href="/admin/customers" class="btn btn-success m-1">QUẢN LÝ KHÁCH HÀNG</a></li>
                    <li class="nav-item"><a href="/admin/reviews" class="btn btn-success m-1">QUẢN LÝ ĐÁNH GIÁ</a></li>
                    <li class="nav-item"><a href="/admin/inventory" class="btn btn-success m-1">TỒN KHO</a></li>
                    <li class="nav-item"><a href="/admin/chat" class="btn btn-success m-1">CHAT</a></li>
                    <li class="nav-item"><a href="/admin/statistics" class="btn btn-success m-1">THỐNG KÊ ĐƠN VÀ DOANH THU</a></li>
                </ul>
            </div>
        </div>
    </nav>

</header>
