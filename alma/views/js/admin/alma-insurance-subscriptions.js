(function ($) {
    const subscriptionData = dataSubscriptions


    $(document).ready(function () {
        var checkFunction = setInterval(function() {
            if (typeof getSubscriptionDatafromCms === "function") {
                clearInterval(checkFunction);
                getSubscriptionDatafromCms(subscriptionData);
            }
        }, 700)
    })


})(jQuery);