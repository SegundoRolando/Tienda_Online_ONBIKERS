<?php
    if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="wdr-loader"></div>
<div class="wdr">
    <div class="wdr-alert-top-right" id="notify-msg-holder"></div>

    <h2 class="wdr_tabs_container nav-tab-wrapper">
        <?php esc_html_e('Migrate rules from v1 to v2', 'woo-discount-rules'); ?>
    </h2>

    <div class="wdr_settings">
        <div class="wdr_migration_container">
            <button class="button" type="button" id="awdr_do_v1_v2_migration"><?php esc_html_e('Migrate', 'woo-discount-rules'); ?></button>
            <button class="button" type="button" id="awdr_skip_v1_v2_migration"><?php esc_html_e('Skip', 'woo-discount-rules'); ?></button>
            <div class="wdr_migration_process">
            </div>
            <div class="wdr_migration_process_status">
            </div>
        </div>
    </div>
</div>