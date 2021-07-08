/**
 * 2018-2021 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

window.onload = function () {
    $(function () {
        function initWidget(
            merchantId,
            apiMode,
            containerId,
            purchaseAmount,
            plans
        ) {
            var widgets = Alma.Widgets.initialize(merchantId, apiMode);

            widgets.add(Alma.Widgets.PaymentPlans, {
                container: "#" + containerId,
                purchaseAmount: purchaseAmount,
                plans: plans,
            });

            widgets.render();
        }

        function refreshWidgets() {
            var settings = $("#alma-cart-widget").data("settings");

            var purchaseAmount = settings.amount;
            if (settings.refreshPrice) {
                if (settings.psVersion == "1.7") {
                    var $price = $(".cart-total .value");
                }

                purchaseAmount = $price
                    .text()
                    .replace(settings.thousandSeparator, "")
                    .replace(settings.decimalSeparator, ".")
                    .replace(/[^\d.]/g, "");

                purchaseAmount = Alma.Utils.priceToCents(purchaseAmount);
            }

            initWidget(
                settings.merchantId,
                settings.apiMode,
                "alma-cart-widget",
                purchaseAmount,
                settings.plans
            );
        }

        if (window.prestashop != null && window.prestashop.on != null) {
            prestashop.on("updatedCart", refreshWidgets);
        }

        refreshWidgets();
        window.__alma_refreshWidgets = refreshWidgets;
    });
};
