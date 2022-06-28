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

class DeliveryDisabled implements ArrayInterface
{
    /**
     * Return Options Array
     *
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'no-reason', 'label' => __('No Reason')],
            ['value' => 'not-physical', 'label' => __('Not Physical')],
            ['value' => 'store-pick-up', 'label' => __('Store Pick Up')],
            ['value' => 'parcel-shop-selected', 'label' => __('Parcel Shop Selected')],
            ['value' => 'parcel-shop-auto', 'label' => __('Parcel Shop Auto')]
        ];
    }
}
