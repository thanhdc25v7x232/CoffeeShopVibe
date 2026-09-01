<?php

namespace App\Services\Payments;

class PaymentGatewayFactory
{
    // Nguồn duy nhất cho danh sách phương thức thanh toán: checkout view và OrderController
    // đều đọc từ đây để không lệch nhau khi thêm/bớt phương thức.
    private const LABELS = [
        'cod' => 'Thanh toán khi nhận hàng (COD)',
        'momo' => 'Ví MoMo',
        'vnpay' => 'VNPAY (QR / ATM / Visa / Mastercard)',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
    ];

    public static function labels(): array
    {
        return self::LABELS;
    }

    // Nguồn cấu hình duy nhất cho mapping code -> gateway. Đổi qua .env, không phải sửa code:
    // điền đủ biến của một phương thức thì phương thức đó "bật", để trống thì tự rơi về
    // UnavailablePaymentGateway (tương đương payment.momo.enabled=false của Spring).
    public static function make(string $method): PaymentGatewayInterface
    {
        if ($method === 'cod') {
            return new CodPaymentGateway();
        }

        if ($method === 'momo') {
            $phone = trim($_ENV['MOMO_PERSONAL_PHONE'] ?? '');
            if ($phone !== '') {
                return new ManualQrPaymentGateway(
                    self::LABELS['momo'],
                    'Số điện thoại MoMo',
                    $phone,
                    trim($_ENV['MOMO_PERSONAL_QR'] ?? '') ?: null,
                    trim($_ENV['MOMO_PERSONAL_NAME'] ?? '') ?: null
                );
            }
        }

        if ($method === 'bank_transfer') {
            $account = trim($_ENV['BANK_TRANSFER_ACCOUNT_NUMBER'] ?? '');
            if ($account !== '') {
                $bankName = trim($_ENV['BANK_TRANSFER_BANK_NAME'] ?? '');
                return new ManualQrPaymentGateway(
                    self::LABELS['bank_transfer'],
                    $bankName !== '' ? "Số tài khoản ({$bankName})" : 'Số tài khoản',
                    $account,
                    trim($_ENV['BANK_TRANSFER_QR'] ?? '') ?: null,
                    trim($_ENV['BANK_TRANSFER_ACCOUNT_NAME'] ?? '') ?: null
                );
            }
        }

        return new UnavailablePaymentGateway(self::LABELS[$method] ?? $method);
    }
}
