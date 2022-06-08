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
                    template: 'OnPay_Magento2/payment/onpay-paypal'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_paypal.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_paypal.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_paypal.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_paypal.redirect_url);
                    return false;
                }
            }
        );
    }
);
