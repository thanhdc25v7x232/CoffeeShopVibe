<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    // False khi thiếu cấu hình (chưa có khóa API) hoặc cổng chưa được cắm vào hệ thống.
    public function isAvailable(): bool;

    /**
     * Khởi tạo thanh toán cho một đơn hàng vừa tạo.
     *
     * @param array $order Bản ghi DON_HANG vừa tạo (dh_ma, dh_tongtien, ...)
     * @return array{redirect_url: ?string} redirect_url null nghĩa là không cần rời khỏi site (vd. COD)
     */
    public function createPayment(array $order): array;
}
