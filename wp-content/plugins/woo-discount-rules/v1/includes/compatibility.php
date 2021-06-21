<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class FlycartWooDiscountRulesPricingRules
 */
if (!class_exists('FlycartWooDiscountRulesCompatibility')) {
    class FlycartWooDiscountRulesCompatibility
    {
        protected static $is_pro_version = false;

        protected static $discount_base;

        /**
         * Initialize the scripts
         * */
        public static function init()
        {
            self::set_is_pro_version();
            self::set_discount_base_object();
            if (self::$is_pro_version) {
                add_filter('woocommerce_cart_totals_coupon_label', 'FlycartWooDiscountRulesCompatibility::change_woocommerce_cart_totals_coupon_label', 10, 2);
                $additional_taxonomies = self::$discount_base->getConfigData('additional_taxonomies', array(), 'taxonomy');
                if (FlycartWooDiscountRulesGeneralHelper::is_countable($additional_taxonomies)) {
                    add_filter('woo_discount_rules_accepted_taxonomy_for_category', 'FlycartWooDiscountRulesCompatibility::woo_discount_rules_accepted_taxonomy_for_category', 10);
                    add_filter('woo_discount_rules_load_additional_taxonomy', 'FlycartWooDiscountRulesCompatibility::woo_discount_rules_load_additional_taxonomy', 10, 2);
                }
            }
        }

        /**
         * Set is pro version or not
         * */
        protected static function set_is_pro_version()
        {
            $purchase_helper = new FlycartWooDiscountRulesPurchase();
            self::$is_pro_version = $purchase_helper->isPro();
        }

        /**
         * Set is pro version or not
         * */
        protected static function set_discount_base_object()
        {
            self::$discount_base = FlycartWooDiscountBase::get_instance();
        }

        /**
         * To support additional taxonomy in categories
         * */
        public static function woo_discount_rules_accepted_taxonomy_for_category($taxonomy)
        {
            $additional_taxonomies = self::$discount_base->getConfigData('additional_taxonomies', array(), 'taxonomy');
            if (FlycartWooDiscountRulesGeneralHelper::is_countable($additional_taxonomies)) {
                foreach ($additional_taxonomies as $additional_taxonomy) {
                    $taxonomy[] = $additional_taxonomy;
                }
            }

            return $taxonomy;
        }

        /**
         * To support additional taxonomy in categories
         * */
        public static function woo_discount_rules_load_additional_taxonomy($categories, $product_id)
        {
            $additional_taxonomies = self::$discount_base->getConfigData('additional_taxonomies', array(), 'taxonomy');
            if (FlycartWooDiscountRulesGeneralHelper::is_countable($additional_taxonomies)) {
                foreach ($additional_taxonomies as $taxonomy) {
                    $terms = get_the_terms($product_id, $taxonomy);
                    if (!empty($terms)) {
                        if ((is_object($terms) || is_array($terms))) {
                            if (FlycartWooDiscountRulesGeneralHelper::is_countable($terms)) {
                                foreach ($terms as $term) {
                                    if (!empty($term->term_id)) {
                                        $categories[] = $term->term_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $categories;
        }

        /**
         * To change the discount name/code in front end
         * */
        public static function change_woocommerce_cart_totals_coupon_label($text, $coupon)
        {
            $customize_coupon_name_html = self::$discount_base->getConfigData('customize_coupon_name_html', '');
            if (!empty($customize_coupon_name_html)) {
                global $flycart_woo_discount_rules;
                //process only for woo discount rules
                if (!empty($flycart_woo_discount_rules->cart_rules) && !empty($flycart_woo_discount_rules->cart_rules->coupon_code)) {
                    $coupon_code = FlycartWoocommerceCoupon::get_code($coupon);
                    //process only for woo discount rules
                    if (!empty($coupon_code) && $coupon_code == $flycart_woo_discount_rules->cart_rules->coupon_code) {
                        $applied_rules = $flycart_woo_discount_rules->cart_rules->matched_discounts;
                        $rule_names = '';

                        if (isset($applied_rules) && count($applied_rules) && isset($applied_rules['name'])) {
                            $rule_names = implode(', ', $applied_rules['name']);
                        }
                        $new_text = __($customize_coupon_name_html, 'woo-discount-rules');
                        if (!empty($new_text)) {
                            if (!empty($rule_names)) {
                                $new_text = str_replace('{rule_name}', $rule_names, $new_text);
                            }
                            //change the value here to have your own customized display
                            $text = $new_text;
                        }
                    }
                }
            }

            return $text;
        }
    }

    FlycartWooDiscountRulesCompatibility::init();
}