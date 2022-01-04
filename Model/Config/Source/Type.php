<?php

namespace OnPay\OnPay\Model\Config\Source;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'transaction', 'label' => __('Transaction')]
        ];
    }
}
