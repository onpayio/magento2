<?php

namespace OnPay\Magento2\Model\Payment;

class OnPayCardMethod extends AbstractOnPayMethod {
    const METHOD_CODE = 'onpay_card';
    protected $_code = self::METHOD_CODE;
}
