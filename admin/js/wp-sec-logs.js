(function ($) {
    "use strict";

    $(function () {
        $("#login_error").click(() => {
            var postdata = "action=logs_change_options&param=login_error";
            post(postdata);
        });
        $("#new_users").click(() => {
            var postdata = "action=logs_change_options&param=new_users";
            post(postdata);
        });
        $("#plugin_logs").click(() => {
            var postdata = "action=logs_change_options&param=plugin_logs";
            post(postdata);
        });

        function loading() {
            $("#result-logs").empty();
            $("#result-logs").append(
                `<table class="orange-lines"><tr><td class="font-white"><label for="tablecell">Loading...</label></td></tr></table>`
            );
        }

        function next() {
            var current_pages = $("#current_page").val();
            var total_pages = $("#total_pages").val();
            if (parseInt(current_pages) >= parseInt(total_pages)) {
                return;
            }
            var page = parseInt(current_pages) + 1;
            callapi($("#active_tab").val(), page);
        }

        function back() {
            var current_pages = $("#current_page").val();

            if (parseInt(current_pages) == 1) return;

            var page = parseInt(current_pages) - 1;

            if (page <= 0) {
                page = 1;
            }

            callapi($("#active_tab").val(), page);
        }

        function post(postdata) {
            jQuery.post(ajaxurl, postdata, (response) => {
                if (response[response.length - 1] == 0) {
                    response = response.slice(0, -1);
                }

                $("#result-logs").empty();
                $(".tabs-result-logs").empty();

                $(".tabs-result-logs").append(response);

                const el = document.getElementsByClassName("nav-tab-logs");
                if (el && el[0] && el[0].id) {
                    callapi(el[0].id);
                }
            });
        }

        function callapi(tab, page = 1) {
            loading();

            var postdata = `action=logs_login_errors&param=${tab}&page=${page}`;

            jQuery.post(ajaxurl, postdata, (response) => {
                response = JSON.parse(response);

                if (response.data.length === 0) {
                    var content = `<div class="border-orange-lines"><table class="orange-lines"><tr><td class="font-white"><label for="tablecell">No records found</label></td></tr></table></div>`;
                    $("#result-logs").empty();
                    $("#result-logs").append(content);
                    return;
                }

                if (tab === "login") {
                    var files =
                        "<thead><tr><th class='font-orange'>Username login</th><th class='font-orange'>Error</th><th class='font-orange'>Message</th><th class='font-orange'>Created at</th></tr></thead>";
                } else if (tab === "new-users") {
                    var files =
                        "<thead><tr><th class='font-orange'>Id</th><th class='font-orange'>Username</th><th class='font-orange'>Email</th><th class='font-orange'>Created at</th></tr></thead>";
                } else if (tab === "plugin-logs") {
                    var files =
                        "<thead><tr><th class='font-orange'>Plugin name</th><th class='font-orange'>Action</th><th class='font-orange'>Created at</th></tr></thead>";
                } else if (tab === "activity-blocks" || tab === "activity-attacks") {
                    var files =
                        "<thead><tr><th class='font-orange'>IP</th><th class='font-orange'>Type problem</th><th class='font-orange'>Block at</th><th class='font-orange'>Url try access</th></tr></thead>";
                }

                for (var index = 0; index < response.data.length; ++index) {
                    var fourth_value = "";
                    if (response.data[index]["fourth_value"])
                        fourth_value = `<td class="font-white">${response.data[index]["fourth_value"]}</td>`;

                    files += `<tr class="${index % 2 === 0 ? "alternate" : ""}"> \ 
							<td class="font-white"><label for="tablecell">${
                        response.data[index]["first_value"]
                    }</label></td> \ 
							<td class="font-white">${response.data[index]["second_value"] || ""}</td> \ 
							<td class="font-white">${response.data[index]["third_value"]}</td> \ 
							${fourth_value}
						</tr>`;
                }

                var content = `<div class="border-orange-lines"><table class="orange-lines">${files}</table></div>`;

                var totalPages = Math.ceil(response.total / response.per_page);

                content += `<div class="tablenav">
					<input type="hidden" id="active_tab" name="active_tab" value="${tab}" />
					<input type="hidden" id="current_page" name="current_page" value="${
                    response.page
                }" />
					<input type="hidden" id="total_pages" name="total_pages" value="${totalPages}" />
					<div class="tablenav-pages">
						<a class='prev-page disabled' title='Go to previous page' id="back">&lsaquo;&lsaquo;</a>
						<span class="paging-input">
							<span class='current-page'> ${response.page} of ${Math.ceil(
                    response.total / response.per_page
                )}</span>
						</span>
						<a class='next-page' title='Go to next page' id="next">&rsaquo;&rsaquo;</a>
					</div>
				</div>`;

                $("#result-logs").empty();
                $("#result-logs").append(content);

                $("#next").click(function () {
                    next();
                });

                $("#back").click(function () {
                    back();
                });
            });
        }

        // $("h2.tabs-result-logs").on("click", ".nav-tab-logs", function (event) {
        //     const el = document.getElementsByClassName("nav-tab-logs");
        //     for (let index = 0; index < el.length; index++) {
        //         const element = el[index];
        //         if (element.classList.contains("nav-tab-active-blue")) {
        //             element.classList.remove("nav-tab-active-blue");
        //         }
        //     }
        //
        //     event.target.classList.add("nav-tab-active-blue");
        //     callapi(event.target.id);
        // });
        $("#wp-sec-submenu").on("click", ".nav-tab-logs", function (event) {
            const el = document.getElementsByClassName("nav-tab-logs");
            for (let index = 0; index < el.length; index++) {
                const element = el[index];
                if (element.classList.contains("nav-tab-active-blue")) {
                    element.classList.remove("nav-tab-active-blue");
                }
            }

            event.target.classList.add("nav-tab-active-blue");
            callapi(event.target.id);
        });

        const el = document.getElementsByClassName("nav-tab-logs");
        if (el && el[0] && el[0].id) {
            callapi(el[0].id);
        }
    });
})(jQuery);
