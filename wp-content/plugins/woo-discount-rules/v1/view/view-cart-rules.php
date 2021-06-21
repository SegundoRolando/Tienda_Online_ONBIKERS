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
// Dummy Object.
$obj = new stdClass();

$data = (isset($config[0]) ? $config[0] : array());
$rule_id = (isset($data->ID)) ? $data->ID : 0;

$discounts = array();
$discount_rules = array();
if (isset($data->discount_rule)) {
    $discount_rules = (is_string($data->discount_rule) ? json_decode($data->discount_rule, true) : array('' => ''));
}
foreach ($discount_rules as $index => $rule) {
    foreach ($rule as $id => $value) {
        if(!in_array($id, array('product_variants'))){
            $discounts[$id] = $value;
        }
    }
}
$discount_rules = $discounts;
if (empty($discount_rules)) {
    $discount_rules = array(0 => '');
    $type = 'subtotal_least';
}
$flycartWooDiscountRulesPurchase = new FlycartWooDiscountRulesPurchase();
$isPro = $flycartWooDiscountRulesPurchase->isPro();
$woo_settings = new FlycartWooDiscountBase();
$current_date_and_time = FlycartWooDiscountRulesGeneralHelper::getCurrentDateAndTimeBasedOnTimeZone();
$has_large_no_of_coupon = FlycartWooDiscountBase::hasLargeNumberOfCoupon();
?>
    <div class="container-fluid woo_discount_loader_outer">
        <form id="form_cart_rule">
            <div class="row-fluid">
                <div class="<?php echo $isPro? 'col-md-12': 'col-md-9'; ?>">
                    <div class="col-md-12 rule_buttons_con" align="right">
                        <input type="submit" id="saveCartRule" value="<?php esc_html_e('Save Rule', 'woo-discount-rules'); ?>" class="btn btn-primary">
                        <a href="?page=woo_discount_rules&tab=cart-rules" class="btn btn-warning"><?php esc_html_e('Close and go back to list', 'woo-discount-rules'); ?></a>
                        <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('introduction/cart-discount-rules', 'cart_rules', 'btn btn-info'); ?>
                    </div>
                    <?php if ($rule_id == 0) { ?>
                        <div class="col-md-12"><h2><?php esc_html_e('New Cart Rule', 'woo-discount-rules'); ?></h2></div>
                    <?php } else { ?>
                        <div class="col-md-12"><h2><?php esc_html_e('Edit Cart Rule', 'woo-discount-rules'); ?>
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
                                <div class="col-md-3"><label><?php esc_html_e('Priority :', 'woo-discount-rules') ?> <i
                                                class="text-muted glyphicon glyphicon-exclamation-sign"
                                                title="<?php esc_attr_e('The Simple Ranking concept to said, which one is going to execute first and so on.', 'woo-discount-rules'); ?>"></i></label>
                                </div>
                                <div class="col-md-6"><input type="number" class="rule_order"
                                                             id="rule_order"
                                                             name="rule_order"
                                                             value="<?php echo(isset($data->rule_order) ? $data->rule_order : 1); ?>"
                                                             placeholder="<?php esc_attr_e('ex. 1', 'woo-discount-rules'); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Rule Name', 'woo-discount-rules'); ?> <i
                                                class="text-muted glyphicon glyphicon-exclamation-sign"
                                                title="<?php esc_attr_e('Rule Descriptions.', 'woo-discount-rules'); ?>"></i></label></div>
                                <div class="col-md-6"><input type="text" class="form-control rule_descr"
                                                             id="rule_name"
                                                             name="rule_name"
                                                             value="<?php echo(isset($data->rule_name) ? $data->rule_name : ''); ?>"
                                                             placeholder="<?php esc_attr_e('ex. Standard Rule.', 'woo-discount-rules'); ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Rule Description', 'woo-discount-rules'); ?> <i
                                                class="text-muted glyphicon glyphicon-exclamation-sign"
                                                title="<?php esc_attr_e('Rule Descriptions.', 'woo-discount-rules'); ?>"></i></label></div>
                                <div class="col-md-6"><input type="text" class="form-control rule_descr"
                                                             name="rule_descr"
                                                             value="<?php echo(isset($data->rule_descr) ? $data->rule_descr : ''); ?>"
                                                             id="rule_descr"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3"><label> <?php esc_html_e('Validity', 'woo-discount-rules'); ?>
                                        <span class="woocommerce-help-tip" data-tip="<?php esc_attr_e('Period of Rule Active. Format: month/day/Year Hour:Min', 'woo-discount-rules'); ?>"></label></div>
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
                                               placeholder="<?php esc_attr_e('To', 'woo-discount-rules'); ?>"></div>
                                    <span class="wdr_current_date_and_time_string"><?php echo sprintf(esc_html__('Current date and time: %s', 'woo-discount-rules'), date('m/d/Y H:i', strtotime($current_date_and_time))); ?></span>
                                </div>
                            </div>
                        </div>
                        <div align="right">
                            <input type="button" class="btn btn-success restriction_tab" value="<?php esc_attr_e('Next', 'woo-discount-rules'); ?>">
                        </div>
                    </div>

                    <div class="col-md-12 wdr_hide" id="restriction_block"><h4 class="text text-muted"> <?php esc_html_e('Cart Conditions', 'woo-discount-rules'); ?> </h4>
                        <a href=javascript:void(0) id="add_cart_rule" class="button button-primary"><i
                                    class="glyphicon glyphicon-plus"></i>
                            <?php esc_html_e('Add Condition', 'woo-discount-rules'); ?></a>
                        <hr>
                        <div class="form-group">
                            <div id="cart_rules_list">
                                <?php
                                $i = 0;
                                foreach ($discount_rules as $rule_type => $rule) {

                                    if (!empty($discount_rules)) {
                                        if (!isset($discount_rules[0])) {
                                            $type = $rule_type;
                                        }
                                    }
                                    // Dummy Entry for One Rule at starting.
                                    // Note : Must having at least one rule on starting.
                                    $rule = (!is_null($rule) ? $rule : array(0 => '1'));
                                    ?>
                                    <div class="cart_rules_list row">
                                        <div class="col-md-3 form-group">
                                            <label>
                                                <?php esc_html_e('Type', 'woo-discount-rules'); ?>
                                                <select class="form-control cart_rule_type"
                                                        id="cart_condition_type_<?php echo $i; ?>"
                                                        name="discount_rule[<?php echo $i; ?>][type]">
                                                    <optgroup label="<?php esc_attr_e('Cart Subtotal', 'woo-discount-rules'); ?>">
                                                        <option
                                                                value="subtotal_least"<?php if ($type == 'subtotal_least') { ?> selected=selected <?php } ?>>
                                                            <?php esc_html_e('Subtotal at least', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                                value="subtotal_less"<?php if ($type == 'subtotal_less') { ?> selected=selected <?php } ?>>
                                                            <?php esc_html_e('Subtotal less than', 'woo-discount-rules'); ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Cart Item Count', 'woo-discount-rules'); ?>">
                                                        <option
                                                                value="item_count_least"<?php if ($type == 'item_count_least') { ?> selected=selected <?php } ?>>
                                                            <?php esc_html_e('Number of line items in the cart (not quantity) at least', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                                value="item_count_less"<?php if ($type == 'item_count_less') { ?> selected=selected <?php } ?>>
                                                            <?php esc_html_e('Number of line items in the cart (not quantity) less than', 'woo-discount-rules'); ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Quantity Sum', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="quantity_least" <?php
                                                            }
                                                            if ($type == 'quantity_least') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Total number of quantities in the cart at least', 'woo-discount-rules'); ?>
                                                                <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Total number of quantities in the cart at least', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>

                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="quantity_less" <?php
                                                            }
                                                            if ($type == 'quantity_less') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Total number of quantities in the cart less than', 'woo-discount-rules'); ?>
                                                                <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Total number of quantities in the cart less than', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Products', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="products_in_list" <?php
                                                            } ?>
                                                            <?php if ($type == 'products_in_list') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('Products in cart', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="products_not_in_list" <?php
                                                            } ?>
                                                            <?php if ($type == 'products_not_in_list') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('Exclude products', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="exclude_sale_products" <?php
                                                            } ?>
                                                            <?php if ($type == 'exclude_sale_products') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('Exclude on sale products', 'woo-discount-rules'); ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Categories In Cart', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="categories_in" <?php
                                                            } ?>
                                                            <?php if ($type == 'categories_in') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('Categories in cart', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="atleast_one_including_sub_categories" <?php
                                                            } ?>
                                                            <?php if ($type == 'atleast_one_including_sub_categories') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('Including sub-categories in cart', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="in_each_category" <?php
                                                            } ?>
                                                            <?php if ($type == 'in_each_category') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('In each category', 'woo-discount-rules'); ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="exclude_categories" <?php
                                                            } ?>
                                                            <?php if ($type == 'exclude_categories') { ?> selected="selected"
                                                            <?php } ?>><?php esc_html_e('Exclude categories in cart', 'woo-discount-rules'); ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Customer Details (must be logged in)', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="users_in" <?php
                                                            }
                                                            if ($type == 'users_in') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('User in list', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('User in list', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="roles_in" <?php
                                                            }
                                                            if ($type == 'roles_in') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('User role in list', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('User role in list', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Customer Email', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_email_tld" <?php
                                                            }
                                                            if ($type == 'customer_email_tld') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Email with TLD (Eg: edu)', 'woo-discount-rules'); ?><b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Email with TLD (Eg: edu)', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_email_domain" <?php
                                                            }
                                                            if ($type == 'customer_email_domain') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Email with Domain (Eg: gmail.com)', 'woo-discount-rules'); ?><b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Email with Domain (Eg: gmail.com)', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Customer Billing Details', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_billing_city" <?php
                                                            }
                                                            if ($type == 'customer_billing_city') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Billing city', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Billing city', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Customer Shipping Details', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_shipping_city" <?php
                                                            }
                                                            if ($type == 'customer_shipping_city') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Shipping city', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Shipping city', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_shipping_state" <?php
                                                            }
                                                            if ($type == 'customer_shipping_state') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Shipping state', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Shipping state', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="shipping_countries_in" <?php
                                                            }
                                                            if ($type == 'shipping_countries_in') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Shipping country in list', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Shipping country in list', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_shipping_zip_code" <?php
                                                            }
                                                            if ($type == 'customer_shipping_zip_code') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Shipping zip code', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Shipping zip code', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Purchase History', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_based_on_first_order" <?php
                                                            }
                                                            if ($type == 'customer_based_on_first_order') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('First Order discount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('First Order discount', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_based_on_purchase_history" <?php
                                                            }
                                                            if ($type == 'customer_based_on_purchase_history') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Purchased amount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Purchased amount', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_based_on_purchase_history_order_count" <?php
                                                            }
                                                            if ($type == 'customer_based_on_purchase_history_order_count') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Number of previous orders made', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Number of previous orders made', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_based_on_purchase_history_product_order_count" <?php
                                                            }
                                                            if ($type == 'customer_based_on_purchase_history_product_order_count') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Number of previous orders made with following products', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Number of previous orders made with following products', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="customer_based_on_purchase_history_product_quantity_count" <?php
                                                            }
                                                            if ($type == 'customer_based_on_purchase_history_product_quantity_count') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Number of quantity(s) in previous orders made with following products', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Number of quantity(s) in previous orders made with following products', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                    <optgroup label="<?php esc_attr_e('Coupon applied', 'woo-discount-rules'); ?>">
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="create_dynamic_coupon" <?php
                                                            }
                                                            if ($type == 'create_dynamic_coupon') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Create your own coupon', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Create your own coupon', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="coupon_applied_any_one" <?php
                                                            }
                                                            if ($type == 'coupon_applied_any_one') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('Atleast any one (Select from WooCommerce)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('Atleast any one (Select from WooCommerce)', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                        <option
                                                            <?php if (!$pro) { ?> disabled <?php } else { ?> value="coupon_applied_all_selected" <?php
                                                            }
                                                            if ($type == 'coupon_applied_all_selected') { ?> selected=selected <?php } ?>>
                                                            <?php if (!$pro) { ?>
                                                                <?php esc_html_e('All selected (Select from WooCommerce)', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                            <?php } else { ?>
                                                                <?php esc_html_e('All selected (Select from WooCommerce)', 'woo-discount-rules'); ?>
                                                            <?php } ?>
                                                        </option>
                                                    </optgroup>
                                                </select>
                                            </label>
                                            <div class="wdr_cart_rule_doc_con">
                                            </div>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>
                                                <?php
                                                $inline_attr = '';
                                                if(in_array($type, array('exclude_sale_products', 'customer_based_on_first_order'))){
                                                    $inline_attr = ' style="display:none"';
                                                }
                                                ?>
                                                <span class="value_text_<?php echo $i; ?>"<?php echo $inline_attr; ?>><?php esc_html_e('Value', 'woo-discount-rules'); ?></span>
                                                <?php
                                                $users_list = array();
                                                $class = 'style="display:none"';
                                                $hit = false;
                                                if ($type == 'users_in') {
                                                    $users_list = $discount_rules[$type];
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                }
                                                ?>
                                                <div id="user_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <?php
                                                    echo FlycartWoocommerceProduct::getUserAjaxSelectBox($users_list, "discount_rule[".$i."][users_to_apply]");
                                                    ?>
                                                </div>
                                                <?php
                                                $category_list = array();
                                                $class = 'style="display:none"';
                                                if (in_array($type, array('categories_atleast_one', 'categories_in', 'in_each_category', 'atleast_one_including_sub_categories', 'exclude_categories'))) {
                                                    $category_list = $discount_rules[$type];
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                }
                                                ?>
                                                <div id="category_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <select class="category_list selectpicker"
                                                            id="cart_category_list_<?php echo $i; ?>"
                                                            multiple
                                                            title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                            name="discount_rule[<?php echo $i; ?>][category_to_apply][]">
                                                        <?php foreach ($category as $index => $cat) { ?>
                                                            <option
                                                                    value="<?php echo $index; ?>"<?php if (in_array($index, $category_list)) { ?> selected=selected <?php } ?>><?php echo $cat; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php
                                                $class = 'style="display:none"';
                                                $products_list = array();
                                                if (in_array($type, array('products_in_list', 'products_not_in_list'))) {
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                    $products_list = isset($discount_rules[$type])? $discount_rules[$type] : array();
                                                    if(isset($products_list)){
                                                        if(!is_array($products_list)){
                                                            $products_list = json_decode((isset($products_list) ? $products_list : '{}'), true);
                                                        }
                                                    } else {
                                                        $products_list = array();
                                                    }
                                                } ?>
                                                <div id="products_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <div class="form-group" id="products_list_<?php echo $i; ?>">
                                                        <?php
                                                        echo FlycartWoocommerceProduct::getProductAjaxSelectBox($products_list, 'discount_rule['.$i.'][products]');
                                                        ?>
                                                    </div>
                                                </div>
                                                <?php
                                                $class = 'style="display:none"';
                                                if ($type == 'exclude_sale_products') {
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                } ?>
                                                <div id="exclude_on_sale_products_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <?php
                                                    echo esc_html__('This will exclude the on sale products from discount', 'woo-discount-rules');
                                                    ?>
                                                </div>
                                                <?php
                                                $coupon_list = array();
                                                $class = 'style="display:none"';
                                                if (in_array($type, array('coupon_applied_any_one', 'coupon_applied_all_selected'))) {
                                                    $coupon_list = $discount_rules[$type];
                                                    if(!empty($coupon_list)){
                                                        if(is_string($coupon_list)) $coupon_list = explode(',', $coupon_list);
                                                    } else {
                                                        $coupon_list = array();
                                                    }
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                }
                                                ?>
                                                <div id="coupon_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <?php
                                                    if($has_large_no_of_coupon){
                                                        ?>
                                                        <select class="coupons_selectbox_multi_select_wdr" multiple id="cart_coupon_list_<?php echo $i; ?>" name="discount_rule[<?php echo $i; ?>][coupon_to_apply][]">
                                                            <?php echo FlycartWooDiscountBase::loadSelectedCouponOptions($coupon_list); ?>
                                                        </select>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <select class="coupon_list selectpicker"
                                                                id="cart_coupon_list_<?php echo $i; ?>"
                                                                multiple
                                                                title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                                name="discount_rule[<?php echo $i; ?>][coupon_to_apply][]">
                                                            <?php foreach ($coupons as $coupon_code => $coupon_name) { ?>
                                                                <option
                                                                        value="<?php echo $coupon_code; ?>"<?php if (in_array($coupon_code, $coupon_list)) { ?> selected=selected <?php } ?>><?php echo $coupon_name; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                                $roles_list = array();
                                                $class = 'style="display:none"';
                                                if ($type == 'roles_in') {
                                                    $roles_list = $discount_rules[$type];
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                } ?>
                                                <div id="roles_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <select class="roles_list selectpicker"
                                                            id="cart_roles_list_<?php echo $i; ?>" multiple
                                                            title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                            name="discount_rule[<?php echo $i; ?>][user_roles_to_apply][]">
                                                        <?php foreach ($userRoles as $index => $user) { ?>
                                                            <option
                                                                    value="<?php echo $index; ?>"<?php if (in_array($index, $roles_list)) { ?> selected=selected <?php } ?>><?php echo $user; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php
                                                $countries_list = array();
                                                $class = 'style="display:none"';
                                                if ($type == 'shipping_countries_in') {
                                                    $countries_list = $discount_rules[$type];
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                } ?>
                                                <div id="countries_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <select class="country_list selectpicker"
                                                            data-live-search="true"
                                                            id="cart_countries_list_<?php echo $i; ?>"
                                                            multiple
                                                            title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                            name="discount_rule[<?php echo $i; ?>][countries_to_apply][]">
                                                        <?php foreach ($countries as $index => $country) { ?>
                                                            <option
                                                                    value="<?php echo $index; ?>"<?php if (in_array($index, $countries_list)) { ?> selected=selected <?php } ?>><?php echo $country; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php
                                                $order_status_list = array();
                                                $class = 'style="display:none"';
                                                $woocommerce_order_status = wc_get_order_statuses();
                                                $purchased_history_amount = $purchased_history_duration_days = '';
                                                $purchased_history_type = 'atleast';
                                                $purchased_history_duration = 'all_time';
                                                $purchase_history_status_list = $product_purchase_history_list = array();
                                                if ($type == 'customer_based_on_first_order'){
                                                    $hit = true;
                                                }
                                                if ($type == 'customer_based_on_purchase_history' || $type == 'customer_based_on_purchase_history_order_count' || $type == 'customer_based_on_purchase_history_product_order_count' || $type == 'customer_based_on_purchase_history_product_quantity_count') {
                                                    $purchase_history_status_list = isset($discount_rules[$type]['purchase_history_order_status'])? $discount_rules[$type]['purchase_history_order_status'] : array();
                                                    $purchased_history_amount = isset($discount_rules[$type]['purchased_history_amount'])? $discount_rules[$type]['purchased_history_amount'] : 0;
                                                    $purchased_history_type = isset($discount_rules[$type]['purchased_history_type'])? $discount_rules[$type]['purchased_history_type'] : 'atleast';
                                                    $purchased_history_duration = isset($discount_rules[$type]['purchased_history_duration'])? $discount_rules[$type]['purchased_history_duration'] : 'all_time';
                                                    $purchased_history_duration_days = isset($discount_rules[$type]['purchased_history_duration_days'])? $discount_rules[$type]['purchased_history_duration_days'] : '';
                                                    if(empty($purchase_history_status_list)){
                                                        $purchase_history_status_list[] = 'wc-completed';
                                                    }
                                                    $class = 'style="display:block"';
                                                    $hit = true;
                                                    $purchase_history_products = isset($discount_rules[$type]['purchase_history_products'])? $discount_rules[$type]['purchase_history_products'] : array();
                                                    if(isset($purchase_history_products)){
                                                        if(is_array($purchase_history_products))
                                                            $product_purchase_history_list = $purchase_history_products;
                                                        else
                                                            $product_purchase_history_list = json_decode((isset($purchase_history_products) ? $purchase_history_products : '{}'), true);
                                                    } else {
                                                        $product_purchase_history_list = array();
                                                    }
                                                } ?>
                                                <div id="purchase_history_div_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <div class="form-group<?php echo (in_array($type, array('customer_based_on_purchase_history_product_order_count', 'customer_based_on_purchase_history_product_quantity_count')))? '': ' wdr_hide';?>" id="purchase_history_products_list_<?php echo $i; ?>">
                                                        <?php
                                                        echo FlycartWoocommerceProduct::getProductAjaxSelectBox($product_purchase_history_list, 'discount_rule['.$i.'][purchase_history_products]');
                                                        ?>
                                                    </div>
                                                    <select class="selectpicker purchased_history_type" name="discount_rule[<?php echo $i; ?>][purchased_history_type]">
                                                        <option value="atleast"<?php echo ($purchased_history_type == 'atleast')? ' selected="selected"': ''; ?>><?php esc_html_e('Greater than or equal to', 'woo-discount-rules'); ?></option>
                                                        <option value="less_than_or_equal"<?php echo ($purchased_history_type == 'less_than_or_equal')? ' selected="selected"': ''; ?>><?php esc_html_e('Less than or equal to', 'woo-discount-rules'); ?></option>
                                                    </select>
                                                    <input name="discount_rule[<?php echo $i; ?>][purchased_history_amount]" value="<?php echo $purchased_history_amount; ?>" type="text"/> <?php esc_html_e('and the order status should be', 'woo-discount-rules'); ?>
                                                    <select class="order_status_list selectpicker"
                                                            data-live-search="true"
                                                            id="order_status_list_<?php echo $i; ?>"
                                                            multiple
                                                            title="<?php esc_html_e('None selected', 'woo-discount-rules'); ?>"
                                                            name="discount_rule[<?php echo $i; ?>][purchase_history_order_status][]">
                                                        <?php foreach ($woocommerce_order_status as $index => $woocommerce_order_sts) { ?>
                                                            <option
                                                                    value="<?php echo $index; ?>"<?php if (in_array($index, $purchase_history_status_list)) { ?> selected=selected <?php } ?>><?php echo $woocommerce_order_sts; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <select class="selectpicker purchased_history_duration" data-index="<?php echo $i; ?>" name="discount_rule[<?php echo $i; ?>][purchased_history_duration]">
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
                                                    <span class="purchased_history_duration_days_con" id="purchased_history_duration_days_con_<?php echo $i; ?>">
                                                        <input name="discount_rule[<?php echo $i; ?>][purchased_history_duration_days]" value="<?php echo $purchased_history_duration_days; ?>" placeholder="30" type="text"/> <?php esc_html_e('in days', 'woo-discount-rules'); ?>
                                                    </span>
                                                </div>
                                                <?php
                                                if ($hit) {
                                                    $class = 'style="display:none"';
                                                } else {
                                                    $class = 'style="display:block"';
                                                }
                                                $dynamic_coupon_already_exists = false;
                                                if($type == 'create_dynamic_coupon'){
                                                    if(!empty($discount_rules[$type])){
                                                        $dynamic_coupon_already_exists = FlycartWooDiscountRulesGeneralHelper::checkCouponAlreadyExistsInWooCommerce($discount_rules[$type]);
                                                    }
                                                }
                                                ?>
                                                <div id="general_<?php echo $i; ?>" <?php echo $class; ?>>
                                                    <input type="text"
                                                           class="form-control<?php echo ($dynamic_coupon_already_exists === true)? ' wdr_invalid': ''; ?>" value="<?php echo(isset($discount_rules[$type]) && !is_array($discount_rules[$type]) ? $discount_rules[$type] : ''); ?>"
                                                           name="discount_rule[<?php echo $i; ?>][option_value]">
                                                </div>
                                            </label>
                                            <div class="notice inline notice-alt notice-warning cart_rule_validation_error wdr_validation_notice" <?php echo ($dynamic_coupon_already_exists === true)? 'style="display:block;"': ''; ?>>
                                                <?php echo ($dynamic_coupon_already_exists === true)? "<p>".esc_html__('Coupon already exists in WooCommerce. Please select another name', 'woo-discount-rules')."</p>": ''; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-1"><label> <?php esc_html_e('Action', 'woo-discount-rules'); ?> </label><br>
                                            <a href=javascript:void(0) class="btn btn-danger remove_cart_rule"><?php esc_html_e('Remove', 'woo-discount-rules'); ?></a>
                                        </div>
                                    </div>
                                    <?php
                                    $i++;
                                }
                                ?>
                            </div>
                            <?php $show_promotion_messages = $woo_settings->getConfigData('show_promotion_messages', array());
                            if((!empty($show_promotion_messages)) || apply_filters('woo_discount_rules_load_promotion_messages_manually', false)){
                                ?>
                                <div class="show_promotion_message_cart_block">
                                    <h4><?php esc_html_e('Promotion message', 'woo-discount-rules'); ?></h4>
                                    <div class="show_promotion_message_fields_con">
                                        <div class="form-group">
                                            <div class="row promotion_subtotal_from_con">
                                                <div class="col-md-3">
                                                    <label> <?php esc_html_e('Subtotal from', 'woo-discount-rules'); ?> </label>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <?php $promotion_subtotal_from = (isset($data->promotion_subtotal_from))? $data->promotion_subtotal_from : '' ?>
                                                        <input class="form-control" type="text" name="promotion_subtotal_from" id="promotion_subtotal_from" value="<?php echo $promotion_subtotal_from; ?>" />
                                                        <span class="wdr_desc_text"><?php echo __('Set a threshold from which you want to start showing the promotion message<br>Example:  Let\'s say, you offer a 10% discount for $1000 and above. You may want to set 900 here. So that the customer can see the promo text when his cart subtotal reaches 900','woo-discount-rules'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label> <?php esc_html_e('Message', 'woo-discount-rules'); ?> </label>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <?php $promotion_message = (isset($data->promotion_message))? $data->promotion_message : '' ?>
                                                        <textarea class="form-control" name="promotion_message" id="promotion_message" placeholder="<?php esc_html_e('Spend {{difference_amount}} more and get 10% discount', 'woo-discount-rules'); ?>"><?php echo $promotion_message; ?></textarea>
                                                        <span class="wdr_desc_text">
                                                            <?php esc_html_e('{{difference_amount}} -> Difference amount to get discount', 'woo-discount-rules'); ?><br>
                                                            <?php _e('<b>Eg:</b> Spend {{difference_amount}} more and get 10% discount', 'woo-discount-rules'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                        </div>
                        <div align="right">
                            <input type="button" class="btn btn-warning general_tab" value="<?php esc_attr_e('Previous', 'woo-discount-rules'); ?>">
                            <input type="button" class="btn btn-success discount_tab" value="<?php esc_attr_e('Next', 'woo-discount-rules'); ?>">
                        </div>
                        <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('introduction/cart-discount-rules', 'cart_rules', 'btn btn-info cart_rules_guide_btn', esc_html__('Guide to create cart rules', 'woo-discount-rules')); ?>
                    </div>

                    <!-- TODO: Implement ForEach Concept -->
                    <div class="col-md-12 wdr_hide" id="discount_block"><h4 class="text text-muted"> <?php esc_html_e('Discount', 'woo-discount-rules'); ?></h4>
                        <?php
                        $discount_type = 'percentage_discount';
                        $to_discount = 0;
                        if (isset($data)) {
                            if (isset($data->discount_type)) {
                                $discount_type = $data->discount_type;
                            }
                            if (isset($data->to_discount)) {
                                $to_discount = $data->to_discount;
                            }
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label> <?php esc_html_e('Discount Type :', 'woo-discount-rules'); ?>
                                        <select class="form-control" id="cart_rule_discount_type" name="discount_type">
                                            <option
                                                    value="percentage_discount" <?php if ($discount_type == 'percentage_discount') { ?> selected=selected <?php } ?>>
                                                <?php esc_html_e('Percentage Discount', 'woo-discount-rules'); ?>
                                            </option>
                                            <option
                                                <?php if (!$pro) { ?> disabled <?php } else { ?> value="price_discount" <?php }
                                                if ($discount_type == 'price_discount') { ?> selected=selected <?php } ?>>
                                                <?php if (!$pro) { ?>
                                                    <?php esc_html_e('Price Discount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                <?php } else { ?>
                                                    <?php esc_html_e('Price Discount', 'woo-discount-rules'); ?>
                                                <?php } ?>
                                            </option>
                                            <option
                                                <?php if (!$pro) { ?> disabled <?php } else { ?> value="product_discount" <?php }
                                                if ($discount_type == 'product_discount') { ?> selected=selected <?php } ?>>
                                                <?php if (!$pro) { ?>
                                                    <?php esc_html_e('Product Discount', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                <?php } else { ?>
                                                    <?php esc_html_e('Product Discount', 'woo-discount-rules'); ?>
                                                <?php } ?>
                                            </option>
                                            <option
                                                <?php if (!$pro) { ?> disabled <?php } else { ?> value="shipping_price" <?php }
                                                if ($discount_type == 'shipping_price') { ?> selected=selected <?php } ?>>
                                                <?php if (!$pro) { ?>
                                                    <?php esc_html_e('Free shipping', 'woo-discount-rules'); ?> <b><?php echo $suffix; ?></b>
                                                <?php } else { ?>
                                                    <?php esc_html_e('Free shipping', 'woo-discount-rules'); ?>
                                                <?php } ?>
                                            </option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2" id="cart_rule_discount_value_con" >
                                <div class="form-group">
                                    <label> <?php esc_html_e('value :', 'woo-discount-rules'); ?>
                                        <input type="text" name="to_discount" class="form-control"
                                               value="<?php echo $to_discount; ?>">
                                    </label>
                                </div>
                            </div>
                            <div id="cart_rule_product_discount_field" class="col-md-10">
                                <div class="form-group">
                                    <label> <?php esc_html_e('Select products :', 'woo-discount-rules'); ?></label>
                                    <br>
                                    <?php
                                    $discounted_product_list = array();
                                    if(isset($data->cart_discounted_products)){
                                        if(is_array($data->product_to_discount))
                                            $discounted_product_list = $data->cart_discounted_products;
                                        else
                                            $discounted_product_list = json_decode((isset($data->cart_discounted_products) ? $data->cart_discounted_products : '{}'), true);
                                    }
                                    echo FlycartWoocommerceProduct::getProductAjaxSelectBox($discounted_product_list, 'cart_discounted_products');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <label for="product_discount_quantity"> <?php esc_html_e('Quantity :', 'woo-discount-rules'); ?></label>
                                    <?php
                                    $product_discount_quantity = 1;
                                    if (isset($data->product_discount_quantity)) {
                                        $product_discount_quantity = $data->product_discount_quantity;
                                    }
                                    ?>
                                    <input type="number" min="1" name="product_discount_quantity" id="product_discount_quantity" class="small-text"
                                           value="<?php echo $product_discount_quantity; ?>">
                                </div>
                            </div>
                        </div>
                        <div align="right">
                            <input type="button" class="btn btn-warning restriction_tab" value="<?php esc_attr_e('Previous', 'woo-discount-rules'); ?>">
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!$isPro){ ?>
                <!-- Sidebar -->
                <?php include_once(__DIR__ . '/template/sidebar.php'); ?>
                <!-- Sidebar END -->
            <?php } ?>
            <input type="hidden" name="has_large_number_of_coupon" id="has_large_number_of_coupon" value="<?php echo $has_large_no_of_coupon; ?>">
            <input type="hidden" name="rule_id" id="rule_id" value="<?php echo $rule_id; ?>">
            <input type="hidden" name="form" value="<?php echo $form; ?>">
            <input type="hidden" id="ajax_path" value="<?php echo admin_url('admin-ajax.php'); ?>">
            <input type="hidden" id="admin_path" value="<?php echo admin_url('admin.php?page=woo_discount_rules'); ?>">
            <input type="hidden" id="pro_suffix" value="<?php echo $suffix; ?>">
            <input type="hidden" id="is_pro" value="<?php echo $pro; ?>">
            <input type="hidden" id="flycart_wdr_woocommerce_version" value="<?php echo $flycart_wdr_woocommerce_version; ?>">
            <input type="hidden" name="wdr_nonce" value="<?php echo  FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_save_cart_rule'); ?>">
            <input type="hidden" name="wdr_search_nonce" value="<?php echo  FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_ajax_search_field'); ?>">
        </form>
        <div class="woo_discount_loader">
            <div class="lds-ripple"><div></div><div></div></div>
        </div>
    </div>

<?php include_once(WOO_DISCOUNT_DIR . '/view/includes/footer.php'); ?>