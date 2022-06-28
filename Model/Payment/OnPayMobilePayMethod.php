<?php

namespace OnPay\Magento2\Model\Payment;

class OnPayMobilePayMethod extends AbstractOnPayMethod
{
    const METHOD_CODE = 'onpay_mobilepay';
    protected $_code = self::METHOD_CODE;
}
