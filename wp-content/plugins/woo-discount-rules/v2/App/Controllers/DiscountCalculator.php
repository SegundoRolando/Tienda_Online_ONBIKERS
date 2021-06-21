<?php

namespace Wdr\App\Controllers;

use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Rule;
use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DiscountCalculator extends Base
{
    public static $original_price_of_product = array(), $filtered_exclusive_rule = false, $rules, $applied_rules = array(), $total_discounts = array(), $cart_adjustments = array(), $price_discount_apply_as_cart_discount = array(), $tax_display_type = NULL;
    public $is_cart = false;

    /**
     * Initialize the cart calculator with rule list
     * @param $rules
     */
    function __construct($rules)
    {
        parent::__construct();
        self::$rules = $rules;
    }

    /**
     * calculate price of product
     * @param $product
     * @param $quantity
     * @param bool $is_cart
     * @param bool $ajax_price
     * @return array|bool
     */
    function getProductPriceToDisplay($product, $quantity, $is_cart = false, $ajax_price = false, $cart_item = array())
    {

        $this->is_cart = $is_cart;
        if (!is_a($product, 'WC_Product')) {
            if (is_integer($product)) {
                $product = self::$woocommerce_helper->getProduct($product);
            } else {
                return false;
            }
        }
        if (!$product) {
            return false;
        }
        return $this->mayApplyPriceDiscount($product, $quantity, $custom_price = 0, $ajax_price, $cart_item, $is_cart);
    }

    /**
     * get default layout messages by rules to display discount table
     * @param $product
     * @param $get_variable_product_table
     * @return array
     */
    function getDefaultLayoutMessagesByRules($product, $get_variable_product_table = false)
    {
        $response_ranges = array();
        if ((!empty(self::$rules) && !empty($product))) {
            $calculate_discount_from = self::$config->getConfig('calculate_discount_from', 'sale_price');
            $language_helper_object = self::$language_helper;
            $discount_calculator = $this;
            foreach (self::$rules as $rule) {
                if (!$rule->isEnabled()) {
                    continue;
                }
                $rule_id = $rule->getId();
                $hide_bulk_table = apply_filters('advanced_woo_discount_rules_hide_specific_rules_in_bulk_table', false, $rule_id, $rule, $product);
                if ($hide_bulk_table) {
                    continue;
                }
                $chosen_languages = $rule->getLanguages();
                if (!empty($chosen_languages)) {
                    $current_language = $language_helper_object::getCurrentLanguage();
                    if (!in_array($current_language, $chosen_languages)) {
                        continue;
                    }
                }
                $hasFilter = $rule->hasFilter();
                $filters = $rule->getFilter();
                $rule_id = $rule->getId();
                $has_bulk_discount = $rule->hasBulkDiscount();
                if(Woocommerce::displayTableIfAnyOneVariantHasDiscount() === false){
                    $is_filter_passed = $rule->isFilterPassed($product, false, true);
                } else {
                    $is_filter_passed = $rule->isFilterPassed($product, true);
                }

                $is_variable_product = Woocommerce::productTypeIs($product, array('variable', 'variable-subscription'));
                $product_price = $this->getProductPriceFromConfig($product, $calculate_discount_from, $is_variable_product);
                $product_price = apply_filters('advanced_woo_discount_rules_bulk_table_product_price', $product_price, $product, $calculate_discount_from, $hasFilter, $filters);
                if ($has_bulk_discount) {
                    if ($is_filter_passed) {
                        $bulk_adjustments = $rule->getBulkAdjustments();
                        if (isset($bulk_adjustments) && !empty($bulk_adjustments) && isset($bulk_adjustments->ranges) && !empty($bulk_adjustments->ranges)) {
                            foreach ($bulk_adjustments->ranges as $range) {
                                if (isset($range->value) && $range->value != '') {
                                    $discount_type = (isset($range->type) && !empty($range->type)) ? $range->type : 0;
                                    $from = intval((isset($range->from) && !empty($range->from)) ? $range->from : 0);
                                    $to = intval((isset($range->to) && !empty($range->to)) ? $range->to : 0);
                                    if ((empty($to) && empty($from)) || empty($discount_type)) {
                                        continue;
                                    } else {
                                        $discount_price = $rule->calculator($discount_type, $product_price, $range->value);
                                        if( $calculate_discount_from != 'sale_price' && Woocommerce::productTypeIs($product, array('variable')) && $discount_type == 'flat'){
                                            $discount_price = $this->mayHaveTax($product, $discount_price);
                                        }
                                        $discounted_price = floatval($product_price) - floatval($discount_price);
                                        if ($discounted_price < 0) {
                                            $discounted_price = 0;
                                        }

                                        if($calculate_discount_from != 'sale_price' && Woocommerce::productTypeIs($product, array('variable'))) {
                                            /*Process lowest regular price of variant*/
                                            if($discount_type == 'fixed_price'){
                                                $discounted_price = $this->mayHaveTax($product, $discounted_price);
                                            }
                                        }else{
                                            $discounted_price = $this->mayHaveTax($product, $discounted_price);
                                        }
                                        $rule_title = isset($range->label) && !empty($range->label) ? $range->label : $rule->getTitle();
                                        $discount_value = $range->value;
                                        $discount_method = 'bulk';
                                        $this->defaultLayoutRowDataFormation($response_ranges, $from, $to, $rule_id, $discount_method, $discount_type, $discount_value, $discount_price, $discounted_price, $rule_title);
                                    }
                                }
                            }
                        }
                    }
                }
                $response_ranges = apply_filters('advanced_woo_discount_rules_bulk_table_range_based_on_rule', $response_ranges, $rule, $discount_calculator, $product, $product_price);
            }
        }
        $response_ranges = apply_filters('advanced_woo_discount_rules_bulk_table_ranges', $response_ranges, self::$rules, $product);
        if (!empty($response_ranges)) {
            $response_ranges['layout']['type'] = 'default';
        }
        return $response_ranges;
    }

    /**
     * get Product Price From Configuration
     * @param $product
     * @param $calculate_discount_from
     * @param $is_variable_product
     * @return bool
     */
    function getProductPriceFromConfig($product, $calculate_discount_from, $is_variable_product){
        $product_price = 0;
        if ($calculate_discount_from == 'regular_price') {
            $product_price = self::$woocommerce_helper->getProductRegularPrice($product);
            if (empty($product_price) && $is_variable_product) {
                $product_price = self::$woocommerce_helper->get_variation_regular_price($product, 'min');
            }
            if(empty($product_price)){
                $product_price = self::$woocommerce_helper->getProductPrice($product);
            }
        } else {
            $product_price = self::$woocommerce_helper->getProductPrice($product);
        }
        return $product_price;
    }

    /**
     * @param $response_ranges
     * @param $from
     * @param $to
     * @param $rule_id
     * @param $discount_method
     * @param $discount_type
     * @param $discount_value
     * @param $discount_price
     * @param $discounted_price
     * @param $rule_title
     * @param $conditions
     */
    function defaultLayoutRowDataFormation(&$response_ranges, $from, $to, $rule_id, $discount_method, $discount_type, $discount_value, $discount_price, $discounted_price, $rule_title)
    {
        $response_ranges[] = array(
            'from' => $from,
            'to' => $to,
            'rule_id' => $rule_id,
            'discount_method' => $discount_method,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'discount_price' => $discount_price,
            'discounted_price' => $discounted_price,
            'rule_title' => __($rule_title, 'woo-discount-rules')
        );
    }

    /**
     * get default layout messages by rules to display discount table
     * @param $product
     * @return array
     */
    function getAdvancedLayoutMessagesByRules($product)
    {
        $advanced_layout = array();
        if (!empty(self::$rules) && !empty($product)) {
            $calculate_discount_from = self::$config->getConfig('calculate_discount_from', 'sale_price');
            if ($calculate_discount_from == 'regular_price') {
                $product_price = self::$woocommerce_helper->getProductRegularPrice($product);
                if (empty($product_price)) {
                    $product_price = self::$woocommerce_helper->getProductPrice($product);
                }
            } else {
                $product_price = self::$woocommerce_helper->getProductPrice($product);
            }
            if(empty($product_price)){
                $product_price = 0;
            }
            $language_helper_object = self::$language_helper;
            $discount_calculator = $this;
            foreach (self::$rules as $rule) {
                if (!$rule->isEnabled()) {
                    continue;
                }
                $discounted_title_text = $rule->getTitle();
                $chosen_languages = $rule->getLanguages();
                if (!empty($chosen_languages)) {
                    $current_language = $language_helper_object::getCurrentLanguage();
                    if (!in_array($current_language, $chosen_languages)) {
                        continue;
                    }
                }
                $has_product_discount = $rule->hasProductDiscount();
                $has_bulk_discount = $rule->hasBulkDiscount();
                $has_cart_discount = $rule->hasCartDiscount();
                $skip_rule = $rule->getAdvancedDiscountMessage('display', 0);
                $discount_type = $rule->getRuleDiscountType();
                if (empty($skip_rule)) {
                    continue;
                }
                $html_content = $rule->getAdvancedDiscountMessage('badge_text');
                //if ($has_product_discount || $has_bulk_discount || $has_set_discount || $has_cart_discount) {
                if ($rule->isFilterPassed($product, true) && !empty($html_content)) {
                    if ($has_product_discount) {
                        $product_adjustments = $rule->getProductAdjustments();
                        if (is_object($product_adjustments) && !empty($product_adjustments) && !empty($product_adjustments->value)) {
                            $discount_method = "product_discount";
                            $discount_price = $rule->calculator($product_adjustments->type, $product_price, $product_adjustments->value);
                            $value = (isset($product_adjustments->value) && !empty($product_adjustments->value)) ? $product_adjustments->value : 0;
                            $badge_bg_color = $rule->getAdvancedDiscountMessage('badge_color_picker', '#ffffff');
                            $badge_text_color = $rule->getAdvancedDiscountMessage('badge_text_color_picker', '#000000');
                            $this->advancedLayoutTextFormation($advanced_layout, $rule, $product_adjustments->type, $discount_method, $product_price, $value, $discount_price, $discounted_title_text, $html_content, $badge_bg_color, $badge_text_color);
                        }
                    }
                    if ($has_cart_discount) {
                        $cart_discount = $rule->getCartAdjustments();
                        if (!empty($cart_discount)) {
                            if (is_object($cart_discount) && !empty($cart_discount) && !empty($cart_discount->value)) {
                                $discount_method = "cart_discount";
                                $discount_price = $rule->calculator($cart_discount->type, $product_price, $cart_discount->value);
                                $value = (isset($cart_discount->value) && !empty($cart_discount->value)) ? $cart_discount->value : 0;
                                $badge_bg_color = $rule->getAdvancedDiscountMessage('badge_color_picker', '#ffffff');
                                $badge_text_color = $rule->getAdvancedDiscountMessage('badge_text_color_picker', '#000000');
                                $this->advancedLayoutTextFormation($advanced_layout, $rule, $cart_discount->type, $discount_method, $product_price, $value, $discount_price, $discounted_title_text, $html_content, $badge_bg_color, $badge_text_color);
                            }
                        }
                    }
                    if ($has_bulk_discount) {
                        $bulk_adjustments = $rule->getBulkAdjustments();
                        if (isset($bulk_adjustments) && is_object($bulk_adjustments) && !empty($bulk_adjustments) && isset($bulk_adjustments->ranges) && !empty($bulk_adjustments->ranges)) {
                            foreach ($bulk_adjustments->ranges as $range) {
                                if (isset($range->value) && !empty($range->value)) {
                                    $min = intval(isset($range->from) ? $range->from : 0);
                                    $max = intval(isset($range->to) ? $range->to : 0);
                                    if (empty($min) && empty($max)) {
                                        continue;
                                    } else {
                                        $discount_method = "bulk_discount";
                                        $discount_type = isset($range->type)? $range->type: 'percentage';
                                        $discount_price = $rule->calculator($discount_type, $product_price, $range->value);
                                        $value = (isset($range->value) && !empty($range->value)) ? $range->value : 0;
                                        $badge_bg_color = $rule->getAdvancedDiscountMessage('badge_color_picker', '#ffffff');
                                        $badge_text_color = $rule->getAdvancedDiscountMessage('badge_text_color_picker', '#000000');
                                        $this->advancedLayoutTextFormation($advanced_layout, $rule, $discount_type, $discount_method, $product_price, $value, $discount_price, $discounted_title_text, $html_content, $badge_bg_color, $badge_text_color, $min, $max);
                                    }
                                }
                            }
                        }
                    }
                    if($discount_type == 'wdr_free_shipping' || $discount_type == 'wdr_buy_x_get_x_discount'){
                        $discount_method = "free_shipping";
                        $badge_bg_color = $rule->getAdvancedDiscountMessage('badge_color_picker', '#ffffff');
                        $badge_text_color = $rule->getAdvancedDiscountMessage('badge_text_color_picker', '#000000');
                        $this->advancedLayoutTextFormation($advanced_layout, $rule, 'free_shipping', $discount_method, $product_price, '0', '0', $discounted_title_text, $html_content, $badge_bg_color, $badge_text_color, 0, 0);
                    }
                }
                //}
                $advanced_layout = apply_filters('advanced_woo_discount_rules_advance_table_based_on_rule', $advanced_layout, $rule, $discount_calculator, $product, $product_price, $html_content);
            }
        }
        if (!empty($advanced_layout)) {
            $advanced_layout['layout']['type'] = 'advanced';
        }
        return $advanced_layout;
    }

    /**
     * get advanced message format
     * @param $type
     * @param $product_price
     * @param $value
     * @param $discount_price
     * @param $min
     * @param $advanced_layout
     * @param $rule
     * @param $discounted_title_text
     * @param $html_content
     * @param $badge_bg_color
     * @param $badge_text_color
     * @param $discount_method
     * @param $max
     */
    function advancedLayoutTextFormation(&$advanced_layout, $rule, $type, $discount_method, $product_price, $value, $discount_price, $discounted_title_text, $html_content, $badge_bg_color, $badge_text_color, $min = 0, $max = 0)
    {
        $discount_text = '';
        $discounted_price_text = '';
        switch ($type) {
            case 'fixed_price':
                if (!empty($value) && !empty($product_price)) {
                    $value = Woocommerce::getConvertedFixedPrice($value, 'fixed_price');
                    if($value < 0){
                        $value = 0;
                    }
                    $discount = $product_price - $value;
                    $discount_text = Woocommerce::formatPrice($discount);
                    $discounted_price_text = Woocommerce::formatPrice($value);
                }
                break;
            case 'fixed_set_price':
                if (!empty($value) && !empty($min) && !empty($product_price)) {
                    $value = Woocommerce::getConvertedFixedPrice($value, 'fixed_set_price');
                    $discounted_price = $value / $min;
                    if($discounted_price < 0){
                        $discounted_price = 0;
                    }
                    $discount = $product_price - $discounted_price;
                    $discount_text = Woocommerce::formatPrice($discount);
                    $discounted_price_text = Woocommerce::formatPrice($discounted_price);
                }
                break;
            case 'percentage':
                if (!empty($value) && !empty($discount_price) && !empty($product_price)) {
                    $discount = $product_price - $discount_price;
                    if($discount < 0){
                        $discount = 0;
                    }
                    $discount_text = $value . '%';
                    $discounted_price_text = Woocommerce::formatPrice($discount);
                }
                break;
            case 'free_shipping':
                //code is poetry
                break;
            default:
            case 'flat':
                if (!empty($value) && !empty($product_price)) {
                    $value = Woocommerce::getConvertedFixedPrice($value, 'flat');
                    $discount = $product_price - $value;
                    if($discount < 0){
                        $discount = 0;
                    }
                    $discount_text = Woocommerce::formatPrice($value);
                    $discounted_price_text = Woocommerce::formatPrice($discount);
                }
                break;
        }
        //if (!empty($discount_text) && !empty($discounted_price_text)) {
        $dont_allow_duplicate = true;
        if ($discount_method == "bulk_discount") {
            $searchForReplace = array('{{title}}', '{{min_quantity}}', '{{max_quantity}}', '{{discount}}', '{{discounted_price}}');//, '{{min_quantity}}', '{{max_quantity}}', '{{discount}}', '{{discounted_price}}'
            $string_to_replace = array($discounted_title_text, $min, $max, $discount_text, $discounted_price_text); //, $min, $max, $discount_text, $discounted_price_text
            $html_content = str_replace($searchForReplace, $string_to_replace, $html_content);
        } elseif ($discount_method == "set_discount") {
            $searchForReplace = array('{{title}}', '{{min_quantity}}', '{{discount}}', '{{discounted_price}}'); //, '{{min_quantity}}', '{{discount}}', '{{discounted_price}}'
            $string_to_replace = array($discounted_title_text, $min, $discount_text, $discounted_price_text);//, $min, $discount_text, $discounted_price_text
            $html_content = str_replace($searchForReplace, $string_to_replace, $html_content);
            $searchForRemove = array('/{{max_quantity}}/');
            $replacements = array('');
            $html_content = preg_replace($searchForRemove, $replacements, $html_content);
        } else if($discount_method == 'free_shipping'){
            $searchForReplace = array('{{title}}');
            $string_to_replace = array($discounted_title_text);
            $html_content = str_replace($searchForReplace, $string_to_replace, $html_content);
            $searchForRemove = array('/{{min_quantity}}/', '/{{max_quantity}}/', '/{{discount}}/', '/{{discounted_price}}/');
            $replacements = array('', '');
            $html_content = preg_replace($searchForRemove, $replacements, $html_content);
        }else {
            $searchForReplace = array('{{title}}', '{{discount}}', '{{discounted_price}}');//, '{{discount}}', '{{discounted_price}}'
            $string_to_replace = array($discounted_title_text, $discount_text, $discounted_price_text);//, $discount_text, $discounted_price_text
            $html_content = str_replace($searchForReplace, $string_to_replace, $html_content);
            $searchForRemove = array('/{{min_quantity}}/', '/{{max_quantity}}/');
            $replacements = array('', '');
            $html_content = preg_replace($searchForRemove, $replacements, $html_content);
        }
        if (!empty($advanced_layout)) {
            foreach ($advanced_layout as $layout_options) {
                $check_exists = array($layout_options['badge_text']);
                if (in_array($html_content, $check_exists)) {
                    $dont_allow_duplicate = false;
                    break;
                }
            }
        }
        if ($dont_allow_duplicate) {
            $advanced_layout[] = array(
                'badge_bg_color' => $badge_bg_color,
                'badge_text_color' => $badge_text_color,
                'badge_text' => $html_content,
                'rule_id' => $rule->rule->id,
            );
        }
        //}
    }

    /**
     * Check has exclusive rule
     * */
    function hasExclusiveFromRules(){
        $rules = array();
        if(!empty(self::$rules)){
            foreach (self::$rules as $key => $values){
                if($values->rule->enabled == 1 && $values->rule->exclusive == 1){
                    $rules[$key] = $values;
                }
            }
        }

        return $rules;
    }

    /**
     * Filter exclusive rule
     * */
    function filterExclusiveRule($quantity, $ajax_price, $is_cart, $manual_request){
        if(self::$filtered_exclusive_rule === true){
            // if we doesn't do this. BUY X GET Y auto add will calculate wrong
            return;
        }
        self::$filtered_exclusive_rule = true;
        $exclusive_rules = $this->hasExclusiveFromRules();
        if(!empty($exclusive_rules)){
            $cart = self::$woocommerce_helper->getCart();
            $rule_passed = $has_exclusive_rule = false;
            if(!empty($cart)){
                $price_display_condition = self::$config->getConfig('show_strikeout_when', 'show_when_matched');
                foreach ($cart as $key => $cart_item){
                    foreach ($exclusive_rules as $rule_id => $rule){
                        $product = $cart_item['data'];
                        $quantity = $cart_item['quantity'];
                        $calculate_discount_from = self::$config->getConfig('calculate_discount_from', 'sale_price');
                        if (empty($custom_price)) {
                            if ($calculate_discount_from == 'regular_price') {
                                $product_price = self::$woocommerce_helper->getProductRegularPrice($product);
                            } else {
                                $product_price = self::$woocommerce_helper->getProductPrice($product);
                            }
                        } else {
                            $product_price = $custom_price;
                        }

                        if ($rule->isFilterPassed($product) || $rule->rule->discount_type == 'wdr_free_shipping') {
                            if ($rule->hasConditions()) {
                                if ($rule->isCartConditionsPassed($cart)) {
                                    $rule_passed = true;
                                }
                            } else {
                                $rule_passed = true;
                            }
                            if($rule_passed){
                                if($rule->rule->discount_type == 'wdr_free_shipping'){
                                    $has_exclusive_rule = true;
                                } else {
                                    if(!in_array($rule->rule->discount_type, array('wdr_buy_x_get_x_discount', 'wdr_set_discount'))){
                                        if ($discounted_price = $rule->calculateDiscount($product_price, $quantity, $product, $ajax_price, $cart_item, $price_display_condition, $is_cart, $manual_request)) {
                                            $has_exclusive_rule = true;
                                        } else {
                                            $rule_passed = apply_filters('advanced_woo_discount_rules_is_rule_passed_with_out_discount_for_exclusive_rule', false, $product, $rule, $cart_item);
                                            if($rule_passed){
                                                $has_exclusive_rule = true;
                                            }
                                        }
                                    } else {
                                        $rule_passed = apply_filters('advanced_woo_discount_rules_is_rule_passed_with_out_discount_for_exclusive_rule', false, $product, $rule, $cart_item);
                                        if($rule_passed){
                                            $has_exclusive_rule = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            $process_discount = apply_filters('advanced_woo_discount_rules_process_discount_for_product_which_do_not_matched_filters', false, $product, $rule, $cart_item);
                            if($process_discount){
                                $discounted_price = $rule->calculateDiscount($product_price, $quantity, $product, $ajax_price, $cart_item, $price_display_condition, $is_cart);
                                if($discounted_price > 0){
                                    $has_exclusive_rule = true;
                                }
                            }
                        }
                        $has_exclusive_rule = apply_filters('advanced_woo_discount_rules_is_rule_passed_for_exclusive_rule', $has_exclusive_rule, $product, $rule, $cart_item);
                        if($has_exclusive_rule){
                            self::$rules = array($rule_id => $rule);
                            break;
                        }
                    }
                    if($has_exclusive_rule){
                        break;
                    }
                }
            }
        }
    }

    /**
     * check the product has the price discount
     * @param $product
     * @param $quantity
     * @param $custom_price
     * @param $ajax_price
     * @param $is_cart
     * @param $cart_item
     * @return array|bool
     */
    function mayApplyPriceDiscount($product, $quantity, $custom_price = 0, $ajax_price = false, $cart_item = array(), $is_cart=true, $manual_request = false)
    {
        $this->filterExclusiveRule($quantity, $ajax_price, $is_cart, $manual_request);
        if (!empty(self::$rules) && !empty($product)) {
            $calculate_discount_from = self::$config->getConfig('calculate_discount_from', 'sale_price');
            if (empty($custom_price)) {
                if ($calculate_discount_from == 'regular_price') {
                    $product_price = self::$woocommerce_helper->getProductRegularPrice($product);
                } else {
                    $product_price = self::$woocommerce_helper->getProductPrice($product);
                    if($product_price <= 0){
                        if(isset($product->awdr_product_original_price) && !empty($product->awdr_product_original_price)){
                            $product_price = $product->awdr_product_original_price;
                        }
                    }
                }
            } else {
                $product_price = $custom_price;
            }

            $original_product_price = apply_filters('advanced_woo_discount_rules_product_original_price_on_before_calculate_discount', $product_price, $product, $quantity, $cart_item, $calculate_discount_from);
            $calculate_from_price = $product_price = apply_filters('advanced_woo_discount_rules_product_price_on_before_calculate_discount', $product_price, $product, $quantity, $cart_item, $calculate_discount_from);

            $exclusive_rules = $discounts = $exclude_products = array();
            $cart = self::$woocommerce_helper->getCart();
            $product_id = self::$woocommerce_helper->getProductId($product);
            $matched_item_key = (isset($cart_item['key']))? $cart_item['key']: $product_id;
            $language_helper_object = self::$language_helper;
            $apply_rule_to = self::$config->getConfig('apply_product_discount_to', 'biggest_discount');
            $price_display_condition = self::$config->getConfig('show_strikeout_when', 'show_when_matched');
            $apply_discount_subsequently = false;
            $price_as_cart_discount = array();
            $this_apply_as_cart_rule = false;
            $show_stike_out_depends_cart_rule = array();
            foreach (self::$rules as $rule) {
                $discount_type = $rule->getRuleDiscountType();
                if (!$rule->isEnabled()) {
                    continue;
                }
                $chosen_languages = $rule->getLanguages();
                if (!empty($chosen_languages)) {
                    $current_language = $language_helper_object::getCurrentLanguage();
                    if (!in_array($current_language, $chosen_languages)) {
                        continue;
                    }
                }
                $rule_id = $rule->getId();

                $has_additional_rules = ($rule->hasProductDiscount() || $rule->hasCartDiscount() || $rule->hasBulkDiscount());
                $has_additional_rules = apply_filters('advanced_woo_discount_rules_has_any_discount', $has_additional_rules, $rule);
                $filter_passed = false;
                $discounted_price = 0;
                if ($has_additional_rules) {
                    if ($rule->isFilterPassed($product)) {
                        $filter_passed = true;
                        if ($rule->hasConditions()) {
                            if (!$rule->isCartConditionsPassed($cart)) {
                                continue;
                            }
                        }
                        $rule::$set_discounts = $rule::$simple_discounts = $rule::$bulk_discounts  = array();
                        if ($discounted_price = $rule->calculateDiscount($product_price, $quantity, $product, $ajax_price, $cart_item, $price_display_condition, $is_cart, $manual_request)) {
                            $cart_discounted_price = $cart_discount_for_single_qty_from_array = 0;
                            $discount_label = '';
                            if(!is_array($discounted_price)){
                                $cart_discounted_price = $discounted_price * $quantity;
                            }else{
                                $discount_label = (isset($discounted_price[0]['label']) && !empty($discounted_price[0]['label'])) ? $discounted_price[0]['label'] : '';
                                $discounted_price_array = $discounted_price;
                                $discounted_price = (isset($discounted_price[0]['discount_fee']) && !empty($discounted_price[0]['discount_fee'])) ? $discounted_price[0]['discount_fee'] : 0;
                                if(isset($discounted_price_array[0]['discount_type'])){
                                    if($discounted_price_array[0]['discount_type'] != "flat_in_subtotal"){
                                        $cart_discount_for_single_qty_from_array = $discounted_price;
                                        $discounted_price = $discounted_price * $quantity;
                                    }
                                }
                            }
                            if ($discounted_price > 0 && $rule->isExclusive()) {
                                array_push($exclusive_rules, $rule_id);
                            }
                            if($apply_rule_to == "all"){
                                $apply_discount_subsequently = self::$config->getConfig('apply_discount_subsequently', 0);
                            }
                            if ($apply_discount_subsequently && !empty($apply_discount_subsequently)) {
                                if (isset(self::$total_discounts[$rule_id][$product_id]['product_price']) && !empty(self::$total_discounts[$rule_id][$product_id]['product_price'])) {
                                    $product_price = self::$total_discounts[$rule_id][$product_id]['product_price'];
                                } else {
                                    if(!empty($cart_discount_for_single_qty_from_array)){
                                        $product_price = $product_price - $cart_discount_for_single_qty_from_array;
                                    }else{
                                        $product_price = $product_price - $discounted_price;
                                    }
                                    self::$total_discounts[$rule_id][$product_id]['product_price'] = $product_price;
                                }
                            }
                            //if(!empty($cart_item)) {
                            $this_apply_as_cart_rule = false;
                            switch ($discount_type) {
                                case 'wdr_simple_discount':
                                    if ($simple_discount = $rule->getProductAdjustments()) {
                                        if (isset($simple_discount->apply_as_cart_rule) && !empty($simple_discount->apply_as_cart_rule)) {
                                            $this_apply_as_cart_rule = true;
                                            if(!empty($cart_item)) {
                                                $price_as_cart_discount[$rule_id][$product_id] = array(
                                                    'discount_type' => 'wdr_simple_discount',
                                                    'discount_label' => $simple_discount->cart_label,
                                                    'discount_value' => $simple_discount->value,
                                                    'discounted_price' => $cart_discounted_price,
                                                    'rule_name' => $rule->getTitle(),
                                                    'cart_item_key' => isset($cart_item['key']) ? $cart_item['key'] : '',
                                                    'product_id' => self::$woocommerce_helper->getProductId($cart_item['data']),
                                                    'rule_id' => $rule_id,
                                                );
                                                $discounts[$rule_id] = $discounted_price;
                                            }
                                        }
                                    }
                                    break;
                                case 'wdr_cart_discount':
                                    if ($cart_discount = $rule->getCartAdjustments()) {
                                        $this_apply_as_cart_rule = true;
                                        if(!empty($cart_item)) {
                                            $price_as_cart_discount[$rule_id][$product_id] = array(
                                                'discount_type' => 'wdr_cart_discount',
                                                'apply_type' => $cart_discount->type,
                                                'discount_label' => $discount_label,
                                                'discount_value' => $cart_discount->value,
                                                'discounted_price' => $discounted_price,
                                                'rule_name' => $rule->getTitle(),
                                                'cart_item_key' => isset($cart_item['key']) ? $cart_item['key'] : '',
                                                'product_id' => self::$woocommerce_helper->getProductId($cart_item['data']),
                                                'rule_id' => $rule_id,
                                            );
                                            $discounts[$rule_id] = (isset($discounted_price_array[0]['discount_fee']) && !empty($discounted_price_array[0]['discount_fee'])) ? $discounted_price_array[0]['discount_fee'] : 0;
                                        }
                                    }
                                    break;
                                case 'wdr_bulk_discount':
                                    if ($bulk_discount = $rule->getBulkAdjustments()) {
                                        if (isset($bulk_discount->apply_as_cart_rule) && !empty($bulk_discount->apply_as_cart_rule)) {
                                            $this_apply_as_cart_rule = true;
                                            if(!empty($cart_item)) {
                                                $price_as_cart_discount[$rule_id][$product_id] = array(
                                                    'discount_type' => 'wdr_bulk_discount',
                                                    'discount_label' => $bulk_discount->cart_label,
                                                    'discount_value' => 0,
                                                    'discounted_price' => $cart_discounted_price,
                                                    'rule_name' => $rule->getTitle(),
                                                    'cart_item_key' => isset($cart_item['key']) ? $cart_item['key'] : '',
                                                    'product_id' => self::$woocommerce_helper->getProductId($cart_item['data']),
                                                    'rule_id' => $rule_id,
                                                );
                                                $discounts[$rule_id] = $discounted_price;
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    $apply_discount_in_cart = apply_filters('advanced_woo_discount_rules_apply_the_discount_as_fee_in_cart', false, $rule);
                                    if($apply_discount_in_cart === true){
                                        $this_apply_as_cart_rule = true;
                                        $price_as_cart_discount = apply_filters('advanced_woo_discount_rules_fee_values', $price_as_cart_discount, $rule, $cart_discounted_price, $product_id, $cart_item);
                                        $discounts[$rule_id] = $discounted_price;
                                    }
                                    break;
                            }
                            $show_stike_out_depends_cart_rule[] = ($this_apply_as_cart_rule === true) ? 'yes' : 'no';
                            if( $this_apply_as_cart_rule === true){
                                continue;
                            }
                            //}
                            if($discount_type === 'wdr_cart_discount'){
                                continue;
                            }
                            $set_discounts = $rule::$set_discounts;
                            $simple_discounts = $rule::$simple_discounts;
                            $bulk_discounts = $rule::$bulk_discounts;
                            if ($ajax_price) {
                                self::$total_discounts['ajax_product'][$rule_id]['set_discount'] = isset($set_discounts[$product_id]) ? $set_discounts[$product_id] : 0;
                                self::$total_discounts['ajax_product'][$rule_id]['bulk_discount'] = isset($bulk_discounts[$product_id]) ? $bulk_discounts[$product_id] : 0;
                                self::$total_discounts['ajax_product'][$rule_id]['simple_discount'] = isset($simple_discounts[$product_id]) ? $simple_discounts[$product_id] : 0;
                            }else{
                                self::$total_discounts[$matched_item_key][$rule_id]['set_discount'] = isset($set_discounts[$product_id]) ? $set_discounts[$product_id] : 0;
                                self::$total_discounts[$matched_item_key][$rule_id]['bulk_discount'] = isset($bulk_discounts[$product_id]) ? $bulk_discounts[$product_id] : 0;
                                self::$total_discounts[$matched_item_key][$rule_id]['simple_discount'] = isset($simple_discounts[$product_id]) ? $simple_discounts[$product_id] : 0;
                            }
                        }
                    } else {
                        $process_discount = apply_filters('advanced_woo_discount_rules_process_discount_for_product_which_do_not_matched_filters', false, $product, $rule, $cart_item);
                        if($process_discount){
                            $discounted_price = $rule->calculateDiscount($product_price, $quantity, $product, $ajax_price, $cart_item, $price_display_condition, $is_cart);
                            if ($discounted_price > 0 && $rule->isExclusive()) {
                                array_push($exclusive_rules, $rule_id);
                            }
                        }
                    }
                    if($discounted_price > 0){
                        if ($ajax_price) {
                            self::$total_discounts['ajax_product'][$rule_id] = apply_filters('advanced_woo_discount_rules_calculated_discounts_of_each_rule_for_ajax_price', self::$total_discounts['ajax_product'][$rule_id], $product_id, $rule_id, $filter_passed, $cart_item, $is_cart, $rule);
                            $ajax_discounts[$rule_id] = $discounted_price;
                        }else{
                            if(!isset(self::$total_discounts[$matched_item_key][$rule_id])){
                                self::$total_discounts[$matched_item_key][$rule_id] = array();
                            }
                            self::$total_discounts[$matched_item_key][$rule_id] = apply_filters('advanced_woo_discount_rules_calculated_discounts_of_each_rule', self::$total_discounts[$matched_item_key][$rule_id], $product_id, $rule_id, $filter_passed, $cart_item, $is_cart, $rule);
                            $discounts[$rule_id] = $discounted_price;
                        }
                    }
                }
            }
            $product_price = $original_product_price;
            if (isset($ajax_discounts) && !empty($ajax_discounts)) {
                //If exclusive rules is not empty then apply only exclusive rule
                $rules = $this->pickRule($exclusive_rules, $ajax_discounts, $apply_rule_to);
                $discounted_price = 0;
                foreach ($rules as $rule_id){
                    $discounted_price += $ajax_discounts[$rule_id];
                }
//                $discounted_price  = array_sum($ajax_discounts);
                if ($discounted_price < 0) {
                    $discounted_price = 0;
                }
                return array(
                    'initial_price' => $product_price,
                    'discounted_price' => $discounted_price,
                    'initial_price_with_tax' => $this->mayHaveTax($product, $product_price),
                    'discounted_price_with_tax' => $this->mayHaveTax($product, $discounted_price),
                    'total_discount_details' => self::$total_discounts['ajax_product'],
                    'apply_as_cart_rule' => $show_stike_out_depends_cart_rule,
                );
            }
            if (empty($discounts)) {
                return false;
            }
            //If exclusive rules is not empty then apply only exclusive rule
            $rules = $this->pickRule($exclusive_rules, $discounts, $apply_rule_to);
            $discount_price = 0;
            $price_discounts = $valid_discounts = array();
            if (isset($rules) && !empty($rules) && !empty($discounts)) {
                foreach ($rules as $rule_id) {
                    if(isset(self::$total_discounts[$matched_item_key]) && isset(self::$total_discounts[$matched_item_key][$rule_id])){
                        $valid_discounts[$matched_item_key][$rule_id] = self::$total_discounts[$matched_item_key][$rule_id];
                    }
                    if(!empty($price_as_cart_discount) && isset($price_as_cart_discount[$rule_id])){
                        if(isset(self::$price_discount_apply_as_cart_discount[$rule_id])){
                            self::$price_discount_apply_as_cart_discount[$rule_id] = array_merge(self::$price_discount_apply_as_cart_discount[$rule_id], $price_as_cart_discount[$rule_id]);
                        } else {
                            self::$price_discount_apply_as_cart_discount[$rule_id] = $price_as_cart_discount[$rule_id];
                        }
                    }else{
                        if (isset(self::$rules[$rule_id]) && isset($discounts[$rule_id])) {
                            if(!empty($discounts[$rule_id])){
                                //$discount_price += $discounts[$rule_id];
                                if(isset(self::$total_discounts[$matched_item_key]) && isset(self::$total_discounts[$matched_item_key][$rule_id])){
                                    $matched_price_discounts = $this->getDiscountForMatchedItemAndRule(self::$total_discounts[$matched_item_key][$rule_id]);
                                    if(!empty($matched_price_discounts)){
                                        $price_discounts = array_merge($price_discounts, $matched_price_discounts);
                                    }
                                }
                            }
                            self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                        }
                    }
                }
            }
            if(!empty($valid_discounts)){
                unset(self::$total_discounts[$matched_item_key]);
                self::$total_discounts[$matched_item_key] = $valid_discounts[$matched_item_key];
            }
            $product_price = floatval($product_price);
            if($product_price == floatval($calculate_from_price)){
                $discount_values = $this->calculateDiscountFromMatchedRule($product_price, $matched_item_key, $quantity, $price_discounts, $product_price);
            } else {
                $discount_values = $this->calculateDiscountFromMatchedRule($product_price, $matched_item_key, $quantity, $price_discounts, floatval($calculate_from_price));
            }

            $discounted_price = $product_price - $discount_values['discount_price'];
            if ($discounted_price < 0 ) {
                $discounted_price = 0;
            }

            $discount_prices = array(
                'initial_price' => $product_price,
                'discounted_price' => $discounted_price,
                'initial_price_with_tax' => $this->mayHaveTax($product, $product_price),
                'discounted_price_with_tax' => $this->mayHaveTax($product, $discounted_price),
                'total_discount_details' => self::$total_discounts,
                'cart_discount_details' => $this->getCartDiscountPrices($cart, true),
                'apply_as_cart_rule' => $show_stike_out_depends_cart_rule,
                'discount_lines' => $discount_values['discount_lines'],
            );
            return apply_filters('advanced_woo_discount_rules_discount_prices_of_product', $discount_prices, $product, $quantity, $cart_item);
        }
        return false;
    }

    /**
     * Merge additional discounts
     * @param $price_discounts array
     * @return array
     * */
    protected function mergeAdditionalDiscounts($price_discounts){
        $price_discounts_new = array();
        foreach ($price_discounts as $price_discount){
            $price_discounts_new[] = $price_discount;
            if(isset($price_discount['additional_discounts']) && !empty($price_discount['additional_discounts'])){
                $price_discounts_new = array_merge($price_discounts_new, $price_discount['additional_discounts']);
            }
        }

        return $price_discounts_new;
    }

    /**
     * Calculate discount from matched rule
     *
     * @param $product_price int/float
     * @param $matched_item_key string
     * @param $quantity int
     * @param $price_discounts int/float
     * @return array
     * */
    protected function calculateDiscountFromMatchedRule($product_price, $matched_item_key, $quantity, $price_discounts, $calculate_discount_from){
        if(isset(self::$original_price_of_product[$matched_item_key])){
            $product_price = self::$original_price_of_product[$matched_item_key];
        } else {
            self::$original_price_of_product[$matched_item_key] = $product_price;
        }
        $rule = new Rule();
        $apply_rule_to = self::$config->getConfig('apply_product_discount_to', 'biggest_discount');
        $apply_subsequently = false;
        if($apply_rule_to == "all"){
            $apply_discount_subsequently = self::$config->getConfig('apply_discount_subsequently', 0);
            if($apply_discount_subsequently) $apply_subsequently = true;
        }
        $discount_price = 0;
        $discount_lines = array();
        $discount_lines['non_applied'] = array('quantity' => $quantity, 'discount' => 0, 'price' => $product_price, 'calculate_discount_from' => $calculate_discount_from);
        $price_discounts = $this->mergeAdditionalDiscounts($price_discounts);
        foreach ($price_discounts as $price_discount){
//            $discount_price = $discount_price+($price_discount['discount_price']);
            $remaining_qty = $discount_qty = $price_discount['discount_quantity'];
            $available_qty = $discount_lines['non_applied']['quantity'];
            $applied_qty = 0;
            if($available_qty > 0 && $discount_qty <= $available_qty){
                $current_product_price = $discount_lines['non_applied']['calculate_discount_from'];
                $available_qty = $discount_lines['non_applied']['quantity'];
                $discount_lines['non_applied']['quantity'] = $available_qty - $discount_qty;
                $current_discount_amount = $rule->calculator($price_discount['discount_type'], $current_product_price, $price_discount['discount_value']);
                if($apply_subsequently === true) $current_product_price = $current_product_price - $current_discount_amount;
                $remaining_qty -= $discount_qty;
                $applied_qty += $discount_qty;
                $discount_lines[] = array('quantity' => $discount_qty, 'discount' => $current_discount_amount, 'original_price' => $product_price, 'discounted_price' => ($product_price-$current_discount_amount));
            } else {
                if(!empty($discount_lines)){
                    foreach ($discount_lines as $key_f => $discount_line){
                        if($key_f !== 'non_applied'){
                            if($apply_subsequently === true){
                                $current_product_price = $discount_lines['non_applied']['calculate_discount_from'] - $discount_lines[$key_f]['discount'];
                            } else {
                                $current_product_price = $discount_lines['non_applied']['calculate_discount_from'];
                            }
                            $available_qty = $discount_lines[$key_f]['quantity'];
                            if($available_qty > $discount_qty){
                                $new_row = $discount_lines[$key_f];
                                $new_row['quantity'] = $available_qty-$discount_qty;
                                $available_qty = $discount_lines[$key_f]['quantity'] = $discount_qty;
                                $discount_lines[] = $new_row;
                            }

                            $remaining_qty -= $available_qty;
                            $applied_qty += $available_qty;
                            $discount_lines['non_applied']['quantity'] = $available_qty - $discount_qty;
                            $current_discount_amount = $rule->calculator($price_discount['discount_type'], $current_product_price, $price_discount['discount_value']);
                            $discount_lines[$key_f]['discount'] = $discount_lines[$key_f]['discount']+$current_discount_amount;
                            $discount_lines[$key_f]['discounted_price'] = $product_price - $discount_lines[$key_f]['discount'];
                        }
                    }
                    if($remaining_qty > 0){
                        $available_qty = $quantity-$applied_qty;
                        if($remaining_qty <= $available_qty){
                            $current_product_price = $discount_lines['non_applied']['calculate_discount_from'];
                            $discount_lines['non_applied']['quantity'] = $available_qty - $remaining_qty;
                            $current_discount_amount = $rule->calculator($price_discount['discount_type'], $current_product_price, $price_discount['discount_value']);
                            if($apply_subsequently === true) $current_product_price = $current_product_price - $current_discount_amount;
                            $discount_lines[] = array('quantity' => $remaining_qty, 'discount' => $current_discount_amount, 'original_price' => $product_price, 'discounted_price' => ($product_price-$current_discount_amount));
                            $remaining_qty -= $remaining_qty;
                            $applied_qty += $remaining_qty;
                        }
                    }
                }
            }
        }
        $discount_amount = 0;
        foreach ($discount_lines as $discount_line){
            $discount_amount += $discount_line['discount']*$discount_line['quantity'];
        }
        $discount_price = $discount_amount/$quantity;

        return array('discount_price' => $discount_price, 'discount_lines' => $discount_lines);
    }

    protected function getDiscountForMatchedItemAndRule($matched_discounts){
        $matched_items = array();
        foreach ($matched_discounts as $key => $matched_discount){
            if(isset($matched_discount['discount_price']) && $matched_discount['discount_price'] > 0){
                $matched_discount['discount_rule_type'] = $key;
                $matched_items[] = $matched_discount;
            }
        }

        return $matched_items;
    }

    /**
     * Calculate tax for products
     * @param $product
     * @param $price
     * @param $quantity
     * @return float
     */
    function mayHaveTax($product, $price, $quantity = 1)
    {
        if (empty($product) || empty($price) || empty($quantity)) {
            return $price;
        }
        if ($this->is_cart) {
            self::$tax_display_type = get_option('woocommerce_tax_display_cart');
        } else {
            self::$tax_display_type = get_option('woocommerce_tax_display_shop');
        }
        if (self::$tax_display_type === 'excl') {
            return self::$woocommerce_helper->getExcludingTaxPrice($product, $price, $quantity);
        } else {
            return self::$woocommerce_helper->getIncludingTaxPrice($product, $price, $quantity);
        }
    }

    /**
     * Sale badge display or not
     * @param $product
     * @param $sale_badge
     * @return bool
     */
    function saleBadgeDisplayChecker($product, $sale_badge)
    {
        if (!empty(self::$rules)) {
            $language_helper_object = self::$language_helper;
            foreach (self::$rules as $rule) {
                if (!$rule->isEnabled()) {
                    continue;
                }
                $chosen_languages = $rule->getLanguages();
                if (!empty($chosen_languages)) {
                    $current_language = $language_helper_object::getCurrentLanguage();
                    if (!in_array($current_language, $chosen_languages)) {
                        continue;
                    }
                }
                if ($rule->isFilterPassed($product, $sale_badge)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * calculate the cart discount prices
     * @param $cart
     * @param bool $discount_calculation_call
     * @return array
     */
    function getCartDiscountPrices($cart, $discount_calculation_call = false)
    {
        $cart_discount_arr = array();
        $cart_discount_against_product = array();
        $apply_as_cart_fee_details = self::$price_discount_apply_as_cart_discount;
        if (!empty($apply_as_cart_fee_details) && !empty($cart)) {
            foreach ($apply_as_cart_fee_details as $rule_id => $product_id){
                $discount_value = 0;
                $rule_applied_product_id = array();
                foreach ($product_id as $detail) {
                    $discount_value += isset($detail['discounted_price']) ? $detail['discounted_price'] : 0 ;
                    $label = (isset($detail['discount_label']) && !empty($detail['discount_label'])) ? $detail['discount_label'] : $detail['rule_name'];
                    $value = (isset($detail['discount_value']) && !empty($detail['discount_value'])) ? $detail['discount_value'] : 0;
                    $product_id = isset($detail['product_id']) ? $detail['product_id'] : 0;
                    $rule_applied_product_id = array_merge($rule_applied_product_id, array($product_id));
                    $current_discounted_price = isset($detail['discounted_price']) ? $detail['discounted_price'] : 0 ;
                    $cart_discount_against_product[$product_id][$rule_id] = $current_discounted_price;
                }
                if(!empty($rule_applied_product_id)){
                    $rule_applied_product_id = array_unique($rule_applied_product_id);
                }
                self::$cart_adjustments[$rule_id]['cart_discount'] = isset($value) ? $value : '';
                self::$cart_adjustments[$rule_id]['cart_shipping'] = 'no';
                self::$cart_adjustments[$rule_id]['cart_discount_label'] = isset($label) ? $label : '';
                self::$cart_adjustments[$rule_id]['cart_discount_price'] = $discount_value;
                self::$cart_adjustments[$rule_id]['cart_discount_product_price'] = $cart_discount_against_product;
                self::$cart_adjustments[$rule_id]['applied_product_ids'] = $rule_applied_product_id;
            }
            array_push($cart_discount_arr, $apply_as_cart_fee_details);
            if ($discount_calculation_call) {
                return self::$cart_adjustments;
            }
        }
        return $cart_discount_arr;
    }

    /**
     * check freeshipping if available using cart
     * @param $cart
     * @return array
     */
    public static function getFreeshippingMethod(){
        foreach (self::$rules as $rule) {
            $language_helper_object = self::$language_helper;
            $chosen_languages = $rule->getLanguages();
            if (!empty($chosen_languages)) {
                $current_language = $language_helper_object::getCurrentLanguage();
                if (!in_array($current_language, $chosen_languages)) {
                    continue;
                }
            }
            //$rule_id = $rule->getId();
            $discount_type = $rule->getRuleDiscountType();
            $rule_id = $rule->rule->id;
            if ($discount_type == "wdr_free_shipping") {
                $cart_items = self::$woocommerce_helper->getCart();
                if(!empty($cart_items)){
                    foreach ($cart_items as $cart_item){
                        //if ($rule->isFilterPassed($cart_item['data'])) {
                        if ($rule->hasConditions()) {
                            if (!$rule->isCartConditionsPassed($cart_items)) {
                                continue;
                            }
                        }
                        self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                        return array('free_shipping'=>1);
                        //}
                    }
                }
            }
        }
        return array();
    }


    /**
     * Pick the applicable rule
     * @param $exclusive_rules
     * @param $matched_rules
     * @param $pick
     * @return array
     */
    function pickRule($exclusive_rules, $matched_rules, $pick)
    {
        $rules = array();
        if (!empty($exclusive_rules)) {
            if (isset($exclusive_rules[0])) {
                $rule_id = $exclusive_rules[0];
                $rules[] = $rule_id;
                if (isset(self::$rules[$rule_id])) {
                    self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                }
            }
        } else {
            switch ($pick) {
                case 'all':
                    if (!empty($matched_rules)) {
                        foreach ($matched_rules as $rule_id => $discount) {
                            $rules[] = $rule_id;
                            if (isset(self::$rules[$rule_id])) {
                                self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                            }
                        }
                    }
                    break;
                case 'biggest_discount':
                    $rule_id_list = array_keys($matched_rules, max($matched_rules));
                    $rule_id = reset($rule_id_list);
                    $rules[] = $rule_id;
                    if (isset(self::$rules[$rule_id])) {
                        self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                    }
                    break;
                case 'lowest_discount':
                    $rule_id_list = array_keys($matched_rules, min($matched_rules));
                    $rule_id = reset($rule_id_list);
                    $rules[] = $rule_id;
                    if (isset(self::$rules[$rule_id])) {
                        self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                    }
                    break;
                default:
                case 'first':
                    reset($matched_rules);
                    $rule_id = key($matched_rules);
                    $rules[] = $rule_id;
                    if (isset(self::$rules[$rule_id])) {
                        self::$applied_rules[$rule_id] = self::$rules[$rule_id];
                    }
                    break;
            }
        }
        return $rules;
    }

    /**
     * get used coupons from discount rules
     * @return array
     */
    static public function getUsedCoupons(){
        $all_used_coupons = array();
        foreach (self::$rules as $rule) {
            $used_coupons_per_rule = $rule->hasUsedCoupons();
            if($used_coupons_per_rule && !empty($used_coupons_per_rule)){
                $all_used_coupons = array_merge($all_used_coupons,$used_coupons_per_rule);
            }
        }
        $all_used_coupons = array_merge($all_used_coupons, Helper::getAvailableCouponNameFromRules());
        $all_used_coupons = array_unique($all_used_coupons);
        return $all_used_coupons;
    }

    public static function getFilterBasedCartQuantities($condition_type, $rule){
        $filter_calculate_values = 0;
        $cart_items = self::$woocommerce_helper->getCart(true);
        foreach ($cart_items as $cart_item){
            if(Helper::isCartItemConsideredForCalculation(true, $cart_item, "qty_based_on_filters")){
                if ($rule->isFilterPassed($cart_item['data'])) {
                    if($condition_type == 'cart_subtotal'){
                        $filter_calculate_values += self::$woocommerce_helper->getCartLineItemSubtotal($cart_item);
                    }elseif ($condition_type == 'cart_quantities'){
                        $filter_calculate_values += intval((isset($cart_item['quantity'])) ? $cart_item['quantity'] : 0);
                    }elseif ($condition_type == 'cart_line_items_count'){
                        $filter_calculate_values += 1;
                    }else{
                        return 0;
                    }
                }
            }
        }
        return $filter_calculate_values;
    }
}