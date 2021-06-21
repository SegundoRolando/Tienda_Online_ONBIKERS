<?php

namespace Wdr\App\Models;

use Wdr\App\Controllers\OnSaleShortCode;
use Wdr\App\Helpers\Language;
use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit;

class Recipes
{
   public $simple_discount_recipe = array(
        'simple_recipe_1' => array(
            'title' => 'Coupon based user role discount - sample',
            'enabled' => 1,
            'exclusive' => 0,
            'usage_limits' => 0,
            'date_from' => '',
            'date_to' => '',
            'filters' => '{"1":{"type":"all_products"}}',
            'conditions' => '{"2":{"type":"user_role","options":{"operator":"in_list","value":["customer"]}},"3":{"type":"cart_coupon","options":{"operator":"custom_coupon","custom_value":"summer"}}}',
            'additional' => '{"condition_relationship":"and"}',
            'product_adjustments' => '{"type":"percentage","value":"10","cart_label":""}',
            'cart_adjustments' => '[]',
            'buy_x_get_x_adjustments' => '[]',
            'buy_x_get_y_adjustments' => '[]',
            'bulk_adjustments' => '{"cart_label":""}',
            'rule_language' => '[]',
            'set_adjustments' => '{"cart_label":""}',
            'advanced_discount_message' => '{"display":"0","badge_color_picker":"#ffffff","badge_text_color_picker":"#000000","badge_text":""}',
            'discount_type' => 'wdr_simple_discount',
            'used_coupons' => '[]'
            ),
        'simple_recipe_2' => array(
            'title' => 'Subtotal tiered discount - sample',
            'enabled' => 1,
            'exclusive' => 0,
            'usage_limits' => 0,
            'date_from' => '',
            'date_to' => '',
            'filters' => '{"1":{"type":"all_products"}}',
            'conditions' => '{"2":{"type":"cart_subtotal","options":{"operator":"greater_than_or_equal","value":"500","calculate_from":"from_cart"}},"3":{"type":"cart_subtotal","options":{"operator":"less_than_or_equal","value":"1000","calculate_from":"from_cart"}}}',
            'additional' => '{"condition_relationship":"and"}',
            'product_adjustments' => '{"type":"percentage","value":"20","cart_label":""}',
            'cart_adjustments' => '[]',
            'buy_x_get_x_adjustments' => '[]',
            'buy_x_get_y_adjustments' => '[]',
            'bulk_adjustments' => '{"cart_label":""}',
            'rule_language' => '[]',
            'set_adjustments' => '{"cart_label":""}',
            'advanced_discount_message' => '{"display":"0","badge_color_picker":"#ffffff","badge_text_color_picker":"#000000","badge_text":""}',
            'discount_type' => 'wdr_simple_discount',
            'used_coupons' => '[]'
        ),
       'bundle_recipe_1' => array(
           'title' => 'Set Discount - sample ',
           'enabled' => 1,
           'exclusive' => 0,
           'usage_limits' => 0,
           'date_from' => '',
           'date_to' => '',
           'filters' => '{"1":{"type":"all_products"}}',
           'conditions' => '[]',
           'additional' => '{"condition_relationship":"and"}',
           'product_adjustments' => '[]',
           'cart_adjustments' => '[]',
           'buy_x_get_x_adjustments' => '[]',
           'buy_x_get_y_adjustments' => '[]',
           'bulk_adjustments' => '{"cart_label":""}',
           'rule_language' => '[]',
           'set_adjustments' => '{"operator":"product_cumulative","ranges":{"1":{"from":"3","value":"10","type":"fixed_set_price","label":""}},"cart_label":""}',
           'advanced_discount_message' => '{"display":"0","badge_color_picker":"#ffffff","badge_text_color_picker":"#000000","badge_text":""}',
           'discount_type' => 'wdr_set_discount',
           'used_coupons' => '[]'
       ),
       'buyx_gety_recipe_1' => array(
           'title' => 'Buy X get X - Buy 1 get 1 free - sample',
           'enabled' => 1,
           'exclusive' => 0,
           'usage_limits' => 0,
           'date_from' => '',
           'date_to' => '',
           'filters' => '{"1":{"type":"all_products"}}',
           'conditions' => '[]',
           'additional' => '{"condition_relationship":"and"}',
           'product_adjustments' => '[]',
           'cart_adjustments' => '[]',
           'buy_x_get_x_adjustments' => '[]',
           'buy_x_get_y_adjustments' => '{"type":"bxgy_all","operator":"product_cumulative","mode":"cheapest","ranges":{"1":{"from":"1","to":"1","free_qty":"1","free_type":"free_product","free_value":"","recursive":"1","product_varients":[],"product_variants_for_sale_badge":[]}}}',
           'bulk_adjustments' => '{"cart_label":""}',
           'rule_language' => '[]',
           'set_adjustments' => '[]',
           'advanced_discount_message' => '{"display":"0","badge_color_picker":"#ffffff","badge_text_color_picker":"#000000","badge_text":""}',
           'discount_type' => 'wdr_buy_x_get_y_discount',
           'used_coupons' => '[]'
       ),
       'buyx_gety_recipe_2' => array(
           'title' => 'Buy X get Y - Buy 2 get 1 free - sample',
           'enabled' => 1,
           'exclusive' => 0,
           'usage_limits' => 0,
           'date_from' => '',
           'date_to' => '',
           'filters' => '{"1":{"type":"all_products"}}',
           'conditions' => '[]',
           'additional' => '{"condition_relationship":"and"}',
           'product_adjustments' => '[]',
           'cart_adjustments' => '[]',
           'buy_x_get_x_adjustments' => '[]',
           'buy_x_get_y_adjustments' => '{"type":"bxgy_all","operator":"product_cumulative","mode":"cheapest","ranges":{"1":{"from":"2","to":"1","free_qty":"1","free_type":"free_product","free_value":"","recursive":"1","product_varients":[],"product_variants_for_sale_badge":[]}}}',
           'bulk_adjustments' => '{"cart_label":""}',
           'rule_language' => '[]',
           'set_adjustments' => '[]',
           'advanced_discount_message' => '{"display":"0","badge_color_picker":"#ffffff","badge_text_color_picker":"#000000","badge_text":""}',
           'discount_type' => 'wdr_buy_x_get_y_discount',
           'used_coupons' => '[]'
       ),
    );

    function recipeDetails(){
        return $this->simple_discount_recipe;
    }

    function save($arg)
    {
        //$current_time = current_time('mysql', true);
        $current_date_time = '';
        if (function_exists('current_time')) {
            $current_time = current_time('timestamp');
            $current_date_time = date('Y-m-d H:i:s', $current_time);
        }
        $current_user = get_current_user_id();
        $rule_id = NULL;



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

        $rule_id = DBTable::saveRule($column_format, $arg, $rule_id);
        if($rule_id){
            OnSaleShortCode::updateOnsaleRebuildPageStatus($rule_id);
            //do_action('advanced_woo_discount_rules_after_save_rule', $rule_id, $post, $arg);
        }
        return $rule_id;
    }
}