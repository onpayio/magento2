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

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * PaymentAction OnPay\Magento2\Model\Config\Source\PaymentAction
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class PaymentAction implements ArrayInterface
{
    /**
     * Config array
     *
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => MethodInterface::ACTION_AUTHORIZE, 'label' => __('Authorize')],
            ['value' => MethodInterface::ACTION_AUTHORIZE_CAPTURE, 'label' => __('Authorize & Capture')]
        ];
    }
}
