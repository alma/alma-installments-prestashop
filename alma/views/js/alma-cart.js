/**
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
 (function ($) {
    $(function () {
        if ($("#alma-widget-config").length) {
            var selectors = JSON.parse($("#alma-widget-config").val());
        }

        function initWidget(
            merchantId,
            apiMode,
            containerId,
            purchaseAmount,
            locale,
            showIfNotEligible,
            plans
        ) {
            callApiMode = Alma.ApiMode.LIVE;
            if (apiMode === 'test') {
                callApiMode = Alma.ApiMode.TEST;
            }
            var widgets = Alma.Widgets.initialize(merchantId, callApiMode);

            widgets.add(Alma.Widgets.PaymentPlans, {
                container: containerId,
                purchaseAmount: purchaseAmount,
                locale: locale,
                hideIfNotEligible: !showIfNotEligible,
                plans: plans,
            });
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

            let position =
                1 == selectors.isCartCustom
                    ? selectors.cartPosition
                    : "#alma-cart-widget";

            initWidget(
                settings.merchantId,
                settings.apiMode,
                position,
                purchaseAmount,
                settings.locale,
                settings.showIfNotEligible,
                settings.plans
            );
        }

        if ($("#alma-widget-config").length) {
            if (window.prestashop != null && window.prestashop.on != null) {
                prestashop.on("updatedCart", refreshWidgets);
            }

            refreshWidgets();
            window.__alma_refreshWidgets = refreshWidgets;
        }

        //Insurance Cart
        let itemsCart = document.querySelectorAll('.cart-items .cart-item');
        itemsCart.forEach((item) => {
            let dataProduct = item.querySelector('.alma-data-product');
            let actionsInsuranceProduct = dataProduct.querySelector('.actions-alma-insurance-product');
            let isAlmaInsuranceProduct = parseInt(dataProduct.dataset.isAlmaInsurance);
            let noInsuranceAssociated = parseInt(dataProduct.dataset.noInsuranceAssociated);
            if (!isAlmaInsuranceProduct && noInsuranceAssociated) {
                actionsInsuranceProduct.style.display = 'block';
                item.append(actionsInsuranceProduct);
                let formQty = item.querySelector('.qty');
                formQty.querySelector('input').readOnly = true;
                formQty.querySelector('.input-group-btn-vertical').remove();
            }
            if (isAlmaInsuranceProduct) {
                item.remove();
            }
        })
    });
})(jQuery);
