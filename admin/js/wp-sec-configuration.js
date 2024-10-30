(function ($) {
    "use strict";
    $(function () {
        $("#button-scanner").click(() => {

            const report = $("#users_can_register").is(":checked")

            var postdata = "action=execute_analize&report=" + report;
            loading($("#button-scan"));
            post(postdata);
        });

        function post(postdata) {
            jQuery.post(ajaxurl, postdata, (response) => {
                if (response) {
                    response = JSON.parse(response);
                    response_erros(response);
                } else {
                }
            });
        }

        function loading(id) {
            id.empty();
            id.append(`<label>Loading...</label>`);
        }

        function response_erros(response) {
            $("#config_header").empty();
            $("#config_content").empty();
            $("#config_footer").empty();

            var hardening_options = "";

            response.data.hardening_options.forEach((option) => {
                hardening_options += ", " + option;
            });

            var header = '<div style="display: flex; justify-content: space-between;  align-items: center;" class="background-dark">' +
                '<div style="height: 50px; padding: 12px; display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">' +
                `<div><img src="${configuration_scanner_ajax.plugins_url}/admin/images/imagotype_grey_and_white_shield.png" style="padding: 8px; height: 34px;"></div>` +
                '<div class="medium-size bold">WordPress Security</div>' +
                '</div>' +
                `<img src="${configuration_scanner_ajax.plugins_url}/admin/images/logo-horizontal_white.png" height="24px" style="padding: 52px; float: right;" />`
            '</div>';

            var body = '<div style="margin-top: 1px;  display: flex; justify-content: space-around; width: 100%;" class="background-dark">' +
                `<img src="${configuration_scanner_ajax.plugins_url}/admin/images/imagotype_grey_and_white_shield.png" style="padding: 36px; height: 130px;">` +
                '<div style="width: 100%; padding-top: 36px;">' +
                '<div>' +
                `<div class="medium-size">WordPress Status <span class="font-orange">${response.data.created_at}</span></div>` +
                '<div class="big-size padding-12 padding-bottom-off padding-left-off">Its good, almost perfect.</div>' +
                '<div class="medium-size padding-12 padding-left-off"><span class="font-orange">35 items were scaned</span> on your WordPress Website.</div>' +
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
                '<div class="padding-12 padding-left-off" id="button-scan">' +
                '<input class="button_dashboard fix_now" type="button" id="button-scanner" name="scanner" value="Fix Now" />' +
                // '<button name="fix_now" id="button-fixed" class="button_dashboard fix_now">Fix Now</button>'+
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

            var footer = '<div style="padding: 20px 180px;" class="small-size background-dark">' +
                '<div class="font-orange">Did you know?</div>' +
                '<div>We can also offer you some extra protection, for exemple add reCAPTCHA or double authentication at administrative login, disable theme editing, hide the version of wordpress, disable WP_DEBUG avoiding to show the errors, disable access to WordPress directories by the clients browser and remove the author page on the blog.</div>' +
                '</div>';

            $("#config_header").append(header);
            $("#config_content").append(body);
            $("#config_footer").append(footer);

            $("#button-scanner").click(() => {
                var postdata = "action=config_fixed";
                loading($("#button-scan"));

                jQuery.post(ajaxurl, postdata, (response) => {
                    console.log(response);
                    location.reload();
                });
            });
            console.log(response);
        }
    });
})(jQuery);
