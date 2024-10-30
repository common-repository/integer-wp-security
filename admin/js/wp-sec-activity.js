(function ($) {
  "use strict";

  $(function () {
    $("#activity_check_to_block_ip").click(() => {
      var postdata =
        "action=activity_change_options&param=activity_check_to_block_ip";
      post(postdata);
    });
    $("#activity_wrong_login").click(() => {
      var postdata =
        "action=activity_change_options&param=activity_wrong_login";
      post(postdata);
    });
    $("#activity_comment_black_word").click(() => {
      var postdata =
        "action=activity_change_options&param=activity_comment_black_word";
      post(postdata);
    });
    $("#change_time_block_ip").click(() => {
      var block_ip = $("#time_block_ip").val();

      if (block_ip.trim() === null && block_ip.trim() === "") return;

      var postdata =
        "action=activity_change_options&activity_time_block_ip=" +
        block_ip.trim();

      post(postdata);
    });
    $("#change_time_wrong_login").click(() => {
      var wrong_login = $("#time_wrong_login").val();

      if (wrong_login.trim() === null && wrong_login.trim() === "") return;

      var postdata =
        "action=activity_change_options&activity_time_wrong_login=" +
        wrong_login.trim();

      post(postdata);
    });
    $("#activity_blocking_time").change(() => {
      var blocking_time = $("#activity_blocking_time").val();
      console.log(blocking_time);
      if (blocking_time.trim() === null && blocking_time.trim() === "") return;

      var postdata =
        "action=activity_change_options&activity_blocking_time=" +
        blocking_time.trim();

      post(postdata);
    });
    $("#change_time_comment_spam").click(() => {
      var comment_spam_time = $("#time_comment_spam").val();
      
      if (comment_spam_time.trim() === null && comment_spam_time.trim() === "") return;

      var postdata =
        "action=activity_change_options&activity_time_comment_spam=" +
        comment_spam_time.trim();

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
