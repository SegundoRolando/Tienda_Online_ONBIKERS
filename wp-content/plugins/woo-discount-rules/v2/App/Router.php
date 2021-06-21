<?php

namespace Wdr\App;

use Wdr\App\Controllers\Admin\Settings;
use Wdr\App\Controllers\Admin\Tabs\AdvancedSection;
use Wdr\App\Controllers\Admin\WDRAjax;
use Wdr\App\Controllers\Admin\Tabs;
use Wdr\App\Controllers\ManageDiscount;
use Wdr\App\Controllers\ShortCodeManager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Router
{
    /**
     * Contains all major class objects to manage plugin
     * @var
     */
    public static $admin, $manage_discount, $ajax_requests, $chart_data_request, $short_code_manager;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        self::$admin = (!empty(self::$admin)) ? self::$admin : new Settings();
        self::$ajax_requests = (!empty(self::$ajax_requests)) ? self::$ajax_requests : new WDRAjax();
        self::$chart_data_request = (!empty(self::$chart_data_request)) ? self::$chart_data_request : new Tabs\Statistics();
        do_action('advanced_woo_discount_rules_before_initialize');
        $this->init();
        do_action('advanced_woo_discount_rules_after_initialize');
    }

    /**
     *Init the plugin hooks after plugin loaded
     */
    function init()
    {
        $compatibility = Tabs\Compatible::getInstance();
        $compatibility->runCompatibilityScripts();
        //admin ajax requests
        add_action('wp_ajax_wdr_ajax', array(self::$ajax_requests, 'wdr_ajax_requests'));
        add_action('wp_ajax_awdr_get_product_discount', array(self::$ajax_requests, 'awdr_get_discount_of_a_product'));
        add_action('wp_ajax_nopriv_awdr_get_product_discount', array(self::$ajax_requests, 'awdr_get_discount_of_a_product'));
        // All hooks needed for Admin
        if (is_admin() || wp_doing_ajax()) {
            add_action('admin_menu', array(self::$admin, 'AddMenu'));
            add_action('admin_enqueue_scripts', array(self::$admin, 'adminScripts'), 100);
            add_filter('plugin_action_links_' . WDR_PLUGIN_BASENAME, array( self::$admin, 'wdr_action_link' ));
            add_action('admin_notices', array(self::$admin, 'adminNotices'), 100);
            add_action('wp_ajax_wdr_admin_statistics', array( self::$chart_data_request, 'ajax' ));
            add_filter('woocommerce_screen_ids', function($screen_ids){
                $screen_ids[] = 'woocommerce_page_woo_discount_rules';
                $screen_ids[] = 'my-store_page_woo_discount_rules';
                return $screen_ids;
            });
        }
        add_action('admin_init', array(self::$admin, 'setupSurveyForm'), 10);
        /**
         * All hooks needed for both admin and site
         */
        $manage_discount_class = self::$manage_discount = (!empty(self::$manage_discount)) ? self::$manage_discount : new ManageDiscount();
        add_filter('advanced_woo_discount_rules_get_product_discount_price', array(self::$manage_discount, 'calculateProductDiscountPrice'), 100, 3);
        add_filter('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', array(self::$manage_discount, 'calculateProductDiscountPrice'), 100, 7);
        //Showing you saved text
        $display_saving_text = $manage_discount_class::$config->getConfig('display_saving_text', 'disabled');
        add_action('woocommerce_checkout_create_order_line_item', array(self::$manage_discount, 'onCreateWoocommerceOrderLineItem'), 10, 4);
        if ($display_saving_text != "disabled") {
            //Savings per line item
            if (in_array($display_saving_text, array('on_each_line_item', 'both_line_item_and_after_total'))) {
                add_action('woocommerce_after_order_itemmeta', array(self::$manage_discount, 'orderItemMetaDiscountDetails'), 1000, 3);
                add_filter('woocommerce_cart_item_subtotal', array(self::$manage_discount, 'getCartProductSubtotalPriceHtml'), 10, 3);
                add_filter('woocommerce_order_formatted_line_subtotal', array(self::$manage_discount, 'orderSubTotalDiscountDetails'), 1000, 3);
            }
            //Display total savings of order
            if (in_array($display_saving_text, array('after_total', 'both_line_item_and_after_total'))) {
                add_filter('woocommerce_cart_totals_order_total_html', array(self::$manage_discount, 'getCartTotalPriceHtml'), 10, 1);
                add_action('woocommerce_get_formatted_order_total', array(self::$manage_discount, 'displayTotalSavingsInOrderAfterOrderTotal'), 10,2);
                add_action( 'woocommerce_admin_order_totals_after_total', array( self::$manage_discount, 'displayTotalSavingsThroughDiscountInOrder'), 10);
            }
        }
        add_filter('advanced_woo_discount_rules_get_order_line_item_you_saved_text', array(self::$manage_discount, 'orderSubTotalDiscountDetails'), 10, 3);
        add_filter('advanced_woo_discount_rules_get_order_total_you_saved_text', array(self::$manage_discount, 'displayTotalSavingsInOrderAfterOrderTotal'), 10,2);

        $show_subtotal_promotion = $manage_discount_class::$config->getConfig('show_subtotal_promotion', '');
        $show_cart_quantity_promotion = $manage_discount_class::$config->getConfig('show_cart_quantity_promotion', '');
        if($show_subtotal_promotion == 1 || $show_cart_quantity_promotion == 1){
            $show_promo_message = $manage_discount_class::$config->getConfig('show_promo_text', '');
            if(!empty($show_promo_message) && is_array($show_promo_message)){
                if(in_array('shop_page', $show_promo_message)){
                    add_action('woocommerce_before_shop_loop', array(self::$manage_discount, 'displayPromotionMessages'), 10);
                }
                if(in_array('product_page', $show_promo_message)){
                    add_action('woocommerce_before_single_product', array(self::$manage_discount, 'displayPromotionMessages'), 10);
                }
                if(in_array('cart_page', $show_promo_message)){
                    add_action('woocommerce_before_cart', array(self::$manage_discount, 'displayPromotionMessages'), 10);
                }
                if(in_array('checkout_page', $show_promo_message)){
                    add_action('woocommerce_before_checkout_form', array(self::$manage_discount, 'displaySubTotalPromotionMessagesInCheckoutContainer'), 10);
                    add_action('woocommerce_review_order_before_cart_contents', array(self::$manage_discount, 'displaySubTotalPromotionMessagesInCheckout'), 10);
                }
            }
        }
        /**
         *  All hooks needed for front End
         */
        if (!is_admin() || wp_doing_ajax()) {
            //Suppress third party plugins from modifying the price
            $suppress_other_discount_plugins = $manage_discount_class::$config->getConfig('suppress_other_discount_plugins', 0);
            if (!empty($suppress_other_discount_plugins)) {
                add_action("wp_loaded", array(self::$manage_discount, 'suppressOtherDiscountPlugins'));
            }

            // Add required Styles
            add_action('wp_enqueue_scripts', array(self::$manage_discount, 'loadAssets'));
            add_action('wp_ajax_nopriv_wdr_ajax', array(self::$ajax_requests, 'wdr_ajax_requests'));
            // Set price for catalog and single product view
            add_filter('woocommerce_get_price_html', array(self::$manage_discount, 'getPriceHtml'), 100, 2);
            add_filter('woocommerce_get_price_html', array(self::$manage_discount, 'getPriceHtmlSalePriceAdjustment'), 9, 2);
            add_filter('woocommerce_variable_price_html', array(self::$manage_discount, 'getVariablePriceHtml'), 100, 2);

            add_filter('woocommerce_coupon_message', array(self::$manage_discount, 'removeAppliedMessageOfThirdPartyCoupon'), 10, 3);

            //Show on sale badge
            $show_on_sale_badge = $manage_discount_class::$config->getConfig('show_on_sale_badge', 'disabled');
            if (in_array($show_on_sale_badge, array('when_condition_matches', 'at_least_has_any_rules'))) {
               add_filter('woocommerce_product_is_on_sale', array(self::$manage_discount, 'isProductInSale'), 100, 2);
                $customize_on_sale_badge = $manage_discount_class::$config->getConfig('customize_on_sale_badge', '');
                if($customize_on_sale_badge == 1){
                    //For changing the sale tag text
                   add_filter( 'woocommerce_sale_flash', array(self::$manage_discount, 'replaceSaleTagText'), 100, 3);
                }
                $force_override_on_sale_badge = $manage_discount_class::$config->getConfig('force_override_on_sale_badge', '');
                if($force_override_on_sale_badge == 1){
                    add_action( "wp_loaded", array( self::$manage_discount, 'removeOnSaleFlashEvent' ) );
                    // change template of sale tag
                    add_filter('wc_get_template', array( self::$manage_discount, 'changeTemplateForSaleTag'), 10, 5);
                }
            }

            //Fix mini cart strikeout not displays while remove an cart item in cart.
            add_action('woocommerce_before_mini_cart', array(self::$manage_discount, 'reCalculateCartTotal'), 10);
            //cart
            add_action('woocommerce_before_calculate_totals', array(self::$manage_discount, 'applyCartProductDiscount'), 1000);
            add_action('woocommerce_cart_item_price', array(self::$manage_discount, 'getCartPriceHtml'), 1000, 3);
            add_filter('woocommerce_cart_totals_coupon_label', array(self::$manage_discount, 'overwriteCouponLabel'), 10, 2);
            add_action('woocommerce_cart_calculate_fees', array(self::$manage_discount, 'applyCartDiscount'));
            add_filter('woocommerce_get_shop_coupon_data', array(self::$manage_discount, 'checkCouponToApply'), 10, 2);

            add_action('woocommerce_after_calculate_totals', array(self::$manage_discount, 'applyVirtualCouponForCartRules'), 10);
            add_filter('woocommerce_get_shop_coupon_data', array(self::$manage_discount, 'validateVirtualCouponForCartRules'), 10, 2);
            add_filter('woocommerce_cart_totals_coupon_label',  array(self::$manage_discount, 'changeCouponLabelInFrontEnd'), 10, 2);

            add_filter('woocommerce_cart_totals_coupon_html', array(self::$manage_discount, 'hideZeroCouponValue'), 10, 2);
            $show_rule_message = $manage_discount_class::$config->getConfig('show_applied_rules_message_on_cart', 0);
            if (!empty($show_rule_message)) {
                //Show discount applied message
                add_action('woocommerce_before_cart', array(self::$manage_discount, 'showAppliedRulesMessages'), 10);
                add_action('woocommerce_before_checkout_form', array(self::$manage_discount, 'displayPromotionMessagesInCheckoutContainer'), 10);
                add_action('woocommerce_review_order_before_cart_contents', array(self::$manage_discount, 'displayPromotionMessagesInCheckout'), 10);
            }
            //After place order button clicked
            add_action('woocommerce_checkout_update_order_meta', array(self::$manage_discount, 'orderItemsSaved'), 10, 2);
            //Showing the bulk table
            $show_bulk_table = $manage_discount_class::$config->getConfig('show_bulk_table', 0);
            $position_to_show_bulk_table = $manage_discount_class::$config->getConfig('position_to_show_bulk_table', 'woocommerce_before_add_to_cart_form');
            if (!empty($show_bulk_table)) {
                add_action($position_to_show_bulk_table, array(self::$manage_discount, 'showBulkTableInPosition'));
            }
            $position_to_show_discount_bar = $manage_discount_class::$config->getConfig('position_to_show_discount_bar', 'woocommerce_before_add_to_cart_form');

            add_action($position_to_show_discount_bar, array(self::$manage_discount, 'showAdvancedTableInPosition'));

            add_action('advanced_woo_discount_rules_load_discount_table', array(self::$manage_discount, 'showBulkTableInPositionManually'), 10);
            add_action('advanced_woo_discount_rules_load_discount_bar', array(self::$manage_discount, 'showAdvancedTableInPositionManually'), 10);

            //Short code manager
            self::$short_code_manager = (!empty(self::$short_code_manager)) ? self::$short_code_manager : new ShortCodeManager();
            add_shortcode('awdr_sale_items_list', array(self::$short_code_manager, 'saleItemsList'));

            /*$display_banner_text = $manage_discount_class::$config->getConfig('display_banner_text', '');
            if($display_banner_text != '' && is_array($display_banner_text) && !empty($display_banner_text)){
                foreach ($display_banner_text as $display_hook){
                    add_action($display_hook, array(self::$short_code_manager, 'bannerContent'));
                }
            }
            add_shortcode('awdr_banner_content', array(self::$short_code_manager, 'bannerContent'));*/

            // For handling BOGO
            add_filter('advanced_woo_discount_rules_after_processed_bogo_free_auto_add', array(self::$manage_discount, 'removeThirdPartyCoupon'));
        }

        add_action('advanced_woo_discount_rules_after_initialize', array(self::$manage_discount, 'awdrExportCsv'));

        //Deprecated the event advanced_woo_discount_rules_additional_fee_value
        add_filter('advanced_woo_discount_rules_additional_fee_amount', array(self::$manage_discount, 'applyTaxInFee'), 10, 2);

        //For loading snippets
        $advance_option = new AdvancedSection();
        $advance_option->runAdvancedOption($manage_discount_class::$config);
    }
}