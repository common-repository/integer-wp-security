<?php
wp_enqueue_script( 'jquery' );
wp_enqueue_style( 'wp-sec-admin-style', plugin_dir_url( __FILE__ ) . '../css/form-uninstall.css' );
$options = get_option( 'sec_report' );

if ( empty( $options['active'] ) || empty( $options['host_key'] ) ) {
	?>
    <script type="text/javascript">var canCheck = false</script>
	<?php
} else {
	?>
    <script type="text/javascript">var canCheck = true</script>
	<?php
}
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var deactivationLink;
        jQuery('#the-list').find('[data-slug="integer-wp-security"] span.deactivate a').click(function (event) {
            if (!canCheck) {
                return;
            }
            deactivationLink = jQuery(this).attr('href');
            jQuery("#sec_uninstall_feedback_dialog").show();
            event.preventDefault();
        });
        jQuery(".sec-submit-btn").click(function () {
            var selectedVal = jQuery('[name=sec_feedback_key]:checked').val();
            if (!selectedVal) {
                location.href = deactivationLink;
                return;
            }
            var message = '';
            var messageElement = jQuery('#sec_' + selectedVal);
            switch (selectedVal) {
                case 'dont_like_plugin':
                    message = "Don't like this plugin. <br> Reason : ";
                    if (messageElement.length > 0) {
                        message = message + messageElement.val();
                    }
                    break;
                case 'found_one_better':
                    message = messageElement.length > 0 ?
                        'Found one better. <br> Reason : ' + messageElement.val() : 'Found one better';
                    break;
                case 'dont_fix_problem':
                    message = messageElement.length > 0 ?
                        "Don't fix your problem. <br> Reason : " + messageElement.val() : "Don't fix your problem. ";
                    break;
                case 'hard_to_use':
                    message = messageElement.length > 0 ?
                        'Hard to use. <br> Reason : ' + messageElement.val() : 'Hard to use';
                    break;
                case 'other_reason':
                    message = messageElement.length > 0 ?
                        'Other. <br> Reason : ' + messageElement.val() : 'Other :';
                    break;
            }

            var data = {
                'action': 'send_uninstall_feedback',
                'msg': message
            }
            jQuery.post(ajaxurl, data, function (response) {
                location.href = deactivationLink;
            });
        });

        jQuery(".sec-cancel-button, .close_dialog").click(function () {
            jQuery("#sec_uninstall_feedback_dialog").hide();
        });

        // Show hide child parent options
        jQuery('.child-rows').slideUp();

        jQuery('.sec-dialog-input').change(function () {
            jQuery('.child-rows').hide();
            if (jQuery(this).is(':checked')) {
                var child = jQuery(this).data('child-row');
                jQuery('#' + child).slideDown();
            }
        });
    });
</script>
<div id="sec_uninstall_feedback_dialog" class="sec_dialog" style="display:none;">
    <div class="modal-content">
        <div class="modal-header"><h5><?php _e( 'Do you want to uninstall?', 'integer-wp-security' ); ?></h5>
            <button type="button" class="close close_dialog">
                <span>×</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="sec-content-row content-heading">
                Tell me why do you want to uninstall this is plugin?
            </div>
            <div class="sec-row content-row">
                <div class="sec-control">
                    <input data-child-row="sec_feedback_dont_like" id="sec_key_dont_like" class="sec-dialog-input"
                           type="radio" name="sec_feedback_key" value="dont_like_plugin">
                    <label for="sec_key_dont_like" class="sec-dialog-label">Don't like this plugin.</label>
                </div>
            </div>
            <div class="child-rows" id="sec_feedback_dont_like">
                <div class="row">
                    <p class="description"><b>You can talk about why you don't liked this plugin?</b></p>
                    <input class="input-field" type="text" id="sec_dont_like_plugin" name="sec_dont_like_plugin"
                           placeholder="Issue Details">
                </div>
            </div>
            <div class="sec-row content-row">
                <div class="sec-control">
                    <input data-child-row="sec_feedback_one_better" id="sec_key_found_one_better"
                           class="sec-dialog-input" type="radio" name="sec_feedback_key" value="found_one_better">
                    <label for='sec_key_found_one_better' class="sec-dialog-label">Found one better.</label>
                </div>
            </div>
            <div class="child-rows" id="sec_feedback_one_better">
                <div class="row">
                    <p class="description"><b>Can you tell us which plugin and why is it better?</b></p>
                    <input class="input-field" type="text" id="sec_found_one_better" name="sec_found_one_better"
                           placeholder="Issue Details">
                </div>
            </div>
            <div class="sec-row content-row">
                <div class="sec-control">
                    <input data-child-row="sec_feedback_dont_fix_problem" id="sec_key_dont_fix_problem"
                           class="sec-dialog-input" type="radio" name="sec_feedback_key" value="dont_fix_problem">
                    <label for="sec_key_dont_fix_problem" class="sec-dialog-label">Don't fix your problem.</label>
                </div>
            </div>
            <div class="child-rows" id="sec_feedback_dont_fix_problem">
                <div class="row">
                    <p class="description"><b>What problem were you trying to solve?</b></p>
                    <input class="input-field" type="text" id="sec_dont_fix_problem" name="sec_dont_fix_problem"
                           placeholder="Issue Details">
                </div>
            </div>
            <div class="sec-row content-row">
                <div class="sec-control">
                    <input data-child-row="sec_feedback_hard_use" id="sec_key_hard_use" class="sec-dialog-input"
                           type="radio" name="sec_feedback_key" value="hard_to_use">
                    <label for="sec_key_hard_use" class="sec-dialog-label">Hard to use</label>
                </div>
            </div>
            <div class="child-rows" id="sec_feedback_hard_use">
                <div class="row">
                    <p class="description"><b>What difficulties did you have during use?</b></p>
                    <input class="input-field" type="text" id="sec_hard_to_use" name="sec_hard_to_use"
                           placeholder="Issue Details">
                </div>
            </div>
            <div class="sec-row content-row">
                <div class="sec-control">
                    <input data-child-row="sec_feedback_other" id="sec_key_other" class="sec-dialog-input" type="radio"
                           name="sec_feedback_key" value="other_reason">
                    <label for="sec_key_other" class="sec-dialog-label">Others</label>
                </div>
            </div>
            <div class="child-rows" id="sec_feedback_other">
                <div class="row">
                    <p class="description"><b>Why do you no longer want to use our plugin?</b></p>
                    <input class="input-field" type="text" id="sec_other_reason" name="sec_other_reason"
                           placeholder="Issue Details">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="sec-modal-button-wrap">
                <button type="button" class="sec-cancel-button button button-danger" role="button">
                    <span class="sec-modal-button-text"><?php esc_html_e( 'Cancel', 'integer-wp-security' ); ?></span>
                </button>
                <button type="button" class="sec-submit-btn button button-primary" role="button">
                    <span class="sec-modal-button-text"><?php esc_html_e( 'Submit', 'integer-wp-security' ); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>
