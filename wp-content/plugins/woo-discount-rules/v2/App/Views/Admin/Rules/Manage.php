<?php
    if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div style="overflow:auto">
    <div class="awdr-container"><br/>
        <?php
        if(isset($wdr_404_found) && !empty($wdr_404_found)){
            echo "<h2 style='color: red;'>".$wdr_404_found."</h2>";
        }else{
            $current_time = '';
            if (function_exists('current_time')) {
                $current_time = current_time('timestamp');
            }
            $rule_status = $rule->getRuleVaildStatus();
            $check_rule_limit = $rule->checkRuleUsageLimits();
            $rule_id = $rule->getId();
            if ($rule_status == 'in_future') { ?>
                <div class="notice inline notice notice-warning notice-alt awdr-rule-limit-disabled">
                    <p class="rule_limit_msg_future">
                        <b><?php esc_html_e('This rule is not running currently: ', 'woo-discount-rules'); ?></b><?php esc_html_e(' Start date and time is set in the future date', 'woo-discount-rules'); ?>
                    </p><?php
                    if ($check_rule_limit == 'Disabled') {?>
                        <p class="rule_limit_msg">
                            <b><?php esc_html_e('This rule is not running currently: ', 'woo-discount-rules'); ?></b><?php esc_html_e(' Rule reached maximum usage limit  ', 'woo-discount-rules'); ?>
                        </p><?php
                    } ?>
                </div><?php
            } elseif ($rule_status == 'expired') {
                ?>
                <div class="notice inline notice notice-warning notice-alt awdr-rule-limit-disabled">
                    <p class="rule_limit_msg_expired">
                        <b><?php esc_html_e('This rule is not running currently: ', 'woo-discount-rules'); ?></b><?php esc_html_e(' Validity expired', 'woo-discount-rules'); ?>
                    </p><?php
                    if ($check_rule_limit == 'Disabled') {?>
                        <p class="rule_limit_msg">
                            <b><?php esc_html_e('This rule is not running currently: ', 'woo-discount-rules'); ?></b><?php esc_html_e(' Rule reached maximum usage limit  ', 'woo-discount-rules'); ?>
                        </p><?php
                    } ?>
                </div><?php
            }else{
                if($check_rule_limit == 'Disabled') {?>
                    <div class="notice inline notice notice-warning notice-alt awdr-rule-limit-disabled">
                        <p class="rule_limit_msg">
                            <b><?php esc_html_e('This rule is not running currently: ', 'woo-discount-rules'); ?></b><?php esc_html_e(' Rule reached maximum usage limit  ', 'woo-discount-rules'); ?>
                        </p>
                    </div><?php
                }
            }?>
            <div class="notice inline notice notice-warning notice-alt awdr-rule-limit-disabled-outer" style="display: none; padding: 10px;">
                <p class="rule_limit_msg_outer"></p>
            </div>
                <form id="wdr-save-rule" name="rule_generator">
                <div class="wdr-sticky-header" id="ruleHeader">
                    <div class="wdr-enable-rule">
                        <div class="wdr-field-title" style="width: 45%">
                            <input class="wdr-title" type="text" name="title" placeholder="<?php esc_attr('Rule Title', 'woo-discount-rules'); ?>"
                                   value="<?php echo esc_attr($rule->getTitle()); ?>"><!--awdr-clear-both-->
                        </div><?php
                        $is_rtl_enabled = \Wdr\App\Helpers\Woocommerce::isRTLEnable();
                        if(!$is_rtl_enabled){?>
                            <div class="page__toggle">
                                <label class="toggle">
                                    <input class="toggle__input" type="checkbox"
                                           name="enabled" <?php echo ($rule->isEnabled()) ? 'checked' : '' ?> value="1">
                                    <span class="toggle__label"><span
                                                class="toggle__text"><?php _e('Enable?', 'woo-discount-rules'); ?></span></span>
                                </label>

                            </div>
                            <div class="page__toggle">
                                <label class="toggle">
                                    <input class="toggle__input" type="checkbox"
                                           name="exclusive" <?php echo ($rule->isExclusive()) ? 'checked' : '' ?> value="1">
                                    <span class="toggle__label"><span
                                                class="toggle__text"><?php _e('Apply this rule if matched and ignore all other rules', 'woo-discount-rules'); ?></span></span>
                                </label>

                            </div><?php
                        }else{?>
                            <div class="awdr_normal_enable_check_box">
                                <label>
                                    <input type="checkbox" name="enabled" class="awdr_enable_check_box_html" <?php echo ($rule->isEnabled()) ? 'checked' : '' ?> value="1"><?php _e('Enable?', 'woo-discount-rules'); ?>
                                </label>

                            </div>
                            <div class="awdr_normal_exclusive_check_box">
                                <label>
                                    <input class="awdr_exclusive_check_box_html" type="checkbox"name="exclusive" <?php echo ($rule->isExclusive()) ? 'checked' : '' ?> value="1">
                                    <?php _e('Apply this rule if matched and ignore all other rules', 'woo-discount-rules'); ?>
                                </label>
                            </div><?php
                        }

                        if (isset($rule_id) && !empty($rule_id)) { ?>
                            <span class="wdr_desc_text awdr_valide_date_in_desc">
                            <?php esc_html_e('#Rule ID: ', 'woo-discount-rules'); ?><b><?php echo $rule_id; ?></b>
                            </span><?php
                        } ?>
                        <div class="awdr-common-save">
                            <button type="submit" class="btn btn-primary wdr_save_stay">
                                <?php _e('Save', 'woo-discount-rules'); ?></button>
                            <button type="button" class="btn btn-success wdr_save_close">
                                <?php _e('Save & Close', 'woo-discount-rules'); ?></button>
                            <a href="<?php echo admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules'))); ?>"
                               class="btn btn-danger" style="text-decoration: none">
                                <?php _e('Cancel', 'woo-discount-rules'); ?></a>
                        </div>
                    </div>
                    <div class="awdr_discount_type_section">
                        <?php
                        $wdr_product_discount_types = $base->getDiscountTypes();
                        $rule_discount_type = $rule->getRuleDiscountType();
                        ?>
                        <div class="wdr-discount-type">
                            <b style="display: block;"><?php _e('Choose a discount type', 'woo-discount-rules'); ?></b>
                            <select name="discount_type" class="awdr-product-discount-type wdr-discount-type-selector"
                                    data-placement="wdr-discount-template-placement">
                                <optgroup label="">
                                    <option value="not_selected"><?php _e("Select Discount Type", 'woo-discount-rules'); ?></option>
                                </optgroup><?php
                                if (isset($wdr_product_discount_types) && !empty($wdr_product_discount_types)) {
                                    foreach ($wdr_product_discount_types as $wdr_discount_key => $wdr_discount_value) {
                                        ?>
                                    <optgroup label="<?php echo $wdr_discount_key; ?>">
                                        <?php
                                        foreach ($wdr_discount_value as $key => $value) {
                                            $enable_option = true;
                                            if (isset($value['enable']) && $value['enable'] === false) {
                                                $enable_option = false;
                                            }
                                            ?>
                                            <option
                                            <?php if ($enable_option) {
                                                ?>
                                                value="<?php echo $key; ?>"
                                                <?php
                                            } else {
                                                ?>
                                                disabled="disabled"
                                                <?php
                                            } ?>
                                            <?php echo ($rule_discount_type && $rule_discount_type == $key) ? 'selected' : ''; ?>><?php _e($value['label'], 'woo-discount-rules'); ?></option><?php
                                        } ?>
                                        </optgroup><?php
                                    }
                                } ?>
                            </select>
                            <sub><a href="https://docs.flycart.org/en/articles/3788550-product-adjustment-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=product_adjustment_document" target="_blank" class="awdr_doc_wdr_simple_discount" style="<?php echo ($rule_discount_type != 'wdr_simple_discount') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                            <sub><a href="https://docs.flycart.org/en/articles/3806593-cart-adjustment-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=cart_adjustment_document" target="_blank" class="awdr_doc_wdr_cart_discount" style="<?php echo ($rule_discount_type != 'wdr_cart_discount') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                            <sub><a href="https://docs.flycart.org/en/articles/3807036-free-shipping-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=free_shipping_document" target="_blank" class="awdr_doc_wdr_free_shipping" style="<?php echo ($rule_discount_type != 'wdr_free_shipping') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                            <sub><a href="https://docs.flycart.org/en/articles/3807208-bulk-discounts-or-tiered-pricings-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bulk_adjustment_document" target="_blank" class="awdr_doc_wdr_bulk_discount" style="<?php echo ($rule_discount_type != 'wdr_bulk_discount') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                            <sub><a href="https://docs.flycart.org/en/articles/3809899-bundle-set-discount-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=set_bundle_adjustment_document" target="_blank" class="awdr_doc_wdr_set_discount" style="<?php echo ($rule_discount_type != 'wdr_set_discount') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                            <sub><a href="https://docs.flycart.org/en/articles/3810071-buy-one-get-one-free-buy-x-get-x-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bxgx_adjustment" target="_blank" class="awdr_doc_wdr_buy_x_get_x_discount" style="<?php echo ($rule_discount_type != 'wdr_buy_x_get_x_discount') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                            <sub><a href="https://docs.flycart.org/en/articles/3810570-buy-x-get-y-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bxgy_adjustment_document" target="_blank" class="awdr_doc_wdr_buy_x_get_y_discount" style="<?php echo ($rule_discount_type != 'wdr_buy_x_get_y_discount') ? 'display: none' : '';?>"><?php _e("Read Docs", 'woo-discount-rules'); ?></a></sub>
                        </div>
                    </div>
                </div>
                <div class="awdr-hidden-new-rule" style="<?php echo (is_null($rule_id)) ? "display:none;" : "" ?>">

                    <!-- ------------------------Rule Filter Section Start------------------------ -->
                    <div class="wdr-rule-filters-and-options-con awdr-filter-section">
                        <div class="wdr-rule-menu">
                            <h2 class="awdr-filter-heading"><?php _e("Filter", 'woo-discount-rules'); ?></h2>
                           <div class="awdr-filter-content">
                               <p><?php _e("Choose which <b>gets</b> discount (products/categories/attributes/SKU and so on )", 'woo-discount-rules'); ?></p>
                               <p><?php _e("Note : You can also exclude products/categories.", 'woo-discount-rules'); ?></p>
                           </div>
                        </div>
                        <div class="wdr-rule-options-con">
                            <div id="wdr-save-rule" name="rule_generator">
                                <input type="hidden" name="action" value="wdr_ajax">
                                <input type="hidden" name="method" value="save_rule">
                                <input type="hidden" name="awdr_nonce" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_save_rule'); ?>">
                                <input type="hidden" name="wdr_save_close" value="">
                                <div id="rule_template">
                                    <?php include 'Filters/Main.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ------------------------Rule Filter Section End-------------------------- -->

                    <!-- ------------------------Rule Discount Section Start---------------------- -->
                    <?php
                    //product adjustments
                    $product_adjustments = ($rule->getProductAdjustments()) ? $rule->getProductAdjustments() : false;

                    //cart adjustments
                    $cart_adjustment = $rule->getCartAdjustments();
                    //Bulk adjustments
                    if ($get_bulk_adjustments = $rule->getBulkAdjustments()) {
                        $bulk_adj_operator = (isset($get_bulk_adjustments->operator) && !empty($get_bulk_adjustments->operator)) ? $get_bulk_adjustments->operator : 'product_cumulative';
                        $bulk_adj_as_cart = (isset($get_bulk_adjustments->apply_as_cart_rule) && !empty($get_bulk_adjustments->apply_as_cart_rule)) ? $get_bulk_adjustments->apply_as_cart_rule : '';
                        $bulk_adj_as_cart_label = (isset($get_bulk_adjustments->cart_label) && !empty($get_bulk_adjustments->cart_label)) ? $get_bulk_adjustments->cart_label : '';
                        $bulk_adj_ranges = (isset($get_bulk_adjustments->ranges) && !empty($get_bulk_adjustments->ranges)) ? $get_bulk_adjustments->ranges : false;
                        $bulk_cat_selector = (isset($get_bulk_adjustments->selected_categories) && !empty($get_bulk_adjustments->selected_categories)) ? $get_bulk_adjustments->selected_categories : false;
                    } else {
                        $bulk_adj_operator = 'product_cumulative';
                        $bulk_adj_as_cart = '';
                        $bulk_adj_as_cart_label = '';
                        $bulk_adj_ranges = false;
                        $bulk_cat_selector = false;
                    }
                    $show_bulk_discount = $rule->showHideDiscount($bulk_adj_ranges); ?>
                    <div class="awdr-discount-container">
                        <div class="awdr-discount-row">
                            <div class="wdr-rule-filters-and-options-con">
                                <div class="wdr-rule-menu">
                                    <h2 class="awdr-discount-heading"><?php _e("Discount", 'woo-discount-rules'); ?></h2>
                                    <div class="awdr-discount-content">
                                        <p><?php _e("Select discount type and its value (percentage/price/fixed price)", 'woo-discount-rules'); ?></p>
                                    </div>
                                </div>
                                <div class="wdr-rule-options-con">
                                    <div class="wdr-discount-template">
                                        <div class="wdr-block wdr-discount-template-placement">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ------------------------Rule Discount Section End------------------------ -->

                    <!-- ------------------------Rule Condition Section Start--------------------- -->
                    <div class="awdr-condition-container">
                        <div class="awdr-condition-row">
                            <div class="wdr-rule-filters-and-options-con">
                                <?php include 'Conditions/Main.php'; ?>
                            </div>
                        </div>
                    </div>
                    <!-- ------------------------Rule Condition Section End----------------------- -->


                    <!-- ------------------------Rule Discount Batch Section Start---------------- -->
                    <?php
                    if ($rule->hasAdvancedDiscountMessage()) {
                        $badge_display = $rule->getAdvancedDiscountMessage('display', 0);
                        $badge_bg_color = $rule->getAdvancedDiscountMessage('badge_color_picker', '#ffffff');
                        $badge_text_color = $rule->getAdvancedDiscountMessage('badge_text_color_picker', '#000000');
                        $badge_text = $rule->getAdvancedDiscountMessage('badge_text');
                    } else {
                        $badge_display = false;
                        $badge_bg_color = '#ffffff';
                        $badge_text_color = '#000000';
                        $badge_text = false;
                    }
                    ?>
                    <?php include 'DiscountBatch/Main.php'; ?>
                    <!-- ------------------------Rule Discount Batch Section End------------------ -->

                </div>
                <input type="hidden" name="wdr_ajax_select2" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_select2'); ?>">
                </form><?php

        }?>
    </div>
</div>
<?php include 'Discounts/Main.php'; ?>
<div class="awdr-default-template" style="display: none;">
    <?php
    do_action('advanced_woo_discount_rules_admin_after_load_rule_fields', $rule);
    $discount_types = $base->discountElements();
    //$i = '{i}';
    foreach ($discount_types as $type => $discount_type) {
        (isset($discount_type['template']) && !empty($discount_type['template'])) ? include $discount_type['template'] : '';
    }
    include "Others/CommonTemplates.php";?>
</div>


