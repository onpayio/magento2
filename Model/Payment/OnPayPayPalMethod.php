<?php

namespace OnPay\Magento2\Model\Payment;

class OnPayPayPalMethod extends AbstractOnPayMethod
{
    const METHOD_CODE = 'onpay_paypal';
    protected $_code = self::METHOD_CODE;
}
