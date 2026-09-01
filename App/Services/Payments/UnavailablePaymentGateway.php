<?php

namespace App\Services\Payments;

use RuntimeException;

// Đại diện cho các cổng thanh toán đã có chỗ trong kiến trúc (MoMo, VNPAY, chuyển khoản)
// nhưng chưa được cắm API thật — dùng chung một lớp cho đến khi có credentials để triển khai riêng.
class UnavailablePaymentGateway implements PaymentGatewayInterface
{
    private string $label;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function isAvailable(): bool
    {
        return false;
    }

    public function createPayment(array $order): array
    {
        throw new RuntimeException("Phương thức thanh toán \"{$this->label}\" hiện chưa khả dụng.");
    }
}
