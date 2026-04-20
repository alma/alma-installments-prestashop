const ALMA_CART_WIDGET_SELECTORS = [
    '#alma-widget-cart',
    '#alma-widget-ShoppingCartFooter',
];

const ALMA_PRODUCT_WIDGET_SELECTORS = [
    '#alma-widget-product',
    '#alma-widget-ProductPriceBlock',
];

const ALMA_WIDGET_SELECTORS = [
    ...ALMA_CART_WIDGET_SELECTORS,
    ...ALMA_PRODUCT_WIDGET_SELECTORS,
];

const TEST_MODE = 'test';

function getCartAmountInCents() {
    if (typeof prestashop === 'undefined' || !prestashop.cart || !prestashop.cart.totals) {
        console.error('Prestashop cart totals are not available.');
        return null;
    }
    const totals = prestashop.cart.totals;
    const total = totals.total_including_tax || totals.total;
    if (!total || total.amount === undefined) {
        console.error('Total amount is not available in cart totals.');
        return null;
    }
    return Math.round(parseFloat(total.amount) * 100);
}

function findWidgetContainer($, selectors) {
    for (const selector of selectors) {
        const $el = $(selector);
        if ($el.length) return $el;
    }
    return null;
}

function initAlmaWidgetFromContainer($container, Alma) {
    let widgetConfig = $container.data('widget-config');

    if (typeof widgetConfig.plans === 'string') {
        widgetConfig.plans = JSON.parse(widgetConfig.plans);
    }

    const mode = (widgetConfig.mode === TEST_MODE) ? Alma.ApiMode.TEST : Alma.ApiMode.LIVE;
    const widgets = Alma.Widgets.initialize(
        widgetConfig.merchantId,
        mode,
    );

    widgets.add(Alma.Widgets.PaymentPlans, {
        container: widgetConfig.containerId,
        purchaseAmount: widgetConfig.purchaseAmount,
        locale: widgetConfig.locale,
        hideIfNotEligible: widgetConfig.hideIfNotEligible,
        plans: widgetConfig.plans,
    });

    return widgets;
}

function initAlmaWidget($, Alma) {
    const $container = findWidgetContainer($, ALMA_WIDGET_SELECTORS);
    if (!$container) {
        console.error('No Alma widget container found on the page.');
        return null;
    }
    return initAlmaWidgetFromContainer($container, Alma);
}

// module is defined in Node.js environments, but not in browsers.
// This check allows the code to be used for unit test and browser contexts.
if (typeof module !== 'undefined') {
    module.exports = { initAlmaWidget, findWidgetContainer, initAlmaWidgetFromContainer, getCartAmountInCents };
} else {
    (function ($) {
        $(function () {
            initAlmaWidget($, Alma);

            if (typeof prestashop !== 'undefined') {
                prestashop.on('updateCart', function () {
                    const newAmount = getCartAmountInCents();
                    // If we can't get the new amount, we shouldn't try to update the widget.
                    if (newAmount === null) return;

                    const $widget = findWidgetContainer($, ALMA_CART_WIDGET_SELECTORS);
                    if (!$widget) return;

                    const config = $widget.data('widget-config');
                    if (!config) return;

                    config.purchaseAmount = newAmount;
                    $widget.data('widget-config', config);

                    initAlmaWidget($, Alma);
                });
            }
        });
    })(jQuery);
}
