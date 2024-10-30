(function ($) {
  "use strict";

  $(function () {
    $("#active").click(() => {
      var postdata = "action=gdpr_change_options&param=active";
      post(postdata);
    });

    function post(postdata) {
      jQuery.post(ajaxurl, postdata, (response) => {
        
        if (response) {
          response = JSON.parse(response);
        }

        console.log(response);
      });
    }
  });
})(jQuery);
