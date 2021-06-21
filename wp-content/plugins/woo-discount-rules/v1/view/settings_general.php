<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="">
    <br/>
    <h4><?php esc_html_e('General Settings', 'woo-discount-rules'); ?></h4>
    <hr>
</div>
<div class="">
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('License Key :', 'woo-discount-rules'); ?>
            </label>
        </div>
        <div class="col-md-6">
            <?php if($isPro){ ?>
                <input type="text" class="" name="license_key" id="woo-disc-license-key"
                       value="<?php if (isset($data['license_key'])) echo $data['license_key']; ?>"
                       placeholder="<?php esc_attr_e('Your Unique License Key', 'woo-discount-rules'); ?>">
                <input type="button" id="woo-disc-license-check" value="<?php esc_attr_e('Validate Key', 'woo-discount-rules'); ?>" class="button button-info">
                <?php
                $verifiedLicense = get_option('woo_discount_rules_verified_key', 0);
                if (isset($data['license_key']) && $data['license_key'] != '') {
                    if ($verifiedLicense) {
                        ?>
                        <span class="license-success">&#10004;</span>
                        <?php
                    } else {
                        ?>
                        <div class="license-failed notice-message error inline notice-error notice-alt">
                            <?php esc_html_e('License key seems to be Invalid. Please enter a valid license key', 'woo-discount-rules'); ?>
                        </div>
                        <?php
                    }
                }
                ?>
                <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('introduction/license-key-activation', 'license'); ?>
                <br>
                <div id="woo-disc-license-check-msg">

                </div>
                <div class="wdr_desc_text_con">
                <span class="wdr_desc_text">
                    <?php esc_html_e('Tip: Install pro package before validating the licence', 'woo-discount-rules'); ?>
                </span>
                </div>
            <?php } else { ?>
                <?php esc_html_e('Install pro package for validating the licence', 'woo-discount-rules'); ?>
            <?php } ?>

        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Enable dropdowns (applies only for the rule engine in the backend.)', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['enable_bootstrap'] = (isset($data['enable_bootstrap']) ? $data['enable_bootstrap'] : 1); ?>
        <div class="col-md-6">
            <label><input type="radio" name="enable_bootstrap" id="enable_bootstrap_id" data-val="<?php echo $data['enable_bootstrap']; ?>" value="1" <?php echo ($data['enable_bootstrap'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
            <label><input type="radio" name="enable_bootstrap" value="0" <?php echo ($data['enable_bootstrap'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
            <div class="wdr_desc_text_con">
                <span class="wdr_desc_text">
                    <?php esc_html_e('Disabling this setting may affect dropdowns in rule engine (Disabling this setting is not recommended). Change this only if you know what you are doing.', 'woo-discount-rules'); ?>
                </span>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Force refresh the cart widget while add and remove item to cart', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['force_refresh_cart_widget'] = (isset($data['force_refresh_cart_widget']) ? $data['force_refresh_cart_widget'] : 0); ?>
        <div class="col-md-6">
            <label><input type="radio" name="force_refresh_cart_widget" value="1" <?php echo ($data['force_refresh_cart_widget'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
            <label><input type="radio" name="force_refresh_cart_widget" value="0" <?php echo ($data['force_refresh_cart_widget'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Choose how discount rules should work when WooCommerce coupons (or third party) coupons are used?', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['do_not_run_while_have_third_party_coupon'] = (isset($data['do_not_run_while_have_third_party_coupon']) ? $data['do_not_run_while_have_third_party_coupon'] : 0); ?>
        <div class="col-md-6">
            <select class="selectpicker" name="do_not_run_while_have_third_party_coupon" id="do_not_run_while_have_third_party_coupon">
                <option <?php if ($data['do_not_run_while_have_third_party_coupon'] == '0') { ?> selected=selected <?php } ?>
                        value="0"><?php esc_html_e("Let both coupons and discount rules run together", 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['do_not_run_while_have_third_party_coupon'] == '1') { ?> selected=selected <?php } ?>
                        value="1"><?php esc_html_e('Disable the discount rules (coupons will work)', 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['do_not_run_while_have_third_party_coupon'] == 'remove_coupon') { ?> selected=selected <?php } ?>
                        value="remove_coupon"><?php esc_html_e('Disable the coupons (discount rules will work)', 'woo-discount-rules'); ?>
                </option>
            </select>
        </div>
    </div>
    <?php if($isPro){ ?>
        <div class="row form-group">
            <div class="col-md-2">
                <label>
                    <?php esc_html_e('Hide $0.00 (zero value) of coupon codes in the totals column. Useful when a coupon used with discount rule conditions', 'woo-discount-rules'); ?>
                </label>
            </div>
            <?php $data['remove_zero_coupon_price'] = (isset($data['remove_zero_coupon_price']) ? $data['remove_zero_coupon_price'] : 1); ?>
            <div class="col-md-6">
                <label><input type="radio" name="remove_zero_coupon_price" value="1" <?php echo ($data['remove_zero_coupon_price'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
                <label><input type="radio" name="remove_zero_coupon_price" value="0" <?php echo ($data['remove_zero_coupon_price'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
            </div>
        </div>
    <?php } ?>
</div>
