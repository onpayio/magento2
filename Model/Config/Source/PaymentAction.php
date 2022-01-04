<?php

namespace OnPay\OnPay\Model\Config\Source;

use Magento\Payment\Model\MethodInterface;

class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => MethodInterface::ACTION_AUTHORIZE, 'label' => __('Authorize')],
            ['value' => MethodInterface::ACTION_AUTHORIZE_CAPTURE, 'label' => __('Authorize & Capture')]
        ];
    }
}
