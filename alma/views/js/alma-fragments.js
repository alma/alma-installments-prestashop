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
    $("html, body").animate(
        {
            scrollTop: $('#alma-inpage-' + paymentOptionId).offset().top,
        },
        4500
    );
    // Prestashop 1.7+
    if ($('#pay-with-' + paymentOptionId + '-form').length !== 0) {
        $('#pay-with-' + paymentOptionId + '-form form').submit(function (e) {
            e.preventDefault();
            inPage.startPayment();
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
    if ($(".alma-inpage").length !== 0) {
        // PS 1.7 : paymentOptions
        $(".ps-shown-by-js").click(function () {
            let paymentOptionId = $(this).attr('id');
            $('#' + paymentOptionId + '-additional-information .alma-inpage').attr('id', 'alma-inpage-' + paymentOptionId);
            let blockIframeInPage = $('#alma-inpage-' + paymentOptionId);
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
