    <?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    ?>
    <br>

    <div id="wpbody-content" class="awdr-container">
        <?php
        do_action('advanced_woo_discount_rules_on_settings_header');
        ?>
        <div class="awdr-configuration-form">
            <form name="configuration_form" id="configuration-form" method="post">

                <h1><?php _e('General', 'woo-discount-rules') ?></h1>
                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                    <?php
                    do_action('advanced_woo_discount_rules_before_general_settings_fields', $configuration);
                    ?>
                    <tr>
                        <td scope="row">
                            <label for="calculate_discount_from" class="awdr-left-align"><?php _e('Calculate discount from', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('sale price or regular price', 'woo-discount-rules'); ?> - <a href="https://docs.flycart.org/en/articles/4167066-discount-based-on-regular-price-sale-price-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=sale_regular_price_settings" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></span>
                        </td>
                        <td>
                            <select name="calculate_discount_from">
                                <option value="sale_price" <?php echo ($configuration->getConfig('calculate_discount_from', 'sale_price') == 'sale_price') ? 'selected' : ''; ?>><?php _e('Sale price', 'woo-discount-rules'); ?></option>
                                <option value="regular_price" <?php echo ($configuration->getConfig('calculate_discount_from', 'sale_price') == 'regular_price') ? 'selected' : ''; ?> ><?php _e('Regular price', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="apply_product_discount_to" class="awdr-left-align"><?php _e('Apply discount', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Highest/Lowest/First/All matched rules', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <select name="apply_product_discount_to" class="apply_product_and_cart_discount_to" data-subsequent="apply_product_discount_subsequently_row">
                                <option value="biggest_discount" <?php echo ($configuration->getConfig('apply_product_discount_to', 'biggest_discount') == 'biggest_discount') ? 'selected' : ''; ?>><?php _e('Biggest one from matched rules', 'woo-discount-rules'); ?></option>
                                <option value="lowest_discount" <?php echo ($configuration->getConfig('apply_product_discount_to', 'biggest_discount') == 'lowest_discount') ? 'selected' : ''; ?>><?php _e('Lowest one from matched rules', 'woo-discount-rules'); ?></option>
                                <option value="first" <?php echo ($configuration->getConfig('apply_product_discount_to', 'biggest_discount') == 'first') ? 'selected' : ''; ?> ><?php _e('First matched rules', 'woo-discount-rules'); ?></option>
                                <option value="all" <?php echo ($configuration->getConfig('apply_product_discount_to', 'biggest_discount') == 'all') ? 'selected' : ''; ?>><?php _e('All matched rules', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="apply_product_discount_subsequently_row" style="<?php echo ($configuration->getConfig('apply_product_discount_to', 'biggest_discount') != 'all') ? 'display:none' : ''; ?>">
                        <td scope="row">
                            <label for="awdr_subsequent_discount" class="awdr-left-align"><?php _e('Apply discount subsequently', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('discounts applied subsequently', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="radio" name="apply_discount_subsequently" id="do_apply_discount_subsequently"
                                   value="1" <?php echo($configuration->getConfig('apply_discount_subsequently', 0) ? 'checked' : '') ?>><label
                                    for="do_apply_discount_subsequently"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                            <input type="radio" name="apply_discount_subsequently"
                                   id="do_not_apply_discount_subsequently"
                                   value="0" <?php echo(!$configuration->getConfig('apply_discount_subsequently', 0) ? 'checked' : '') ?>><label
                                    for="do_not_apply_discount_subsequently"><?php _e('No', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="disable_coupon_when_rule_applied" class="awdr-left-align"><?php _e('Choose how discount rules should work', 'woo-discount-rules') ?> - <a href=" https://docs.flycart.org/en/articles/4178875-choose-how-discount-rules-should-work-when-woocommerce-coupons-are-used-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=choose_how_discount_rules_works_setting" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Choose how discount rules should work when WooCommerce coupons (or third party) coupons are used?', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <?php
                            $disable_coupon_when_rule_applied = $configuration->getConfig('disable_coupon_when_rule_applied', 'run_both');
                            ?>
                            <select name="disable_coupon_when_rule_applied" class="disable_coupon_when_rule_applied">
                                <option value="run_both" <?php echo ($disable_coupon_when_rule_applied == 'run_both') ? 'selected' : ''; ?>><?php _e('Let both coupons and discount rules run together', 'woo-discount-rules'); ?></option>
                                <option value="disable_coupon" <?php echo ($disable_coupon_when_rule_applied == 'disable_coupon') ? 'selected' : ''; ?>><?php _e('Disable the coupons (discount rules will work)', 'woo-discount-rules'); ?></option>
                                <option value="disable_rules" <?php echo ($disable_coupon_when_rule_applied == 'disable_rules') ? 'selected' : ''; ?> ><?php _e('Disable the discount rules (coupons will work)', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label class="awdr-left-align"><?php _e('Refresh order review in checkout', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Useful when you have purchase history/shipping address based discount.', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="radio" name="refresh_order_review" id="refresh_order_review_enable"
                                   value="1" <?php echo($configuration->getConfig('refresh_order_review', 0) ? 'checked' : '') ?>><label
                                    for="refresh_order_review_enable"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                            <input type="radio" name="refresh_order_review"
                                   id="refresh_order_review_disable"
                                   value="0" <?php echo(!$configuration->getConfig('refresh_order_review', 0) ? 'checked' : '') ?>><label
                                    for="refresh_order_review_disable"><?php _e('No', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label class="awdr-left-align"><?php _e('Suppress third party discount plugins', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Suppress third party plugins from modifying the prices. other discount plugins may not works!', 'woo-discount-rules'); ?></span>
                            <span class="wdr_settings_desc_text awdr-clear-both text-warning"><?php esc_attr_e('Change this option only if recommended.', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="radio" name="suppress_other_discount_plugins" id="suppress_other_discount_plugins"
                                   value="1" <?php echo($configuration->getConfig('suppress_other_discount_plugins', 0) ? 'checked' : '') ?>><label
                                    for="suppress_other_discount_plugins"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                            <input type="radio" name="suppress_other_discount_plugins"
                                   id="do_not_suppress_other_discount_plugins"
                                   value="0" <?php echo(!$configuration->getConfig('suppress_other_discount_plugins', 0) ? 'checked' : '') ?>><label
                                    for="do_not_suppress_other_discount_plugins"><?php _e('No', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <?php
                    do_action('advanced_woo_discount_rules_general_settings_fields', $configuration);
                    ?>
                    </tbody>
                </table>

                <h1><?php _e('Product', 'woo-discount-rules') ?></h1>

                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('On-sale badge', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('show on-sale badge', 'woo-discount-rules'); ?> - <a href="https://docs.flycart.org/en/articles/4098969-sale-page-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=show_on_sale_badge_setting" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></span>
                        </td>
                        <td>
                            <?php
                            $show_on_sale_badge = $configuration->getConfig('show_on_sale_badge', 'disabled');
                            ?>
                            <select name="show_on_sale_badge" class="on_sale_badge_condition">
                                <option value="when_condition_matches" <?php echo ($show_on_sale_badge == 'when_condition_matches') ? 'selected' : ''; ?> ><?php _e('Show only after a rule condition is matched exactly', 'woo-discount-rules'); ?></option>
                                <option value="at_least_has_any_rules" <?php echo ($show_on_sale_badge == 'at_least_has_any_rules') ? 'selected' : ''; ?>><?php _e('Show on products that are covered under any discount rule in the plugin', 'woo-discount-rules'); ?></option>
                                <option value="disabled" <?php echo ($show_on_sale_badge == 'disabled') ? 'selected' : ''; ?>><?php _e('Do not show', 'woo-discount-rules'); ?></option>
                             </select>
                        </td>
                    </tr>
                    <tr class="sale_badge_toggle" style="<?php echo ($show_on_sale_badge == 'disabled')? 'display:none;':''?>">
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Do you want to customize the sale badge?', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Customize the sale badge', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <?php
                            $customize_on_sale_badge = $configuration->getConfig('customize_on_sale_badge', '');
                            $force_override_on_sale_badge = $configuration->getConfig('force_override_on_sale_badge', '');
                            ?>
                            <input type="checkbox" name="customize_on_sale_badge" id="customize_on_sale_badge"
                                   value="1" <?php echo ( $customize_on_sale_badge == 1 ? 'checked' : '') ?>><label
                                    for="customize_on_sale_badge" class="padding10"><?php _e('Yes, I would like to customize the sale badge', 'woo-discount-rules'); ?></label>
                            <br>
                            <input type="checkbox" name="force_override_on_sale_badge" id="force_override_on_sale_badge"
                                   value="1" <?php echo ( $force_override_on_sale_badge == 1 ? 'checked' : '') ?>><label
                                    for="force_override_on_sale_badge" class="padding10"><?php _e('Force override the label for sale badge (useful when your theme has override for sale badge).', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <tr class="sale_badge_customizer" style="<?php echo ($show_on_sale_badge != 'disabled' && $customize_on_sale_badge == 1) ? '':'display:none;'?>">
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Sale badge content', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php _e('You can use HTML inside. <br><b>IMPORTANT NOTE:</b> This customized sale badge will be applicable only for products that are part of the discount rules configured in this plugin <b>Eg:</b><span class="onsale">Sale!</span>', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <textarea name="on_sale_badge_html"
                                      placeholder='<span class="onsale"><?php _e('Sale!', 'woo-discount-rules') ?></span>'
                                      rows="5"
                                      cols="30"><?php echo $configuration->getConfig('on_sale_badge_html', '<span class="onsale">Sale!</span>'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Show discount table ', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show discount table on product page', 'woo-discount-rules'); ?> - <a href="https://docs.flycart.org/en/articles/4230405-all-about-discount-table-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=show_discount_table_setting" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></span>
                        </td>
                        <td>
                            <input type="radio" name="show_bulk_table" id="show_bulk_table_layout" class="bulk_table_layout settings_option_show_hide"
                                   value="1" <?php echo($configuration->getConfig('show_bulk_table', 0) ? 'checked' : '') ?> data-name="hide_table_position"><label
                                    for="show_bulk_table_layout"><?php _e('Yes', 'woo-discount-rules'); ?></label>
                            <input type="radio" name="show_bulk_table" id="dont_show_bulk_table_layout" class="bulk_table_layout settings_option_show_hide"
                                   value="0" <?php echo(!$configuration->getConfig('show_bulk_table', 0) ? 'checked' : '') ?> data-name="hide_table_position"><label
                                    for="dont_show_bulk_table_layout"><?php _e('No', 'woo-discount-rules'); ?></label>
                            <a class="wdr-popup-link" style="<?php echo (!$configuration->getConfig('show_bulk_table', 0)) ? 'display:none' : ''; ?>"><span class="modal-trigger" data-modal="modal-name"><?php _e("Customize Discount Table", 'woo-discount-rules'); ?></a>
                        </td>

                    </tr>
                    <tr class="hide_table_position"
                        style="<?php echo (!$configuration->getConfig('show_bulk_table', 0) ? 'display:none' : ''); ?>">
                        <td scope="row">
                            <label for="position_to_show_bulk_table" class="awdr-left-align"><?php _e('Position to show discount table', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Position to show discount table on product page', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <select name="position_to_show_bulk_table">
                                <option value="woocommerce_before_add_to_cart_form" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_before_add_to_cart_form') ? 'selected' : ''; ?> ><?php _e('Woocommerce before add to cart form', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_product_meta_end" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_product_meta_end') ? 'selected' : ''; ?>><?php _e('Woocommerce product meta end', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_product_meta_start" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_product_meta_start') ? 'selected' : ''; ?>><?php _e('Woocommerce product meta start', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_after_add_to_cart_form" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_after_add_to_cart_form') ? 'selected' : ''; ?>><?php _e('Woocommerce after add to cart form', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_after_single_product" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_after_single_product') ? 'selected' : ''; ?>><?php _e('Woocommerce after single product', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_before_single_product" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_before_single_product') ? 'selected' : ''; ?>><?php _e('Woocommerce before single product', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_after_single_product_summary" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_after_single_product_summary') ? 'selected' : ''; ?>><?php _e('Woocommerce after single product summary', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_before_single_product_summary" <?php echo ($configuration->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form') == 'woocommerce_before_single_product_summary') ? 'selected' : ''; ?>><?php _e('Woocommerce before single product summary', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="position_to_show_discount_bar" class="awdr-left-align"><?php _e('Position to show discount bar', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Position to show discount bar on product page', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <select name="position_to_show_discount_bar">
                                <option value="woocommerce_before_add_to_cart_form" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_before_add_to_cart_form') ? 'selected' : ''; ?> ><?php _e('Woocommerce before add to cart form', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_product_meta_end" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_product_meta_end') ? 'selected' : ''; ?>><?php _e('Woocommerce product meta end', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_product_meta_start" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_product_meta_start') ? 'selected' : ''; ?>><?php _e('Woocommerce product meta start', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_after_add_to_cart_form" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_after_add_to_cart_form') ? 'selected' : ''; ?>><?php _e('Woocommerce after add to cart form', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_after_single_product" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_after_single_product') ? 'selected' : ''; ?>><?php _e('Woocommerce after single product', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_before_single_product" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_before_single_product') ? 'selected' : ''; ?>><?php _e('Woocommerce before single product', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_after_single_product_summary" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_after_single_product_summary') ? 'selected' : ''; ?>><?php _e('Woocommerce after single product summary', 'woo-discount-rules'); ?></option>
                                <option value="woocommerce_before_single_product_summary" <?php echo ($configuration->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form') == 'woocommerce_before_single_product_summary') ? 'selected' : ''; ?>><?php _e('Woocommerce before single product summary', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Show strikeout price', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show product strikeout price on', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="checkbox" name="modify_price_at_shop_page" id="modify_price_at_shop_page"
                                   value="1" <?php echo($configuration->getConfig('modify_price_at_shop_page', 1) ? 'checked' : '') ?>><label
                                    for="modify_price_at_shop_page" class="padding10"><?php _e('On shop page?', 'woo-discount-rules'); ?></label>
                            <input type="checkbox" name="modify_price_at_product_page" id="modify_price_at_product_page"
                                   value="1" <?php echo($configuration->getConfig('modify_price_at_product_page', 1) ? 'checked' : '') ?>><label
                                    for="modify_price_at_product_page" class="padding10"><?php _e('On product page?', 'woo-discount-rules'); ?></label>
                            <input type="checkbox" name="modify_price_at_category_page" id="modify_price_at_category_page"
                                   value="1" <?php echo($configuration->getConfig('modify_price_at_category_page', 1) ? 'checked' : '') ?>><label
                                    for="modify_price_at_category_page" class="padding10"><?php _e('On category page?', 'woo-discount-rules'); ?></label>

                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Show Strikeout when', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show Strikeout when this option is matched', 'woo-discount-rules'); ?> - <a href="https://docs.flycart.org/en/articles/4178894-display-discounted-price-with-strikethrough-on-default-prices-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=show_strike_out_setting" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></span>
                        </td>
                        <td>
                            <select name="show_strikeout_when">
                                <option value="show_when_matched" <?php echo ($configuration->getConfig('show_strikeout_when', 'show_when_matched') == 'show_when_matched') ? 'selected' : ''; ?> ><?php _e('Show when a rule condition is matched', 'woo-discount-rules'); ?></option>
                                <option value="show_after_matched" <?php echo ($configuration->getConfig('show_strikeout_when', 'show_when_matched') == 'show_after_matched') ? 'selected' : ''; ?>><?php _e('Show after a rule condition is matched', 'woo-discount-rules'); ?></option>
                                <option value="show_dynamically" <?php echo ($configuration->getConfig('show_strikeout_when', 'show_when_matched') == 'show_dynamically') ? 'selected' : ''; ?>><?php _e('Shown on quantity update (dynamic)', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <?php
                    do_action('advanced_woo_discount_rules_product_settings_fields', $configuration);
                    ?>

                    </tbody>
                </table>

                <h1><?php _e('Cart', 'woo-discount-rules'); ?></h1>

                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Show strikeout on cart', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show price strikeout on cart', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="radio" name="show_strikeout_on_cart" id="show_strikeout_on_cart"
                                   value="1" <?php echo($configuration->getConfig('show_strikeout_on_cart', 1) ? 'checked' : '') ?>><label
                                    for="show_strikeout_on_cart"><?php _e('Yes', 'woo-discount-rules'); ?></label>

                            <input type="radio" name="show_strikeout_on_cart" id="dont_show_strikeout_on_cart"
                                   value="0" <?php echo(!$configuration->getConfig('show_strikeout_on_cart', 1) ? 'checked' : '') ?>><label
                                    for="dont_show_strikeout_on_cart"><?php _e('No', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Apply cart discount as', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Apply cart discount as fee/coupon', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <select name="apply_cart_discount_as">
                                <option value="fee" <?php echo ($configuration->getConfig('apply_cart_discount_as', 'fee') == 'fee') ? 'selected' : ''; ?> ><?php _e('Fee', 'woo-discount-rules'); ?></option>
                                <option value="coupon" <?php echo ($configuration->getConfig('apply_cart_discount_as', 'fee') == 'coupon') ? 'selected' : ''; ?>><?php _e('Coupon', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Combine all cart discounts', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Combine all cart discounts in single discount label', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="radio" name="combine_all_cart_discounts" id="combine_all_cart_discounts"
                                   data-name="combine_all_cart_discounts"
                                   value="1"
                                   class="settings_option_show_hide" <?php echo($configuration->getConfig('combine_all_cart_discounts', 0) ? 'checked' : '') ?>><label
                                    for="combine_all_cart_discounts"><?php _e('Yes', 'woo-discount-rules'); ?></label>

                            <input type="radio" name="combine_all_cart_discounts" id="dont_combine_all_cart_discounts"
                                   data-name="combine_all_cart_discounts"
                                   value="0"
                                   class="settings_option_show_hide" <?php echo(!$configuration->getConfig('combine_all_cart_discounts', 0) ? 'checked' : '') ?>><label
                                    for="dont_combine_all_cart_discounts"><?php _e('No', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <tr class="combine_all_cart_discounts"
                        style="<?php echo(!$configuration->getConfig('combine_all_cart_discounts', 0) ? 'display:none' : '') ?>">
                        <td scope="row">
                            <label for="discount_label_for_combined_discounts" class="awdr-left-align"><?php _e('Discount label for combined discounts', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Discount label for combined discounts', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="text" name="discount_label_for_combined_discounts"
                                   value="<?php echo $configuration->getConfig('discount_label_for_combined_discounts', 'Cart discount'); ?>">
                        </td>
                    </tr>
                    <?php
                    do_action('advanced_woo_discount_rules_cart_settings_fields', $configuration);
                    ?>
                    </tbody>
                </table>
                <h1><?php _e('Promotion', 'woo-discount-rules'); ?></h1>
                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Condition based promotion', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php _e('Display Condition based promotion messages in cart/product/shop pages<br>If enabled an option to add promotion message will displays on each rule(when promotion condition is added)', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="checkbox" name="show_subtotal_promotion" id="show_subtotal_promotion" class="awdr_show_condition_promotion_message"
                                   value="1" <?php echo($configuration->getConfig('show_subtotal_promotion', 0) ? 'checked' : '') ?>><label
                                    for="show_subtotal_promotion" class="padding10"><?php _e('Subtotal Promotion?', 'woo-discount-rules'); ?></label>
                            <input type="checkbox" name="show_cart_quantity_promotion" id="show_cart_quantity_promotion" class="awdr_show_condition_promotion_message"
                                   value="1" <?php echo($configuration->getConfig('show_cart_quantity_promotion', 0) ? 'checked' : '') ?>><label
                                    for="show_cart_quantity_promotion" class="padding10"><?php _e('Cart Quantity Promotion?', 'woo-discount-rules'); ?></label>
                        </td>

                    </tr>
                    <tr class="awdr_promotion_message_display_pages" <!--style="--><?php /*echo ($configuration->getConfig('show_subtotal_promotion', 0) || $configuration->getConfig('show_cart_quantity_promotion', 0)) ? '' : 'display:none'; */?>">
                        <td scope="row">
                            <label for="show_promo_text" class="awdr-left-align"><?php _e('Condition based promo text', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Condition based promo text (available only for subtotal based discounts) ', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <?php $show_promo_text = $configuration->getConfig('show_promo_text', ''); ?>
                            <select name="show_promo_text[]" multiple class="edit-all-loaded-values" id="show_promo_text" data-placeholder="<?php esc_attr_e("Select the page to display promotion message", 'woo-discount-rules');?>">
                                <option value="shop_page" <?php echo (!empty($show_promo_text) && is_array($show_promo_text) && in_array('shop_page', $show_promo_text)) ? 'selected' : ''; ?>><?php _e('Shop page', 'woo-discount-rules'); ?></option>
                                <option value="product_page" <?php echo (!empty($show_promo_text) && is_array($show_promo_text) && in_array('product_page', $show_promo_text)) ? 'selected' : ''; ?> ><?php _e('Product page', 'woo-discount-rules'); ?></option>
                                <option value="cart_page" <?php echo (!empty($show_promo_text) && is_array($show_promo_text) && in_array('cart_page', $show_promo_text)) ? 'selected' : ''; ?> ><?php _e('Cart page', 'woo-discount-rules'); ?></option>
                                <option value="checkout_page" <?php echo (!empty($show_promo_text) && is_array($show_promo_text) && in_array('checkout_page', $show_promo_text)) ? 'selected' : ''; ?> ><?php _e('Checkout page', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="display_saving_text" class="awdr-left-align"><?php _e('Display you saved text', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Display you saved text when rule applied', 'woo-discount-rules'); ?> - <a href="https://docs.flycart.org/en/articles/4129525-display-you-saved-message-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=display_you_saved_text" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></span>
                        </td>
                        <td>
                            <select name="display_saving_text" class="settings_option_show_hide_on_change">
                                <option value="disabled" <?php echo ($configuration->getConfig('display_saving_text', 'disabled') == 'disabled') ? 'selected' : ''; ?>><?php _e('Disabled', 'woo-discount-rules'); ?></option>
                                <option value="on_each_line_item" <?php echo ($configuration->getConfig('display_saving_text', 'disabled') == 'on_each_line_item') ? 'selected' : ''; ?> ><?php _e('On each line item', 'woo-discount-rules'); ?></option>
                                <option value="after_total" <?php echo ($configuration->getConfig('display_saving_text', 'disabled') == 'after_total') ? 'selected' : ''; ?> ><?php _e('On after total', 'woo-discount-rules'); ?></option>
                                <option value="both_line_item_and_after_total" <?php echo ($configuration->getConfig('display_saving_text', 'disabled') == 'both_line_item_and_after_total') ? 'selected' : ''; ?> ><?php _e('Both in line item and after total', 'woo-discount-rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="display_you_saved_text"
                        style="<?php echo ($configuration->getConfig('display_saving_text', 'disabled') == 'disabled') ? 'display:none' : ''; ?>">
                        <td scope="row">
                            <label for="you_saved_text" class="awdr-left-align"><?php _e('Savings text to show', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('You save text to show when rule applied', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <textarea name="you_saved_text" rows="5"
                                      cols="30"><?php echo $configuration->getConfig('you_saved_text', 'You saved {{total_discount}}'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="" class="awdr-left-align"><?php _e('Show a discount applied message on cart?', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show message in cart page on rule applied', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <input type="radio" name="show_applied_rules_message_on_cart" class="settings_option_show_hide"
                                   id="show_applied_rules_message_on_cart" data-name="hide_alert_message_text"
                                   value="1" <?php echo($configuration->getConfig('show_applied_rules_message_on_cart', 0) ? 'checked' : '') ?>><label
                                    for="show_applied_rules_message_on_cart"><?php _e('Yes', 'woo-discount-rules'); ?></label>

                            <input type="radio" name="show_applied_rules_message_on_cart" class="settings_option_show_hide"
                                   id="dont_show_applied_rules_message_on_cart" data-name="hide_alert_message_text"
                                   value="0" <?php echo(!$configuration->getConfig('show_applied_rules_message_on_cart', 0) ? 'checked' : '') ?>><label
                                    for="dont_show_applied_rules_message_on_cart"><?php _e('No', 'woo-discount-rules'); ?></label>
                        </td>
                    </tr>
                    <tr class="hide_alert_message_text" style="<?php echo (!$configuration->getConfig('show_applied_rules_message_on_cart', 0)) ? 'display:none' : ''; ?>">
                        <td scope="row">
                            <label for="applied_rule_message" class="awdr-left-align"><?php _e('Applied rule message text on cart', 'woo-discount-rules') ?></label>
                            <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Text to show when rule applied', 'woo-discount-rules'); ?></span>
                        </td>
                        <td>
                            <textarea name="applied_rule_message"
                                      rows="5"
                                      cols="30"><?php echo $configuration->getConfig('applied_rule_message', __('Discount <strong>{{title}}</strong> has been applied to your cart.', 'woo-discount-rules')); ?></textarea>
                        </td>
                    </tr>
                    <?php
                    do_action('advanced_woo_discount_rules_promotion_settings_fields', $configuration);
                    ?>
                    </tbody>
                </table>
                <!--<h1><?php /*_e('Banner', 'woo-discount-rules'); */?></h1>
                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                    <?php /*if(!$is_pro) { */?>
                        <tr class="" style="">
                            <td scope="row">
                                <label for="applied_rule_message"
                                       class="awdr-left-align"><?php /*_e('Banner Content', 'woo-discount-rules') */?></label>
                                <span class="wdr_desc_text awdr-clear-both"><?php /*_e('A static banner you that you want to display in your storefront. <br><br> <b>NOTE:</b> It is a static banner. You can use any content or html here.', 'woo-discount-rules'); */?></span>
                            </td>
                            <td>
                                <?php /*_e("Unlock this feature by <a href='https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=unlock_pro' target='_blank'>Upgrading to Pro</a>", 'woo-discount-rules'); */?>
                            </td>
                        </tr>
                        <tr class="" style="">
                            <td scope="row">
                                <label for="applied_rule_message"
                                       class="awdr-left-align"><?php /*_e('Banner Content display position', 'woo-discount-rules') */?></label>
                                <span class="wdr_desc_text awdr-clear-both"><?php /*_e('Choose a display position for the banner in your storefront', 'woo-discount-rules'); */?></span>
                            </td>
                            <td><?php /*_e("Unlock this feature by <a href='https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=unlock_pro' target='_blank'>Upgrading to Pro</a>", 'woo-discount-rules'); */?></td>
                        </tr>
                    <?php /*} */?>

                    <?php
/*                    do_action('advanced_woo_discount_rules_promotion_settings_fields', $configuration);
                    */?>
                    </tbody>
                </table>-->
                <h1><?php _e('On-Sale page', 'woo-discount-rules'); ?> - <a href="https://docs.flycart.org/en/articles/4098969-sale-page-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=on_sale_page_settings" target="_blank"><?php esc_html_e('Read Docs', 'woo-discount-rules'); ?></a></h1>
                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                    <tr>
                        <td scope="row">
                            <?php
                            _e('Select rules for the On Sale Page', 'woo-discount-rules' );
                            ?>
                        </td>
                        <td scope="row">
                            <?php if($is_pro){
                                $awdr_rebuild_on_sale_rules = $configuration->getConfig('awdr_rebuild_on_sale_rules', array()); ?>
                            <div class="awdr_rebuild_on_sale_list_progress">
                            </div>
                            <div class="awdr_rebuild_on_sale_list_con">
                                <div class="wdr-select-filed-hight wdr-search-box">
                                    <select id="awdr_rebuild_on_sale_rules" name="awdr_rebuild_on_sale_rules[]" multiple
                                            class="edit-all-loaded-values"
                                            data-list=""
                                            data-field="autoloaded"
                                            data-placeholder="<?php esc_attr_e("Type the name of the rule to select it", 'woo-discount-rules');?>"
                                            style="">
                                        <option value="all"
                                            <?php if(!empty($awdr_rebuild_on_sale_rules) && is_array($awdr_rebuild_on_sale_rules)){
                                                if(in_array("all", $awdr_rebuild_on_sale_rules)){
                                                    echo ' selected ';
                                                }
                                            } ?>
                                        ><?php esc_attr_e("All active rules", 'woo-discount-rules'); ?></option>
                                        <?php
                                        $rules = \Wdr\App\Controllers\ManageDiscount::$available_rules;
                                        if(!empty($rules) && is_array($rules)){
                                            foreach ($rules as $rule){
                                                if($rule->rule->enabled == 1){
                                                    ?>
                                                    <option value="<?php echo $rule->rule->id; ?>"
                                                    <?php if(!empty($awdr_rebuild_on_sale_rules) && is_array($awdr_rebuild_on_sale_rules)){
                                                        if(in_array($rule->rule->id, $awdr_rebuild_on_sale_rules)){
                                                            echo ' selected ';
                                                        }
                                                    } ?>
                                                    ><?php echo $rule->rule->title; ?></option>
                                                    <?php
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="awdr_rebuild_on_sale_list_notice">
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning" id="awdr_rebuild_on_sale_list" data-awdr_nonce="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_rule_build_index'); ?>"><?php _e('Save and Build Index', 'woo-discount-rules' ); ?></button>
                            <?php } else {
                                _e("Unlock this feature by <a href='https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=unlock_pro' target='_blank'>Upgrading to Pro</a>", 'woo-discount-rules');
                            }?>
                        </td>
                    </tr>
                    <?php if($is_pro){ ?>
                    <tr>
                        <td scope="row" colspan="2">
                            <?php
                            _e('ShortCode to load all products which has discount through Woo Discount Rules', 'woo-discount-rules' );
                            ?>
                            <span id="awdr_shortcode_text">[awdr_sale_items_list]</span>
                            <button type="button" class="btn btn-warning" id="awdr_shortcode_copy_btn"><?php _e('Copy ShortCode', 'woo-discount-rules' ); ?></button>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <h1><?php _e('Third party plugin conflict fixes & options', 'woo-discount-rules'); ?></h1>
                <p><?php _e('Use these advanced options ONLY when you use a third party plugin that interacts with product pricing & discounts and only when you DONT see the discounts applying. Otherwise these options should be left as NO.', 'woo-discount-rules'); ?></p>
                <p style="color:tomato; font-weight: normal;"><?php _e('IMPORTANT: Please consult with our support team by opening a ticket at <a href="https://www.flycart.org/support" target="_blank">https://www.flycart.org/support</a> before you use these options.', 'woo-discount-rules'); ?></p>
                <table class="wdr-general-setting form-table">
                    <tbody style="background-color: #fff;">
                        <tr>
                            <td scope="row">
                                <label for="" class="awdr-left-align"><?php _e('Do you have custom prices set using another plugin or custom code? (Example: A wholesale price or a country specific pricing)', 'woo-discount-rules') ?></label>
                                <span class="wdr_settings_desc_text awdr-clear-both"><?php _e('If you have custom prices for your products like using another plugin and if you do not see the discount NOT applied, enable this option.', 'woo-discount-rules'); ?></span>
                            </td>
                            <td>
                                <input type="radio" name="wdr_override_custom_price" class="settings_option_show_hide"
                                       id="wdr_override_custom_price_1"
                                       value="1" <?php echo($configuration->getConfig('wdr_override_custom_price', 0) ? 'checked' : '') ?>><label
                                        for="wdr_override_custom_price_1"><?php _e('Yes', 'woo-discount-rules'); ?></label>

                                <input type="radio" name="wdr_override_custom_price" class="settings_option_show_hide"
                                       id="wdr_override_custom_price_0"
                                       value="0" <?php echo(!$configuration->getConfig('wdr_override_custom_price', 0) ? 'checked' : '') ?>><label
                                        for="wdr_override_custom_price_0"><?php _e('No', 'woo-discount-rules'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label for="" class="awdr-left-align"><?php _e('Disable re-calculating the cart total on cart page', 'woo-discount-rules') ?></label>
                                <span class="wdr_settings_desc_text awdr-clear-both"><?php _e('This will be helpful if you see the discounts not working. It could be because other plugins might be force re-calculating the totals in cart.', 'woo-discount-rules'); ?></span>
                            </td>
                            <td>
                                <input type="radio" name="disable_recalculate_total" class="settings_option_show_hide"
                                       id="do_disable_recalculate_total_1"
                                       value="1" <?php echo($configuration->getConfig('disable_recalculate_total', 0) ? 'checked' : '') ?>><label
                                        for="do_disable_recalculate_total_1"><?php _e('Yes', 'woo-discount-rules'); ?></label>

                                <input type="radio" name="disable_recalculate_total" class="settings_option_show_hide"
                                       id="do_disable_recalculate_total_0"
                                       value="0" <?php echo(!$configuration->getConfig('disable_recalculate_total', 0) ? 'checked' : '') ?>><label
                                        for="do_disable_recalculate_total_0"><?php _e('No', 'woo-discount-rules'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">
                                <label for="" class="awdr-left-align"><?php _e('Disable re-calculating the total when applying the coupon.', 'woo-discount-rules') ?></label>
                                <span class="wdr_settings_desc_text awdr-clear-both"><?php _e('This will be useful, if you see the discounts being removed after the coupon applies... or the discount does not work after applying a coupon.', 'woo-discount-rules'); ?></span>
                            </td>
                            <td>
                                <input type="radio" name="disable_recalculate_total_when_coupon_apply" class="settings_option_show_hide"
                                       id="disable_recalculate_total_when_coupon_apply_1"
                                       value="1" <?php echo($configuration->getConfig('disable_recalculate_total_when_coupon_apply', 0) ? 'checked' : '') ?>><label
                                        for="disable_recalculate_total_when_coupon_apply_1"><?php _e('Yes', 'woo-discount-rules'); ?></label>

                                <input type="radio" name="disable_recalculate_total_when_coupon_apply" class="settings_option_show_hide"
                                       id="disable_recalculate_total_when_coupon_apply_0"
                                       value="0" <?php echo(!$configuration->getConfig('disable_recalculate_total_when_coupon_apply', 0) ? 'checked' : '') ?>><label
                                        for="disable_recalculate_total_when_coupon_apply_0"><?php _e('No', 'woo-discount-rules'); ?></label>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php
                do_action('advanced_woo_discount_rules_after_settings_fields', $configuration);
                ?>

            <!--Bulk Table Popup start-->

            <div class="modal" id="modal-name">
                <div class="modal-sandbox"></div>
                <div class="modal-box">
                    <div class="modal-header">
                        <div class="close-modal"><span class="wdr-close-modal-box">&#10006;</span></div>
                        <h1 class="wdr-modal-header-title"><?php _e("Customize Discount Table", 'woo-discount-rules'); ?></h1>
                    </div>
                    <div class="modal-body">
                        <p class="awdr-save-green wdr-alert-success" style="display: none;"><?php _e('Settings Saved', 'woo-discount-rules') ?></p>
                        <p class="awdr-error-red wdr-alert-error" style="display: none;"><?php _e('Oops! Something went wrong.', 'woo-discount-rules') ?></p>
                        <p class="wdr-customizer-notes"><b><?php _e('Note:', 'woo-discount-rules') ?></b><?php _e(" This table contains sample content for design purpose.", 'woo-discount-rules'); ?></p>
                        <div style="width: 100%">
                            <div class="wdr-customizer-container">
                                <div class="wdr-customizer-grid">
                                    <div class="wdr_customize_table_settings">
                                        <table class="form-table popup-bulk-table">
                                            <tbody style="background-color: #fff;">

                                                <tr>
                                                    <th scope="row">
                                                        <label for="" class="awdr-left-align"><?php _e('Table Header', 'woo-discount-rules') ?></label>
                                                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show/Hide table header column names', 'woo-discount-rules'); ?></span>
                                                    </th>
                                                    <td >
                                                        <input type="radio" name="table_column_header" id="show_table_header" class="bulk_table_customizer_preview"
                                                               value="1" data-colname="wdr_bulk_table_thead" data-showhide="show" <?php echo($configuration->getConfig('table_column_header', 1) ? 'checked' : '') ?>><label
                                                                for="show_table_header"><?php _e('Show', 'woo-discount-rules'); ?></label>
                                                        <input type="radio" name="table_column_header" id="dont_show_table_header" class="bulk_table_customizer_preview"
                                                               value="0" data-colname="wdr_bulk_table_thead" data-showhide="hide" <?php echo(!$configuration->getConfig('table_column_header', 1) ? 'checked' : '') ?>><label
                                                                for="dont_show_table_header"><?php _e("Don't Show", 'woo-discount-rules'); ?></label>
                                                    </td>
                                                </tr>
                                                <tr class="">
                                                    <th scope="row">
                                                        <label for="" class="awdr-left-align"><?php _e('Title column Name on table', 'woo-discount-rules') ?></label>
                                                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Give name for rule title column', 'woo-discount-rules'); ?></span>
                                                    </th>
                                                    <td class="awdr_table_columns">
                                                        <input type="checkbox" name="table_title_column" value="1" class="bulk_table_customizer_show_hide_column"
                                                               data-colname="popup_table_title_column"
                                                            <?php echo($configuration->getConfig('table_title_column', 1) ? 'checked' : '') ?>>
                                                        <input type="text" style="width: 90% !important;" class="awdr_popup_col_name_text_box awdr_popup_col_title_keyup" data-keyup="title_on_keyup" name="table_title_column_name" value="<?php echo $configuration->getConfig('table_title_column_name', 'Title');?>">
                                                    </td>
                                                </tr>
                                                <tr class="">
                                                    <th scope="row">
                                                        <label for="" class="awdr-left-align"><?php _e('Discount column Name on table', 'woo-discount-rules') ?></label>
                                                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Give name for discount column', 'woo-discount-rules'); ?></span>
                                                    </th>
                                                    <td class="awdr_table_columns">
                                                        <input type="checkbox" name="table_discount_column" value="1" class="bulk_table_customizer_show_hide_column"
                                                               data-colname="popup_table_discount_column"
                                                            <?php echo($configuration->getConfig('table_discount_column', 1) ? 'checked' : '') ?>>
                                                        <input type="text" style="width: 90% !important;" class="awdr_popup_col_name_text_box" data-keyup="discount_on_keyup" name="table_discount_column_name" value="<?php echo $configuration->getConfig('table_discount_column_name', 'Discount');?>">
                                                    </td>
                                                </tr>
                                                <tr class="">
                                                    <th scope="row">
                                                        <label for="" class="awdr-left-align"><?php _e('Range column Name on table', 'woo-discount-rules') ?></label>
                                                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Give name for range column', 'woo-discount-rules'); ?></span>
                                                    </th>
                                                    <td class="awdr_table_columns">
                                                        <input type="checkbox" name="table_range_column" value="1" class="bulk_table_customizer_show_hide_column"
                                                               data-colname="popup_table_range_column"
                                                            <?php echo($configuration->getConfig('table_range_column', 1) ? 'checked' : '') ?>>
                                                        <input type="text" style="width: 90% !important;" class="awdr_popup_col_name_text_box" data-keyup="range_on_keyup" name="table_range_column_name" value="<?php echo $configuration->getConfig('table_range_column_name', 'Range');?>">
                                                    </td>
                                                </tr>
                                                <tr class="">
                                                    <th scope="row">
                                                        <label for="" class="awdr-left-align"><?php _e('Discount column value on table', 'woo-discount-rules') ?></label>
                                                        <span class="wdr_settings_desc_text awdr-clear-both"><?php esc_attr_e('Show Discount value/price in table', 'woo-discount-rules'); ?></span>
                                                    </th>
                                                    <td>
                                                        <p><input type="radio" name="table_discount_column_value" id="show_table_discount_column_value" class="popup_table_discount_column_value"
                                                               value="1" <?php echo($configuration->getConfig('table_discount_column_value', 1) ? 'checked' : '') ?>><label
                                                                for="show_table_discount_column_value"><?php _e('Discount Value', 'woo-discount-rules'); ?></label></p>
                                                        <p><input type="radio" name="table_discount_column_value" id="dont_show_table_discount_column_value" class="popup_table_discount_column_value"
                                                               value="0" <?php echo(!$configuration->getConfig('table_discount_column_value', 1) ? 'checked' : '') ?>><label
                                                                for="dont_show_table_discount_column_value"><?php _e("Discounted Price", 'woo-discount-rules'); ?></label></p>
                                                    </td>
                                                </tr>
                                               <!-- <tr>
                                                    <th scope="row">
                                                        <label for=""><?php /*_e('Color Picker', 'woo-discount-rules') */?></label>
                                                        <span style="float: right" class="wdr-tool-tip"
                                                              title="<?php /*_e("Rule name / title", 'woo-discount-rules'); */?>"> &#63</span>
                                                    </th>
                                                    <td>
                                                        <input type="color" id="colorpicker" name="color" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" value="#bada55">
                                                        <input type="text" name="wdr_color_picker" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" value="#bada55" id="hexcolor">
                                                    </td>
                                                </tr>-->
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="wdr_customize_table" style="background-color: #fff;"><?php
                                            $tbl_title = $configuration->getConfig('customize_bulk_table_title', 0);
                                            $tbl_range = $configuration->getConfig('customize_bulk_table_range', 1);
                                            $tbl_discount = $configuration->getConfig('customize_bulk_table_discount', 2);


                                            $tbl_title_text = $configuration->getConfig('table_title_column_name', 'Title');
                                            $tbl_discount_text = $configuration->getConfig('table_discount_column_name', 'Discount');
                                            $tbl_range_text = $configuration->getConfig('table_range_column_name', 'Range');

                                            $table_sort_by_columns = array(
                                                'tbl_title' => $tbl_title,
                                                'tbl_range' => $tbl_range,
                                                'tbl_discount' => $tbl_discount,
                                            );
                                            asort($table_sort_by_columns);
                                            ?>
                                            <table id="sort_customizable_table" class="wdr_bulk_table_msg sar-table">
                                                <thead class="wdr_bulk_table_thead">
                                                    <tr class="wdr_bulk_table_tr wdr_bulk_table_thead" style="">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {
                                                                ?>
                                                            <th id="customize-bulk-table-title" class="wdr_bulk_table_td popup_table_title_column awdr-dragable"
                                                                style="<?php if(!$configuration->getConfig('table_column_header', 0)){
                                                                    echo 'display:none';
                                                                }else{
                                                                    echo((!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '');
                                                                } ?>"><span class="title_on_keyup"><?php _e($tbl_title_text, 'woo-discount-rules') ?></span>
                                                                </th><?php
                                                            } elseif ($column == "tbl_discount") {
                                                                ?>
                                                            <th id="customize-bulk-table-discount" class="wdr_bulk_table_td popup_table_discount_column awdr-dragable"
                                                                style="<?php if(!$configuration->getConfig('table_column_header', 0)){
                                                                    echo 'display:none';
                                                                }else{
                                                                    echo((!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '');
                                                                } ?>"><span class="discount_on_keyup"><?php _e($tbl_discount_text, 'woo-discount-rules') ?></span>
                                                                </th><?php
                                                            } else {
                                                                ?>
                                                            <th id="customize-bulk-table-range" class="wdr_bulk_table_td popup_table_range_column awdr-dragable"
                                                                style="<?php if(!$configuration->getConfig('table_column_header', 0)){
                                                                    echo 'display:none';
                                                                }else{
                                                                    echo((!$configuration->getConfig('table_range_column', 0)) ? 'display:none' : '');
                                                                }?>"><span class="range_on_keyup"><?php _e($tbl_range_text, 'woo-discount-rules') ?></span></th><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Bulk Rule', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(12);
                                                                    _e(' flat', 'woo-discount-rules'); ?></span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(33); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('1 - 5', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Bulk Rule', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>">
                                                                     14%
                                                                </span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(38.70); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('11 - 15', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Bulk Flat discount', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(10);
                                                                    _e(' flat', 'woo-discount-rules'); ?> </span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(35); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('50 - 60', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Bulk percentage discount', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>">
                                                                    10% </span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(40.50); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('70 - 80', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Bulk % discount', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>">
                                                                    50% </span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(22.50); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('450 - 500', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Bulk flat', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(10);
                                                                    _e(' flat', 'woo-discount-rules'); ?></span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(35); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('600 - 700', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('set percentage discount', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>">
                                                                   10%</span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(40.50); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('5', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('Fixed discount for set', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(20); ?></span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                    echo  \Wdr\App\Helpers\Woocommerce::formatPrice(2); ?></span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('10', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                    <tr class="wdr_bulk_table_tr bulk_table_row">
                                                        <?php foreach ($table_sort_by_columns as $column => $order) {
                                                            if ($column == "tbl_title") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_title popup_table_title_column col_index_1" data-colindex="1"
                                                                style="<?php echo (!$configuration->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                                                <?php _e('set flat discount', 'woo-discount-rules'); ?>
                                                                </td><?php

                                                            } elseif ($column == "tbl_discount") {?>
                                                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  popup_table_discount_column col_index_2" data-colindex="2"
                                                                style="<?php echo (!$configuration->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                                                <span class="wdr_table_discounted_value" style="<?php echo ( !$configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                echo  \Wdr\App\Helpers\Woocommerce::formatPrice(30); ?></span>
                                                                <span class="wdr_table_discounted_price" style="<?php echo ( $configuration->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php
                                                                echo  \Wdr\App\Helpers\Woocommerce::formatPrice(2);?> </span>
                                                                </td><?php
                                                            } else {?>
                                                                <td class="wdr_bulk_table_td wdr_bulk_range popup_table_range_column col_index_3" data-colindex="3"
                                                                    style="<?php echo (!$configuration->getConfig('table_range_column', 0)) ? 'display:none':'';?>"><?php _e('15', 'woo-discount-rules'); ?></td><?php
                                                            }
                                                        }?>
                                                    </tr>
                                                </tbody>
                                            </table>





                                            <p class="advanced_layout_preview"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br />
                            <a class="bulk-table-customized-setting wdr-model-bottom-btn btn btn-primary" style="text-decoration: none">Save</a>
                            <a class="close-modal wdr-model-bottom-btn btn btn-danger" style="text-decoration: none">Close</a>
                        </div>
                </div>
            </div>

                <!--Bulk Table Popup end-->


                <div class="save-configuration">
                    <input type="hidden" class="customizer_save_alert" name="customizer_save_alert" value="">
                    <input type="hidden" name="customize_bulk_table_title" class="customize_bulk_table_title" value="<?php echo $configuration->getConfig('customize_bulk_table_title', 0); ?>">
                    <input type="hidden" name="customize_bulk_table_discount" class="customize_bulk_table_discount" value="<?php echo $configuration->getConfig('customize_bulk_table_discount', 2); ?>">
                    <input type="hidden" name="customize_bulk_table_range" class="customize_bulk_table_range" value="<?php echo $configuration->getConfig('customize_bulk_table_range', 1); ?>">

                    <input type="hidden" name="method" value="save_configuration">
                   <!-- <input type="hidden" class="customize_banner_content" name="customize_banner_content" value="">-->
                    <input type="hidden" name="action" value="wdr_ajax">
                    <input type="hidden" name="awdr_nonce" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_save_configuration'); ?>">
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary save-configuration-submit"
                                             value="Save Changes"></p>
                </div>
            </form>
        </div>
    </div>





