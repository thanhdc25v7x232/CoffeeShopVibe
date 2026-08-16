<?php

// File này khai báo toàn bộ route của ứng dụng.
// Được require từ public/index.php, dùng chung biến $router đã khởi tạo ở đó.

// --- KHAI BÁO CÁC ROUTE ---

// Đăng nhập

$router->post('/logout', '\App\Controllers\Auth\LoginController@destroy');
$router->get('/register', '\App\Controllers\Auth\RegisterController@create');
$router->post('/register', '\\App\Controllers\Auth\RegisterController@store');
$router->get('/login', '\App\Controllers\Auth\LoginController@create');
$router->post('/login', '\App\Controllers\Auth\LoginController@store');

// Thông tin tài khoản
$router->get('/account', '\App\Controllers\AccountController@show');
$router->post('/account', '\App\Controllers\AccountController@update');
$router->get('/account/orders', '\App\Controllers\AccountController@orders');
$router->get('/account/orders/version', '\App\Controllers\AccountController@ordersVersion');


// Trang chủ
$router->get('/', '\App\Controllers\HomeController@index');

// Tìm kiếm sản phẩm
$router->get('/search', '\App\Controllers\HomeController@search');

// Xem sản phẩm theo loại
$router->get('/category/{id}', '\App\Controllers\HomeController@category');

// Sản phẩm
$router->get('/san-pham', '\App\Controllers\ProductController@index');
$router->get('/san-pham/{id}', '\App\Controllers\ProductController@show');
$router->get('/api/products-version', '\App\Controllers\ProductController@version');

// Cửa hàng
$router->get('/cua-hang', '\App\Controllers\StoreController@locator');
$router->get('/api/stores-version', '\App\Controllers\StoreController@version');

// Giới thiệu / Về Vibe
$router->get('/ve-vibe', '\App\Controllers\HomeController@about');

// Liên hệ
$router->get('/lien-he', '\App\Controllers\HomeController@contact');

// Giỏ hàng
$router->get('/cart', '\App\Controllers\CartController@index');
$router->post('/cart/add', '\App\Controllers\CartController@add');
$router->get('/cart/add-ajax', '\App\Controllers\CartController@addAjax');
$router->post('/cart/update', '\App\Controllers\CartController@update');
$router->post('/cart/remove', '\App\Controllers\CartController@remove');
$router->post('/cart/clear', '\App\Controllers\CartController@clear');
$router->get('/cart/count', '\App\Controllers\CartController@count');

// Đơn hàng
$router->get('/checkout', '\App\Controllers\OrderController@checkout');
$router->post('/dat-hang', '\App\Controllers\OrderController@store');
$router->get('/dat-hang/thanh-cong', '\App\Controllers\OrderController@success');

// Admin đăng nhập
$router->get('/admin/login', '\App\Controllers\Auth\LoginController@index');
$router->post('/admin/login', '\App\Controllers\Auth\LoginController@authenticate');
$router->get('/admin/register', '\App\Controllers\Auth\AdminRegisterController@create');
$router->post('/admin/register', '\App\Controllers\Auth\AdminRegisterController@store');

// Admin Dashboard
$router->get('/admin', '\App\Controllers\AdminController@index');
$router->post('/admin/logout', '\App\Controllers\AdminController@logout');
$router->get('/admin/orders', '\App\Controllers\AdminController@orders');
$router->get('/admin/order-detail', '\App\Controllers\AdminController@orderDetail');
$router->post('/admin/update-order-status', '\App\Controllers\AdminController@updateOrderStatus');
$router->get('/admin/statistics', '\App\Controllers\AdminController@statistics');
$router->get('/admin/customers', '\App\Controllers\AdminController@customers');
$router->get('/admin/customer-detail', '\App\Controllers\AdminController@customerDetail');
$router->get('/admin/orders/new-check', '\App\Controllers\AdminController@ordersNewCheck');

// Quản lý sản phẩm
$router->get('/admin/products/create', '\App\Controllers\ProductController@create');
$router->post('/admin/products/store', '\App\Controllers\ProductController@store');
$router->post('/admin/products/update', '\App\Controllers\ProductController@update');
$router->post('/admin/products/delete', '\App\Controllers\ProductController@delete');
$router->post('/admin/products/delete-image', '\App\Controllers\ProductController@deleteImage');

// Quản lý cửa hàng
$router->get('/admin/stores', '\App\Controllers\StoreController@index');
$router->post('/admin/stores/save', '\App\Controllers\StoreController@save'); // Dùng chung cho Thêm & Sửa
$router->post('/admin/stores/delete', '\App\Controllers\StoreController@delete');

// Quản lý khuyến mãi
$router->get('/admin/promotions', '\App\Controllers\PromotionController@index');
$router->post('/admin/promotions/save', '\App\Controllers\PromotionController@save');
$router->post('/admin/promotions/delete', '\App\Controllers\PromotionController@delete');

// Khuyến mãi (trang công khai)
$router->get('/khuyen-mai', '\App\Controllers\HomeController@promotions');
