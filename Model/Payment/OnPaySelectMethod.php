<?php

namespace OnPay\Magento2\Model\Payment;

class OnPaySelectMethod extends AbstractOnPayMethod {
    const METHOD_CODE = 'onpay_select';
    protected $_code = self::METHOD_CODE;
}
