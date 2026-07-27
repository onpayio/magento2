<?php
declare(strict_types=1);

namespace OnPay\Magento2\Test\Unit\Model\Payment;

use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use OnPay\Magento2\Model\Payment\OnPayCardMethod;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that capture, refund, cancel and void raise a
 * ValidatorException with the expected message when the payment
 * has no OnpayUUID (i.e. was never authorized at OnPay).
 */
class AbstractOnPayMethodTest extends TestCase
{
    /**
     * Instantiate the payment method without invoking Magento's
     * DI-heavy parent constructor.
     */
    private function newMethod(): OnPayCardMethod
    {
        return (new \ReflectionClass(OnPayCardMethod::class))
            ->newInstanceWithoutConstructor();
    }

    private function newPaymentWithoutUuid(): Payment
    {
        $order = $this->createMock(Order::class);

        $payment = $this->createMock(Payment::class);
        $payment->method('getOrder')->willReturn($order);
        $payment->method('getAdditionalInformation')->willReturn([]); // no OnpayUUID

        return $payment;
    }

    public function testCaptureThrowsValidatorExceptionWithPhraseWhenOnpayUuidMissing(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Payment is not authorized.');

        $this->newMethod()->capture($this->newPaymentWithoutUuid(), 10.00);
    }

    public function testRefundThrowsValidatorExceptionWithPhraseWhenOnpayUuidMissing(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('refund');

        $this->newMethod()->refund($this->newPaymentWithoutUuid(), 10.00);
    }

    public function testCancelThrowsValidatorExceptionWithPhraseWhenOnpayUuidMissing(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('cancelled');

        $this->newMethod()->cancel($this->newPaymentWithoutUuid());
    }

    public function testVoidThrowsValidatorExceptionWithPhraseWhenOnpayUuidMissing(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('voided');

        $this->newMethod()->void($this->newPaymentWithoutUuid());
    }
}
