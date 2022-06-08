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
                    template: 'OnPay_Magento2/payment/onpay-vipps'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_vipps.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_vipps.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_vipps.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_vipps.redirect_url);
                    return false;
                }
            }
        );
    }
);
