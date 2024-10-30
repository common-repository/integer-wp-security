(function ($) {
    "use strict";

    $(function () {
        $("h2.nav-tab-wrapper").on("click", ".nav-tab", function (event) {
            const el = document.getElementsByClassName("nav-tab");
            for (let index = 0; index < el.length; index++) {
                const element = el[index];
                if (element.classList.contains("nav-tab-active")) {
                    element.classList.remove("nav-tab-active");
                }
            }
            event.target.classList.add("nav-tab-active");
        });

        function post(postdata) {
            jQuery.post(ajaxurl, postdata, (response) => {
                if (response) {
                    response = JSON.parse(response);
                    show_dashboard(response);
                } else {
                }
            });
        }

        function loading() {
            $("#config_content").empty();
            $("#config_content").append(`<label>Loading...</label>`);
        }

        function show_dashboard(response, check_now = false) {
            $("#config_header").empty();
            $("#config_content").empty();
            $("#config_footer").empty();

            var totalSum = Number(response.data.easy_users) + Number(response.data.easy_password) + Number(response.data.files_permission) + Number(response.data.tables_without_prefix);
            var textValue = '';

            if (totalSum == 0) {
                textValue = 'Its perfect!!'
            }
            if (totalSum > 0 && totalSum <= 4) {
                textValue = 'Its good, almost perfect.'
            }

            if (totalSum > 4 && totalSum <= 100) {
                textValue = 'Its ok, we can help you.'
            }

            if (totalSum > 100 && totalSum <= 1000) {
                textValue = 'It is bad,we can help you.'
            }

            if (totalSum > 1000) {
                textValue = 'It is really bad, we can help you.'
            }

            var buttonScan = '<div id="button-scan">' +
                '<input class="button_dashboard fix_now" type="button" id="button-scanner-dashboard" name="scanner" value="Rescan and fix" />' +
                '</div>';
            var tryAgain = '<div id="button-scan">' +
                '<span class="font-orange medium-size">Scanner and Fixed running a few moments a go.</span> <span class="small-size" style="text-decoration: underline" id="try_again">You want try again?</span>' +
                '</div>';

            var button = !check_now ? buttonScan : tryAgain

            var header = '<div style="display: flex; justify-content: space-between;  align-items: center;" class="background-dark">' +
                '<div style="height: 50px; padding: 8px 30px; font-family: Arial, Helvetica, sans-serif; display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">' +
                `<div><img src="${configuration_scanner_ajax.plugins_url}/admin/images/imagotype_grey_and_white_shield.png" style="padding: 8px; height: 34px;"></div>` +
                '<div class="medium-size bold">WordPress Security</div>' +
                '</div>' +
                `<img src="${configuration_scanner_ajax.plugins_url}/admin/images/logo-horizontal_white.png" height="24px" style="padding: 8px 30px; float: right;" />`
            '</div>';

            var body = '<div style="margin-top: 1px;  display: flex; justify-content: space-around; width: 100%;" class="background-dark">' +
                `<img src="${configuration_scanner_ajax.plugins_url}/admin/images/imagotype_grey_and_white_shield.png" style="padding: 36px; height: 130px;">` +
                '<div style="width: 100%; padding-top: 36px;">' +
                '<div>' +
                `<div class="medium-size">WordPress Status <span class="font-orange">${response.data.created_at}</span></div>` +
                `<div class="big-size padding-12 padding-bottom-off padding-left-off">${textValue}</div>` +
                `<div class="medium-size padding-12 padding-left-off"><span class="font-orange"> ${totalSum} risk items found</span> on your WordPress Website.</div>` +
                `<div class="small-size">- You have ${response.data.easy_users} user with standard name and ${response.data.easy_password} user with easy password.</div>` +
                `<div class="small-size">- You have ${response.data.files_permission} files with wrong permissions.</div>` +
                // `<div class="small-size">- You have ${response.data.corrupted_files} corrupted files.</div>`+
                // `<div class="small-size">- You have ${response.data.suspicious_file} suspicious files.</div>`+
                `<div class="small-size">- You have ${response.data.ip_attacks} ip attacks.</div>` +
                `<div class="small-size">- You have ${response.data.ip_blocks} ip blocks.</div>` +
                `<div class="small-size">- You have ${response.data.tables_without_prefix} tables without the standard wordpress prefixe.</div>` +
                `<div class="small-size">- ${response.data.php_version}</div>` +
                '<div class="small-size">Reported bugs and security issues are fixed and regular poin releases are made.</div>' +
                '<div class="small-size padding-12 padding-left-off">Do you want to correct the problems pointed out above?</div>' +
                '<div class="padding-12 padding-left-off">' +
                button +
                // '<button name="let_me_check" class="button_dashboard let_me_check" id="let_me_check">Let me Check</button>'+
                '</div>' +
                '</div>' +
                '</div>' +
                // '<div  style="width: 100%; align-items: flex-end;  padding-top: 36px;">'+
                //     '<div style="display: flex; justify-content: flex-end; padding: 10px 36px 10px 36px;">'+
                //         '<div style="text-align: end; padding-right: 16px;">'+
                //             '<div class="medium-size bold">Settings</div>'+
                //             '<div class="medium-size font-orange">Scan settings</div>'+
                //         '</div>'+
                //         '<div class="parent">'+
                //             `<img style="width: 40px;" src="${response.data.image_path}/wp-content/plugins/wp-sec/admin/images/icons/settings.svg">`+
                //         '</div>'+
                //     '</div>'+
                //     '<div style="display: flex; justify-content: flex-end; padding: 10px 36px 10px 36px;">'+
                //         '<div style="text-align: end; padding-right: 16px;">'+
                //             '<div class="medium-size bold">Lastest Scans</div>'+
                //             '<div class="medium-size font-orange">No previous scans</div>'+
                //         '</div>'+
                //         '<div>'+
                //             `<img style="width: 40px;" src="${response.data.image_path}/wp-content/plugins/wp-sec/admin/images/icons/return.svg">`+
                //         '</div>'+
                //     '</div>'+
                //     '<div style="display: flex; justify-content: flex-end; padding: 10px 36px 10px 36px;">'+
                //         '<div style="text-align: end; padding-right: 16px;">'+
                //             '<div class="medium-size bold">Schedule Scans</div>'+
                //             '<div class="medium-size font-orange">Schedule automatic scans</div>'+
                //         '</div>'+
                //         '<div>'+
                //             `<img style="width: 40px;" src="${response.data.image_path}/wp-content/plugins/wp-sec/admin/images/icons/calendar.svg">'`
                //         '</div>'+
                //     '</div>'+
                // '</div>'+
                '</div>';

            var footer = '<div style="padding: 20px 180px; margin-bottom: 20px" class="small-size background-dark">' +
                '<div class="font-orange">Did you know?</div>' +
                '<div>We can also offer you some extra protection, for exemple add reCAPTCHA or double authentication at administrative login, disable theme editing, hide the version of wordpress, disable WP_DEBUG avoiding to show the errors, disable access to WordPress directories by the clients browser and remove the author page on the blog.</div>' +
                '</div>';

            $("#config_header").append(header);
            $("#config_content").append(body);
            $("#config_footer").append(footer);

            $("#button-scanner-dashboard").click(() => {
                var postdata = "action=execute_analize";
                console.log(postdata)
                loadingButton($("#button-scan"));
                post(postdata);
            });

            $("#try_again").click(() => {
                $("#button-scan").empty();
                $("#button-scan").append(buttonScan);
            });

            function post(postdata) {
                jQuery.post(ajaxurl, postdata, (response) => {
                    if (response) {
                        response = JSON.parse(response);
                        show_dashboard(response, true);
                    } else {
                    }
                });
            }

            function loadingButton(id) {
                console.log(id)
                id.empty();
                id.append(`<label>Loading...</label>`);
            }
        }

        var post_resume = function () {
            if ($("#show_resume_dashboard").val() === "true") {
                loading();
                post("action=last_scanner");
            }
        };

        window.post_resume = post_resume();
    });

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
})(jQuery);
