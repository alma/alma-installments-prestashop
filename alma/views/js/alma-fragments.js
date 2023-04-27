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
const almaPay = function (paymentData, paymentOptionId) {
    let showPayButton = false;
    // Prestashop 1.6-
    if ($('.alma-inpage-ps16').length !== 0) {
        showPayButton = true;
    }
    const inPage = new Alma.InPage.initialize(paymentData.id, {
        environment: $('#alma-inpage-' + paymentOptionId).data("apimode"),
        onUserCloseModal: () => {
            inPage.unmount();
            $('.ps-shown-by-js').prop('checked', false);
        },
        showPayButton: showPayButton,
    });
    inPage.mount('#alma-inpage-' + paymentOptionId);
    // Prestashop 1.7+
    if ($('#payment-confirmation').length !== 0) {
        $('#payment-confirmation').on('click', function(e) {
            e.preventDefault();
            inPage.startPayment();
            return false;
        });
    }
};

const processAlmaPayment = function (url, paymentOptionId) {
    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        data: {
            ajax: true,
            action: "payment",
        },
    })
    .done(function (paymentData) {
        almaPay(paymentData, paymentOptionId);
    })
    .fail(function () {
        window.location.href = "index.php?controller=order&step=1";
    });
};

const isAlmaPayment = function (url) {
    return url.indexOf("module/alma/payment") !== -1 || url.indexOf("module=alma") !== -1;
};

const almaInPageOnload = function() {
    let loading = "<div class='loadingIndicator'><svg width=\"120\" height=\"134\" viewBox=\"0 0 120 134\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\n" +
        "<path d=\"M83.8164 41.0325C79.1708 22.8241 69.3458 17 59.9939 17C50.642 17 40.8171 22.8241 36.1715 41.0325L16 117H35.8804C39.119 104.311 49.1016 97.2436 59.9939 97.2436C70.8863 97.2436 80.8689 104.324 84.1075 117H104L83.8164 41.0325ZM59.9939 79.5428C53.6623 79.5428 47.925 82.0552 43.6918 86.1283L55.0936 41.9207C56.1853 37.6953 57.7985 36.3503 60.0061 36.3503C62.2136 36.3503 63.8269 37.6953 64.9185 41.9207L76.3082 86.1283C72.075 82.0552 66.3256 79.5428 59.9939 79.5428Z\" fill=\"#FA5022\"/>\n" +
        "</svg></div>";
    if ($(".alma-inpage").length !== 0) {
        // PS 1.7 : paymentOptions
        $(".ps-shown-by-js").click(function () {
            let paymentOptionId = $(this).attr('id');
            $('#' + paymentOptionId + '-additional-information .alma-inpage').attr('id', 'alma-inpage-' + paymentOptionId);
            let blockIframeInPage = $('#alma-inpage-' + paymentOptionId);
            blockIframeInPage.html(loading);
            if (blockIframeInPage.length !==0 && blockIframeInPage.data("isinpageenabled")) {
                let url = $('#pay-with-' + paymentOptionId + '-form form').attr("action");

                if (isAlmaPayment(url)) {
                    processAlmaPayment(url, paymentOptionId);
                }
            }
        });
    }
    if ($('.alma-inpage-ps16').length !== 0) {
        $(".alma-inpage-ps16").click(function (e) {
            let paymentOptionId = $(this).attr('id');
            let blockIframeInPage = $('#alma-inpage-' + paymentOptionId);
            blockIframeInPage.html(loading);
            if (blockIframeInPage.length !== 0 && blockIframeInPage.data("isinpageenabled")) {
                e.preventDefault();
                processAlmaPayment(this.href, paymentOptionId);
            }
        });
    }
}
window.onload = function () {
    almaInPageOnload();
};
