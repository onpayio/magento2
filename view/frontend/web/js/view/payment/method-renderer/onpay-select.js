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
                    template: 'OnPay_Magento2/payment/onpay-select'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_select.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_select.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_select.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_select.redirect_url);
                    return false;
                }
            }
        );
    }
);
