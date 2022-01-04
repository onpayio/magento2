<?php

namespace OnPay\OnPay\Model\Config\Source;

class Secure implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('No')],
            ['value' => 'forced', 'label' => __('Yes')]
        ];
    }
}
