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
                    template: 'OnPay_Magento2/payment/onpay-card'
                },
                getLogo: function () {
                    return window.checkoutConfig.payment.onpay_card.logo;
                },
                displayTitleLogo: function () {
                    return window.checkoutConfig.payment.onpay_card.logo_title;
                },
                getInstructions: function () {
                    return window.checkoutConfig.payment.onpay_card.instructions;
                },
                afterPlaceOrder: function () {
                    $.mage.redirect(window.checkoutConfig.payment.onpay_card.redirect_url);
                    return false;
                }
            }
        );
    }
);
