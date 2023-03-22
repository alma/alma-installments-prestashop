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
almaPay = function (paymentData, paymentOptionId) {
    const inPage = new Alma.InPage.initialize(paymentData.id, {
        environment: $('#alma-inpage-' + paymentOptionId).data("apimode"),
    });
    inPage.mount('#alma-inpage-' + paymentOptionId);
    $("html, body").animate(
        {
            scrollTop: $('#alma-inpage-' + paymentOptionId).offset().top,
        },
        4500
    );
    $('#payment-' + paymentOptionId + '-form').submit(function (e) {
        e.preventDefault();
        inPage.startPayment();
    });
    $('.custom-radio .ps-shown-by-js').on('change', function () {
        $('.custom-checkbox .ps-shown-by-js').prop('checked', false);
        inPage.unmount();
    });
};

processAlmaPayment = function (url, paymentOptionId) {
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

isAlmaPayment = function (url) {
    return url.indexOf("module/alma/payment") !== -1 || url.indexOf("module=alma") !== -1;
};

almaInPageOnload = function() {
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
}
window.onload = function () {
    almaInPageOnload();
};
