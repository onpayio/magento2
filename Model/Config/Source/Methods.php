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

namespace OnPay\OnPay\Model\Config\Source;

/**
 * Methods OnPay\OnPay\Model\Config\Source\Methods
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
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
            ['value' => '', 'label' => __('Select any method')],
            ['value' => 'card', 'label' => __('Card')],
            ['value' => 'mobilepay', 'label' => __('Mobile Pay')],
            ['value' => 'mobilepay_checkout', 'label' => __('Mobile Pay Checkout')],
            ['value' => 'viabill', 'label' => __('Viabill')],
            ['value' => 'anyday', 'label' => __('Any Day')],
            ['value' => 'applepay', 'label' => __('Apple Pay')]
        ];
    }
}
