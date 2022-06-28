<?php

namespace OnPay\Magento2\Model\Payment;

class OnPaySwishMethod extends AbstractOnPayMethod
{
    const METHOD_CODE = 'onpay_swish';
    protected $_code = self::METHOD_CODE;
}
