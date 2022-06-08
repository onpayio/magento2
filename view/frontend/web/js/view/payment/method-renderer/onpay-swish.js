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
                    template: 'OnPay_Magento2/payment/onpay-swish'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_swish.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_swish.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_swish.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_swish.redirect_url);
                    return false;
                }
            }
        );
    }
);
