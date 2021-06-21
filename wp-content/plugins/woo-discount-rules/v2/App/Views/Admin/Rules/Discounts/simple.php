<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$is_pro = \Wdr\App\Helpers\Helper::hasPro();
?>
<div class="wdr_simple_discount">
    <div class="wdr-discount-group" data-index="{i}">
        <div class="wdr-simple-discount-main">
            <div class="wdr-simple-discount-inner">
                <div class="simple_discount_option wdr-select-filed-hight">
                    <select name="product_adjustments[type]" class="product_discount_option  awdr-left-align">
                        <option value="percentage" <?php echo (isset($product_adjustments->type) && $product_adjustments->type == 'percentage') ? 'selected' : ''; ?>><?php _e('Percentage discount', 'woo-discount-rules'); ?></option>
                        <option value="flat" <?php echo (isset($product_adjustments->type) && $product_adjustments->type == 'flat') ? 'selected' : ''; ?>><?php _e('Fixed discount', 'woo-discount-rules'); ?></option>
                        <?php if($is_pro){ ?>
                            <option value="fixed_price" <?php echo (isset($product_adjustments->type) && $product_adjustments->type == 'fixed_price') ? 'selected' : ''; ?>><?php _e('Fixed price per item', 'woo-discount-rules'); ?></option>
                        <?php } else {
                            ?>
                            <option disabled><?php _e('Fixed price per item -PRO-', 'woo-discount-rules'); ?></option>
                        <?php
                        }?>
                    </select>
                    <span class="wdr_desc_text awdr-clear-both"><?php _e('Discount Type', 'woo-discount-rules'); ?></span>
                </div>
                <div class="simple_discount_value wdr-input-filed-hight">
                    <input name="product_adjustments[value]"
                           type="number"
                           class="product_discount_value"
                           value="<?php echo (isset($product_adjustments->value)) ? $product_adjustments->value : ''; ?>"
                           placeholder="0.00" min="0" step="any" style="width: 100%;">
                    <span class="wdr_desc_text"><?php _e('Value', 'woo-discount-rules'); ?></span>
                </div>
            </div><?php
            $is_enabled_rtl = \Wdr\App\Helpers\Woocommerce::isRTLEnable();?>
            <div class="apply_discount_as_cart_section">
                <div class="apply_as_cart_checkbox awdr_rtl_compatible <?php echo (!$is_enabled_rtl) ? 'page__toggle' : ''; ?>">
                    <label class="<?php echo (!$is_enabled_rtl) ? 'toggle' : ''; ?>">
                        <input class="<?php echo (!$is_enabled_rtl) ? 'toggle__input' : ''; ?> apply_fee_coupon_checkbox" type="checkbox"
                               name="product_adjustments[apply_as_cart_rule]" <?php echo (isset($product_adjustments->apply_as_cart_rule) && !empty($product_adjustments->apply_as_cart_rule)) ? 'checked' : '' ?> value="1">
                        <span class="<?php echo (!$is_enabled_rtl) ? 'toggle__label' : ''; ?>"><span
                                    class="<?php echo (!$is_enabled_rtl) ? 'toggle__text toggle_tic' : ''; ?> "><?php _e('Show discount in cart as coupon instead of changing the product price ?', 'woo-discount-rules'); ?></span></span>
                    </label>
                </div>
                <div class="simple_discount_value wdr-input-filed-hight apply_fee_coupon_label" style="<?php echo (isset($product_adjustments->apply_as_cart_rule) && !empty($product_adjustments->apply_as_cart_rule)) ? '' : 'display: none;' ?> <?php echo ($is_enabled_rtl) ? 'padding-top: 0px !important;' : ''; ?>">
                    <input name="product_adjustments[cart_label]"
                           type="text"
                           value="<?php echo (isset($product_adjustments->cart_label)) ? $product_adjustments->cart_label : ''; ?>"
                           placeholder="<?php esc_attr('Discount Label', 'woo-discount-rules'); ?>">
                </div>
            </div>
        </div>
    </div>
</div>