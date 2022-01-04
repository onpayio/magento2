<?php

namespace OnPay\OnPay\Model\Config\Source;

class Design implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'window1', 'label' => __('Window 1')]
        ];
    }
}
