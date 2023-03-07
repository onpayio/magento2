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

namespace OnPay\Magento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\MethodInterface;

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
