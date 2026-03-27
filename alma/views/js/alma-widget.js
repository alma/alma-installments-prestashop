function getCartAmountInCents() {
    if (typeof prestashop === 'undefined' || !prestashop.cart || !prestashop.cart.totals) {
        return null;
    }
    const totals = prestashop.cart.totals;
    const total = totals.total_including_tax || totals.total;
    if (!total || total.amount === undefined) {
        return null;
    }
    return Math.round(parseFloat(total.amount) * 100);
}

function initAlmaWidget($, Alma) {
    if (!$("#alma-widget-ShoppingCartFooter").length && !$("#alma-widget-cart").length) {
        return null;
    }
    let widgetConfig = $("#alma-widget-ShoppingCartFooter").data('widget-config');
    if ($("#alma-widget-cart").length) {
        widgetConfig = $("#alma-widget-cart").data('widget-config');
    }

    const mode = (widgetConfig.mode === 'test') ? Alma.ApiMode.TEST : Alma.ApiMode.LIVE
    const widgets = Alma.Widgets.initialize(
        widgetConfig.merchantId,
        mode,
    )

    if (typeof widgetConfig.plans === 'string') {
        widgetConfig.plans = JSON.parse(widgetConfig.plans);
    }

    widgets.add(Alma.Widgets.PaymentPlans, {
        container: widgetConfig.containerId,
        purchaseAmount: widgetConfig.purchaseAmount,
        locale: widgetConfig.locale,
        hideIfNotEligible: widgetConfig.hideIfNotEligible,
        plans: widgetConfig.plans,
    })

    return widgets;
}

if (typeof module !== 'undefined') {
    module.exports = { initAlmaWidget, getCartAmountInCents };
} else {
    (function ($) {
        $(function () {
            const widgets = initAlmaWidget($, Alma);

            if (typeof prestashop !== 'undefined') {
                prestashop.on('updateCart', function () {
                    const newAmount = getCartAmountInCents();
                    if (newAmount === null) return;

                    const $widget = $("#alma-widget-cart").length
                        ? $("#alma-widget-cart")
                        : $("#alma-widget-ShoppingCartFooter");

                    if (!$widget.length) return;

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
