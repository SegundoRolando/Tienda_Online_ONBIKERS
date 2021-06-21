<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<br>

<div id="wpbody-content" class="awdr-container">
    <div class="awdr-advanced-configuration-form">
        <form name="awdr_advanced_configuration_form" id="awdr_advanced_configuration_form" method="post">
            <h2><?php _e('Discount Rules Advanced Options <span style="color:tomato; font-weight: normal;"> - Change this option only if recommended.</span>', 'woo-discount-rules') ?></h2>
            <table class="wdr-general-setting form-table">
                <tbody style="background-color: #fff;">
                <tr>
                    <td scope="row" style="width: 30%">
                        <label for="" class="awdr-left-align"><?php _e('Enable when discount not applied (custom price) ', 'woo-discount-rules') ?></label>
                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Apply discount for the products which already have custom price or discount', 'woo-discount-rules'); ?></span>
                    </td>
                    <td>
                        <input type="radio" name="wdr_override_custom_price" id="do_wdr_override_custom_price"
                               value="1" <?php echo($configuration->getConfig('wdr_override_custom_price', 0) ? 'checked' : '') ?>><label
                                for="do_wdr_override_custom_price"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                        <input type="radio" name="wdr_override_custom_price"
                               id="do_not_wdr_override_custom_price"
                               value="0" <?php echo(!$configuration->getConfig('wdr_override_custom_price', 0) ? 'checked' : '') ?>><label
                                for="do_not_wdr_override_custom_price"><?php _e('No', 'woo-discount-rules'); ?></label>
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <label for="" class="awdr-left-align"><?php _e('Disable recalculate total', 'woo-discount-rules') ?></label>
                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Disable recalculate total', 'woo-discount-rules'); ?></span>
                    </td>
                    <td>
                        <input type="radio" name="wdr_disable_recalculate_total" id="do_disable_recalculate_total"
                               value="1" <?php echo($configuration->getConfig('wdr_disable_recalculate_total', 0) ? 'checked' : '') ?>><label
                                for="do_disable_recalculate_total"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                        <input type="radio" name="wdr_disable_recalculate_total"
                               id="do_not_disable_recalculate_total"
                               value="0" <?php echo(!$configuration->getConfig('wdr_disable_recalculate_total', 0) ? 'checked' : '') ?>><label
                                for="do_not_disable_recalculate_total"><?php _e('No', 'woo-discount-rules'); ?></label>
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <label for="" class="awdr-left-align"><?php _e('Disable recalculate when coupon apply', 'woo-discount-rules') ?></label>
                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Disable recalculate when coupon apply', 'woo-discount-rules'); ?> </span>
                    </td>
                    <td>
                        <input type="radio" name="wdr_recalculate_total_when_coupon_apply" id="do_recalculate_total_when_coupon_apply"
                               value="1" <?php echo($configuration->getConfig('wdr_recalculate_total_when_coupon_apply', 0) ? 'checked' : '') ?>><label
                                for="do_recalculate_total_when_coupon_apply"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                        <input type="radio" name="wdr_recalculate_total_when_coupon_apply"
                               id="do_not_recalculate_total_when_coupon_apply"
                               value="0" <?php echo(!$configuration->getConfig('wdr_recalculate_total_when_coupon_apply', 0) ? 'checked' : '') ?>><label
                                for="do_not_recalculate_total_when_coupon_apply"><?php _e('No', 'woo-discount-rules'); ?></label>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="save-configuration">
                <input type="hidden" name="method" value="save_advanced_option">
                <input type="hidden" name="action" value="wdr_ajax">
                <input type="hidden" name="awdr_nonce" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_save_advanced_option_config'); ?>">
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary save-configuration-submit"
                                         value="Save"></p>
            </div>
        </form>
    </div>
</div>