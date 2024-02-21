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
        const subscriptionData = dataSubscriptions

        var checkFunction = setInterval(function() {
            if (typeof getSubscriptionDatafromCms === "function") {
                clearInterval(checkFunction);
                getSubscriptionDatafromCms(subscriptionData);
            }
        }, 700)

        window.addEventListener('message', (e) => {
            if (e.data.type === 'sendCancelSubscriptionToCms') {
                console.log('Cancel subscription', e.data)

                /* TODO : generate url in template */
                $.ajax({
                    type: 'POST',
                    url: '/module/alma/cancellation?action=cancel',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        action: 'cancel',
                        token: $(this).attr('data-token'),
                        subscription_id: $(this).attr('data-subscription-id')
                    },
                })
                .success(function() {
                    //Success
                })
                .error(function(e) {
                    // Error
                });
            }
        })

    })
})(jQuery);
