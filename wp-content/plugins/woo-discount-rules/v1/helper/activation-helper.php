<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! class_exists( 'FlycartWooDiscountRulesActivationHelper' ) ) {
    class FlycartWooDiscountRulesActivationHelper
    {
        public static function isWooCommerceActive()
        {
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));
            if (is_multisite()) {
                $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
            }
            return in_array('woocommerce/woocommerce.php', $active_plugins, false) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
        }

        public static function flyCartWooDiscountRulesAddSampleRules(){
            $price_rule = self::flyCartWooDiscountRulesGetRulesFromPost('woo_discount');
            $cart_rule = self::flyCartWooDiscountRulesGetRulesFromPost('woo_discount_cart');
            if($price_rule){
                // do nothing
            } else {
                $price_rules = self::flyCartWooDiscountRulesSamplePriceRules();
                self::flyCartWooDiscountRulesInsertSampleRules($price_rules);
            }
            if($cart_rule){
                // do nothing
            } else {
                $cart_rules = self::flyCartWooDiscountRulesSampleCartRules();
                self::flyCartWooDiscountRulesInsertSampleRules($cart_rules);
            }
        }

        public static function flyCartWooDiscountRulesInsertSampleRules($rules){
            foreach ($rules as $rule){
                $metaData = $rule['meta'];
                unset($rule['meta']);
                $insert = wp_insert_post($rule);
                if($insert){
                    foreach ($metaData as $index => $value) {
                        add_post_meta($insert, $index, $value, true);
                    }
                }
            }
        }

        /**
         * Get Email template from post
         * */
        public static function flyCartWooDiscountRulesGetRulesFromPost($type){
            if($type != ''){
                global $wpdb;
                $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = '".sanitize_text_field($type)."'" );
                return $postid;
            } else {
                return false;
            }
        }

        public static function flyCartWooDiscountRulesSampleCartRules(){
            $rules = array();

            $rules['cart_subtotal'] = array(
                'post_type' 	=> 'woo_discount_cart',
                'post_title' 	=> 'Sample - Subtotal based discount',
                'post_name' 	=> 'sample-subtotal-based-discount',
                'post_content'  => 'Sample - Subtotal based discount',
                'post_status'  => 'publish',
                'meta' => array(
                    'rule_order' => 1,
                    'rule_name' => 'Sample - Subtotal based discount',
                    'rule_descr' => 'Get 10% off when subtotal reaches $500',
                    'date_from' => '',
                    'date_to' => '',
                    'discount_rule' => '[{"subtotal_least":"500"}]',
                    'promotion_subtotal_from' => '300',
                    'promotion_message' => 'Spend {{difference_amount}} and get 10% off',
                    'discount_type' => 'percentage_discount',
                    'to_discount' => '10',
                    'product_discount_quantity' => '1',
                    'cart_discounted_products' => '{}',
                    'dynamic_coupons_to_apply' => '',
                    'status' => 'disable'
                )
            );

            return $rules;
        }

        public static function flyCartWooDiscountRulesSamplePriceRules(){
            $rules = array();
            $rules['store_wide'] = array(
                'post_type' 	=> 'woo_discount',
                'post_title' 	=> 'Sample - Store wide discount',
                'post_name' 	=> 'sample-store-wide-discount',
                'post_content'  => 'Sample - Store wide discount',
                'post_status'  => 'publish',
                'meta' => array(
                    'rule_order' => 1,
                    'rule_name' => 'Sample - Store wide discount',
                    'rule_descr' => '20% discount on all products in store',
                    'rule_method' => 'qty_based',
                    'date_from' => '',
                    'date_to' => '',
                    'apply_to' => 'all_products',
                    'is_cumulative_for_products' => '1',
                    'customer' => 'all',
                    'coupons_to_apply_option' => 'none',
                    'dynamic_coupons_to_apply' => '',
                    'subtotal_to_apply_option' => 'none',
                    'subtotal_to_apply' => '',
                    'based_on_purchase_history' => '0',
                    'product_based_condition' => '{}',
                    'discount_range' => '{"1":{"min_qty":"1","max_qty":"999","discount_type":"percentage_discount","to_discount":"20","discount_product_option":"all","discount_bogo_qty":"1","discount_product_item_type":"dynamic","discount_product_items":"","discount_product_qty":"","discount_product_discount_type":"","discount_product_percent":"","title":"Sample - Store wide discount"}}',
                    'product_based_discount' => '{}',
                    'coupons_to_apply' => '',
                    'exclude_sale_items' => '0',
                    'user_roles_to_apply' => '[]',
                    'product_to_exclude' => 'a:0:{}',
                    'status' => 'disable',
                    'wpml_language' => '',
                    'product_to_exclude_variants' => 'a:0:{}',
                )
            );

            $rules['bulk_discount'] = array(
                'post_type' 	=> 'woo_discount',
                'post_title' 	=> 'Sample - Bulk discounts / Tiered pricing discounts',
                'post_name' 	=> 'sample-bulk-discounts-tiered-pricing-discounts',
                'post_content'  => 'Sample - Bulk discounts / Tiered pricing discounts',
                'post_status'  => 'publish',
                'meta' => array(
                    'rule_order' => 2,
                    'rule_name' => 'Sample - Bulk discounts / Tiered pricing discounts',
                    'rule_descr' => 'Buy 6 - 11 quantities get 5% off, Buy 12 - 17 quantities get 10% off and so on',
                    'rule_method' => 'qty_based',
                    'date_from' => '',
                    'date_to' => '',
                    'apply_to' => 'all_products',
                    'is_cumulative_for_products' => '1',
                    'customer' => 'all',
                    'coupons_to_apply_option' => 'none',
                    'dynamic_coupons_to_apply' => '',
                    'subtotal_to_apply_option' => 'none',
                    'subtotal_to_apply' => '',
                    'based_on_purchase_history' => '0',
                    'product_based_condition' => '{}',
                    'discount_range' => '{"1":{"min_qty":"6","max_qty":"11","discount_type":"percentage_discount","to_discount":"5","discount_product_option":"all","discount_bogo_qty":"1","discount_product_item_type":"dynamic","discount_product_items":"","discount_product_qty":"","discount_product_discount_type":"","discount_product_percent":"","title":"Sample - Bulk discounts / Tiered pricing discounts"},"2":{"min_qty":"12","max_qty":"17","discount_type":"percentage_discount","to_discount":"10","discount_product_option":"all","discount_bogo_qty":"","discount_product_item_type":"dynamic","discount_product_items":"","discount_product_qty":"","discount_product_discount_type":"","discount_product_percent":"","title":"Sample - Bulk discounts / Tiered pricing discounts"},"3":{"min_qty":"18","max_qty":"999","discount_type":"percentage_discount","to_discount":"15","discount_product_option":"all","discount_bogo_qty":"","discount_product_item_type":"dynamic","discount_product_items":"","discount_product_qty":"","discount_product_discount_type":"","discount_product_percent":"","title":"Sample - Bulk discounts / Tiered pricing discounts"}}',
                    'product_based_discount' => '{}',
                    'coupons_to_apply' => '',
                    'exclude_sale_items' => '0',
                    'user_roles_to_apply' => '[]',
                    'product_to_exclude' => 'a:0:{}',
                    'status' => 'disable',
                    'wpml_language' => '',
                    'product_to_exclude_variants' => 'a:0:{}',
                )
            );

            return $rules;
        }
    }
}
if (!function_exists('onWooDiscountActivate')) {
    function onWooDiscountActivate() {
        // Dependency Check.
        if (!FlycartWooDiscountRulesActivationHelper::isWooCommerceActive()) wp_die('Please Install WooCommerce to Continue !');

        FlycartWooDiscountRulesActivationHelper::flyCartWooDiscountRulesAddSampleRules();
    }
}
if (!function_exists('onWooDiscountDeactivation')) {
    function onWooDiscountDeactivation() {}
}