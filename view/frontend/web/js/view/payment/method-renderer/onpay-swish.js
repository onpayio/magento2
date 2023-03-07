/**
 * OnPay Magento2 module
 *
 * @category  Payment_Method
 * @package   OnPay_Magento2
 * @copyright OnPay
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author URI: https://onpay.io
 */

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
