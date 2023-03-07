<?php

/**
 * OnPay Magento2 module
 *
 * @category  Payment_Method
 * @package   OnPay_Magento2
 * @copyright OnPay
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author URI: https://onpay.io
 */

namespace OnPay\Magento2\Model\Payment;

class OnPayMobilePayMethod extends AbstractOnPayMethod
{
    const METHOD_CODE = 'onpay_mobilepay';
    protected $_code = self::METHOD_CODE;
}
