<?php

/**
 * OnPay Magento2 module
 * php version 7.4.27
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author: Julian F. Christmas
 * Version: 1.0.0
 * Author URI: https://intelligodenmark.dk
 */

namespace OnPay\Magento2\Model\Config\Source;

/**
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class DeliveryDisabled implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return Options Array
     *
     * @return void
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
