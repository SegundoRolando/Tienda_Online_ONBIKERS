<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
$active = 'pricing-rules';
include_once(WOO_DISCOUNT_DIR . '/view/includes/header.php');
include_once(WOO_DISCOUNT_DIR . '/view/includes/sub-menu.php');

$config = (isset($config)) ? $config : '{}';
$rule_id = 0;
$form = '';

$status = 'publish';

if (is_string($config)) {
    $data = json_decode($config);
} elseif (is_object($config)) {
    if (isset($config->form)) {
        $form = $config->form;
    }
}
$data = $config;
$rule_id = (isset($data->ID)) ? $data->ID : 0;

$flycartWooDiscountRulesPurchase = new FlycartWooDiscountRulesPurchase();
$isPro = $flycartWooDiscountRulesPurchase->isPro();
$attributes = array();
if($isPro){
    $attributes = FlycartWooDiscountRulesAdvancedHelper::get_all_product_attributes();
}
$woo_settings = new FlycartWooDiscountBase();
$current_date_and_time = FlycartWooDiscountRulesGeneralHelper::getCurrentDateAndTimeBasedOnTimeZone();
?>
<div class="container-fluid woo_discount_loader_outer">
    <form id="form_price_rule">
        <div class="row-fluid">
            <div class="<?php echo $isPro? 'col-md-12': 'col-md-8'; ?>">
                <div class="col-md-12 rule_buttons_con" align="right">
                    <input type="submit" id="savePriceRule" value="<?php esc_html_e('Save Rule', 'woo-discount-rules'); ?>" class="btn btn-primary">
                    <a href="?page=woo_discount_rules" class="btn btn-warning"><?php esc_html_e('Close and go back to list', 'woo-discount-rules'); ?></a>
                    <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('introduction/price-discount-rules', 'price_rules', 'btn btn-info'); ?>
                </div>
                <?php if ($rule_id == 0) { ?>
                    <div class="col-md-12"><h2><?php esc_html_e('New Price Rule', 'woo-discount-rules'); ?></h2></div>
                <?php } else { ?>
                    <div class="col-md-12"><h2><?php esc_html_e('Edit Price Rule', 'woo-discount-rules'); ?>
                            | <?php echo(isset($data->rule_name) ? $data->rule_name : ''); ?></h2></div>
                <?php } ?>
                <div class="col-md-12" id="general_block"><h4 class="text text-muted"> <?php esc_html_e('General', 'woo-discount-rules'); ?></h4>
                    <hr>
                    <?php
                    $date_from = (isset($data->date_from) ? $data->date_from : false);
                    $date_to = (isset($data->date_to) ? $data->date_to : false);
                    $validateDateString = FlycartWooDiscountRulesGeneralHelper::validateDateAndTimeWarningText($date_from, $date_to);
                    if(!empty($validateDateString)){
                        ?>
                        <div class="notice inline notice notice-warning notice-alt">
                            <p>
                                <b><?php esc_html_e("This rule is not running currently: ", 'woo-discount-rules'); ?></b><?php echo $validateDateString; ?>
                            </p>
                        </div>
                        <br>
                        <?php
                    }
                    ?>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3"><label> <?php esc_html_e('Priority :', 'woo-discount-rules'); ?> </label>
                                <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('The Simple Ranking concept, which one is going to execute first and so on.', 'woo-discount-rules'); ?>"></span>
                            </div>
                            <div class="col-md-6"><input type="number" class="rule_order"
                                                         id="rule_order"
                                                         name="rule_order"
                                                         min=1
                                                         value="<?php echo(isset($data->rule_order) ? $data->rule_order : 1); ?>"
                                                         placeholder="<?php esc_html_e('ex. 1', 'woo-discount-rules'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label> <?php esc_html_e('Rule Name', 'woo-discount-rules'); ?>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Rule name / title.', 'woo-discount-rules'); ?>"></span>
                                </label>
                            </div>
                            <div class="col-md-6"><input type="text" class="form-control rule_descr"
                                                         id="rule_name"
                                                         name="rule_name"
                                                         value="<?php echo(isset($data->rule_name) ? $data->rule_name : ''); ?>"
                                                         placeholder="<?php esc_attr_e('ex. Standard Rule.', 'woo-discount-rules'); ?>"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3"><label> <?php esc_html_e('Rule Description', 'woo-discount-rules'); ?>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Rule Descriptions.', 'woo-discount-rules'); ?>"></span>
                                </label></div>
                            <div class="col-md-6"><input type="text" class="form-control rule_descr"
                                                         name="rule_descr"
                                                         value="<?php echo(isset($data->rule_descr) ? $data->rule_descr : ''); ?>"
                                                         id="rule_descr"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3"><label> <?php esc_html_e('Method', 'woo-discount-rules'); ?>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Method to Apply.', 'woo-discount-rules'); ?>"></span>
                                </label></div>
                            <?php $opt = (isset($data->rule_method) ? $data->rule_method : ''); ?>
                            <div class="col-md-6"><select class="form-control"
                                                          name="rule_method" id="price_rule_method">
                                    <option
                                            value="qty_based" <?php if ($opt == 'qty_based') { ?> selected=selected <?php } ?>>
                                        <?php esc_html_e('Quantity / category / product / user role based discounts and BOGO deals ', 'woo-discount-rules'); ?>
                                    </option>
                                    <option
                                        <?php if (!$pro) { ?> disabled <?php } else { ?> value="product_based" <?php
                                        }
                                        if ($opt == 'product_based') { ?> selected=selected <?php } ?>>
                                        <?php if (!$pro) { ?>
                                            <?php esc_html_e('Dependent / conditional based discount (by individual product)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                        <?php } else { ?>
                                            <?php esc_html_e('Dependent / conditional based discount (by individual product)', 'woo-discount-rules'); ?>
                                        <?php } ?>
                                    </option>
                                </select></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3"><label> <?php esc_html_e('Validity', 'woo-discount-rules'); ?>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Period of Rule Active. Format: month/day/Year Hour:Min', 'woo-discount-rules'); ?>"></span></label></div>
                            <div class="col-md-6">
                                <?php
                                $date_from = (isset($data->date_from) ? $data->date_from : '');
                                $date_to = (isset($data->date_to) ? $data->date_to : '');
                                if($date_from != '') $date_from = date( 'm/d/Y H:i', strtotime($date_from));
                                if($date_to != '') $date_to = date( 'm/d/Y H:i', strtotime($date_to));
                                ?>
                                <div class="form-inline">
                                    <input type="text"
                                           name="date_from"
                                           class="form-control wdr_datepicker"
                                           value="<?php echo $date_from; ?>"
                                           placeholder="<?php esc_attr_e('From', 'woo-discount-rules'); ?>">
                                    <input type="text" name="date_to"
                                           class="form-control wdr_datepicker"
                                           value="<?php echo $date_to; ?>"
                                           placeholder="<?php esc_attr_e('To - Leave Empty if No Expiry', 'woo-discount-rules'); ?>"></div>
                                <span class="wdr_current_date_and_time_string"><?php echo sprintf(esc_html__('Current date and time: %s', 'woo-discount-rules'), date('m/d/Y H:i', strtotime($current_date_and_time))); ?></span>
                            </div>
                        </div>
                        <?php
                        $show_discount_table = $woo_settings->getConfigData('show_discount_table', 'show');
                        if($show_discount_table == 'advance'){
                            ?>
                            <br>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3"><label> <?php esc_html_e('Format for advanced table display option (see plugin settings)', 'woo-discount-rules'); ?> <i
                                                    class="text-muted glyphicon glyphicon-exclamation-sign"
                                                    title="<?php esc_attr_e('Used when advanced table display option is set in the plugin settings', 'woo-discount-rules'); ?>"></i></label></div>
                                    <?php $opt = (isset($data->advance_table_format) ? $data->advance_table_format : ''); ?>
                                    <div class="col-md-6">
                                        <textarea name="advance_table_format" class="price_rule_display_format" placeholder="<?php esc_attr_e('Buy {{min_quantity}} or more quantity and get {{discount}} as discount', 'woo-discount-rules'); ?>" value="<?php echo esc_attr($opt); ?>"><?php echo $opt; ?></textarea>
                                        <div class="wdr_desc_text_con">
                                    <span class="wdr_desc_text">
                                        <?php esc_html_e('{{title}} -> Title', 'woo-discount-rules'); ?><br>
                                        <?php esc_html_e('{{description}} -> Description', 'woo-discount-rules'); ?><br>
                                        <?php esc_html_e('{{min_quantity}} -> Minimum quantity', 'woo-discount-rules'); ?><br>
                                        <?php esc_html_e('{{max_quantity}} -> Maximum quantity', 'woo-discount-rules'); ?><br>
                                        <?php esc_html_e('{{discount}} -> Discount', 'woo-discount-rules'); ?><br>
                                        <?php esc_html_e('{{discounted_price}} -> Discounted price', 'woo-discount-rules'); ?><br>
                                        <?php esc_html_e('{{condition}} -> Rule condition text', 'woo-discount-rules'); ?>
                                    </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div align="right">
                            <input type="button" class="btn btn-success restriction_tab" value="<?php esc_attr_e('Next', 'woo-discount-rules'); ?>">
                        </div>
                    </div>
                </div>

                <div class="col-md-12 wdr_hide" id="restriction_block"><h4 class="text text-muted"> <?php esc_html_e('Discount Conditions', 'woo-discount-rules'); ?></h4>
                    <hr>
                    <div class="qty_based_condition_cont price_discount_condition_con">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Apply To', 'woo-discount-rules'); ?> </label>
                                    <div class="wdr_desc_text_con apply_to_hint">
                                    </div>
                                </div>
                                <?php $opt = (isset($data->apply_to) ? $data->apply_to : ''); ?>
                                <div class="col-md-9"><select class="selectpicker"
                                                              name="apply_to" id="apply_to">
                                        <option
                                                value="all_products" <?php if ($opt == 'all_products') { ?> selected=selected <?php } ?>>
                                            <?php esc_html_e('All products', 'woo-discount-rules'); ?>
                                        </option>
                                        <option
                                                value="specific_products" <?php if ($opt == 'specific_products') { ?> selected=selected <?php } ?>>
                                            <?php esc_html_e('Specific products', 'woo-discount-rules'); ?>
                                        </option>
                                        <option
                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="specific_category" <?php }
                                            if ($opt == 'specific_category') { ?> selected=selected <?php } ?>>
                                            <?php if (!$pro) { ?>
                                                <?php esc_html_e('Specific categories', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                            <?php } else { ?>
                                                <?php esc_html_e('Specific categories', 'woo-discount-rules'); ?>
                                            <?php } ?>
                                        </option>
                                        <option
                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="specific_attribute" <?php }
                                            if ($opt == 'specific_attribute') { ?> selected=selected <?php } ?>>
                                            <?php if (!$pro) { ?>
                                                <?php esc_html_e('Specific attributes', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                            <?php } else { ?>
                                                <?php esc_html_e('Specific attributes', 'woo-discount-rules'); ?>
                                            <?php } ?>
                                        </option>
                                    </select>
                                    <div class="form-group" id="product_list">
                                        <?php $products_list = json_decode((isset($data->product_to_apply) ? $data->product_to_apply : '{}'), true);
                                        echo FlycartWoocommerceProduct::getProductAjaxSelectBox($products_list, 'product_to_apply');
                                        ?>
                                    </div>
                                    <?php $is_cumulative_for_products = (isset($data->is_cumulative_for_products))? $data->is_cumulative_for_products : 1 ?>
                                    <div class="form-group" id="cumulative_for_products_cont">
                                        <input type="checkbox" name="is_cumulative_for_products" id="is_cumulative_for_products" value="1" <?php if($is_cumulative_for_products) { echo "checked"; } ?>> <label class="checkbox_label" for="is_cumulative_for_products"><?php esc_html_e('Check this box to count item quantities in cart cumulatively across products', 'woo-discount-rules'); ?></label>
                                    </div>
                                    <div class="form-group" id="category_list">
                                        <?php $category_list = json_decode((isset($data->category_to_apply) ? $data->category_to_apply : '{}'), true); ?>
                                        <select class="category_list selectpicker" multiple title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                name="category_to_apply[]">
                                            <?php foreach ($category as $index => $value) { ?>
                                                <option
                                                        value="<?php echo $index; ?>"<?php if (in_array($index, $category_list)) { ?> selected=selected <?php } ?>><?php echo $value; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php $is_cumulative = (isset($data->is_cumulative))? $data->is_cumulative : 1 ?>
                                        <input type="checkbox" name="is_cumulative" id="is_cumulative" value="1" <?php if($is_cumulative) { echo "checked"; } ?>> <label class="checkbox_label" for="is_cumulative"><?php esc_html_e('Check this box to count quantities cumulatively across category(ies)', 'woo-discount-rules'); ?></label>
                                        <div class="apply_child_categories">
                                            <?php $apply_child_categories = (isset($data->apply_child_categories))? $data->apply_child_categories : 0 ?>
                                            <input type="checkbox" name="apply_child_categories" id="apply_child_categories" value="1" <?php if($apply_child_categories) { echo "checked"; } ?>> <label class="checkbox_label" for="apply_child_categories"><?php esc_html_e('Check this box to apply child category(ies)', 'woo-discount-rules'); ?></label>
                                        </div>
                                    </div>
                                    <div class="form-group" id="product_attributes_list">
                                        <?php $attribute_list = json_decode((isset($data->attribute_to_apply) ? $data->attribute_to_apply : '{}'), true); ?>
                                        <select class="attribute_list selectpicker" multiple title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                name="attribute_to_apply[]">
                                            <?php foreach ($attributes as $index => $value) { ?>
                                                <option
                                                        value="<?php echo $value['id']; ?>"<?php if (in_array($value['id'], $attribute_list)) { ?> selected=selected <?php } ?>><?php echo $value['text']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php $is_cumulative_attribute = (isset($data->is_cumulative_attribute))? $data->is_cumulative_attribute : 1 ?>
                                        <div class="form-group">
                                            <input type="checkbox" name="is_cumulative_attribute" id="is_cumulative_attribute" value="1" <?php if($is_cumulative_attribute) { echo "checked"; } ?>> <label class="checkbox_label" for="is_cumulative_attribute"><?php esc_html_e('Check this box to count quantities cumulatively across attribute', 'woo-discount-rules'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="product_exclude_list">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3"><label><?php esc_html_e('Exclude products', 'woo-discount-rules'); ?></label>
                                        <div class="wdr_desc_text_con">
                                            <span class="wdr_desc_text">
                                                <?php echo sprintf(__('Use this option to exclude selected products from getting a discount. <a href="%s">Read docs</a>.', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/3357868-how-to-exclude-products-from-the-discount', 'exclude_products')); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <?php
                                        if(isset($data->product_to_exclude)){
                                            if(is_array($data->product_to_exclude))
                                                $product_exclude_list = $data->product_to_exclude;
                                            else
                                                $product_exclude_list = json_decode((isset($data->product_to_exclude) ? $data->product_to_exclude : '{}'), true);
                                        } else {
                                            $product_exclude_list = array();
                                        }

                                        echo FlycartWoocommerceProduct::getProductAjaxSelectBox($product_exclude_list, 'product_to_exclude');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3"><label for="exclude_sale_items"><?php esc_html_e('Exclude sale items', 'woo-discount-rules'); ?></label>
                                        <div class="wdr_desc_text_con">
                                            <span class="wdr_desc_text">
                                                <?php echo __('Tick this checkbox if you wish to exclude products that already have a sale price set via WooCommerce.', 'woo-discount-rules'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <?php
                                        if($pro){
                                            $exclude_sale_items = (isset($data->exclude_sale_items))? $data->exclude_sale_items : 0; ?>
                                            <input type="checkbox" name="exclude_sale_items" id="exclude_sale_items" value="1" <?php if($exclude_sale_items) { echo "checked"; } ?>> <label class="checkbox_label" for="exclude_sale_items"><?php esc_html_e('Check this box if the rule should not apply to items on sale.', 'woo-discount-rules'); ?></label>
                                            <?php
                                        } else {
                                            ?>
                                            <div class="woo-support-in_pro">
                                                <?php
                                                esc_html_e('Supported in PRO version', 'woo-discount-rules');
                                                ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Customers', 'woo-discount-rules'); ?> </label>
                                    <div class="wdr_desc_text_con">
                                            <span class="wdr_desc_text">
                                                <?php echo sprintf(__('Use this option to apply the rule for specific customers. <a href="%s">Read docs</a>.', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/2002022-customer-based-discount-specific-users', 'customer_based_discount')); ?>
                                            </span>
                                    </div>
                                </div>
                                <?php $opt = (isset($data->customer) ? $data->customer : ''); ?>
                                <div class="col-md-9"><select class="selectpicker"
                                                              name="customer" id="apply_customer">
                                        <option value="all" <?php if ($opt == 'all') { ?> selected=selected <?php } ?>>
                                            <?php esc_html_e('All', 'woo-discount-rules'); ?>
                                        </option>
                                        <option
                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="only_given" <?php
                                            }
                                            if ($opt == 'only_given') { ?> selected=selected <?php } ?>>
                                            <?php if (!$pro) { ?>
                                                <?php esc_html_e('Only Given', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                            <?php } else { ?>
                                                <?php esc_html_e('Only Given', 'woo-discount-rules'); ?>
                                            <?php } ?>
                                        </option>
                                    </select>
                                    <div class="form-group" id="user_list">
                                        <?php $users_list = json_decode((isset($data->users_to_apply) ? $data->users_to_apply : '{}'), true);
                                        echo FlycartWoocommerceProduct::getUserAjaxSelectBox($users_list, 'users_to_apply');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('User roles', 'woo-discount-rules') ?> </label>
                                    <div class="wdr_desc_text_con">
                                            <span class="wdr_desc_text">
                                                <?php echo sprintf(__('Use this option to set a discount based on user roles. <a href="%s">Read docs</a>.', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1933842-user-role-based-discount-rules', 'user_role_based_discount')); ?>
                                            </span>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <?php
                                    if($pro){
                                        $roles_list = json_decode((isset($data->user_roles_to_apply) ? $data->user_roles_to_apply : '{}'), true); ?>
                                        <select class="roles_list selectpicker" id="product_roles_list" multiple name="user_roles_to_apply[]" title="<?php esc_html_e('Do not use', 'woo-discount-rules'); ?>">
                                            <?php foreach ($userRoles as $index => $user) { ?>
                                                <option value="<?php echo $index; ?>"<?php if (in_array($index, $roles_list)) { ?> selected=selected <?php } ?>><?php echo $user; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="woo-support-in_pro">
                                            <?php
                                            esc_html_e('Supported in PRO version', 'woo-discount-rules');
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Coupon', 'woo-discount-rules') ?> </label>
                                    <div class="wdr_desc_text_con">
                                        <span class="wdr_desc_text">
                                            <?php echo sprintf(__('Useful if you wish to activate this discount rule via coupon code.<br>You can create your own coupon code here or select coupons created from WooCommerce -> Coupons. <a href="%s">Read docs</a>.', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1818998-activate-discount-rule-using-a-coupon-code-in-woocommerce', 'coupon_based_discount')); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <?php
                                    if($pro){
                                        $coupons_to_apply_option = isset($data->coupons_to_apply_option) ? $data->coupons_to_apply_option : 'none';
                                        $coupons_to_apply = isset($data->coupons_to_apply) ? $data->coupons_to_apply : '';
                                        $dynamic_coupons_to_apply = isset($data->dynamic_coupons_to_apply) ? $data->dynamic_coupons_to_apply : '';
                                        ?>
                                        <select class="selectpicker" id="coupon_option_price_rule" name="coupons_to_apply_option">
                                            <option value="none"<?php if ($coupons_to_apply_option == 'none') { ?> selected=selected <?php } ?>><?php esc_html_e('Do not use', 'woo-discount-rules'); ?></option>
                                            <option value="create_dynamic_coupon"<?php if ($coupons_to_apply_option == 'create_dynamic_coupon') { ?> selected=selected <?php } ?>><?php esc_html_e('Create your own coupon', 'woo-discount-rules'); ?></option>
                                            <option value="any_selected"<?php if ($coupons_to_apply_option == 'any_selected') { ?> selected=selected <?php } ?>><?php esc_html_e('Apply if any one coupon applied (Select from WooCommerce)', 'woo-discount-rules'); ?></option>
                                            <option value="all_selected"<?php if ($coupons_to_apply_option == 'all_selected') { ?> selected=selected <?php } ?>><?php esc_html_e('Apply if all coupon applied (Select from WooCommerce)', 'woo-discount-rules'); ?></option>
                                        </select>
                                        <?php

                                        if(!empty($coupons_to_apply)){
                                            if(is_string($coupons_to_apply)) $coupons_to_apply = explode(',', $coupons_to_apply);
                                        } else {
                                            $coupons_to_apply = array();
                                        }

                                        $has_large_no_of_coupon = FlycartWooDiscountBase::hasLargeNumberOfCoupon();
                                        $dynamic_coupon_already_exists = false;
                                        if($coupons_to_apply_option == 'create_dynamic_coupon'){
                                            if(!empty($dynamic_coupons_to_apply)){
                                                $dynamic_coupon_already_exists = FlycartWooDiscountRulesGeneralHelper::checkCouponAlreadyExistsInWooCommerce($dynamic_coupons_to_apply);
                                            }
                                        }
                                        ?>
                                        <div class="dynamic_coupons_to_apply_price_rule_con">
                                            <input type="text" class="form-control<?php echo ($dynamic_coupon_already_exists === true)? ' wdr_invalid': ''; ?>" name="dynamic_coupons_to_apply" value="<?php echo $dynamic_coupons_to_apply; ?>"/>
                                            <div class="notice inline notice-alt notice-warning dynamic_coupons_to_apply_validation wdr_validation_notice" <?php echo ($dynamic_coupon_already_exists === true)? 'style="display:block;"': ''; ?>>
                                                <?php echo ($dynamic_coupon_already_exists === true)? "<p>".esc_html__('Coupon already exists in WooCommerce. Please select another name', 'woo-discount-rules')."</p>": ''; ?>
                                            </div>
                                        </div>
                                        <div class="coupons_to_apply_price_rule_con">
                                            <?php
                                            if($has_large_no_of_coupon){
                                                ?>
                                                <select class="coupons_selectbox_multi_select_wdr" multiple id="coupons_to_apply" name="coupons_to_apply[]">
                                                    <?php echo FlycartWooDiscountBase::loadSelectedCouponOptions($coupons_to_apply); ?>
                                                </select>
                                                <?php
                                            } else {
                                               ?>
                                                <select class="form-control coupons_to_apply selectpicker" multiple id="coupons_to_apply" name="coupons_to_apply[]">
                                                    <?php
                                                    if(!empty($coupons)){
                                                        foreach ($coupons as $coupon_code => $coupon_title){
                                                            ?>
                                                            <option value="<?php echo $coupon_code; ?>"<?php if(in_array($coupon_code, $coupons_to_apply)) { ?> selected=selected <?php } ?>><?php echo $coupon_title ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <?php
                                            }
                                            ?>
                                            <span class="woo-discount-hint">
                                                <a target="_blank" href="https://docs.flycart.org/woocommerce-discount-rules/coupon-based-discounts/activate-discount-rule-using-a-coupon-code-in-woocommerce">
                                                <?php
                                                esc_html_e('Make sure you have created the coupon already', 'woo-discount-rules');
                                                ?>
                                                </a>
                                            </span>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="woo-support-in_pro">
                                            <?php
                                            esc_html_e('Supported in PRO version', 'woo-discount-rules');
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Subtotal', 'woo-discount-rules') ?></label>
                                    <div class="wdr_desc_text_con">
                                        <span class="wdr_desc_text">
                                            <?php echo sprintf(__('Useful when you want to limit the rule based on subtotal. (Use this only when absolutely necessary.)<br>See cart based discount rule tab (<a href="%s" target="_blank">cart based rule</a>) for effective subtotal based discount rules.', 'woo-discount-rules'), admin_url("admin.php?page=woo_discount_rules&tab=cart-rules")); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <?php
                                    if($pro){
                                        $woocommerce3 = FlycartWoocommerceVersion::isWCVersion3x();
                                        if($woocommerce3){
                                            $subtotal_to_apply_option = isset($data->subtotal_to_apply_option) ? $data->subtotal_to_apply_option : 'none';
                                            $subtotal_to_apply = isset($data->subtotal_to_apply) ? $data->subtotal_to_apply : '';
                                            ?>
                                            <select class="selectpicker" id="subtotal_option_price_rule" name="subtotal_to_apply_option">
                                                <option value="none"<?php if ($subtotal_to_apply_option == 'none') { ?> selected=selected <?php } ?>><?php esc_html_e('Do not use', 'woo-discount-rules'); ?></option>
                                                <option value="atleast"<?php if ($subtotal_to_apply_option == 'atleast') { ?> selected=selected <?php } ?>><?php esc_html_e('Subtotal atleast', 'woo-discount-rules'); ?></option>
                                            </select>
                                            <div class="subtotal_to_apply_price_rule_con">
                                            <span class="woo-discount-hint">
                                                <?php
                                                esc_html_e('Enter the amount', 'woo-discount-rules');
                                                ?>
                                            </span>
                                                <input class="form-control subtotal_to_apply" id="subtotal_to_apply" name="subtotal_to_apply" value="<?php echo $subtotal_to_apply; ?>"/>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <div class="woo-support-in_pro">
                                                <?php
                                                esc_html_e('Supported in WooCommerce 3.x', 'woo-discount-rules');
                                                ?>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="woo-support-in_pro">
                                            <?php
                                            esc_html_e('Supported in PRO version', 'woo-discount-rules');
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label><?php esc_html_e('Purchase History', 'woo-discount-rules'); ?></label>
                                    <div class="wdr_desc_text_con">
                                        <span class="wdr_desc_text">
                                            <?php echo sprintf(__('Useful for providing discounts based on previous purchase history.<br><b>Example:</b> First order discount, discount based on customer’s total spent so far. <a href="%s">Read docs</a>.', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1993999-purchase-history-based-discount', 'purchase_history_based_discount')); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php $based_on_purchase_history = (isset($data->based_on_purchase_history) ? $data->based_on_purchase_history : 0); ?>
                                <div class="col-md-9">
                                    <?php
                                    if($pro){
                                        ?>
                                        <select class="selectpicker" id="based_on_purchase_history" name="based_on_purchase_history">
                                            <option value="0"<?php if ($based_on_purchase_history == '0') { ?> selected=selected <?php } ?>><?php esc_html_e('Do not use', 'woo-discount-rules'); ?></option>
                                            <option value="first_order"<?php if ($based_on_purchase_history === 'first_order') { ?> selected=selected <?php } ?>><?php esc_html_e('First Order discount', 'woo-discount-rules'); ?></option>
                                            <option value="1"<?php if ($based_on_purchase_history == '1') { ?> selected=selected <?php } ?>><?php esc_html_e('Purchased amount', 'woo-discount-rules'); ?></option>
                                            <option value="2"<?php if ($based_on_purchase_history == '2') { ?> selected=selected <?php } ?>><?php esc_html_e('Number of previous orders made', 'woo-discount-rules'); ?></option>
                                            <option value="3"<?php if ($based_on_purchase_history == '3') { ?> selected=selected <?php } ?>><?php esc_html_e('Number of previous orders made with following products', 'woo-discount-rules'); ?></option>
                                            <option value="4"<?php if ($based_on_purchase_history == '4') { ?> selected=selected <?php } ?>><?php esc_html_e('Number of quantity(s) in previous orders made with following products', 'woo-discount-rules'); ?></option>
                                        </select>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="woo-support-in_pro">
                                            <?php
                                            esc_html_e('Supported in PRO version', 'woo-discount-rules');
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    if($pro){
                                        ?>
                                        <div class="form-group" id="based_on_purchase_history_fields">
                                            <?php
                                            $purchased_history_amount = (isset($data->purchased_history_amount) ? $data->purchased_history_amount : 0);
                                            $purchased_history_type = (isset($data->purchased_history_type) ? $data->purchased_history_type : 'atleast');
                                            $purchased_history_duration = (isset($data->purchased_history_duration) ? $data->purchased_history_duration : 'all_time');
                                            $purchased_history_duration_days = (isset($data->purchased_history_duration_days) ? $data->purchased_history_duration_days : '');
                                            ?>
                                            <div class="form-group wdr_hide" id="purchase_history_products">
                                                <?php
                                                if(isset($data->purchase_history_products)){
                                                    if(is_array($data->purchase_history_products))
                                                        $product_purchase_history_list = $data->purchase_history_products;
                                                    else
                                                        $product_purchase_history_list = json_decode((isset($data->purchase_history_products) ? $data->purchase_history_products : '{}'), true);
                                                } else {
                                                    $product_purchase_history_list = array();
                                                }

                                                echo FlycartWoocommerceProduct::getProductAjaxSelectBox($product_purchase_history_list, 'purchase_history_products');
                                                ?>
                                            </div>
                                            <select class="selectpicker purchased_history_type" name="purchased_history_type">
                                                <option value="atleast"<?php echo ($purchased_history_type == 'atleast')? ' selected="selected"': ''; ?>><?php esc_html_e('Greater than or equal to', 'woo-discount-rules'); ?></option>
                                                <option value="less_than_or_equal"<?php echo ($purchased_history_type == 'less_than_or_equal')? ' selected="selected"': ''; ?>><?php esc_html_e('Less than or equal to', 'woo-discount-rules'); ?></option>
                                            </select>
                                            <input type="text" value="<?php echo $purchased_history_amount; ?>" name="purchased_history_amount"/>
                                            <label><?php esc_html_e('and the order status should be', 'woo-discount-rules'); ?></label>
                                            <?php
                                            $woocommerce_order_status = wc_get_order_statuses();
                                            $purchase_history_status_list = json_decode((isset($data->purchase_history_status_list) ? $data->purchase_history_status_list : '{}'), true);
                                            if(empty($purchase_history_status_list)){
                                                $purchase_history_status_list[] = 'wc-completed';
                                            }
                                            ?>
                                            <select class="purchase_history_status_list selectpicker" multiple title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>" name="purchase_history_status_list[]">
                                                <?php foreach ($woocommerce_order_status as $index => $value) { ?>
                                                    <option
                                                            value="<?php echo $index; ?>"<?php if (in_array($index, $purchase_history_status_list)) { ?> selected=selected <?php } ?>><?php echo $value; ?></option>
                                                <?php } ?>
                                            </select>
                                            <select class="selectpicker purchased_history_duration" id="purchased_history_duration" name="purchased_history_duration">
                                                <option value="all_time"<?php echo ($purchased_history_duration == 'all_time')? ' selected="selected"': ''; ?>><?php esc_html_e('From all previous orders', 'woo-discount-rules'); ?></option>
                                                <option value="7_days"<?php echo ($purchased_history_duration == '7_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 7 days', 'woo-discount-rules'); ?></option>
                                                <option value="14_days"<?php echo ($purchased_history_duration == '14_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 14 days', 'woo-discount-rules'); ?></option>
                                                <option value="30_days"<?php echo ($purchased_history_duration == '30_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 30 days', 'woo-discount-rules'); ?></option>
                                                <option value="60_days"<?php echo ($purchased_history_duration == '60_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 60 days', 'woo-discount-rules'); ?></option>
                                                <option value="90_days"<?php echo ($purchased_history_duration == '90_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 90 days', 'woo-discount-rules'); ?></option>
                                                <option value="180_days"<?php echo ($purchased_history_duration == '180_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 180 days', 'woo-discount-rules'); ?></option>
                                                <option value="1_year"<?php echo ($purchased_history_duration == '1_year')? ' selected="selected"': ''; ?>><?php esc_html_e('Last 1 year', 'woo-discount-rules'); ?></option>
                                                <option value="custom_days"<?php echo ($purchased_history_duration == 'custom_days')? ' selected="selected"': ''; ?>><?php esc_html_e('Custom', 'woo-discount-rules'); ?></option>
                                            </select>
                                            <span class="purchased_history_duration_days_con" id="purchased_history_duration_days_con">
                                                <input type="text" placeholder="30" value="<?php echo $purchased_history_duration_days; ?>" class="purchased_history_duration_days" name="purchased_history_duration_days"/>
                                                <label><?php esc_html_e('in days', 'woo-discount-rules'); ?></label>
                                            </span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product_based_condition_cont price_discount_condition_con">
                        <?php
                        $product_based_conditions = json_decode((isset($data->product_based_condition) ? $data->product_based_condition : '{}'), true);
                        $product_based_condition_product_buy_type = isset($product_based_conditions['product_buy_type']) ? $product_based_conditions['product_buy_type'] : 'any';
                        $product_based_condition_product_quantity_rule = isset($product_based_conditions['product_quantity_rule']) ? $product_based_conditions['product_quantity_rule'] : 'more';
                        $product_based_condition_product_quantity_from = isset($product_based_conditions['product_quantity_from']) ? $product_based_conditions['product_quantity_from'] : '';
                        $product_based_condition_product_quantity_to = isset($product_based_conditions['product_quantity_to']) ? $product_based_conditions['product_quantity_to'] : '';
                        $product_based_condition_product_to_buy = isset($product_based_conditions['product_to_buy']) ? $product_based_conditions['product_to_buy'] : array();
                        $product_based_condition_product_to_apply = isset($product_based_conditions['product_to_apply']) ? $product_based_conditions['product_to_apply'] : array();
                        $product_based_condition_product_to_apply_count_option = isset($product_based_conditions['product_to_apply_count_option']) ? $product_based_conditions['product_to_apply_count_option'] : 'all';
                        $product_based_condition_product_to_apply_count = isset($product_based_conditions['product_to_apply_count']) ? $product_based_conditions['product_to_apply_count'] : '';
                        ?>
                        <div class="form-group" id="product_list">
                            <label ><?php esc_html_e('Buy', 'woo-discount-rules') ?></label>
                            <select class="selectpicker" name="product_based_condition[product_buy_type]">
                                <option value="any"<?php echo ($product_based_condition_product_buy_type == 'any')? ' selected="selected"': ''; ?>><?php esc_html_e('Any', 'woo-discount-rules') ?></option>
                                <option value="each"<?php echo ($product_based_condition_product_buy_type == 'each')? ' selected="selected"': ''; ?>><?php esc_html_e('Each', 'woo-discount-rules') ?></option>
                                <option value="combine"<?php echo ($product_based_condition_product_buy_type == 'combine')? ' selected="selected"': ''; ?>><?php esc_html_e('Combine', 'woo-discount-rules') ?></option>
                            </select>
                            <select class="selectpicker" id="product_based_condition_quantity_rule" name="product_based_condition[product_quantity_rule]">
                                <option value="more"<?php echo ($product_based_condition_product_quantity_rule == 'more')? ' selected="selected"': ''; ?>><?php esc_html_e('More than or equal to', 'woo-discount-rules') ?></option>
                                <option value="less"<?php echo ($product_based_condition_product_quantity_rule == 'less')? ' selected="selected"': ''; ?>><?php esc_html_e('Less than or equal to', 'woo-discount-rules') ?></option>
                                <option value="equal"<?php echo ($product_based_condition_product_quantity_rule == 'equal')? ' selected="selected"': ''; ?>><?php esc_html_e('Equal', 'woo-discount-rules') ?></option>
                                <option value="from"<?php echo ($product_based_condition_product_quantity_rule == 'from')? ' selected="selected"': ''; ?>><?php esc_html_e('From', 'woo-discount-rules') ?></option>
                            </select>
                            <input placeholder="<?php esc_html_e('Quantity', 'woo-discount-rules') ?>" type="text" name="product_based_condition[product_quantity_from]" value="<?php echo $product_based_condition_product_quantity_from; ?>"/ >
                            <div class="product_based_condition_to">
                                <label ><?php esc_html_e('to', 'woo-discount-rules')?></label>
                                <input placeholder="<?php esc_html_e('Quantity', 'woo-discount-rules') ?>" type="text" name="product_based_condition[product_quantity_to]" value="<?php echo $product_based_condition_product_quantity_to; ?>"/ >
                            </div>
                            <div class="product_based_condition_product_from">
                                <label ><?php esc_html_e('Product(s) from', 'woo-discount-rules')?></label>
                                <?php echo FlycartWoocommerceProduct::getProductAjaxSelectBox($product_based_condition_product_to_buy, 'product_based_condition[product_to_buy]'); ?>
                            </div>
                            <div class="product_based_condition_get_product_discount">
                                <?php $product_based_condition_get_discount_type = isset($product_based_conditions['get_discount_type']) ? $product_based_conditions['get_discount_type'] : 'product'; ?>
                                <select class="selectpicker" id="product_based_condition_get_discount_type" name="product_based_condition[get_discount_type]">
                                    <option value="product"<?php echo ($product_based_condition_get_discount_type == 'product')? ' selected="selected"': ''; ?>><?php esc_html_e('Apply discount in product(s)', 'woo-discount-rules') ?></option>
                                    <option value="category"<?php echo ($product_based_condition_get_discount_type == 'category')? ' selected="selected"': ''; ?>><?php esc_html_e('Apply discount in category(ies)', 'woo-discount-rules') ?></option>
                                </select>
                            </div>
                            <div class="product_based_condition_get_product_discount get_discount_type_product_tag">
                                <label ><?php esc_html_e('and get discount on ', 'woo-discount-rules') ?></label>
                                <select class="selectpicker" id="product_based_condition_product_to_apply_count_option" name="product_based_condition[product_to_apply_count_option]">
                                    <option value="all"<?php echo ($product_based_condition_product_to_apply_count_option == 'all')? ' selected="selected"': ''; ?>><?php esc_html_e('All', 'woo-discount-rules') ?></option>
                                    <option value="apply_first"<?php echo ($product_based_condition_product_to_apply_count_option == 'apply_first')? ' selected="selected"': ''; ?>><?php esc_html_e('First quantity(s)', 'woo-discount-rules') ?></option>
                                    <option value="skip_first"<?php echo ($product_based_condition_product_to_apply_count_option == 'skip_first')? ' selected="selected"': ''; ?>><?php esc_html_e('Skip first quantity(s)', 'woo-discount-rules') ?></option>
                                </select>
                                <input placeholder="<?php esc_html_e('Quantity', 'woo-discount-rules') ?>" type="text" name="product_based_condition[product_to_apply_count]" id="product_based_condition_product_to_apply_count" value="<?php echo $product_based_condition_product_to_apply_count; ?>"/ >
                            </div>
                            <div class="product_based_condition_get_product_discount get_discount_type_product_tag">
                                <label ><?php esc_html_e(' Product(s) ', 'woo-discount-rules') ?></label>
                                <?php echo FlycartWoocommerceProduct::getProductAjaxSelectBox($product_based_condition_product_to_apply, 'product_based_condition[product_to_apply]'); ?>
                            </div>
                            <div class="product_based_condition_get_product_discount get_discount_type_category_tag">
                                <label ><?php esc_html_e('Category(ies)', 'woo-discount-rules') ?></label>
                                <?php $product_based_condition_category_to_apply = isset($product_based_conditions['category_to_apply']) ? $product_based_conditions['category_to_apply'] : array(); ?>
                                <select class="category_list selectpicker" multiple title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                        name="product_based_condition[category_to_apply][]">
                                    <?php foreach ($category as $index => $value) { ?>
                                        <option
                                                value="<?php echo $index; ?>"<?php if (in_array($index, $product_based_condition_category_to_apply)) { ?> selected=selected <?php } ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('', 'product_dependent#dependant-product-based-rules', 'btn btn-info', esc_html__('Guide for product dependent rules', 'woo-discount-rules')); ?>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div align="right">
                                <input type="button" class="btn btn-warning general_tab" value="<?php esc_attr_e('Previous', 'woo-discount-rules'); ?>">
                                <input type="button" class="btn btn-success discount_tab" value="<?php esc_attr_e('Next', 'woo-discount-rules'); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TODO: Implement ForEach Concept -->
                <div class="col-md-12 wdr_hide" id="discount_block">
                    <h4 class="text text-muted"> <?php esc_html_e('Discount', 'woo-discount-rules'); ?></h4>
                    <div class="qty_based_discount_cont price_discounts_con">
                        <a href=javascript:void(0) class="button button-primary" id="addNewDiscountRange"><i
                                    class="glyphicon glyphicon-plus"></i> <?php esc_html_e('Add quantity ranges', 'woo-discount-rules'); ?></a>
                        <hr>
                        <div class="set_discount_with_range_warning">
                            <div class="notice inline notice-warning notice-alt">
                                <p>
                                    <?php esc_html_e('Bundle (Set) Discount should NOT be mixed with any other adjustment types when adding ranges. Example: If your first range\'s adjustment type is Bundle (Set) Discount, other ranges should also be same type.', 'woo-discount-rules'); ?>
                                </p>
                            </div>
                            <br>
                        </div>
                        <div id="discount_rule_list">
                            <?php
                            $discount_range = array(0 => '');//new stdClass();
                            if (isset($data->discount_range)) {
                                if (is_string($data->discount_range)) {
                                    $discount_range = json_decode($data->discount_range);
                                } else {
                                    $discount_range = $data->discount_range;
                                }
                            }

                            // Make Dummy Element.
                            if ($discount_range == '') $discount_range = array(0 => '');
                            $fieldIndex = 1;
                            foreach ($discount_range as $index => $discount) {
                                ?>
                                <div class="discount_rule_list">
                                    <div class="form-group">
                                        <label><span class="discount_for_min_quantity_text"><?php esc_html_e('Min Quantity', 'woo-discount-rules'); ?></span>
                                            <input type="text"
                                                   name="discount_range[<?php echo $fieldIndex; ?>][min_qty]"
                                                   class="form-control discount_range_min_qty"
                                                   value="<?php echo(isset($discount->min_qty) ? $discount->min_qty : ''); ?>"
                                                   placeholder="<?php esc_html_e('ex. 1', 'woo-discount-rules'); ?>">
                                        </label>
                                        <label class="discount_for_max_quantity_label"><?php esc_html_e('Max Quantity', 'woo-discount-rules'); ?>
                                            <input type="text"
                                                   name="discount_range[<?php echo $fieldIndex; ?>][max_qty]"
                                                   class="form-control discount_range_max_qty"
                                                   value="<?php echo(isset($discount->max_qty) ? $discount->max_qty : ''); ?>"
                                                   placeholder="<?php esc_html_e('ex. 50', 'woo-discount-rules'); ?>"> </label>
                                        <label><?php esc_html_e('Adjustment Type', 'woo-discount-rules'); ?>
                                            <?php
                                            $products_list = (isset($discount->discount_product) ? $discount->discount_product : array());
                                            $discount_product_option = (isset($discount->discount_product_option) ? $discount->discount_product_option : 'all');
                                            if(in_array($discount_product_option, array('any_cheapest', 'any_cheapest_from_all'))){
                                                $discount->discount_product_item_type = 'dynamic';
                                                if($discount_product_option == 'any_cheapest'){
                                                    $discount_product_option = 'more_than_one_cheapest';
                                                    $discount->discount_product_qty = 1;
                                                } else {
                                                    $discount_product_option = 'more_than_one_cheapest_from_all';
                                                    $discount->discount_product_qty = 1;
                                                }
                                            }
                                            ?>
                                            <select class="form-control price_discount_type"
                                                    name="discount_range[<?php echo $fieldIndex; ?>][discount_type]">
                                                <?php $opt = (isset($discount->discount_type) ? $discount->discount_type : '');
                                                if($opt == 'product_discount'){
                                                    if($discount_product_option == 'same_product'){
                                                        $opt = 'buy_x_get_x';
                                                    } else if($discount_product_option == 'all'){
                                                        $opt = 'buy_x_get_y';
                                                    } else if($discount_product_option == 'more_than_one_cheapest'){
                                                        $opt = 'more_than_one_cheapest';
                                                    } else if($discount_product_option == 'more_than_one_cheapest_from_cat'){
                                                        $opt = 'more_than_one_cheapest_from_cat';
                                                    } else if($discount_product_option == 'more_than_one_cheapest_from_all'){
                                                        $opt = 'more_than_one_cheapest_from_all';
                                                    }
                                                }
                                                ?>
                                                <option
                                                        value="percentage_discount" <?php if ($opt == 'percentage_discount') { ?> selected=selected <?php } ?> >
                                                    <?php esc_html_e('Percentage Discount', 'woo-discount-rules'); ?>
                                                </option>

                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="price_discount" <?php
                                                    }
                                                    if ($opt == 'price_discount') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Price Discount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Price Discount', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="fixed_price" <?php
                                                    }
                                                    if ($opt == 'fixed_price') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Fixed Price Per Unit', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Fixed Price Per Unit', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="set_discount" <?php
                                                    }
                                                    if ($opt == 'set_discount') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Bundle (Set) Discount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Bundle (Set) Discount', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="buy_x_get_x" <?php
                                                    }
                                                    if ($opt == 'buy_x_get_x') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Buy X get X (Same product)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Buy X get X (Same product)', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="buy_x_get_y" <?php
                                                    }
                                                    if ($opt == 'buy_x_get_y') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Buy X get Y (Auto add all selected products)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Buy X get Y (Auto add all selected products)', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="more_than_one_cheapest" <?php
                                                    }
                                                    if ($opt == 'more_than_one_cheapest') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Buy X Get Y - Selected item(s) (Cheapest in cart)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Buy X Get Y - Selected item(s) (Cheapest in cart)', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="more_than_one_cheapest_from_cat" <?php
                                                    }
                                                    if ($opt == 'more_than_one_cheapest_from_cat') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Buy X Get Y - Selected Categories (Cheapest in cart)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Buy X Get Y - Selected Categories (Cheapest in cart)', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                                <option
                                                    <?php if (!$pro) { ?> disabled <?php } else { ?> value="more_than_one_cheapest_from_all" <?php
                                                    }
                                                    if ($opt == 'more_than_one_cheapest_from_all') { ?> selected=selected <?php } ?>>
                                                    <?php if (!$pro) { ?>
                                                        <?php esc_html_e('Buy X get Y - Cheapest among all items in cart', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                    <?php } else { ?>
                                                        <?php esc_html_e('Buy X get Y - Cheapest among all items in cart', 'woo-discount-rules'); ?>
                                                    <?php } ?>
                                                </option>
                                            </select></label>
                                        <label>
                                            <span class="price_discount_amount price_discount_amount_tool_tip_con">
                                            </span>
                                        </label>
                                        <label><span class="hide-for-product-discount"><?php esc_html_e('Value', 'woo-discount-rules'); ?></span>
                                            <input type="text"
                                                   name="discount_range[<?php echo $fieldIndex; ?>][to_discount]"
                                                   class="form-control price_discount_amount"
                                                   value="<?php echo(isset($discount->to_discount) ? $discount->to_discount : ''); ?>"
                                                   placeholder="<?php esc_attr_e('ex. 50', 'woo-discount-rules'); ?>">

                                            <div class="price_discount_product_list_con">
                                                <span class="bogo_receive_discount_for_text"><?php esc_html_e('receive discount for', 'woo-discount-rules') ?></span>
                                                <select class="discount_product_option" name="discount_range[<?php echo $fieldIndex; ?>][discount_product_option]">
                                                    <option value="all"<?php echo ($discount_product_option == 'all')? ' selected="selected"': '' ?>><?php esc_html_e('Auto add all selected products', 'woo-discount-rules') ?></option>
                                                    <option value="same_product"<?php echo ($discount_product_option == 'same_product')? ' selected="selected"': '' ?>><?php esc_html_e('Same product', 'woo-discount-rules') ?></option>
                                                    <option value="more_than_one_cheapest_from_cat"<?php echo ($discount_product_option == 'more_than_one_cheapest_from_cat')? ' selected="selected"': '' ?>><?php esc_html_e('Cheapest in cart - selected category(ies)', 'woo-discount-rules') ?></option>
                                                    <option value="more_than_one_cheapest"<?php echo ($discount_product_option == 'more_than_one_cheapest')? ' selected="selected"': '' ?>><?php esc_html_e('Cheapest in cart - selected item(s)', 'woo-discount-rules') ?></option>
                                                    <option value="more_than_one_cheapest_from_all"<?php echo ($discount_product_option == 'more_than_one_cheapest_from_all')? ' selected="selected"': '' ?>><?php esc_html_e('Cheapest among all items in cart', 'woo-discount-rules') ?></option>
                                                </select>
                                                <span class="woocommerce-help-tip discount_product_option_hint" data-tip="<?php esc_attr_e('Auto add all selected products - Automatically added to the cart <br> Same product - get discount in same product <br> Any one cheapest from selected - Get discount in one selected product <br> Any one cheapest from all products - Get discount in one cheapest product  in cart <br> Cheapest in cart - selected category(ies) - cheapest product from the selected category will be discounted <br> Cheapest in cart - selected item(s) - get discount in chosen no.of quantities', 'woo-discount-rules'); ?>"></span>
                                                <div class="discount_product_option_bogo_con hide">
                                                    <label><?php esc_html_e('Free quantity', 'woo-discount-rules'); ?> <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Number of quantity(ies) in each selected product(s)', 'woo-discount-rules'); ?>"></span>
                                                        <input type="text"
                                                               name="discount_range[<?php echo $fieldIndex; ?>][discount_bogo_qty]"
                                                               class="form-control discount_bogo_qty"
                                                               value="<?php echo(isset($discount->discount_bogo_qty) ? $discount->discount_bogo_qty : 1); ?>"
                                                               placeholder="<?php esc_attr_e('ex. 1', 'woo-discount-rules'); ?>" />
                                                    </label>
                                                </div>
                                                <div class="discount_product_option_more_cheapest_con hide">
                                                    <?php
                                                    $discount_product_item_type = (isset($discount->discount_product_item_type) ? $discount->discount_product_item_type : 'dynamic');
                                                    $enable_fixed_item_count_in_bogo = apply_filters('woo_discount_rules_enable_fixed_item_count_in_bogo', false);
                                                    $discount_product_item_count_type_class = ' selectpicker';
                                                    $discount_product_item_count_type_hint_class = '';
                                                    if($enable_fixed_item_count_in_bogo == false){
                                                        $discount_product_item_count_type_hint_class = $discount_product_item_count_type_class = ' hide_discount_product_item_count_type';
                                                    }
                                                    ?>
                                                    <select class="discount_product_item_count_type<?php echo $discount_product_item_count_type_class; ?>" name="discount_range[<?php echo $fieldIndex; ?>][discount_product_item_type]">
                                                        <option value="dynamic"<?php echo ($discount_product_item_type == 'dynamic')? ' selected="selected"': '' ?>><?php esc_html_e('Dynamic item count', 'woo-discount-rules') ?></option>
                                                        <option value="static"<?php echo ($discount_product_item_type == 'static')? ' selected="selected"': '' ?>><?php esc_html_e('Fixed item count (not recommended)', 'woo-discount-rules') ?></option>
                                                    </select>
                                                    <span class="woocommerce-help-tip<?php echo $discount_product_item_count_type_hint_class; ?>" data-tip="<?php esc_attr_e('Fixed item count - You need to provide item count manually. Dynamic item count - System will choose dynamically based on cart', 'woo-discount-rules'); ?>"></span>
                                                    <label class="discount_product_items_count_field"><?php esc_html_e('Item count', 'woo-discount-rules'); ?> <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Discount for number of item(s) in cart', 'woo-discount-rules'); ?>"></span>
                                                        <input type="text"
                                                               name="discount_range[<?php echo $fieldIndex; ?>][discount_product_items]"
                                                               class="form-control discount_product_items_count_field"
                                                               value="<?php echo(isset($discount->discount_product_items) ? $discount->discount_product_items : ''); ?>"
                                                               placeholder="<?php esc_attr_e('ex. 1', 'woo-discount-rules'); ?>" />
                                                    </label>
                                                    <label><?php esc_html_e('Item quantity', 'woo-discount-rules'); ?> <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Discount for number of quantity(ies)', 'woo-discount-rules'); ?>"></span>
                                                        <input type="text"
                                                               name="discount_range[<?php echo $fieldIndex; ?>][discount_product_qty]"
                                                               class="form-control discount_product_qty"
                                                               value="<?php echo(isset($discount->discount_product_qty) ? $discount->discount_product_qty : ''); ?>"
                                                               placeholder="<?php esc_attr_e('ex. 1', 'woo-discount-rules'); ?>" />
                                                    </label>
                                                </div>
                                                <div class="discount_product_option_list_con hide">
                                                    <label><span class="wdr_block_span"><?php esc_html_e('Choose product(s)', 'woo-discount-rules'); ?></span>
                                                        <?php
                                                        echo FlycartWoocommerceProduct::getProductAjaxSelectBox($products_list, "discount_range[".$fieldIndex."][discount_product]");
                                                        ?>
                                                    </label>
                                                </div>
                                                <div class="discount_category_option_list_con hide">
                                                    <?php
                                                    $discount_category_selected = (isset($discount->discount_category) ? $discount->discount_category : array());
                                                    ?>
                                                    <label><span class="wdr_block_span"><?php esc_html_e('Choose category(ies)', 'woo-discount-rules'); ?></span>
                                                        <select class="category_list selectpicker" multiple title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                                name="<?php echo "discount_range[".$fieldIndex."][discount_category][]"; ?>">
                                                            <?php foreach ($category as $index => $value) { ?>
                                                                <option value="<?php echo $index; ?>"<?php if (in_array($index, $discount_category_selected)) { ?> selected=selected <?php } ?>><?php echo $value; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </label>
                                                </div>
                                                <div class="discount_product_percent_con hide">
                                                    <?php
                                                    $discount_product_discount_type = (isset($discount->discount_product_discount_type) ? $discount->discount_product_discount_type : '');
                                                    ?>
                                                    <label><span class="wdr_block_span"><?php esc_html_e('Discount percentage', 'woo-discount-rules'); ?></span>
                                                        <select class="selectpicker discount_product_discount_type" name="discount_range[<?php echo $fieldIndex; ?>][discount_product_discount_type]">
                                                            <option value=""<?php echo ($discount_product_discount_type == '')? ' selected="selected"': '' ?>><?php esc_html_e('100% percent', 'woo-discount-rules') ?></option>
                                                            <option value="limited_percent"<?php echo ($discount_product_discount_type == 'limited_percent')? ' selected="selected"': '' ?>><?php esc_html_e('Limited percent', 'woo-discount-rules') ?></option>
                                                        </select>
                                                    </label>
                                                    <span class="discount_product_percent_field">
                                                    <input type="text"
                                                           name="discount_range[<?php echo $fieldIndex; ?>][discount_product_percent]"
                                                           class="discount_product_percent_field"
                                                           value="<?php echo(isset($discount->discount_product_percent) ? $discount->discount_product_percent : ''); ?>"
                                                           placeholder="<?php esc_attr_e('ex. 10', 'woo-discount-rules'); ?>" /><span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Enter only numeric values. Eg: <b>50</b> for 50% discount', 'woo-discount-rules'); ?>"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                        <label><a href="javascript:void(0)" data-id="<?php echo $fieldIndex; ?>"
                                                  class="btn btn-primary form-control create_duplicate_discount_range"><?php esc_html_e('Duplicate', 'woo-discount-rules'); ?></a>
                                        </label>
                                        <label><a href=javascript:void(0)
                                                  class="btn btn-danger form-control remove_discount_range"><?php esc_html_e('Remove', 'woo-discount-rules'); ?></a>
                                        </label>


                                    </div>
                                </div>
                                <?php $fieldIndex++; } ?>
                        </div>
                        <div class="set_discount_info_text">
                            <div class="notice inline notice-info notice-alt">
                                <p>
                                    <?php echo __('<b>Example for Bundle (Set) Discount:</b> 3 for $10, 6 for $20<br>So customer can add 3 products / quantities and get them for $10<br><b>NOTE:</b> You will need to enter the entire package / bundle cost. Example: 10 (for the 3 for $10 deal)', 'woo-discount-rules'); ?>
                                    <br/>
                                    <a href="<?php echo FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/3197678-buy-3-at-40-set-discount', 'set_discount'); ?>" target="_blank"><?php esc_html_e('Read docs', 'woo-discount-rules'); ?></a>
                                </p>
                            </div>
                            <br>
                        </div>
                        <div class="fixed_price_discount_info_text">
                            <div class="notice inline notice-info notice-alt">
                                <p>
                                    <?php echo __('<b>Example for Fixed Price Per Unit:</b> Product A cost is $20. If customers buy 5 to 10, they can get at $15 each (per unit).<br>Customer adds 6 quantities of Product A. The price per unit will reduce to $15<br><b>NOTE:</b> Enter the Unit price like 15', 'woo-discount-rules'); ?>
                                    <br/>
                                    <a href="<?php echo FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/3381529-fixed-price-per-unit', 'fixed_price_per_unit'); ?>" target="_blank"><?php esc_html_e('Read docs', 'woo-discount-rules'); ?></a>
                                </p>
                            </div>
                            <br>
                        </div>
                        <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('buy-one-get-one-deals/how-to-create-a-perfect-bogo-discount-rule-in-woocommerce', 'bogo_rules', 'btn btn-info', esc_html__('Guide to create perfect BOGO rules', 'woo-discount-rules')); ?>
                    </div>
                    <div class="product_based_discount_cont price_discounts_con">
                        <div class="price_discount_product_list_con">
                            <?php
                            $product_based_discounts = json_decode((isset($data->product_based_discount) ? $data->product_based_discount : '{}'), true);
                            $product_based_discount_type = isset($product_based_discounts['discount_type']) ? $product_based_discounts['discount_type'] : 'percentage_discount';
                            $product_based_discount_value = isset($product_based_discounts['discount_value']) ? $product_based_discounts['discount_value'] : '';
                            ?>
                            <select class="selectpicker" name="product_based_discount[discount_type]">
                                <option value="percentage_discount"<?php echo ($product_based_discount_type == 'percentage_discount')? ' selected="selected"': ''; ?>><?php esc_html_e('Percent', 'woo-discount-rules') ?></option>
                                <option value="price_discount"<?php echo ($product_based_discount_type == 'price_discount')? ' selected="selected"': ''; ?>><?php esc_html_e('Fixed', 'woo-discount-rules') ?></option>
                            </select> <label><?php esc_html_e('Value', 'woo-discount-rules') ?></label>
                            <input type="text" name="product_based_discount[discount_value]" value="<?php echo $product_based_discount_value; ?>" />
                        </div>
                    </div>
                    <div align="right">
                        <input type="button" class="btn btn-warning restriction_tab" value="<?php esc_attr_e('Previous', 'woo-discount-rules'); ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php if(!$isPro){ ?>
            <div class="col-md-1"></div>
            <!-- Sidebar -->
            <?php include_once(__DIR__ . '/template/sidebar.php'); ?>
            <!-- Sidebar END -->
        <?php } ?>
        <input type="hidden" name="rule_id" id="rule_id" value="<?php echo $rule_id; ?>">
        <input type="hidden" name="form" value="<?php echo $form; ?>">
        <input type="hidden" id="ajax_path" value="<?php echo admin_url('admin-ajax.php'); ?>">
        <input type="hidden" id="admin_path"
               value="<?php echo admin_url('admin.php?page=woo_discount_rules'); ?>">
        <input type="hidden" id="pro_suffix" value="<?php echo $suffix; ?>">
        <input type="hidden" id="is_pro" value="<?php echo $pro; ?>">
        <input type="hidden" id="flycart_wdr_woocommerce_version" value="<?php echo $flycart_wdr_woocommerce_version; ?>">
        <input type="hidden" name="wdr_nonce" value="<?php echo  FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_save_price_rule'); ?>">
        <input type="hidden" name="wdr_search_nonce" value="<?php echo  FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_ajax_search_field'); ?>">
    </form>
    <div class="woo_discount_loader">
        <div class="lds-ripple"><div></div><div></div></div>
    </div>
</div>
<?php include_once(WOO_DISCOUNT_DIR . '/view/includes/footer.php'); ?>
