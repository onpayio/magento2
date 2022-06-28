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
 * Version: 1.0.0
 * Author URI: https://onpay.io
 */

namespace OnPay\Magento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentWindowLanguage implements ArrayInterface
{
    /**
     * Return Options Array
     *
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'en', 'label' => __('English')],
            ['value' => 'da', 'label' => __('Danish')],
            ['value' => 'se', 'label' => __('Swedish')],
            ['value' => 'no', 'label' => __('Norwegian')],
            ['value' => 'de', 'label' => __('German')],
            ['value' => 'es', 'label' => __('Spanish')],
            ['value' => 'fr', 'label' => __('French')],
            ['value' => 'it', 'label' => __('Italian')],
            ['value' => 'nl', 'label' => __('Dutch')]
        ];
    }
}
