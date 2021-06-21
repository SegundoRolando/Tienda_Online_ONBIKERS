<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$operator = isset($options->operator) ? $options->operator : 'less_than';
$cart_quantity_promotion_from = isset($options->cart_quantity_promotion_from) ? $options->cart_quantity_promotion_from : false;
$cart_quantity_promotion_message = isset($options->cart_quantity_promotion_message) ? $options->cart_quantity_promotion_message : false;
echo ($render_saved_condition == true) ? '' : '<div class="wdr-cart-quantity-promo-messeage-main">';
if($render_saved_condition != true && isset($i)){
    $i = '{i}';
}
?>

    <div class="wdr_cart_quantity_promotion_container" style="display: grid;">
        <label style="padding-bottom: 20px;"><b><?php _e('Promotion Message', 'woo-discount-rules'); ?></b></label>
        <div class="wdr_cart_cart_quantity_promo_from">
            <label class="awdr-left-align wdr_cart_quantity_promo_filed_name" style="padding-right: 5px;"><?php _e('Quantity from', 'woo-discount-rules'); ?></label>
            <input name="conditions[<?php echo (isset($i)) ? $i : '{i}' ?>][options][cart_quantity_promotion_from]"
                   type="text" class="float_only_field awdr-left-align"
                   value="<?php echo ($cart_quantity_promotion_from) ? $cart_quantity_promotion_from : '' ?>"
                   placeholder="<?php _e('0', 'woo-discount-rules');?>"
                   min="0">
            <span class="wdr_desc_text awdr-clear-both"><?php _e('Set a threshold from which you want to start showing promotion message', 'woo-discount-rules'); ?></span>
            <span class="wdr_desc_text awdr-clear-both"><?php _e("<b>Example:</b> Let's say you offer a 10% discount for 5 quantities and above. you may want to set 3 here. So that the customer can see the promo text when his cart quantities reaches 3", 'woo-discount-rules'); ?></span>
        </div>
        <div class="wdr_cart_quantity_promo_msg">
            <p class="wdr_cart_quantity_promo_filed_name"><?php _e('Message', 'woo-discount-rules'); ?></p>
            <textarea
                name="conditions[<?php echo (isset($i)) ? $i : '{i}' ?>][options][cart_quantity_promotion_message]"
                style="height: 60px;"
                placeholder="<?php _e('Buy {{difference_quantity}} more products and get 10% discount', 'woo-discount-rules'); ?>"><?php echo ($cart_quantity_promotion_message) ? $cart_quantity_promotion_message : ''; ?></textarea>
            <span class="wdr_desc_text awdr-clear-both"><?php _e('{{difference_quantity}} -> Difference amount to get discount', 'woo-discount-rules'); ?></span>
            <span class="wdr_desc_text awdr-clear-both"><?php _e('<b>Eg:</b> Buy {{difference_quantity}} more products and get 10% discount', 'woo-discount-rules'); ?></span>
        </div>
    </div><?php
echo ($render_saved_condition == true) ? '' : '</div>'; ?>

