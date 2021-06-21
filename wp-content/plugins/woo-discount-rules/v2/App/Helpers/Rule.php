<?php

namespace Wdr\App\Helpers;

use stdClass;
use Wdr\App\Controllers\OnSaleShortCode;
use Wdr\App\Models\DBTable;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Rule
{
    public $rule, $available_conditions;
    public static $woocommerce_helper, $set_discount_count = array(), $set_discounts = array(), $bulk_discounts = array(), $simple_discounts = array(), $additional_discounts = array();

    /**
     * @param $rule_data
     * @param $available_conditions
     * @return $this
     */
    function __construct($rule_data = array(), $available_conditions = array())
    {
        if (!empty($rule_data)) {
            $this->rule = $rule_data;
            $this->available_conditions = $available_conditions;
            self::$woocommerce_helper = (empty(self::$woocommerce_helper)) ? new Woocommerce() : self::$woocommerce_helper;
        }
        return $this;
    }

    /**
     * get all rules and set object
     * @param $available_conditions array
     * @return array
     */
    function getAvailableRules($available_conditions, $rule_id = NULL)
    {
        $available_rules = DBTable::getRules($rule_id);
        return $this->getRuleObject($available_rules, $available_conditions);
    }

    /**
     * convert rules to rule object
     * @param $rules
     * @param $conditions
     * @return array
     */
    function getRuleObject($rules, $conditions)
    {
        $rule_list = array();
        if (!empty($rules)) {
            if (is_array($rules)) {
                foreach ($rules as $rule) {
                    $rule_obj = new self($rule, $conditions);
                    $rule_id = $rule_obj->getId();
                    $rule_list[$rule_id] = $rule_obj;
                }
            } else {
                $rule_list = new self($rules, $conditions);
            }
        }
        return $rule_list;
    }

    /**
     * get the rule ID
     * @return int|null
     */
    function getId()
    {
        if (isset($this->rule->id)) {
            return $this->rule->id;
        }
        return NULL;
    }

    /**
     * get the rule ID
     * @return int|null
     */
    function getPriorityId()
    {
        if (isset($this->rule->priority)) {
            return $this->rule->priority;
        }
        return NULL;
    }

    /**
     * Get rule discount type
     * @return bool || Type
     */
    function getRuleDiscountType(){
        if (isset($this->rule->discount_type)) {
            return $this->rule->discount_type;
        }
        return false;
    }

    /**
     * Get rule created by
     * @return bool || Type
     */
    function getRuleCreatedBy(){
        if (isset($this->rule->created_by)) {
            return $this->rule->created_by;
        }
        return false;
    }

    /**
     * Get rule created by
     * @return bool || Type
     */
    function getRuleCreatedOn(){
        if (isset($this->rule->created_on)) {
            return $this->rule->created_on;
        }
        return false;
    }

    /**
     * Get rule created by
     * @return bool || Type
     */
    function getRuleModifiedBy(){
        if (isset($this->rule->modified_by)) {
            return $this->rule->modified_by;
        }
        return false;
    }

    /**
     * Get rule created by
     * @return bool || Type
     */
    function getRuleModifiedOn(){
        if (isset($this->rule->modified_on)) {
            return $this->rule->modified_on;
        }
        return false;
    }

    /**
     * get all rules and set object
     * @param $available_conditions array
     * @return array
     */
    function getAllRules($available_conditions)
    {
        $available_rules = DBTable::getRules();
        return $this->getRuleObject($available_rules, $available_conditions);
    }

    /**
     * get particular and set object
     * @param $rule_id int
     * @param $available_conditions array
     * @return array
     */
    function getRule($rule_id, $available_conditions)
    {
        $rule = DBTable::getRules($rule_id);
        if (empty($rule)) {
            $rule = $this->defaultRuleObj();
        }
        return $this->getRuleObject($rule, $available_conditions);
    }

    /**
     * @param $from
     * @param $to
     * @param $option
     * @return array|object|null
     */
    function getRuleByPeriod($from, $to, $option)
    {
        $rule = DBTable::getRulesByPeriod($from, $to, $option);
        return $rule;
    }

    /**
     * set the default rule obj
     * @return stdClass
     */
    function defaultRuleObj()
    {
        //Todo: change default object if any modification happen in table structure
        $obj = new stdClass();
        $obj->id = NULL;
        $obj->enabled = 1;
        $obj->exclusive = 0;
        $obj->priority = NULL;
        $obj->apply_to = NULL;
        $obj->filters = NULL;
        $obj->conditions = NULL;
        $obj->product_adjustments = NULL;
        $obj->cart_adjustments = NULL;
        $obj->bogo_adjustments = NULL;
        $obj->bulk_adjustments = '{"operator":"product_cumulative","type":"percentage","ranges":false,"table_message":""}';
        $obj->set_adjustments = '{"discount_type":"fixed_set_price","ranges":false,"table_message":""}';
        $obj->other_discounts = NULL;
        $obj->date_from = NULL;
        $obj->date_to = NULL;
        $obj->usage_limits = NULL;
        $obj->rule_language = NULL;
        $obj->used_limits = 0;
        $obj->additional = NULL;
        $obj->max_discount_sum = NULL;
        $obj->advanced_discount_message = NULL;
        $obj->discount_type = NULL;
        return $obj;
    }

    /**
     * get the rule usage limits
     * @return int|null
     */
    function getUsageLimits()
    {
        if (isset($this->rule->usage_limits)) {
            return $this->rule->usage_limits;
        }
        return 0;
    }

    /**
     * get the rule used limits
     * @return int|null
     */
    function getUsedLimits()
    {
        if (isset($this->rule->used_limits)) {
            return $this->rule->used_limits;
        }
        return 0;
    }

    /**
     * check rule limit reached
     */
    function checkRuleUsageLimits(){
        $usage_limit = $this->getUsageLimits();
        $used_limit = $this->getUsedLimits();
        if($usage_limit != 0){
            return ($usage_limit > $used_limit) ? "Active" : "Disabled";
        }
        return 'Active';
    }

    /**
     * Is the rule is exclusive rule
     * @return bool
     */
    function isExclusive()
    {
        if (isset($this->rule->exclusive)) {
            if ($this->rule->exclusive == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Rule title
     * @return string|null
     */
    function getTitle()
    {
        if (isset($this->rule->title)) {
            return $this->rule->title;
        }
        return NULL;
    }

    /**
     * Rule is enabled
     * @return string|null
     */
    function isEnabled()
    {
        if (isset($this->rule->enabled)) {
            if ($this->rule->enabled == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * get the start date of rule
     * @param bool $timestamp
     * @param string $format
     * @return false|string|null
     */
    function getStartDate($timestamp = false, $format = "Y-m-d H:i:s")
    {
        if (isset($this->rule->date_from) && !empty($this->rule->date_from)) {
            if ($timestamp) {
                return $this->rule->date_from;
            }
            return $this->formatDate($this->rule->date_from, $format);
        }
        return NULL;
    }

    /**
     * formatting the date
     * @param $date
     * @param string $format
     * @param bool $time_stamp
     * @return false|string
     */
    function formatDate($date, $format = "Y-m-d H:i:s", $time_stamp = false)
    {
        if ($time_stamp) {
            return strtotime($date);
        }
        return date($format, $date);
    }

    /**
     * get the start date of rule
     * @param bool $timestamp
     * @param string $format
     * @return false|string|null
     */
    function getEndDate($timestamp = false, $format = "Y-m-d H:i:s")
    {
        if (isset($this->rule->date_to) && !empty($this->rule->date_to)) {
            if ($timestamp) {
                return $this->rule->date_to;
            }
            return $this->formatDate($this->rule->date_to, $format);
        }
        return NULL;
    }

    /**
     * get the bulk adjustment details
     * @return array|bool|mixed|object
     */
    function getBulkAdjustments()
    {
        if ($this->hasBulkDiscount()) {
            return json_decode($this->rule->bulk_adjustments);
        }
        return false;
    }

    /**
     * check the rule has bulk discount
     * @return bool
     */
    function hasBulkDiscount()
    {
        if (!empty($this->rule->bulk_adjustments) && $this->rule->bulk_adjustments != '{}' && $this->rule->bulk_adjustments != '[]') {
            return true;
        }
        return false;
    }

    /**
     * check the rule has bulk discount
     * @return bool
     */
    function getBuyXGetYAdjustment()
    {
        if (!empty($this->rule->buy_x_get_y_adjustments) && $this->rule->buy_x_get_y_adjustments != '{}' && $this->rule->buy_x_get_y_adjustments != '[]') {
            return json_decode($this->rule->buy_x_get_y_adjustments);
        }
        return false;
    }

    /**
     * Check the filter is passed for product
     * @param $product
     * @param $sale_badge
     * @return bool
     */
    function isFilterPassed($product, $sale_badge = false, $product_table = false)
    {
        if (!$this->hasFilter()) {
            return true;
        }
        $filters = $this->getFilter();
        if (!empty($filters)) {
            $filter_helper = new Filter();
            $filter_passed = $filter_helper->matchFilters($product, $filters, $sale_badge, $product_table);
            $conditions = $this->getConditions();
            $purchase_first_order_passed = $filter_passed_purchase_first_order = $filter_passed_user_logged_in = $user_logged_in_passed = $filter_passed_user_role = $filter_passed_user_list = $user_role_passed = $user_list_passed = $has_other_conditions = false;
            $condition_relationship = $this->getRelationship('condition', 'and');
            if($filter_passed){
                if(!empty($conditions)){
                    foreach($conditions as $condition){
                        $cart = array();
                        $options = isset($condition->options) ? $condition->options : array();
                        $condition_type = isset($condition->type) ? $condition->type : array();
                        if( !empty($condition_type) ){
                            if (!empty($options) && isset($this->available_conditions[$condition_type]['object']) && is_object($this->available_conditions[$condition_type]['object']) && method_exists($this->available_conditions[$condition_type]['object'], 'check')) {
                                $this->available_conditions[$condition_type]['object']->rule = $this;
                                switch ($condition_type){
                                    case 'user_role':
                                        $user_role_passed = true;
                                        $filter_passed_user_role = $this->available_conditions[$condition_type]['object']->check($cart, $options);
                                        break;
                                    case 'user_list':
                                        $user_list_passed = true;
                                        $filter_passed_user_list = $this->available_conditions[$condition_type]['object']->check($cart, $options);
                                        break;
                                    case 'user_logged_in':
                                        $user_logged_in_passed = true;
                                        $filter_passed_user_logged_in = $this->available_conditions[$condition_type]['object']->check($cart, $options);
                                        break;
                                    case 'purchase_first_order':
                                        $purchase_first_order_passed = true;
                                        $filter_passed_purchase_first_order = $this->available_conditions[$condition_type]['object']->check($cart, $options);
                                        break;
                                }
                            }
                        }

                        if( !empty($condition_type) && !in_array($condition_type, array('user_role', 'user_list', 'user_logged_in', 'purchase_first_order'))){
                            $has_other_conditions = true;
                        }
                    }
                }
                if($user_role_passed || $user_list_passed || $user_logged_in_passed || $purchase_first_order_passed){
                    if($filter_passed_user_role || $filter_passed_user_list || $filter_passed_user_logged_in || $filter_passed_purchase_first_order){
                        if($condition_relationship == 'and'){
                            if($user_role_passed && $user_logged_in_passed){
                                if($filter_passed_user_role && $filter_passed_user_logged_in){
                                }else{
                                    $filter_passed = false;
                                }
                            }
                            if($user_list_passed && $user_logged_in_passed){
                                if($filter_passed_user_list && $filter_passed_user_logged_in){
                                }else{
                                    $filter_passed = false;
                                }
                            }
                            if($purchase_first_order_passed && $user_logged_in_passed){
                                if($filter_passed_purchase_first_order && $filter_passed_user_logged_in){
                                }else{
                                    $filter_passed = false;
                                }
                            }
                            //$this -> rule
                            $filter_passed = apply_filters('advanced_woo_discount_rules_customer_condition_filter_passed', $filter_passed, $this, $product, $sale_badge, $product_table, $conditions);
                        }
                    } else {
                        if($condition_relationship == 'or'){
                            if($has_other_conditions){
                            } else {
                                $filter_passed = false;
                            }
                        } else {
                            $filter_passed = false;
                        }
                    }
                }

            }
        } else {
            $filter_passed = false;
        }
        $rule = $this;
        return apply_filters('advanced_woo_discount_rules_filter_passed', $filter_passed, $rule, $product, $sale_badge, $product_table);
    }

    /**
     * check the rule has filter
     * @return bool
     */
    function hasFilter()
    {
        if (isset($this->rule->filters)) {
            if (empty($this->rule->filters) || $this->rule->filters == '{}' || $this->rule->filters == '[]') {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * get the rule filter
     * @return array|bool|mixed|object
     */
    function getFilter()
    {
        if ($this->hasFilter()) {
            return json_decode($this->rule->filters);
        }
        return false;
    }

    /**
     * get filter type
     * @param $filter
     * @return null
     */
    function getFilterType($filter)
    {
        if (is_object($filter) && isset($filter->type)) {
            return $filter->type;
        } elseif (is_array($filter) && isset($filter['type'])) {
            return $filter['type'];
        }
        return NULL;
    }

    /**
     * get filter method
     * @param $filter
     * @return null
     */
    function getFilterMethod($filter)
    {
        if (is_object($filter) && isset($filter->method)) {
            return $filter->method;
        } elseif (is_array($filter) && isset($filter['method'])) {
            return $filter['method'];
        }
        return NULL;
    }

    /**
     * get filter method
     * @param $filter
     * @return null
     */
    function getFilterOptionValue($filter)
    {
        if (is_object($filter) && isset($filter->value)) {
            return $filter->value;
        } elseif (is_array($filter) && isset($filter['value'])) {
            return $filter['value'];
        }
        return array();
    }

    /**
     * get filter parent product id for sale batch
     * @param $filter
     * @return null
     */
    function getFilterOptionParentValue($filter)
    {
        if (is_object($filter) && isset($filter->product_variants_for_sale_badge)) {
            return $filter->product_variants_for_sale_badge;
        } elseif (is_array($filter) && isset($filter['product_variants_for_sale_badge'])) {
            return $filter['product_variants_for_sale_badge'];
        }
        return array();
    }

    /**
     * get the rule relationship
     * @param $type
     * @param $default
     * @return mixed
     */
    function getRelationship($type, $default)
    {
        $relations = $this->getAdditionalRuleData();
        if (isset($relations[$type . '_relationship']) && !empty($relations[$type . '_relationship'])) {
            return $relations[$type . '_relationship'];
        }
        return $default;
    }

    /**
     * get the show hide bulk or set table
     * @param $default
     * @return mixed
     */
    function showBulkDiscountsTable($default)
    {
        $bulk_table_display = $this->getAdditionalRuleData();
        if (isset($bulk_table_display['bulk_table_display']) && !empty($bulk_table_display['bulk_table_display'])) {
            return $bulk_table_display['bulk_table_display'];
        }
        return $default;
    }

    /**
     * get additional column data
     * @return array|mixed|object
     */
    function getAdditionalRuleData()
    {
        $additional = array();
        if (isset($this->rule->additional)) {
            if (!empty($this->rule->additional) && $this->rule->additional != '{}' && !$this->rule->additional != '[]') {
                $additional = json_decode($this->rule->additional, true);
            }
        }
        return $additional;
    }

    /**
     * check the rule has advanced discount message/layout
     * @return bool
     */
    function hasAdvancedDiscountMessage()
    {
        if (isset($this->rule->advanced_discount_message)) {
            if (empty($this->rule->advanced_discount_message) || $this->rule->advanced_discount_message == '{}' || $this->rule->advanced_discount_message == '[]') {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * get the badge settings
     * @param $key
     * @param $default
     * @return array|bool|mixed|object
     */
    function getAdvancedDiscountMessage($key, $default = false)
    {
        if (empty($key)) {
            return false;
        }
        if ($this->hasAdvancedDiscountMessage()) {
            $badge_settings = json_decode($this->rule->advanced_discount_message);
            if ($key == 'badge_text' && isset($badge_settings->badge_text) && !empty($badge_settings->badge_text)) {
                return htmlspecialchars_decode(__($badge_settings->badge_text, 'woo-discount-rules'));
            }
            if (isset($badge_settings->$key) && !empty($badge_settings->$key)) {
                return $badge_settings->$key;
            } else {
                return $default;
            }
        }
        return false;
    }

    /**
     * Calculate the discount
     * @param $quantity
     * @param $product_price
     * @param $product
     * @param $price_display_condition
     * @param $is_cart
     * @param $ajax_price
     * @param $cart_item
     * @return int
     */
    function calculateDiscount($product_price, $quantity, $product, $ajax_price, $cart_item = array(), $price_display_condition='show_when_matched', $is_cart=true, $manual_request = false)
    {
        $product_id = self::$woocommerce_helper->getProductId($product);
        self::$simple_discounts[$product_id] = 0;
        self::$bulk_discounts[$product_id] = 0;
        self::$set_discounts[$product_id] = 0;
        $product_discount = 0;
        if ($this->hasProductDiscount()) {
            $product_discount = $this->calculateProductDiscount($product_price, $quantity, $product, $product_id, $price_display_condition, $is_cart, $manual_request);
            self::$simple_discounts[$product_id] = $product_discount;
        }
        if ($this->hasCartDiscount()) {
           return $this->calculateCartDiscount($product_price);
        }
        $product_bulk_discount = 0;
        if ($this->hasBulkDiscount()) {
            $product_bulk_discount = $this->calculateProductBulkDiscount($product_price, $quantity, $product, $price_display_condition, $is_cart, $manual_request);
            self::$bulk_discounts[$product_id] = $product_bulk_discount;
        }
        $rule = $this;
        if(is_array($product_discount)) $product_discount = $product_discount['discount_price'];
        if(is_array($product_bulk_discount)) $product_bulk_discount = $product_bulk_discount['discount_price'];
        $discounts = array(
            'product_discount' => $product_discount,
            'product_bulk_discount' => $product_bulk_discount
        );

        $discounts = apply_filters('advanced_woo_discount_rules_discounts_of_each_rule', $discounts, $rule, $product_price, $quantity, $product, $ajax_price, $cart_item, $price_display_condition, $is_cart);

        $total_discount = array_sum($discounts);
        if ($total_discount <= 0) {
            return false;
        }
        $max_discount = $this->getMaxDiscountSum();
        if (!empty($max_discount) && $total_discount > $max_discount) {
            return $max_discount;
        }
        return $total_discount;
    }

    /**
     * check the rule has product discount
     * @return bool
     */
    function hasProductDiscount()
    {
        if (isset($this->rule->product_adjustments)) {
            if (!empty($this->rule->product_adjustments) && $this->rule->product_adjustments != '{}' && $this->rule->product_adjustments != '[]') {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate the product discount
     * @param $price
     * @param $product
     * @param $product_id
     * @param $price_display_condition
     * @param $is_cart
     * @return float|int
     */
    function calculateProductDiscount($price, $quantity, $product, $product_id, $price_display_condition, $is_cart, $manual_request = false)
    {
        $original_qty = $quantity;
        $cart_quantity = 0;
        if($manual_request === false){
            $quantity = 0;
        }
        $cart_items = self::$woocommerce_helper->getCart();
        $discount = $this->getProductAdjustments();
        if(isset($discount->type) && !empty($discount->type) && isset($discount->value) && !empty($discount->value)){
            if(($price_display_condition == "show_when_matched" && !$is_cart) || ($price_display_condition == "show_dynamically" && !$is_cart)){
                if($manual_request === false){
                    $quantity = 1;
                }

            }else if($price_display_condition == "show_after_matched" || $is_cart){
                if(!empty($cart_items)){
                    foreach ($cart_items as $cart_item){
                        $cart_product_parent_id = isset($cart_item['data']) ? self::$woocommerce_helper->getProductParentId($cart_item['data']) : '';
                        $current_product_parent_id = self::$woocommerce_helper->getProductParentId($product);
                        $cart_product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : 0;
                        $cart_variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;
                        if(empty($cart_variation_id)){
                            if(!empty($cart_product_id) && $cart_product_id == $product_id){
                                $cart_quantity = isset($cart_item['quantity']) ? $cart_item['quantity'] : 0;
                            }
                        }
                        if(!empty($cart_variation_id)){
                            if(!empty($cart_product_id) && $cart_product_id == $current_product_parent_id){
                                $cart_quantity = isset($cart_item['quantity']) ? $cart_item['quantity'] : 0;
                            }elseif (empty($cart_product_id) && $cart_product_parent_id == $current_product_parent_id){
                                $cart_quantity = isset($cart_item['quantity']) ? $cart_item['quantity'] : 0;
                            }
                        }
                    }
                    if($manual_request === true){
                        $quantity += $cart_quantity;
                    } else {
                        $quantity = $cart_quantity;
                    }
                }
            }

            if ( $quantity > 0) {
                $discount_price = $this->calculator($discount->type, $price, $discount->value);
                return array(
                    'discount_type' => $discount->type,
                    'discount_value' => $discount->value,
                    'discount_quantity' => $original_qty,
                    'discount_price_per_quantity' => $discount_price,
                    'discount_price' => $discount_price,
                );
            } else {
                return 0;
            }
        }
        return 0;
    }

    /**
     * Calculate the product bulk discount
     * @param $price
     * @param $quantity
     * @param $product
     * @param $ajax_price
     * @param $is_cart
     * @return float|int
     */
    function calculateProductBulkDiscount($price, $quantity, $product, $price_display_condition, $is_cart, $manual_request = false)
    {
        if ($bulk_discount_data = $this->getBulkAdjustments()) {
            if (!isset($bulk_discount_data->ranges) || !isset($bulk_discount_data->operator) || empty($bulk_discount_data->ranges)) {
                return 0;
            }
            return $this->getMatchedBulkDiscount($product, $price, $bulk_discount_data->operator, $bulk_discount_data->ranges, $quantity, $bulk_discount_data, $price_display_condition, $is_cart, $manual_request);
        } else {
            return 0;
        }
    }

    /**
     * get the matched bulk discount (& set discount) row's value
     * @param $operator
     * @param $ranges
     * @param $quantity
     * @param $bulk_discount_data
     * @param $product
     * @param boolean $ajax_price
     * @param $price
     * @param $is_cart
     * @return float|int
     */
    function getMatchedBulkDiscount( $product, $price, $operator, $ranges, $quantity, $bulk_discount_data, $price_display_condition, $is_cart, $manual_request = false)
    {
        $original_qty = $quantity;
        if (empty($ranges)) {
            return 0;
        }
        $cart_quantity = $quantity;
        $cart_items = self::$woocommerce_helper->getCart();
        if($price_display_condition == "show_when_matched" && !$is_cart){
            if(!$manual_request){
                $quantity = 1;
            }
        }else if($price_display_condition == "show_after_matched" || $is_cart){
            if(!$manual_request){
                $quantity = 0;
            }
        }
        switch ($operator) {
            case 'product_cumulative':
                $quantity += $this->getProductCumulativeDiscountQuantity($cart_items);
                break;
            case 'variation':
                $quantity += $this->getProductVariationDiscountQuantity($product, $cart_items);
                break;
            default:
            case 'product':
                $product_id = self::$woocommerce_helper->getProductId($product);
                if(!empty($cart_items)){
                    foreach ($cart_items as $cart_item){
                        $cart_item_product_id = self::$woocommerce_helper->getProductIdFromCartItem($cart_item);
                        if($cart_item_product_id == $product_id){
                            $quantity += isset($cart_item['quantity']) ? $cart_item['quantity'] : 0;
                        }
                    }
                }
                break;
        }
        $rule_id = $this->getId();
        $quantity = apply_filters('advanced_woo_discount_rules_change_bulk_rule_quantity', $quantity, $cart_items, $product, $cart_quantity, $price_display_condition, $is_cart, $manual_request, $rule_id);
        if (empty($quantity)) {
            return 0;
        }
        $matched_row = $this->getBulkDiscountFromRanges($ranges, $quantity);
        if(is_object($matched_row)){
            $type = (isset($matched_row->type) && !empty($matched_row->type)) ? $matched_row->type : false;
            $value = (isset($matched_row->value) && !empty($matched_row->value)) ? $matched_row->value : 0;
            if ($type && !empty($value)) {
                //return $this->calculator($matched_row->type, $price, $matched_row->value);
                $discount_price = $this->calculator($matched_row->type, $price, $matched_row->value);
                return array(
                    'discount_type' => $matched_row->type,
                    'discount_value' => $matched_row->value,
                    'discount_quantity' => $original_qty,
                    'discount_price_per_quantity' => $discount_price,
                    'discount_price' => $discount_price,
                );
            }
            return 0;
        }
        return 0;
    }

    /**
     * Get quantity based on Count adjustment
     *
     * @param $operator string
     * @param $quantity integer
     * @param $product object
     * @param $is_cart boolean
     *
     * @return integer
     * */
    public function getQuantityBasedOnCountAdjustment($operator, $quantity, $product, $is_cart = true){
        $cart_items = self::$woocommerce_helper->getCart();
        switch ($operator) {
            case 'product_cumulative':
                $quantity = $this->getProductCumulativeDiscountQuantity($cart_items, $is_cart, $product, $quantity);
                break;
            case 'variation':
                $quantity = $this->getProductVariationDiscountQuantity($product, $cart_items, $is_cart, $quantity);
                break;
            default:
            case 'product':
                break;
        }

        return $quantity;
    }

    function getCartItemQuantity($cart_item){
        return apply_filters('advanced_woo_discount_rules_cart_item_quantity', $cart_item['quantity'], $cart_item, $this->rule);
    }

    /**
     * get bulk/set product cumulative discount quantities
     * @param $cart_items
     * @return int
     */
    function getProductCumulativeDiscountQuantity($cart_items, $is_cart = true, $product = null, $current_product_quantity = 0)
    {
        $quantity = 0;
        foreach ($cart_items as $cart_item) {
            $include_cart_item = Helper::isCartItemConsideredForCalculation(true, $cart_item, "cumulative_count");
            if($include_cart_item === true){
                if ($this->isFilterPassed(isset($cart_item['data']) ? $cart_item['data'] : $cart_item)) {
                    if ($this->hasConditions()) {
                        if (!$this->isCartConditionsPassed($cart_items)) {
                            continue;
                        }
                        $quantity += $this->getCartItemQuantity($cart_item);
                    } else {
                        $quantity += $this->getCartItemQuantity($cart_item);
                    }
                }
            }
        }
        if(!$is_cart && !empty($product)){
            if ($this->isFilterPassed($product)) {
                if ($this->hasConditions()) {
                    if ($this->isCartConditionsPassed($cart_items)) {
                        $quantity += $current_product_quantity;
                    }

                } else {
                    $quantity += $current_product_quantity;
                }
            }
        }
        return $quantity;
    }

    /**
     * get bulk/set product variation discount quantities
     * @param $product
     * @param $cart_items
     * @return int
     */
    function getProductVariationDiscountQuantity($product, $cart_items, $is_cart = true, $current_product_quantity = 0)
    {
        $quantity = 0;
        $current_product_parent_id = self::$woocommerce_helper->getProductParentId($product);
        if (!empty($current_product_parent_id)) {
            foreach ($cart_items as $cart_item) {
                $include_cart_item = Helper::isCartItemConsideredForCalculation(true, $cart_item, "product_variation_count");
                if($include_cart_item === true){
                    $cart_item_parent_id = self::$woocommerce_helper->getProductParentId(isset($cart_item['data']) ? $cart_item['data'] : $cart_item);
                    if (!empty($cart_item_parent_id) && $cart_item_parent_id == $current_product_parent_id) {
                        $quantity += $this->getCartItemQuantity($cart_item);
                    }
                }
            }
        } else {
            $product_id = self::$woocommerce_helper->getProductId($product);
            if(!empty($cart_items)){
                foreach ($cart_items as $cart_item){
                    $cart_item_product_id = self::$woocommerce_helper->getProductIdFromCartItem($cart_item);
                    if($cart_item_product_id == $product_id){
                        $quantity += isset($cart_item['quantity']) ? $cart_item['quantity'] : 0;
                    }
                }
            }
        }
        if(!$is_cart){
            $quantity += $current_product_quantity;
        }
        return $quantity;
    }

    /**
     * Get the discount value for bulk ranges
     * @param $ranges
     * @param $quantity
     * @return float|int
     */
    function getBulkDiscountFromRanges($ranges, $quantity)
    {
        foreach ($ranges as $range) {
            if (isset($range->value) && !empty($range->value)) {
                $from = intval(isset($range->from) ? $range->from : 0);
                $to = intval(isset($range->to) ? $range->to : 0);
                if (empty($to) && empty($from)) {
                    continue;
                }
                if (empty($to) && !empty($from)) {
                    if ($quantity >= $from) {
                        return $range;
                    }
                } elseif (!empty($to) && !empty($from)) {
                    if ($quantity >= $from && $quantity <= $to) {
                        return $range;
                    }
                } elseif (!empty($to) && empty($from)) {
                    if ($quantity <= $to) {
                        return $range;
                    }
                }
            }
        }
        return 0;
    }

    /**
     * get the product adjustment details
     * @return array|bool|mixed|object
     */
    function getProductAdjustments()
    {
        if ($this->hasProductDiscount()) {
            return json_decode($this->rule->product_adjustments);
        }
        return false;
    }

    /**
     * Calculator to calculate discount price from original price
     * @param $type
     * @param $original_value
     * @param $value
     * @return float|int
     */
    function calculator($type, $original_value, $value)
    {
        $discount = 0;
        if (empty($value) || empty($original_value)) {
            return $discount;
        }
        $original_value = floatval($original_value);
        $value = floatval($value);
        switch ($type) {
            case 'fixed_price':
                $discount = $original_value - self::$woocommerce_helper->getConvertedFixedPrice($value, 'fixed_price');
                break;
            case 'percentage':
                if (!empty($value)) {
                    $discount = $original_value * ($value / 100);
                }
                break;
            default:
            case 'flat':
                $discount = self::$woocommerce_helper->getConvertedFixedPrice($value, 'flat');
                break;
        }
        return $discount;
    }

    /**
     * get the maximum discount sum
     * @return int
     */
    function getMaxDiscountSum()
    {
        if (isset($this->rule->max_discount_sum)) {
            if (!empty($this->rule->max_discount_sum)) {
                return $this->rule->max_discount_sum;
            }
        }
        return 0;
    }

    /**
     * Check the cart has pass the conditions
     * @param $cart
     * @return bool
     */
    function isCartConditionsPassed($cart)
    {
        $rule_object = $this;
        /*if (empty($cart)) {
            //if cart is empty then return with false
            return false;
        }*/
        $conditions_result = array();
        if ($conditions = $this->getConditions()) {
            if (empty($conditions)) {
                //If the rule has no condition then return true
                return apply_filters('advanced_woo_discount_rules_is_conditions_passed', true, $rule_object, $this->rule);
            }
            $condition_relationship = $this->getRelationship('condition', 'and');
            $dont_check_condition = apply_filters('advanced_woo_discount_rules_check_condition', false, $cart, $this, $condition_relationship);
            if($dont_check_condition){
                return apply_filters('advanced_woo_discount_rules_is_conditions_passed', true, $rule_object, $this->rule);
            }
            foreach ($conditions as $condition) {
                $type = isset($condition->type) ? $condition->type : NULL;
                $options = isset($condition->options) ? $condition->options : array();
                if (!empty($type) && !empty($options)) {
                    //if condition available, then check the cart against the condition
                    if (isset($this->available_conditions[$type]['object'])) {
                        if(is_object($this->available_conditions[$type]['object'])){
                            $this->available_conditions[$type]['object']->rule = $this;
                            if(method_exists($this->available_conditions[$type]['object'], 'check')){
                                $is_condition_passed = $this->available_conditions[$type]['object']->check($cart, $options);
                            } else {
                                $is_condition_passed = false;
                            }
                        } else {
                            $is_condition_passed = false;
                        }
                    }elseif(!isset($this->available_conditions[$type]['object'])){
                        $is_custom_taxonomy = strpos($type, "wdr_cart_item_"); //wdr_cart_item_
                        if ( $is_custom_taxonomy === (int) 0 && $is_custom_taxonomy !== false && isset($this->available_conditions['cart_item_products_taxonomy']['object'])) {
                            $custom_taxonomy = str_replace("wdr_cart_item_", "", $type);
                            if(is_object($this->available_conditions['cart_item_products_taxonomy']['object'])){
                                $this->available_conditions['cart_item_products_taxonomy']['object']->rule = $this;
                                if(method_exists($this->available_conditions['cart_item_products_taxonomy']['object'], 'check')){
                                    $options = (array)$options;
                                    $options['custom_taxonomy'] = $custom_taxonomy;
                                    $options = (object)$options;
                                    $is_condition_passed = $this->available_conditions['cart_item_products_taxonomy']['object']->check($cart, $options);
                                } else {
                                    $is_condition_passed = false;
                                }
                            } else {
                                $is_condition_passed = false;
                            }
                        }else{
                            $object_not_available = apply_filters('advanced_woo_discount_rules_condition_object_not_available', false, $cart, $this, $condition_relationship);
                            if($object_not_available){
                                $is_condition_passed = apply_filters('advanced_woo_discount_rules_set_condition_status', false, $cart, $this, $condition_relationship);
                            }else{
                                continue;
                            }
                        }
                    } else {
                        $is_condition_passed = false;
                    }
                    //if relationship is "and" and if current condition get fails, no need to check any other conditions provided by admin.just return rule condition failed
                    if (isset($is_condition_passed) && !$is_condition_passed && $condition_relationship == "and") {
                        return apply_filters('advanced_woo_discount_rules_is_conditions_passed', false, $rule_object, $this->rule);
                    }
                    //if relationship is "or" and if current condition get pass, no need to check any other conditions provided by admin.just return rule condition passed
                    if (isset($is_condition_passed) &&  $is_condition_passed && $condition_relationship == "or") {
                        return apply_filters('advanced_woo_discount_rules_is_conditions_passed', true, $rule_object, $this->rule);
                    }
                    //Check if any conditions fails
                    if (isset($is_condition_passed) && !$is_condition_passed) {
                        $conditions_result[] = false;
                    }
                }
            }
        }
        if (in_array(false, $conditions_result)) {
            return apply_filters('advanced_woo_discount_rules_is_conditions_passed', false, $rule_object, $this->rule);
        }
        return apply_filters('advanced_woo_discount_rules_is_conditions_passed', true, $rule_object, $this->rule);
    }

    /**
     * get the rule conditions
     * @return array|bool|mixed|object
     */
    function getConditions()
    {
        if ($this->hasConditions()) {
            return json_decode($this->rule->conditions);
        }
        return false;
    }

    /**
     * get the rule languages
     * @return array|bool|mixed|object
     */
    function getLanguages()
    {
        if ($this->hasLanguages()) {
            return json_decode($this->rule->rule_language);
        }
        return array();
    }

    /**
     * check the rule has conditions
     * @return bool
     */
    function hasConditions()
    {
        $status = false;
        if (isset($this->rule->conditions)) {
            if (empty($this->rule->conditions) || $this->rule->conditions == '{}' || $this->rule->conditions == '[]') {
                $status = false;
            } else {
                $status = true;
            }
        }
        return apply_filters('advanced_woo_discount_rules_has_rule_conditions', $status, $this->rule);
    }

    /**
     * check the rule has conditions
     * @return bool
     */
    function hasLanguages()
    {
        if (isset($this->rule->rule_language)) {
            if (empty($this->rule->rule_language) || $this->rule->rule_language == '{}' || $this->rule->rule_language == '[]') {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate the cart discount
     * @param $cart_subtotal
     * @return array
     */
    function calculateCartDiscount($product_price)
    {
        $discounts = array();
        if (empty($product_price)) {
            return $discounts;
        }
        if ($adjustment = $this->getCartAdjustments()) {
            if (!empty($adjustment)) {
                $type = isset($adjustment->type) ? $adjustment->type : 'flat';
                $value = isset($adjustment->value) ? $adjustment->value : 0;
                if (in_array($type, array('flat', 'percentage'))) {
                    if (!empty($value)) {
                        $label = isset($adjustment->label) ? $adjustment->label : __('discount', 'woo-discount-rules');
                        $discounts[] = array(
                            'free_shipping' => 0,
                            'discount' => $value,
                            'discount_type' => $type,
                            'label' => $label,
                            'discount_fee' => $this->calculator($type, $product_price, $value),
                        );
                    }
                } elseif($type == 'flat_in_subtotal'){
                    if (!empty($value)) {
                        $label = isset($adjustment->label) ? $adjustment->label : __('discount', 'woo-discount-rules');
                        $discounts[] = array(
                            'free_shipping' => 0,
                            'discount' => $value,
                            'discount_type' => $type,
                            'label' => $label,
                            'discount_fee' => $value,
                        );
                    }
                }else {
                    $discounts[] = array(
                        'free_shipping' => 1
                    );
                }
            }
        }
        return $discounts;
    }

    /**
     * get the product adjustment details
     * @return array|bool|mixed|object
     */
    function getCartAdjustments()
    {
        if ($this->hasCartDiscount()) {
            return json_decode($this->rule->cart_adjustments);
        }
        return false;
    }

    /**
     * check the rule has cart discount
     * @return bool
     */
    function hasCartDiscount()
    {
        if (isset($this->rule->cart_adjustments)) {
            if (!empty($this->rule->cart_adjustments) && $this->rule->cart_adjustments != '{}' && $this->rule->cart_adjustments != '[]') {
                return true;
            }
        }
        return false;
    }

    /**
     * save rule
     * @param $post
     * @return array|int|null
     */
    function save($post)
    {
        //$current_time = current_time('mysql', true);
        $current_date_time = '';
        if (function_exists('current_time')) {
            $current_time = current_time('timestamp');
            $current_date_time = date('Y-m-d H:i:s', $current_time);
        }
        $current_user = get_current_user_id();
        $rule_id = intval($this->getFromArray($post, 'edit_rule', NULL));
        $title = $this->getFromArray($post, 'title', esc_html__('Untitled Rule', 'woo-discount-rules'));
        $title = self::validateHtmlBeforeSave($title);
        $enabled = $this->getFromArray($post, 'enabled', '0');
        $exclusive = $this->getFromArray($post, 'exclusive', '0');
        $date_from = $this->getFromArray($post, 'date_from', NULL);
        $date_from = (isset($date_from) && !empty($date_from)) ? $this->formatDate($date_from, 'Y-m-d H:i:s', true) : NULL;
        $date_to = $this->getFromArray($post, 'date_to', NULL);
        $date_to = (isset($date_to) && !empty($date_to)) ? $this->formatDate($date_to, 'Y-m-d H:i:s', true) : NULL;
        $usage_limits = $this->getFromArray($post, 'usage_limits', '');
        $rule_filters = $this->getFromArray($post, 'filters', array());
        $rule_conditions = $this->getFromArray($post, 'conditions', array());
        $awdr_coupon_names = array();
        if (!empty($rule_conditions)) {
            foreach ($rule_conditions as $coupon_key => $coupon_conditions) {
                $type = (isset($coupon_conditions['type']) && !empty($coupon_conditions['type'])) ? $coupon_conditions['type'] : '';
                $options = (isset($coupon_conditions['options']) && !empty($coupon_conditions['options'])) ? $coupon_conditions['options'] : '';
                $operator = (isset($coupon_conditions['options']['operator']) && !empty($coupon_conditions['options']['operator'])) ? $coupon_conditions['options']['operator'] : '';
                $awdr_woo_coupon_name = (isset($coupon_conditions['options']['value']) && !empty($coupon_conditions['options']['value'])) ? $coupon_conditions['options']['value'] : '';
                $coupon_name = (isset($coupon_conditions['options']['custom_value']) && !empty($coupon_conditions['options']['custom_value'])) ? $coupon_conditions['options']['custom_value'] : '';
                $subtotal_promotion_message = isset($options['subtotal_promotion_message']) ? $options['subtotal_promotion_message'] : '';
                if ($type == 'cart_coupon' && $operator == 'custom_coupon' && $coupon_name != '') {
                    $coupon_name = trim($coupon_name);
                    //$coupon_name = str_replace(' ', '', $coupon_name);
                    $coupon_name = apply_filters('woocommerce_coupon_code', $coupon_name);
                    if (Woocommerce::checkCouponAlreadyExistsInWooCommerce($coupon_name)) {
                        return array(
                            'rule_id' => $rule_id,
                            'coupon_exists' => 'coupon already exists in woocommerce'
                        );
                    } else {
                        $rule_conditions[$coupon_key]['options']['custom_value'] = $coupon_name;
                    }
                    $awdr_coupon_names = array_merge($awdr_coupon_names,array($coupon_name));
                }else if($type == 'cart_coupon'){
                    if(!empty($awdr_woo_coupon_name)){
                        $awdr_coupon_names = array_merge($awdr_coupon_names,$awdr_woo_coupon_name);
                    }
                }else if(!empty($subtotal_promotion_message) && $subtotal_promotion_message != ''){
                    $rule_conditions[$coupon_key]['options']['subtotal_promotion_message'] = self::validateHtmlBeforeSave($subtotal_promotion_message);
                }
            }
        }
        $rule_additional = $this->getFromArray($post, 'additional', array());
        $product_adjustments = $this->getFromArray($post, 'product_adjustments', array());
        if(isset( $product_adjustments['cart_label']) && !empty( $product_adjustments['cart_label'])){
            $product_adjustments['cart_label'] =  self::validateHtmlBeforeSave( $product_adjustments['cart_label']);
        }
        $cart_adjustments = $this->getFromArray($post, 'cart_adjustments', array());
        if(isset($cart_adjustments['label']) && !empty($cart_adjustments['label'])){
            $cart_adjustments['label'] =  self::validateHtmlBeforeSave($cart_adjustments['label']);
        }
        $bulk_adjustments = $this->getFromArray($post, 'bulk_adjustments', array());
        if(isset( $bulk_adjustments['cart_label']) && !empty( $bulk_adjustments['cart_label'])){
            $bulk_adjustments['cart_label'] =  self::validateHtmlBeforeSave( $bulk_adjustments['cart_label']);
        }
        $set_adjustments = $this->getFromArray($post, 'set_adjustments', array());
        if(isset($set_adjustments['cart_label']) && !empty($set_adjustments['cart_label'])){
            $set_adjustments['cart_label'] =  self::validateHtmlBeforeSave($set_adjustments['cart_label']);
        }
        $buyx_getx_adjustments = $this->getFromArray($post, 'buyx_getx_adjustments', array());
        $buy_x_get_y_adjustments = $this->getFromArray($post, 'buyx_gety_adjustments', array());
        if(!empty($buy_x_get_y_adjustments)){
            foreach ($buy_x_get_y_adjustments['ranges'] as $key => $range){
                $buy_x_get_y_adjustments['ranges'][$key]['product_varients'] = array();
                $buy_x_get_y_adjustments['ranges'][$key]['product_variants_for_sale_badge'] = array();
                if(isset($range['products']) && !empty($range['products'])){
                    foreach ($range['products'] as $product_id){
                        $variants =  $this->getVariantsOfProducts(array($product_id));
                        if(!empty($variants)){
                            $buy_x_get_y_adjustments['ranges'][$key]['products_variants'][$product_id] = $variants;
                        }
                        $parent_id = $this->getParentOfVariant(array($product_id));
                        if(!empty($parent_id)){
                            $buy_x_get_y_adjustments['ranges'][$key]['product_variants_for_sale_badge'][] = $parent_id;
                        }
                    }
                }
            }
        }
        $rule_language = $this->getFromArray($post, 'rule_language', array());
        $discount_badge = $this->getFromArray($post, 'discount_badge', array());
        $discount_type = $this->getFromArray($post, 'discount_type', NULL);
        if (isset($_POST['discount_badge'])) {
            $discount_badge_text = (isset($_POST['discount_badge']['badge_text'])) ? $_POST['discount_badge']['badge_text'] : '';
            if (!empty($discount_badge_text)) {
                $discount_badge_text = stripslashes($discount_badge_text);
                $discount_badge['badge_text'] = self::validateHtmlBeforeSave($discount_badge_text);
            }
        }

        if(!empty($awdr_coupon_names)){
            $awdr_coupon_names = array_unique($awdr_coupon_names);
        }
        if($date_from !== null){
            $date_from = intval($date_from);
        }
        if($date_to !== null){
            $date_to = intval($date_to);
        }
        $current_time = current_time('mysql', true);
        $rule_title = (empty($title)) ? esc_html__('Untitled Rule', 'woo-discount-rules') : $title;
        $arg = array(
            'title' => sanitize_text_field($rule_title),
            'enabled' => intval($enabled),
            'exclusive' => intval($exclusive),
            'usage_limits' => intval($usage_limits),
            'date_from' => $date_from,
            'date_to' => $date_to,
            'filters' => json_encode($rule_filters),
            'conditions' => json_encode($rule_conditions),
            'additional' => json_encode($rule_additional),
            'product_adjustments' => json_encode($product_adjustments),
            'cart_adjustments' => json_encode($cart_adjustments),
            'buy_x_get_x_adjustments' => json_encode($buyx_getx_adjustments),
            'buy_x_get_y_adjustments' => json_encode($buy_x_get_y_adjustments),
            'bulk_adjustments' => json_encode($bulk_adjustments),
            'rule_language' => json_encode($rule_language),
            'set_adjustments' => json_encode($set_adjustments),
            'advanced_discount_message' => json_encode($discount_badge),
            'discount_type' => esc_sql($discount_type),
            'used_coupons' => json_encode($awdr_coupon_names),
        );

        if (!is_null($rule_id) && !empty($rule_id)) {
            $arg['modified_by'] = intval($current_user);
            $arg['modified_on'] = esc_sql($current_date_time);
            $column_format = array('%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s');
        }else{
            $arg['created_by'] = intval($current_user);
            $arg['created_on'] = esc_sql($current_date_time);
            $arg['modified_by'] = intval($current_user);
            $arg['modified_on'] = esc_sql($current_date_time);
            $column_format = array('%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s');
        }
        $arg = apply_filters( 'advanced_woo_discount_rules_before_save_rule_column', $arg, $rule_id, $post);

        $rule_id = DBTable::saveRule($column_format, $arg, $rule_id);
        if($rule_id){
            OnSaleShortCode::updateOnsaleRebuildPageStatus($rule_id);
            do_action('advanced_woo_discount_rules_after_save_rule', $rule_id, $post, $arg);
        }
        return $rule_id;
    }

    /**
     * Remove some Html tags before save
     * @param $value
     * @return mixed
     */
    static function validateHtmlBeforeSave($value){
        if (!empty($value)) {
            $html = html_entity_decode($value);
            $html = preg_replace('/(<(script|style|iframe)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $html);
            $allowed_html = array(
                'br' => array(),
                'strong' => array(),
                'span' => array('class' => array()),
                'div' => array('class' => array()),
                'p' => array('class' => array()),
                'table' => array('class' => array(), 'border' => array(), 'cellpadding' => array(), 'cellspacing' => array()),
                'tr' => array('class' => array()),
                'td' => array('class' => array()),
                'th' => array('class' => array()),
                'h4' => array('class' => array()),
                'h3' => array('class' => array()),
                'h1' => array('class' => array()),
                'h2' => array('class' => array()),
            );
            return wp_kses($html, $allowed_html);
        }
        return $value;
    }

    /**
     * Get data from array
     * @param $array
     * @param $key
     * @param $default
     * @return array|mixed
     */
    function getFromArray($array, $key, $default)
    {
        if (!is_array($array)) {
            return $default;
        }
        if (isset($array[$key])) {
            if ($key == 'filters') {
                return $this->addAdditionalDataForFilters($array[$key]);
            } else if ($key == 'conditions') {
                return $this->addAdditionalDataForConditions($array[$key]);
            }
            return $array[$key];
        }
        return $default;
    }

    /**
     * Format filters
     *
     * @param $array_filters array
     * @return array
     * */
    function addAdditionalDataForFilters($array_filters)
    {
        if (!empty($array_filters)) {
            foreach ($array_filters as $key => $array_filter) {
                if (isset($array_filter['type']) && isset($array_filter['value'])) {
                    $array_filters[$key]['product_variants'] = array();
                    if ($array_filter['type'] == 'products' && !empty($array_filter['value'])) {
                        if (is_array($array_filter['value'])) {
                            $array_filters[$key]['product_variants'] = $this->getVariantsOfProducts($array_filter['value']);
                            $array_filters[$key]['product_variants_for_sale_badge'] = $this->getParentOfVariant($array_filter['value']);
                        }
                    }
                }
            }
        }
        return $array_filters;
    }

    /**
     * Format filters
     *
     * @param $array_filters array
     * @return array
     * */
    function addAdditionalDataForConditions($array_filters)
    {
        if (!empty($array_filters)) {
            foreach ($array_filters as $key => $array_filter) {
                if (isset($array_filter['type']) && isset($array_filter['options'])) {
                    if (in_array($array_filter['type'], array('cart_item_product_combination', 'cart_item_products', 'purchase_previous_orders_for_specific_product')) && !empty($array_filter['options'])) {
                        $product_field_key = 'product';
                        if ($array_filter['type'] == 'cart_item_products') {
                            $product_field_key = 'value';
                        } elseif ($array_filter['type'] == 'purchase_previous_orders_for_specific_product') {
                            $product_field_key = 'products';
                        }
                        if (is_array($array_filter['options']) && isset($array_filter['options'][$product_field_key])) {
                            $array_filters[$key]['options']['product_variants'] = array();
                            if (is_array($array_filter['options'][$product_field_key]) && !empty($array_filter['options'][$product_field_key])) {
                                $variants = $this->getVariantsOfProducts($array_filter['options'][$product_field_key]);
                                $array_filters[$key]['options']['product_variants'] = $variants;
                            }
                        }
                    }
                }
            }
        }
        return $array_filters;
    }

    /**
     * Get variants of the products
     *
     * @param $product_ids array
     * @return array
     */
    function getVariantsOfProducts($product_ids)
    {
        $variants = array();
        if (!empty($product_ids)) {
            foreach ($product_ids as $product_id) {
                $product = Woocommerce::getProduct($product_id);
                if (method_exists($product, 'is_type')) {
                    if ($product->is_type(array('variable', 'variable-subscription'))) {
                        $additional_variants = Woocommerce::getProductChildren($product);
                        if (!empty($additional_variants) && is_array($additional_variants)) {
                            $variants = array_merge($variants, $additional_variants);
                        }
                    }
                }
            }
        }
        return $variants;
    }

    /**
     * Get siblings of the variants
     *
     * @param $product_ids array
     * @return array
     */
    function getParentOfVariant($product_ids)
    {
        $variants = array();
        if (!empty($product_ids)) {
            foreach ($product_ids as $product_id) {
               $parent_id = Woocommerce::getProductParentId((int)$product_id);
               if(!empty($parent_id)) {
                   $variants = array_merge($variants, array($parent_id));
               }
            }
        }
        if(!empty($variants)){
            $variants = array_unique($variants);
        }
        return $variants;
    }

    /**
     * search rule by rulename
     * @param $name
     * @param $available_conditions
     * @return array|\stdClass
     */
    function searchRuleByName($name, $available_conditions)
    {
        $rule = DBTable::getRules(null, $name);
        if (empty($rule)) {
            echo "<script> alert('No Records Found!'); </script>";
            return $this->getRuleObject(DBTable::getRules(), $available_conditions);
        }
        return $this->getRuleObject($rule, $available_conditions);
    }

    /**
     * Export all rules
     * @return array|\stdClass
     */
    function exportRuleByName($names)
    {
        return DBTable::getRules(null, null, $names);
    }

    /**
     * Hide Discount blocks if values get empty
     * @param $discount_obj
     * @return int
     */
    function showHideDiscount($discount_obj)
    {
        $show_discount_block = 0;
        if (!empty($discount_obj)) {
            foreach ($discount_obj as $discount_object) {
                $show_discount_block = $discount_object->value;
                if (!empty($show_discount_block)) {
                    return $show_discount_block;
                }
            }
        }
        return $show_discount_block;
    }

    /**
     * get all custom coupons
     * @return array
     */
    function getAllDynamicCoupons()
    {
        $available_rules = DBTable::getRules();
        $custom_coupons = array();
        foreach ($available_rules as $rule) {
            if (isset($rule->conditions) && !empty($rule->conditions) && $rule->conditions != '{}' && $rule->conditions != '[]') {
                $conditions = json_decode($rule->conditions);
                foreach ($conditions as $condition) {
                    $option_obj = (isset($condition->options) && !empty($condition->options) ? $condition->options : '');
                    $type = (isset($condition->type) && !empty($condition->type) ? $condition->type : '');
                    $operator = (isset($option_obj->operator) && !empty($option_obj->operator) ? $option_obj->operator : '');
                    $custom_value = (isset($option_obj->custom_value) && !empty($option_obj->custom_value) ? $option_obj->custom_value : '');
                    if ($type == 'cart_coupon' && $operator == 'custom_coupon' && $custom_value != '') {
                        $custom_coupons[] = $custom_value;
                    }
                }
            }
        }
        return $custom_coupons;
    }

    /**
     * get all custom coupons
     * @return array
     */
    function getCouponsFromDiscountRules()
    {
        $available_rules = DBTable::getRules();
        $custom_coupons = array();
        $woo_coupons = array();
        if (!empty($available_rules)) {
            foreach ($available_rules as $rule) {
                if (isset($rule->conditions) && !empty($rule->conditions) && $rule->conditions != '{}' && $rule->conditions != '[]') {
                    $conditions = json_decode($rule->conditions);
                    foreach ($conditions as $condition) {
                        $option_obj = (isset($condition->options) && !empty($condition->options) ? $condition->options : '');
                        $type = (isset($condition->type) && !empty($condition->type) ? $condition->type : '');
                        $operator = (isset($option_obj->operator) && !empty($option_obj->operator) ? $option_obj->operator : '');
                        $custom_value = (isset($option_obj->custom_value) && !empty($option_obj->custom_value) ? $option_obj->custom_value : '');
                        $value = (isset($option_obj->value) && !empty($option_obj->value) ? $option_obj->value : '');
                        if ($type == 'cart_coupon' && $operator == 'custom_coupon' && !empty($custom_value)) {
                            $custom_coupons[] = $custom_value;
                        } elseif ($type == 'cart_coupon' && $operator != 'custom_coupon' && !empty($value)) {
                            $woo_coupons[] = $value;
                        }
                    }
                }
            }
        }
        return array('custom_coupons' => $custom_coupons, 'woo_coupons' => $woo_coupons);
    }

    /**
     * The rule valid status
     * @return bool|string
     */
    function getRuleVaildStatus()
    {
        $valid_rule = false;
        $current_time = current_time('timestamp');
        $rule_start_date = $this->getStartDate(true);
        $rule_end_date = $this->getEndDate(true);
        if (!is_null($rule_start_date) && $current_time < $rule_start_date) {
            $valid_rule = "in_future";
        } else if (!is_null($rule_end_date) && $current_time > $rule_end_date) {
            $valid_rule = "expired";
        }
        return $valid_rule;
    }

    /**
     * check the rule has product discount
     * @return bool
     */
    function hasUsedCoupons()
    {
        if (isset($this->rule->used_coupons)) {
            if (!empty($this->rule->used_coupons) && $this->rule->used_coupons != '{}' && $this->rule->used_coupons != '[]') {
                return json_decode($this->rule->used_coupons);
            }
        }
        return false;
    }
}