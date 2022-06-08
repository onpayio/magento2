define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'onpay_select',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-select'
            },
            {
                type: 'onpay_card',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-card'
            },
            {
                type: 'onpay_paypal',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-paypal'
            },
            {
                type: 'onpay_mobilepay',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-mobilepay'
            },
            {
                type: 'onpay_viabill',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-viabill'
            },
            {
                type: 'onpay_anyday',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-anyday'
            },
            {
                type: 'onpay_swish',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-swish'
            },
            {
                type: 'onpay_vipps',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpay-vipps'
            }
        );
        return Component.extend({});
    }
);
