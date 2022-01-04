<?php

namespace OnPay\OnPay\Model\Config\Source;

class Methods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Select any method')],
            ['value' => 'card', 'label' => __('Card')],
            ['value' => 'mobilepay', 'label' => __('Mobile Pay')],
            ['value' => 'mobilepay_checkout', 'label' => __('Mobile Pay Checkout')],
            ['value' => 'viabill', 'label' => __('Viabill')],
            ['value' => 'anyday', 'label' => __('AnyDay')],
            ['value' => 'applepay', 'label' => __('Apple Pay')]
        ];
    }
}
