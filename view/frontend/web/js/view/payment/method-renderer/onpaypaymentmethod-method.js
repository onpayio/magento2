define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function ($, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'OnPay_OnPay/payment/onpaypaymentmethod'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.onpay.instructions;
            },
            afterPlaceOrder: function() {
                $.mage.redirect(window.checkoutConfig.payment.onpay.redirect_url);
                return false;
            }
        });
    }
);
