(function ($) {
    "use strict";

    $(function () {
        $("#all_email_notification").click(() => {
            var postdata =
                "action=email_notifications_change_options&param=all_email_notification";
            post(postdata);
        });

        function post(postdata) {
            jQuery.post(ajaxurl, postdata, (response) => {
                if (response) {
                    response = JSON.parse(response);

                    console.log(response);
                }
            });
        }
    });
})(jQuery);
