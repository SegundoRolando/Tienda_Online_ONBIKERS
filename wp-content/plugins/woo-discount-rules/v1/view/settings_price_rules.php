<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div>
    <div class="row form-group">
        <div class="col-md-12">
            <br/>
            <h4><?php esc_html_e('Price rules settings', 'woo-discount-rules'); ?></h4>
            <hr>
        </div>
    </div>
    <div class="tabbable tabs-left">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#wdr_price_rule_setup"><?php esc_html_e('Rule setup', 'woo-discount-rules'); ?></a></li>
            <li><a data-toggle="tab" href="#wdr_price_rule_display"><?php esc_html_e('Display', 'woo-discount-rules'); ?></a></li>
            <li><a data-toggle="tab" href="#wdr_price_rule_offer_table"><?php esc_html_e('Offer table', 'woo-discount-rules'); ?></a></li>
            <li><a data-toggle="tab" href="#wdr_price_rule_sale_badge"><?php esc_html_e('Sale badge', 'woo-discount-rules'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div id="wdr_price_rule_setup" class="tab-pane fade in active">
                <div class="price_rules_s_block_c col-md-12">
                    <div class="row form-group">
                        <?php $data['price_setup'] = (isset($data['price_setup']) ? $data['price_setup'] : 'first'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Rule Setup', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="price_setup">
                                <option <?php if ($data['price_setup'] == 'first') { ?> selected=selected <?php } ?>
                                        value="first" selected="selected"><?php esc_html_e('Apply first matched rule', 'woo-discount-rules'); ?>
                                </option>
                                <option
                                        value="all" <?php if (!$pro) { ?> disabled <?php }
                                if ($data['price_setup'] == 'all') { ?> selected=selected <?php } ?>>
                                    <?php if (!$pro) { ?>
                                        <?php esc_html_e('Apply all matched rules', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                    <?php } else { ?>
                                        <?php esc_html_e('Apply all matched rules', 'woo-discount-rules'); ?>
                                    <?php } ?>
                                </option>
                                <option
                                        value="biggest" <?php if (!$pro) { ?> disabled <?php }
                                if ($data['price_setup'] == 'biggest') { ?> selected=selected <?php } ?>>
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
                                <?php esc_html_e('Apply discount based on', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <?php $data['do_discount_from_regular_price'] = (isset($data['do_discount_from_regular_price']) ? $data['do_discount_from_regular_price'] : 'sale'); ?>
                        <div class="col-md-6">
                            <select class="selectpicker" name="do_discount_from_regular_price" id="do_discount_from_regular_price">
                                <option <?php if ($data['do_discount_from_regular_price'] == 'sale') { ?> selected=selected <?php } ?>
                                        value="sale"><?php esc_html_e('Sale price', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['do_discount_from_regular_price'] == 'regular') { ?> selected=selected <?php } ?>
                                        value="regular"><?php esc_html_e('Regular price', 'woo-discount-rules'); ?>
                                </option>
                            </select>
                            <div class="wdr_desc_text_con">
                                <span class="wdr_desc_text">
                                    <?php esc_html_e('If sale price is not entered in your products, the regular price will be taken', 'woo-discount-rules'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Auto add free product when coupon is applied (For coupon-activated rules)', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <?php $data['add_free_product_on_coupon_applied'] = (isset($data['add_free_product_on_coupon_applied']) ? $data['add_free_product_on_coupon_applied'] : 0); ?>
                        <div class="col-md-6">
                            <label><input type="radio" name="add_free_product_on_coupon_applied" value="1" <?php echo ($data['add_free_product_on_coupon_applied'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
                            <label><input type="radio" name="add_free_product_on_coupon_applied" value="0" <?php echo ($data['add_free_product_on_coupon_applied'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Auto add free product when checkout fields changed (For purchase history rules)', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <?php $data['add_free_product_on_change_checkout_fields'] = (isset($data['add_free_product_on_change_checkout_fields']) ? $data['add_free_product_on_change_checkout_fields'] : 0); ?>
                        <div class="col-md-6">
                            <label><input type="radio" name="add_free_product_on_change_checkout_fields" value="1" <?php echo ($data['add_free_product_on_change_checkout_fields'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
                            <label><input type="radio" name="add_free_product_on_change_checkout_fields" value="0" <?php echo ($data['add_free_product_on_change_checkout_fields'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Include variants when just parent products are chosen in the rules', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <?php $data['include_variants_on_select_parent_product'] = (isset($data['include_variants_on_select_parent_product']) ? $data['include_variants_on_select_parent_product'] : 0); ?>
                        <div class="col-md-6">
                            <label><input type="radio" name="include_variants_on_select_parent_product" value="1" <?php echo ($data['include_variants_on_select_parent_product'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
                            <label><input type="radio" name="include_variants_on_select_parent_product" value="0" <?php echo ($data['include_variants_on_select_parent_product'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="wdr_price_rule_display" class="tab-pane fade">
                <div class="price_rules_s_block_c col-md-12">
                    <div class="row form-group">
                        <?php $data['show_price_discount_on_product_page'] = (isset($data['show_price_discount_on_product_page']) ? $data['show_price_discount_on_product_page'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Show Price discount on product pages :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_price_discount_on_product_page" id="show_price_discount_on_product_page">
                                <option <?php if ($data['show_price_discount_on_product_page'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Show when a rule condition is matched', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_price_discount_on_product_page'] == 'show_after_rule_matches') { ?> selected=selected <?php } ?>
                                        value="show_after_rule_matches"><?php esc_html_e('Show after a rule condition is matched', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_price_discount_on_product_page'] == 'show_on_qty_update') { ?> selected=selected <?php } ?>
                                        value="show_on_qty_update"><?php esc_html_e('Shown on quantity update (dynamic)', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_price_discount_on_product_page'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Don't Show", 'woo-discount-rules'); ?>
                                </option>
                            </select>

                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Show a crossed-out original price along with discounted price at line items in cart', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <?php $data['show_strikeout_in_cart'] = (isset($data['show_strikeout_in_cart']) ? $data['show_strikeout_in_cart'] : 1); ?>
                        <div class="col-md-6">
                            <label><input type="radio" name="show_strikeout_in_cart" value="1" <?php echo ($data['show_strikeout_in_cart'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
                            <label><input type="radio" name="show_strikeout_in_cart" value="0" <?php echo ($data['show_strikeout_in_cart'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="wdr_price_rule_offer_table" class="tab-pane fade">
                <div class="price_rules_s_block_c col-md-12">
                    <div class="row form-group">
                        <?php $data['show_discount_table'] = (isset($data['show_discount_table']) ? $data['show_discount_table'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Discount Table :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_discount_table" id="show_discount_table">
                                <option <?php if ($data['show_discount_table'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Disabled", 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_discount_table'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Default layout', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_discount_table'] == 'advance') { ?> selected=selected <?php } ?>
                                        value="advance"><?php esc_html_e('Advance layout', 'woo-discount-rules'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group discount_table_options discount_table_option_advance">
                        <?php $data['discount_table_placement'] = (isset($data['discount_table_placement']) ? $data['discount_table_placement'] : 'before_cart_form'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Table placement:', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="discount_table_placement">
                                <option <?php if ($data['discount_table_placement'] == 'before_cart_form') { ?> selected=selected <?php } ?>
                                        value="before_cart_form"><?php esc_html_e('Before cart form', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['discount_table_placement'] == 'after_cart_form') { ?> selected=selected <?php } ?>
                                        value="after_cart_form"><?php esc_html_e("After cart form", 'woo-discount-rules'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group discount_table_options">
                        <?php $data['show_discount_table_header'] = (isset($data['show_discount_table_header']) ? $data['show_discount_table_header'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Table header :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_discount_table_header">
                                <option <?php if ($data['show_discount_table_header'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Show', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_discount_table_header'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Don't Show", 'woo-discount-rules'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group discount_table_options">
                        <?php $data['show_discount_title_table'] = (isset($data['show_discount_title_table']) ? $data['show_discount_title_table'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Title column on table :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_discount_title_table">
                                <option <?php if ($data['show_discount_title_table'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Show', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_discount_title_table'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Don't Show", 'woo-discount-rules'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group discount_table_options">
                        <?php $data['show_column_range_table'] = (isset($data['show_column_range_table']) ? $data['show_column_range_table'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Range column on table :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_column_range_table">
                                <option <?php if ($data['show_column_range_table'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Show', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_column_range_table'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Don't Show", 'woo-discount-rules'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group discount_table_options">
                        <?php $data['show_column_discount_table'] = (isset($data['show_column_discount_table']) ? $data['show_column_discount_table'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Discount column on table :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_column_discount_table">
                                <option <?php if ($data['show_column_discount_table'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Show', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_column_discount_table'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Don't Show", 'woo-discount-rules'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="wdr_price_rule_sale_badge" class="tab-pane fade">
                <div class="price_rules_s_block_c col-md-12">
                    <div class="row form-group">
                        <?php $data['show_sale_tag_on_product_page'] = (isset($data['show_sale_tag_on_product_page']) ? $data['show_sale_tag_on_product_page'] : 'show'); ?>
                        <div class="col-md-2">
                            <label>
                                <?php esc_html_e('Show a Sale badge on product pages :', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <select class="selectpicker" name="show_sale_tag_on_product_page">
                                <option <?php if ($data['show_sale_tag_on_product_page'] == 'dont') { ?> selected=selected <?php } ?>
                                        value="dont"><?php esc_html_e("Do not show", 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_sale_tag_on_product_page'] == 'show') { ?> selected=selected <?php } ?>
                                        value="show"><?php esc_html_e('Show only after a rule condition is matched exactly', 'woo-discount-rules'); ?>
                                </option>
                                <option <?php if ($data['show_sale_tag_on_product_page'] == 'show_on_any_rules_matches') { ?> selected=selected <?php } ?>
                                        value="show_on_any_rules_matches"><?php esc_html_e('Show on products that are covered under any price based discount rule in the plugin', 'woo-discount-rules'); ?>
                                </option>
                            </select>

                        </div>
                    </div>
                    <div class="row form-group">
                        <?php $data['customize_sale_tag'] = (isset($data['customize_sale_tag']) ? $data['customize_sale_tag'] : 0); ?>
                        <?php $data['force_customize_sale_tag'] = (isset($data['force_customize_sale_tag']) ? $data['force_customize_sale_tag'] : 0); ?>
                        <div class="col-md-2">
                            <label for="customize_sale_tag">
                                <?php esc_html_e('Do you want to customize the sale badge?', 'woo-discount-rules'); ?>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <?php
                            if($isPro){
                                ?>
                                <input type="checkbox" name="customize_sale_tag" id="customize_sale_tag" value="1" <?php if ($data['customize_sale_tag'] == 1) { ?> checked <?php } ?>> <label for="customize_sale_tag"><?php esc_html_e('Yes, I would like to customize the sale badge', 'woo-discount-rules'); ?></label>
                                <br>
                                <input type="checkbox" name="force_customize_sale_tag" id="force_customize_sale_tag" value="1" <?php if ($data['force_customize_sale_tag'] == 1) { ?> checked <?php } ?>> <label for="force_customize_sale_tag"><?php esc_html_e('Force override the label for sale badge (useful when your theme has override for sale badge).', 'woo-discount-rules'); ?></label>
                                <?php
                            } else {
                                esc_html_e('Supported in PRO version', 'woo-discount-rules');
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    if($isPro){
                        ?>
                        <div class="row form-group customize_sale_tag_option">
                            <?php $data['customize_sale_tag_html'] = (isset($data['customize_sale_tag_html']) ? $data['customize_sale_tag_html'] : '<span class="onsale">Sale!</span>'); ?>
                            <div class="col-md-2">
                                <label>
                                    <?php esc_html_e('Sale badge content (TIP: You can use HTML inside)', 'woo-discount-rules'); ?>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <textarea name="customize_sale_tag_html" id="customize_sale_tag_html_textarea" class="customize_sale_tag_html_textarea" value="<?php echo esc_attr($data['customize_sale_tag_html']); ?>"><?php echo $data['customize_sale_tag_html']; ?></textarea>
                                <div class="wdr_desc_text_con">
                                                <span class="wdr_desc_text">
                                                    <?php esc_html_e('IMPORTANT NOTE: This customized sale badge will be applicable only for products that are part of the discount rules configured in this plugin', 'woo-discount-rules'); ?>
                                                </span>
                                    <br/>
                                    <span class="wdr_desc_text">
                                                    <?php esc_html_e('Eg:', 'woo-discount-rules'); echo htmlentities('<span class="onsale">Sale!</span>'); ?>
                                                </span>
                                </div>
                            </div>
                        </div>
                        <?php
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>