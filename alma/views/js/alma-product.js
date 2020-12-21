(function ($) {
    $(function () {
        function initWidget(merchantId, apiMode, containerId, purchaseAmount, plans) {
            var widgets = Alma.Widgets.initialize(merchantId, apiMode);

            widgets.add(Alma.Widgets.PaymentPlans, {
                container: '#' + containerId,
                purchaseAmount: purchaseAmount,
                plans: plans
            });

            widgets.render();
        }

        function refreshWidgets() {
            $(".alma-pp-container").each(function () {
                var $widget = $(this).find(".alma-widget-container");
                if (!$widget.length) {
                    return;
                }

                var settings = $widget.data("settings");

                var purchaseAmount = settings.amount;
                if (settings.refreshPrice) {
                    var $price = $(settings.priceQuerySelector);
                    if ($price.length === 0) {
                        throw new Error(
                            'Could not find price element with query selector "' +
                            settings.priceQuerySelector +
                            '"'
                        )
                    }

                    purchaseAmount = $price.text()
                        .replace(settings.thousandSeparator, '')
                        .replace(settings.decimalSeparator, '.')
                        .replace(/[^\d.]/g, '');

                    purchaseAmount = Alma.Utils.priceToCents(purchaseAmount);
                    purchaseAmount *= Number($("#buy_block #quantity_wanted").val());
                }

                initWidget(
                    settings.merchantId,
                    settings.apiMode,
                    $widget.attr("id"),
                    purchaseAmount,
                    settings.plans,
                )
            })
        }

        if (window.prestashop != null && window.prestashop.on != null) {
            prestashop.on("updatedProduct", refreshWidgets);
        } else {
            let $body = $("body");

            function delayedRefresh() {
                setTimeout(refreshWidgets, 1);
            }

            $body.on("change", "#buy_block .attribute_select", delayedRefresh);
            $body.on("change", "#buy_block #quantity_wanted", delayedRefresh);
            $body.on("click", "#buy_block .color_pick", delayedRefresh);
            $body.on("click", "#buy_block .attribute_radio", delayedRefresh);
        }

        refreshWidgets();
        window.__alma_refreshWidgets = refreshWidgets;
    });
})(jQuery)
