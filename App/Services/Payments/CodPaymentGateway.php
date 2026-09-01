<?php

namespace App\Services\Payments;

// Thanh toán khi nhận hàng: không có bước gọi cổng thanh toán nào, đơn hàng giữ
// DH_TT_TRANGTHAI = 'unpaid' cho đến khi được thu tiền/xác nhận thủ công.
class CodPaymentGateway implements PaymentGatewayInterface
{
    public function isAvailable(): bool
    {
        return true;
    }

    public function createPayment(array $order): array
    {
        return ['redirect_url' => null];
    }
}
