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
 * Methods OnPay\Magento2\Model\Config\Source\Methods
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 */
class Methods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Config array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'onpay_select', 'label' => __('Select any method')],
            ['value' => 'onpay_card', 'label' => __('Credit Card')],
            ['value' => 'onpay_paypal', 'label' => __('PayPal')],
            ['value' => 'onpay_mobilepay', 'label' => __('Mobile Pay')],
            ['value' => 'onpay_viabill', 'label' => __('Viabill')],
            ['value' => 'onpay_anyday', 'label' => __('Anyday')],
            ['value' => 'onpay_swish', 'label' => __('Swish')],
            ['value' => 'onpay_vipps', 'label' => __('Vipps')],
        ];
    }
}
