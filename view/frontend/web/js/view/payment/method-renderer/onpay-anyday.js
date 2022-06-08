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
                    template: 'OnPay_Magento2/payment/onpay-anyday'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_anyday.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_anyday.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_anyday.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_anyday.redirect_url);
                    return false;
                }
            }
        );
    }
);
