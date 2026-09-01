<style>
    .admin-topbar-logo { height: 60px; }
    .admin-topbar-title { font-size: 1.5rem; }
    @media (max-width: 575.98px) {
        .admin-topbar-logo { height: 42px; }
        .admin-topbar-title { font-size: 1.1rem; }
    }
</style>

<header class="text-center text-light" style="margin:0; padding:0; position: relative; z-index: 1000;">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 px-md-4 py-2" style="
            background-color: rgb(247, 220, 239);
            border-bottom: 1px solid #ccc;
            width: 100%;
            position: relative;
            box-sizing: border-box;">

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/admin" class="d-flex align-items-center gap-2 text-decoration-none" title="Về trang Dashboard">
                <img src="/img/logo/Logo2.jpg" alt="Logo Vibe" class="admin-topbar-logo">
                <h4 class="text-success admin-topbar-title mb-0">VIBE COFFEE</h4>
            </a>
            <i class="text-success d-none d-lg-inline small">Theo đuổi đam mê, thành công sẽ theo đuổi bạn</i>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-success small text-truncate" style="max-width: 160px;">Xin chào, <?php
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
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #B12A82; padding: 0;">
        <div class="container-fluid justify-content-center position-relative">
            <button class="navbar-toggler my-2" type="button" data-bs-toggle="collapse" data-bs-target="#adminMainMenu"
                    aria-controls="adminMainMenu" aria-expanded="false" aria-label="Mở/đóng menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="adminMainMenu">
                <ul class="navbar-nav text-center py-1 align-items-lg-center">
                    <li class="nav-item"><a href="/admin" class="btn btn-success btn-sm m-1">DASHBOARD</a></li>
                    <li class="nav-item"><a href="/admin/stores" class="btn btn-success btn-sm m-1">CỬA HÀNG</a></li>

                    <li class="nav-item dropdown">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle m-1" data-bs-toggle="dropdown">
                            SẢN PHẨM
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/products/create">Quản lý sản phẩm</a></li>
                            <li><a class="dropdown-item" href="/admin/categories">Danh mục</a></li>
                            <li><a class="dropdown-item" href="/admin/promotions">Khuyến mãi</a></li>
                            <li><a class="dropdown-item" href="/admin/inventory">Tồn kho</a></li>
                        </ul>
                    </li>

                    <li class="nav-item"><a href="/admin/orders" class="btn btn-success btn-sm m-1">ĐƠN HÀNG</a></li>

                    <li class="nav-item dropdown">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle m-1" data-bs-toggle="dropdown">
                            KHÁCH HÀNG
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/customers">Quản lý khách hàng</a></li>
                            <li><a class="dropdown-item" href="/admin/reviews">Đánh giá</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle m-1" data-bs-toggle="dropdown">
                            TRÒ CHUYỆN
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/chat">Chat với khách</a></li>
                            <li><a class="dropdown-item" href="/admin/ai-conversations">Trợ lý AI</a></li>
                        </ul>
                    </li>

                    <li class="nav-item"><a href="/admin/statistics" class="btn btn-success btn-sm m-1">THỐNG KÊ</a></li>

                    <li class="nav-item dropdown">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle m-1" data-bs-toggle="dropdown">
                            HỆ THỐNG
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/admin/admins">Tài khoản admin</a></li>
                            <li><a class="dropdown-item" href="/admin/settings">Cài đặt chung</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

</header>
