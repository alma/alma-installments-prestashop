/**
 * 2018-2024 Alma SAS
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

window.addEventListener("load", function() {
    const subscriptionData = dataSubscriptions

    function waitForScript()
    {
        if (typeof getSubscriptionDatafromCms !== 'undefined') {
            setTimeout(getSubscriptionDatafromCms(subscriptionData), 650)
        } else {
            setTimeout(waitForScript, 450)
        }
    }
    waitForScript();

    window.addEventListener('message', (e) => {
        if (e.data.type === 'sendCancelSubscriptionToCms') {
            $.ajax({
                type: 'POST',
                url: subscriptionData.cancelUrl,
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'cancel',
                    token: subscriptionData.token,
                    subscription_id: e.data.cmsSubscription.subscriptionId,
                    reason: e.data.reasonContent
                },
            })
            .success(function(result) {
                sendNotificationToIFrame([
                    {subscriptionBrokerId: e.data.cmsSubscription.subscriptionBrokerId, newStatus: result.state},
                ]);
            })
            .error(function(result) {
                console.log('Error', result);
                sendNotificationToIFrame([
                    {subscriptionBrokerId: e.data.cmsSubscription.subscriptionBrokerId, newStatus: result.responseJSON.state},
                ]);
            });
        }
    })
});
