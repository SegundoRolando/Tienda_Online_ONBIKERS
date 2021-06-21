<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="">
    <div class="row form-group">
        <div class="col-md-12">
            <br/>
            <h4><?php esc_html_e('Cart rules settings', 'woo-discount-rules'); ?></h4>
            <hr>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Discount identifier in the backend', 'woo-discount-rules'); ?>
            </label>
        </div>
        <div class="col-md-6">
            <input type="text" class="" name="coupon_name"
                   value="<?php if (isset($data['coupon_name'])) echo $data['coupon_name']; ?>"
                   placeholder="<?php esc_html_e('Discount Coupon Name', 'woo-discount-rules'); ?>">
        </div>
    </div>
    <?php
    if($isPro){
        ?>
        <div class="row form-group customize_coupon_name_option">
            <?php $data['customize_coupon_name_html'] = (isset($data['customize_coupon_name_html']) ? $data['customize_coupon_name_html'] : ''); ?>
            <div class="col-md-2">
                <label>
                    <?php esc_html_e('Coupon name to be used in the cart/checkout in the storefront', 'woo-discount-rules'); ?>
                </label>
            </div>
            <div class="col-md-6">
                <textarea name="customize_coupon_name_html" id="customize_coupon_name_html_textarea" class="customize_coupon_name_html_textarea" placeholder="<?php esc_attr_e('Discounts applied: {rule_name}', 'woo-discount-rules'); ?>" value="<?php echo esc_attr($data['customize_coupon_name_html']); ?>"><?php echo $data['customize_coupon_name_html']; ?></textarea>
                <div class="wdr_desc_text_con">
                    <span class="wdr_desc_text">
                        {rule_name} <?php esc_html_e('- Rule name. If more than one rule applies in cart, then the rule names will be shown separated by comma(,)', 'woo-discount-rules'); ?>
                    </span>
                    <br/>
                    <span class="wdr_desc_text">
                        <?php esc_html_e('Eg: ', 'woo-discount-rules'); echo htmlentities('Discounts applied: {rule_name}'); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php
    } ?>
    <div class="row form-group">
        <?php $data['cart_setup'] = (isset($data['cart_setup']) ? $data['cart_setup'] : 'first'); ?>
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Rule Setup for Cart:', 'woo-discount-rules'); ?>
            </label>
        </div>
        <div class="col-md-6">
            <select class="selectpicker" name="cart_setup">
                <option <?php if ($data['cart_setup'] == 'first') { ?> selected=selected <?php } ?>
                        value="first"><?php esc_html_e('Apply first matched rule', 'woo-discount-rules'); ?>
                </option>
                <option
                        value="all" <?php if (!$pro) { ?> disabled <?php }
                if ($data['cart_setup'] == 'all') { ?> selected=selected <?php } ?>>
                    <?php if (!$pro) { ?>
                        <?php esc_html_e('Apply all matched rules', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                    <?php } else { ?>
                        <?php esc_html_e('Apply all matched rules', 'woo-discount-rules'); ?>
                    <?php } ?>
                </option>
                <option
                        value="biggest" <?php if (!$pro) { ?> disabled <?php }
                if ($data['cart_setup'] == 'biggest') { ?> selected=selected <?php } ?>>
                    <?php if (!$pro) { ?>
                        <?php esc_html_e('Apply biggest discount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                    <?php } else { ?>
                        <?php esc_html_e('Apply biggest discount', 'woo-discount-rules'); ?>
                    <?php } ?>
                </option>
            </select>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Enable free shipping option', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['enable_free_shipping'] = (isset($data['enable_free_shipping']) ? $data['enable_free_shipping'] : "none"); ?>
        <div class="col-md-6">
            <?php
            if(!$isPro){
                esc_html_e('Supported in PRO version', 'woo-discount-rules');
                ?>
                <select name="enable_free_shipping" id="enable_free_shipping" style="display: none">
                    <option value="none"><?php esc_html_e('Disabled', 'woo-discount-rules'); ?></option>
                </select>
                <?php
            } else {
                ?>
                <select class="selectpicker" name="enable_free_shipping" id="enable_free_shipping">
                    <option <?php if ($data['enable_free_shipping'] == "none") { ?> selected=selected <?php } ?>
                            value="none"><?php esc_html_e('Disabled', 'woo-discount-rules'); ?>
                    </option>
                    <option <?php if ($data['enable_free_shipping'] == "free_shipping") { ?> selected=selected <?php } ?>
                            value="free_shipping"><?php esc_html_e('Use Woocommerce free shipping', 'woo-discount-rules'); ?>
                    </option>
                    <option <?php if ($data['enable_free_shipping'] == "woodiscountfree") { ?> selected=selected <?php } ?>
                            value="woodiscountfree"><?php esc_html_e('Use Woo-Discount free shipping', 'woo-discount-rules'); ?>
                    </option>
                </select>
                <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('shipping-based-discounts/free-shipping-cart-based-rule', 'free_shipping');
            }
            ?>
        </div>
    </div>
    <?php
    if($isPro){
        ?>
        <div class="row form-group" id="woodiscount_settings_free_shipping_con">
            <div class="col-md-2">
                <label>
                    <?php esc_html_e('Free shipping text to be displayed', 'woo-discount-rules'); ?>
                </label>
            </div>
            <div class="col-md-6">
                <?php $data['free_shipping_text'] = ((isset($data['free_shipping_text']) && !empty($data['free_shipping_text'])) ? $data['free_shipping_text'] : __( 'Free Shipping', 'woo-discount-rules' )); ?>
                <input type="text" class="" name="free_shipping_text"
                       value="<?php echo $data['free_shipping_text']; ?>"
                       placeholder="<?php esc_html_e('Free Shipping title', 'woo-discount-rules'); ?>">
            </div>
        </div>
    <?php } ?>
    <div class="row form-group" style="display: none"><!-- Hide this because it is not required after v1.4.36 -->
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Draft', 'woo-discount-rules'); ?>
            </label>
        </div>
        <div class="col-md-6">
            <?php
            $checked = 0;
            if (isset($data['show_draft']) && $data['show_draft'] == 1){
                $checked = 1;
            } ?>
            <input type="checkbox" class="" id="show_draft_1" name="show_draft"
                   value="1" <?php if($checked){ echo 'checked'; } ?>> <label class="checkbox_label" for="show_draft_1"><?php esc_html_e('Exclude Draft products in product select box.', 'woo-discount-rules'); ?></label>
        </div>
    </div>
</div>