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
    getInstallmentByUrl = function (sUrl) {
        var sParamsURL = sUrl.split('?'),
            sURLVariables = sParamsURL[1].split('&'),
            sParameterName,
            sKey,
            sInstallment,
            i;
    
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
    
            if (sParameterName[0] === 'key') {
                sKey =  typeof sParameterName[1] === undefined ? false : decodeURIComponent(sParameterName[1]);
            }
        }

        sInstallment = sKey.split('_');
        return sInstallment[1];
    };

    almaPay = function (paymentData) {
        const fragments = new Alma.Fragments($("#almaFragments").data("merchantid"), {
            mode: $("#almaFragments").data("apimode"),
        });

        fragments.createPaymentForm(paymentData).mount("#alma-payment");
        $("html, body").animate(
            {
                scrollTop: $("#alma-payment").offset().top,
            },
            4500
        );
    };

    processAlmaPayment = function (url) {
        $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: {
                ajax: true,
                action: "payment",
            },
        })
            .done(function (data) {
                almaPay(data);
            })
            .fail(function () {
                window.location.href = "index.php?controller=order&step=1";
            });
    };

    if ($("#almaFragments").length != 0) {
        $('input[name="payment-option"]').change(function () {
            $("#alma-payment").remove();
        });

        $(".ps-shown-by-js").click(function () {
            if ($(this).is(":not(:checked)")) {
                $("#alma-payment").remove();
            }
        });

        // PS 1.7 : paymentOptions
        $(".js-payment-option-form form").submit(function (e) {
            url = $(this).attr("action");

            if (
                url.indexOf("module/alma/payment") != -1 ||
                url.indexOf("module=alma") != -1
            ) {
                if (getInstallmentByUrl(url) <= 4 && $("#almaFragments").data("activatefragment")) {
                    e.preventDefault();

                    $("#payment-confirmation").after(
                        '<div id="alma-payment"></div>'
                    );
                    processAlmaPayment(url);
                }
            }
        });
    }
};
