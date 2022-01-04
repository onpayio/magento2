<?php

namespace OnPay\OnPay\Model\Config\Source;

class DeliveryDisabled implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'no-reason', 'label' => __('No Reason')],
            ['value' => 'not-physical', 'label' => __('Not Physical')],
            ['value' => 'store-pick-up', 'label' => __('Store Pick Up')],
            ['value' => 'parcel-shop-selected', 'label' => __('Parcel Shop Selected')],
            ['value' => 'parcel-shop-auto', 'label' => __('Parcel Shop Auto')]
        ];
    }
}
