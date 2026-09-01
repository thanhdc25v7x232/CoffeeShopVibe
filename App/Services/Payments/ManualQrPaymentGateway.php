<?php

namespace App\Services\Payments;

// Thanh toán thủ công qua QR cá nhân (MoMo cá nhân, chuyển khoản ngân hàng...): không gọi API
// nào, không có Secret Key. Khách quét QR/chuyển khoản rồi tự bấm "Tôi đã thanh toán", đơn
// chuyển sang chờ xác nhận — admin đối chiếu sao kê thực tế rồi mới xác nhận đã thanh toán.
// Đây là bước đệm trước khi có tài khoản MoMo for Business để cắm MomoPaymentGateway thật.
class ManualQrPaymentGateway implements PaymentGatewayInterface
{
    private string $displayName;
    private string $accountLabel;
    private string $accountValue;
    private ?string $qrImagePath;
    private ?string $accountOwnerName;

    public function __construct(
        string $displayName,
        string $accountLabel,
        string $accountValue,
        ?string $qrImagePath,
        ?string $accountOwnerName
    ) {
        $this->displayName = $displayName;
        $this->accountLabel = $accountLabel;
        $this->accountValue = $accountValue;
        $this->qrImagePath = $qrImagePath ?: null;
        $this->accountOwnerName = $accountOwnerName ?: null;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function createPayment(array $order): array
    {
        return ['redirect_url' => null];
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function accountLabel(): string
    {
        return $this->accountLabel;
    }

    public function accountValue(): string
    {
        return $this->accountValue;
    }

    public function qrImagePath(): ?string
    {
        return $this->qrImagePath;
    }

    public function accountOwnerName(): ?string
    {
        return $this->accountOwnerName;
    }

    public function transferNote(int $orderId): string
    {
        return 'DH' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT);
    }
}
