<?php

namespace Wdr\App\Controllers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Configuration
{
    /**
     * settings constant
     * @var string
     */
    const DEFAULT_OPTION = 'woo-discount-config-v2';
    const ADVANCED_OPTION = 'awdr_advanced_option_config_v2';

    public static $instance;

    /**
     * Contains all the configuration details
     * @var array
     */
    private static $config = array(), $advanced_section_config=array(), $default_config = array(
        'modify_price_at_product_page' => 1,//0,1
        'modify_price_at_category_page' => 1,//0,1
        'modify_price_at_shop_page' => 1,//0,1
        'apply_product_discount_to' => 'biggest_discount',//first,biggest_discount,lowest_discount,all
        'apply_cart_discount_to' => 'biggest_discount',//biggest_discount,lowest_discount,first,all
        'calculate_discount_from' => 'sale_price',//sale_price,regular_price
        'show_on_sale_badge' => 'disabled',//when_condition_matches,at_least_has_any_rules,disabled
        'show_strikeout_on_cart' => 1,//1,0
        'show_applied_rules_message_on_cart' => 0,//1,0
        'free_shipping_title' => 'free shipping',
        'apply_cart_discount_as' => 'fee',//coupon,fee
        'combine_all_cart_discounts' => 0,//0,1
        'discount_label_for_combined_discounts' => 'Cart Discount',//show when "combine_all_cart_discounts" is 1
        'applied_rule_message' => 'Discount <strong>{{title}}</strong> has been applied to your cart.',
        'you_saved_text' => 'You saved {{total_discount}}',
        'display_saving_text' => 'disabled',
        'show_bulk_table' => 0,//0,1
        'table_column_header' => 1,//0,1
        'table_title_column' => 1,//0,1
        'table_discount_column' => 1,//0,1
        'table_range_column' => 1,//0,1
        'refresh_order_review' => 0,//1,0
        'suppress_other_discount_plugins' => 0,//1,0
        'show_sale_badge_only_on_condition_passed' => 0,//1,0
        'position_to_show_bulk_table' => 'woocommerce_before_add_to_cart_form',//woocommerce_product_meta_end,woocommerce_product_meta_start,woocommerce_after_add_to_cart_form,woocommerce_before_add_to_cart_form,woocommerce_after_single_product,woocommerce_before_single_product,woocommerce_after_single_product_summary,woocommerce_before_single_product_summary
        'position_to_show_discount_bar' => 'woocommerce_before_add_to_cart_form',//woocommerce_product_meta_end,woocommerce_product_meta_start,woocommerce_after_add_to_cart_form,woocommerce_before_add_to_cart_form,woocommerce_after_single_product,woocommerce_before_single_product,woocommerce_after_single_product_summary,woocommerce_before_single_product_summary
        'customize_bulk_table_title' => 0,
        'customize_bulk_table_discount' => 2,
        'customize_bulk_table_range' => 1,
        'apply_discount_subsequently' => 0, //0,1
        'show_table_discount_column_value' => 1, //0,1
        'table_title_column_name' => 'Title', //Title
        'table_discount_column_name' => 'Discount', //Discount
        'table_range_column_name' => 'Range', //Range
        'apply_cart_discount_subsequently' => 0,//1,0
        'awdr_banner_editer' => 0, //false
        'display_banner_text' => 0, //0
        'show_strikeout_when' => 'show_when_matched', //show_after_matched, show_dynamically
        'disable_coupon_when_rule_applied' => 'run_both', //run_both, disable_coupon, disable_rules
        'customize_on_sale_badge' => '',
        'force_override_on_sale_badge' => '',
        'on_sale_badge_html' => '<span class="onsale">Sale!</span>',
        'licence_key' => '',
        'show_subtotal_promotion' => 0,
        'show_cart_quantity_promotion' => 0,
        'show_promo_text' => '',
        'wdr_override_custom_price' => 0, // 0,1
        'disable_recalculate_total' => 0, // 0,1
        'disable_recalculate_total_when_coupon_apply' => 0, // 0,1
    );
    private static $default_advanced_section_config = array(
        'wdr_override_custom_price' => 0, // 0,1
        'wdr_disable_recalculate_total' => 0, // 0,1
        'wdr_recalculate_total_when_coupon_apply' => 0, // 0,1
    );

    /**
     * To create instance
     * */
    public static function getInstance()
    {
        if (!self::$instance)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Save the configuration
     * @param $data
     * @param $key
     * @return bool
     */
    static function saveConfig($key = self::DEFAULT_OPTION, $data = array())
    {
        return update_option($key, $data);
    }

    /**
     * @param $key - what configuration need to get
     * @param string $default - default value if config value not found
     * @return string - configuration value
     */
    function getConfig($key, $default = '')
    {
        if (empty(self::$config) || empty(self::$advanced_section_config)) {
            $this->setConfig();
        }
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        } elseif (isset(self::$advanced_section_config[$key])){
            return self::$advanced_section_config[$key];
        }elseif (isset(self::$default_config[$key])) {
            //Check config found in default config
            return self::$default_config[$key];
        } else {
            return $default;
        }
    }

    /**
     * Set rule configuration to static variable
     */
    function setConfig()
    {
        self::$config = get_option(self::DEFAULT_OPTION);
        self::$advanced_section_config = get_option(self::ADVANCED_OPTION);
    }
}