<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$is_pro = \Wdr\App\Helpers\Helper::hasPro();
?>
<div class="wdr_cart_discount">
    <div class="wdr-discount-group" data-index="1">
        <div class="wdr-cart-discount-main">
            <div class="wdr-cart-discount-inner" style="padding-bottom: 10px;">
                <div class="cart_discount_option wdr-select-filed-hight">
                    <select class="cart_free_shipping awdr-left-align"
                            name="cart_adjustments[type]">
                        <option value="percentage" <?php echo (!empty($cart_adjustment) && isset($cart_adjustment->type) && $cart_adjustment->type == 'percentage') ? 'selected' : ''; ?>><?php _e('Percentage discount', 'woo-discount-rules'); ?></option>
                        <option value="flat_in_subtotal" <?php echo (!empty($cart_adjustment) && isset($cart_adjustment->type) && $cart_adjustment->type == 'flat_in_subtotal') ? 'selected' : ''; ?>><?php _e('Fixed discount', 'woo-discount-rules'); ?></option>
                        <?php if($is_pro){ ?>
                            <option value="flat" <?php echo (!empty($cart_adjustment) && isset($cart_adjustment->type) && $cart_adjustment->type == 'flat') ? 'selected' : ''; ?>><?php _e('Fixed discount per product', 'woo-discount-rules'); ?></option>
                        <?php } else {
                            ?>
                            <option disabled><?php _e('Fixed discount per product -PRO-', 'woo-discount-rules'); ?></option>
                            <?php
                        }?>
                    </select>
                    <span class="wdr_desc_text awdr-clear"><?php _e('Discount Type', 'woo-discount-rules'); ?></span>
                </div>
                <div class="cart_discount_value wdr-input-filed-hight">
                    <input name="cart_adjustments[value]"
                           type="number"
                           class="awdr_cart_discount_value awdr-left-align"
                           value="<?php echo (isset($cart_adjustment->value)) ? $cart_adjustment->value : ''; ?>"
                           placeholder="0.00" min="0" step="any"
                           style="width: 100%;">
                    <span class="wdr_desc_text awdr-clear"><?php _e('Value', 'woo-discount-rules'); ?></span>
                </div>
                <div class="cart_discount_lable wdr-input-filed-hight">
                    <input name="cart_adjustments[label]"
                           type="text"
                           class="awdr-left-align"
                           value="<?php echo (isset($cart_adjustment->label)) ? $cart_adjustment->label : ''; ?>"
                           placeholder="<?php _e('Discount label', 'woo-discount-rules'); ?>"
                           style="width: 100%;">
                    <span class="wdr_desc_text awdr-clear"><?php _e('Discount Label', 'woo-discount-rules'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>