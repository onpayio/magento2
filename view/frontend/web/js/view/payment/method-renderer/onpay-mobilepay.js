define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function ($, Component) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    redirectAfterPlaceOrder: false,
                    template: 'OnPay_Magento2/payment/onpay-mobilepay'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_mobilepay.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_mobilepay.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_mobilepay.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_mobilepay.redirect_url);
                    return false;
                }
            }
        );
    }
);
