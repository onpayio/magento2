<?php

namespace OnPay\OnPay\Model\Config\Source;

class PaymentWindowLanguage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'en', 'label' => __('English')],
            ['value' => 'da', 'label' => __('Danish')],
            ['value' => 'se', 'label' => __('Swedish')],
            ['value' => 'de', 'label' => __('German')],
            ['value' => 'es', 'label' => __('Spanish')],
            ['value' => 'fr', 'label' => __('French')],
            ['value' => 'it', 'label' => __('Italian')],
            ['value' => 'nl', 'label' => __('Dutch')],
        ];
    }
}
