<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="">
    <div class="row form-group">
        <div class="col-md-12">
            <br/>
            <h4><?php esc_html_e('Promotion settings', 'woo-discount-rules'); ?></h4>
            <hr>
        </div>
    </div>
    <div class="row form-group">
        <?php $data['display_you_saved_text'] = (isset($data['display_you_saved_text']) ? $data['display_you_saved_text'] : 'no'); ?>
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Display savings text (for price rules)', 'woo-discount-rules'); ?>
            </label>
        </div>
        <div class="col-md-6">
            <select class="selectpicker" id="display_you_saved_text" name="display_you_saved_text">
                <option <?php if ($data['display_you_saved_text'] == 'no') { ?> selected=selected <?php } ?>
                        value="no"><?php esc_html_e('Disabled', 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['display_you_saved_text'] == 'on_each_line_item') { ?> selected=selected <?php } ?>
                        value="on_each_line_item"><?php esc_html_e('On each line item', 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['display_you_saved_text'] == 'after_total') { ?> selected=selected <?php } ?>
                        value="after_total"><?php esc_html_e('On after total', 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['display_you_saved_text'] == 'both_line_item_and_after_total') { ?> selected=selected <?php } ?>
                        value="both_line_item_and_after_total"><?php esc_html_e('Both in line item and after total', 'woo-discount-rules'); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="row form-group display_you_saved_text_options">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Savings text to show', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['display_you_saved_text_value'] = (isset($data['display_you_saved_text_value']) ? $data['display_you_saved_text_value'] : " You saved: {{total_discount_price}}"); ?>
        <div class="col-md-6">
            <textarea name="display_you_saved_text_value" class="display_you_saved_text_value" value="<?php echo esc_attr($data['display_you_saved_text_value']); ?>"><?php echo $data['display_you_saved_text_value']; ?></textarea>
            <div class="wdr_desc_text_con">
                                            <span class="wdr_desc_text">
                                                <?php esc_html_e('{{total_discount_price}} -> Total discount applied', 'woo-discount-rules'); ?>
                                            </span>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Show a message on applying price rules in cart', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['message_on_apply_price_discount'] = (isset($data['message_on_apply_price_discount']) ? $data['message_on_apply_price_discount'] : "no"); ?>
        <div class="col-md-6">
            <select class="selectpicker" name="message_on_apply_price_discount" id="message_on_apply_price_discount">
                <option <?php if ($data['message_on_apply_price_discount'] == "no") { ?> selected=selected <?php } ?>
                        value="no"><?php esc_html_e('Disabled', 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['message_on_apply_price_discount'] == "yes") { ?> selected=selected <?php } ?>
                        value="yes"><?php esc_html_e('Enable', 'woo-discount-rules'); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="row form-group message_on_apply_price_discount_options">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Message', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['message_on_apply_price_discount_text'] = (isset($data['message_on_apply_price_discount_text']) ? $data['message_on_apply_price_discount_text'] : "Discount <strong>\"{{title}}\"</strong> has been applied to your cart."); ?>
        <div class="col-md-6">
            <textarea name="message_on_apply_price_discount_text" class="message_on_apply_discount_textarea" value="<?php echo esc_attr($data['message_on_apply_price_discount_text']); ?>"><?php echo $data['message_on_apply_price_discount_text']; ?></textarea>
            <div class="wdr_desc_text_con">
                <span class="wdr_desc_text">
                    <?php esc_html_e('{{title}} -> Rule title', 'woo-discount-rules'); ?><br>
                    <?php esc_html_e('{{description}} -> Rule description', 'woo-discount-rules'); ?>
                </span>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Show a message on applying cart rules in cart', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['message_on_apply_cart_discount'] = (isset($data['message_on_apply_cart_discount']) ? $data['message_on_apply_cart_discount'] : "no"); ?>
        <div class="col-md-6">
            <select class="selectpicker" name="message_on_apply_cart_discount" id="message_on_apply_cart_discount">
                <option <?php if ($data['message_on_apply_cart_discount'] == "no") { ?> selected=selected <?php } ?>
                        value="no"><?php esc_html_e('Disabled', 'woo-discount-rules'); ?>
                </option>
                <option <?php if ($data['message_on_apply_cart_discount'] == "yes") { ?> selected=selected <?php } ?>
                        value="yes"><?php esc_html_e('Enable', 'woo-discount-rules'); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="row form-group message_on_apply_cart_discount_options">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Message', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['message_on_apply_cart_discount_text'] = (isset($data['message_on_apply_cart_discount_text']) ? $data['message_on_apply_cart_discount_text'] : "Discount <strong>\"{{title}}\"</strong> has been applied to your cart."); ?>
        <div class="col-md-6">
            <textarea name="message_on_apply_cart_discount_text" class="message_on_apply_discount_textarea" value="<?php echo esc_attr($data['message_on_apply_cart_discount_text']); ?>"><?php echo $data['message_on_apply_cart_discount_text']; ?></textarea>
            <div class="wdr_desc_text_con">
                <span class="wdr_desc_text">
                    <?php esc_html_e('{{title}} -> Rule title', 'woo-discount-rules'); ?><br>
                    <?php esc_html_e('{{description}} -> Rule description', 'woo-discount-rules'); ?>
                </span>
            </div>
        </div>
    </div>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Subtotal based promo text (available only in cart rules -> subtotal based discounts)', 'woo-discount-rules'); ?>
            </label>
        </div>
        <?php $data['show_promotion_messages'] = (isset($data['show_promotion_messages']) ? $data['show_promotion_messages'] : array());
        if(!is_array($data['show_promotion_messages'])) $data['show_promotion_messages'] = array();
        ?>
        <div class="col-md-6">
            <select class="selectpicker" name="show_promotion_messages[]" multiple id="show_promotion_messages" title="<?php esc_html_e('Disabled', 'woo-discount-rules'); ?>">
                <option <?php if (in_array("shop_page", $data['show_promotion_messages'])) { ?> selected=selected <?php } ?>
                        value="shop_page"><?php esc_html_e('Shop page', 'woo-discount-rules'); ?>
                </option>
                <option <?php if (in_array("product_page", $data['show_promotion_messages'])) { ?> selected=selected <?php } ?>
                        value="product_page"><?php esc_html_e('Product page', 'woo-discount-rules'); ?>
                </option>
                <option <?php if (in_array("cart_page", $data['show_promotion_messages'])) { ?> selected=selected <?php } ?>
                        value="cart_page"><?php esc_html_e('Cart page', 'woo-discount-rules'); ?>
                </option>
                <option <?php if (in_array("checkout_page", $data['show_promotion_messages'])) { ?> selected=selected <?php } ?>
                        value="checkout_page"><?php esc_html_e('Checkout page', 'woo-discount-rules'); ?>
                </option>
            </select>
        </div>
    </div>
</div>