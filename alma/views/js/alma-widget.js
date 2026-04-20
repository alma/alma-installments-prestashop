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

function toCents(amount) {
    return Math.round(parseFloat(amount) * 100);
}

// Returns the total product amount in cents from the embedded data-product attribute.
// If quantity is provided (e.g. from the qty input on manual change), it overrides quantity_wanted.
function getProductAmountFromProductData(productData, quantity) {
    if (!productData || productData.price_amount === undefined) return null;
    const unitPriceInCents = toCents(productData.price_amount);
    if (!unitPriceInCents) return null;
    const qty = quantity !== undefined ? parseInt(quantity, 10) : parseInt(productData.quantity_wanted, 10);
    return unitPriceInCents * (qty || 1);
}

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
    return toCents(total.amount);
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
    module.exports = { initAlmaWidget, findWidgetContainer, initAlmaWidgetFromContainer, getCartAmountInCents, getProductAmountFromProductData, toCents };
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

                // Product unit price in cents, initialized from the widget config (price for qty=1).
                let productUnitPriceInCents = (function () {
                    const $widgetContainer = findWidgetContainer($, ['#alma-widget-product', '#alma-widget-ProductPriceBlock']);
                    if (!$widgetContainer) return null;
                    const config = $widgetContainer.data('widget-config');
                    return config ? config.purchaseAmount : null;
                })();

                function updateProductWidget(newAmount) {
                    const $widget = findWidgetContainer($, ALMA_PRODUCT_WIDGET_SELECTORS);
                    if (!$widget) return;
                    const config = $widget.data('widget-config');
                    if (!config) return;
                    config.purchaseAmount = newAmount;
                    $widget.data('widget-config', config);
                    initAlmaWidget($, Alma);
                }

                prestashop.on('updatedProduct', function () {
                    const $widget = findWidgetContainer($, ALMA_PRODUCT_WIDGET_SELECTORS);
                    if (!$widget) return;
                    const newAmount = getProductAmountFromProductData($widget.data('product'));
                    if (newAmount === null) return;
                    updateProductWidget(newAmount);
                });

                $(document).on('change', '[name="qty"]', function () {
                    const $widget = findWidgetContainer($, ALMA_PRODUCT_WIDGET_SELECTORS);
                    if (!$widget) return;
                    const newQty = parseInt($('[name="qty"]').val(), 10) || 1;
                    const newAmount = getProductAmountFromProductData($widget.data('product'), newQty);
                    if (newAmount === null) return;
                    updateProductWidget(newAmount);
                });
            }
        });
    })(jQuery);
}
