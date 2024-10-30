(function ($) {
    "use strict";

    $(function () {
        $("#disable_plugin_theme_editor").click(() => {
            var postdata =
                "action=hardening_change_options&param=disable_plugin_theme_editor";
            post(postdata);
        });
        $("#remove_version_wordpress").click(() => {
            var postdata =
                "action=hardening_change_options&param=remove_version_wordpress";
            post(postdata);
        });
        $("#hardening_wp_debug").click(() => {
            var postdata = "action=hardening_change_options&param=hardening_wp_debug";
            post(postdata);
        });
        $("#hardening_disable_directory").click(() => {
            var postdata =
                "action=hardening_change_options&param=hardening_disable_directory";
            post(postdata);
        });
        $("#hardening_author_page").click(() => {
            var postdata =
                "action=hardening_change_options&param=hardening_author_page";
            post(postdata);
        });
        $("#hardening_check_to_block_ip").click(() => {
            var postdata =
                "action=hardening_change_options&param=hardening_check_to_block_ip";
            post(postdata);
        });
        $("#hardening_wrong_login").click(() => {
            var postdata =
                "action=hardening_change_options&param=hardening_wrong_login";
            post(postdata);
        });
        $("#hardening_disable_xmlrpc").click(() => {
            var postdata =
                "action=hardening_change_options&param=hardening_disable_xmlrpc";
            post(postdata);
        });

        $("#fix_permission").click(() => {
            var postdata =
                "action=hardening_change_change_permission_files&param=fix_permission";

            if (
                window.confirm(
                    `We are going to change wordpress file and folder permissions to recommend it as shown in the link, do you want to continue?`
                )
            ) {
                post(postdata);
            }
        });
        $("#change_name").click(() => {
            var prefix = $("#prefix_name").val();
            var old_prefix = $("#old_prefix_name").val();
            if (!prefix || prefix == "") return;
            if (prefix.trim() == old_prefix.trim()) return;

            var postdata =
                "action=hardening_change_prefix_tables&prefix=" + prefix.trim();

            if (
                window.confirm(
                    `The prefix of your tables in the database will be changed to ${prefix.trim()}, do you want to continue?`
                )
            ) {
                post(postdata);
            }
        });
        $("#change_login_url").click(() => {
            var login_url = $("#login_url").val();
            var old_login_url = $("#old_login_url").val();
            if (!login_url || login_url == "") return;
            if (login_url.trim() == old_login_url.trim()) return;

            var postdata =
                "action=hardening_change_login_url&login_url=" + login_url.trim();

            if (
                window.confirm(
                    `The login route of your wordpress project will change to /${login_url.trim()}, do you want to continue?`
                )
            ) {
                post(postdata);
            }
        });
        $("#reset_login_url").click(() => {
            var postdata = "action=hardening_reset_login_url&param=reset_login_url";
            if (
                window.confirm(
                    `Do you want to return the route of your wordpress project to the default route?`
                )
            ) {
                post(postdata);
            }
        });

        function post(postdata) {
            jQuery.post(ajaxurl, postdata, (response) => {
                if (response) {
                    response = JSON.parse(response);

                    if (response.action === "reset_login_url") location.reload();

                    if (response.action === "change_permission_files") {
                        if (response.status === "0") {
                            alert(response.message)
                        } else {
                            location.reload();
                        }
                    }

                    if (response.action === "change_login_url") location.reload();

                    if (response.action === "change_prefix_tables") alert(response.message);
                }

                console.log(response);
            });
        }
    });
})(jQuery);
