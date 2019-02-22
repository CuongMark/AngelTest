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
                type: 'test',
                component: 'Angel_Test/js/view/payment/method-renderer/test-method'
            }
        );
        return Component.extend({});
    }
);