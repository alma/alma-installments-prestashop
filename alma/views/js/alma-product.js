(function ($) {
    $(function () {
        // If we're on the a product's page and there's a payment plan container
        if ($("body#product").length > 0 && $(".alma-pp-container").length > 0) {

            function initWidget(merchantId, apiMode, containerId, purchaseAmount, installmentsCounts, minAmount, maxAmount) {
                var widgets = Alma.Widgets.initialize(merchantId, apiMode);

                widgets.create(Alma.Widgets.PaymentPlan, {
                    container: '#' + containerId,
                    purchaseAmount: purchaseAmount,
                    installmentsCount: installmentsCounts,
                    minPurchaseAmount: minAmount,
                    maxPurchaseAmount: maxAmount,

                    templates: {
                        paymentPlan: function() { return "" }
                    }
                });

                widgets.render();
            }

            function refreshWidgets() {
                $(".alma-pp-container").each(function() {
                    var $widget = $(this).find(".alma-widget-container");
                    var purchaseAmount = $widget.data("price");

                    if (Boolean(Number($widget.data("refreshPrice")))) {
                        var $price = $("[itemprop=price]");
                        if ($price.length === 0) {
                            $price = $("#our_price_display");
                        }

                        purchaseAmount = $price.text().replace(",", ".").replace(/[^\d.]/g, "");
                        purchaseAmount = Math.round(Number(purchaseAmount) * 100);
                        purchaseAmount *= Number($("#buy_block #quantity_wanted").val());
                    }

                    initWidget(
                        $widget.data("merchantId"),
                        $widget.data("apiMode"),
                        $widget.attr("id"),
                        purchaseAmount,
                        String($widget.data("installmentsCounts")).split(",").map(function(i) { return Number(i) }),
                        Number($widget.data("minPrice")),
                        Number($widget.data("maxPrice")),
                    )
                })
            }

            if(window.prestashop != null && window.prestashop.on != null) {
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
        }
    });
})(jQuery)
