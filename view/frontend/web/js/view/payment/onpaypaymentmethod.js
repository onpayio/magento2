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
                type: 'onpaypaymentmethod',
                component: 'OnPay_Magento2/js/view/payment/method-renderer/onpaypaymentmethod-method'
            }
        );
        return Component.extend({});
    }
);