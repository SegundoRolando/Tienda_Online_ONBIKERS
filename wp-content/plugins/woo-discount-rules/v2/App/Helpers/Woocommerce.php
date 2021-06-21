<?php

namespace Wdr\App\Helpers;

use WC_Order;
use WC_Order_Refund;
use WC_Product;
use Wdr\App\Controllers\ManageDiscount;
use Wdr\App\Router;
use WP_Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Woocommerce
{
    /**
     * Check product type is found in product
     * @param $product - Woocommerce product object
     * @param $type - product types
     * @return bool
     */
    static $custom_taxonomies;
    static $checkout_post = null;

    protected static $products = array();

    static function productTypeIs($product, $type)
    {
        if (method_exists($product, 'is_type')) {
            return $product->is_type($type);
        }
        return false;
    }

    static function getConvertedFixedPrice($value, $type = '')
    {
        return apply_filters('advanced_woo_discount_rules_converted_currency_value', $value, $type);
    }

    /**
     * Check the order has particular shipping method
     * @param $order
     * @param $method
     * @return bool
     */
    static function orderHasShippingMethod($order, $method)
    {
        if (method_exists($order, 'has_shipping_method')) {
            return $order->has_shipping_method($method);
        }
        return false;
    }

    /**
     * Check the order has particular shipping method
     * @param $order
     * @return bool
     */
    static function getOrderTotal($order)
    {
        if (method_exists($order, 'get_total')) {
            return $order->get_total();
        }
        return 0;
    }

    /**
     * get order object from order id
     * @param $order_id
     * @return array|bool|WC_Order|WC_Order_Refund
     */
    static function getOrder($order_id)
    {
        if (empty($order_id)) {
            return array();
        }
        if (function_exists('wc_get_order')) {
            return wc_get_order($order_id);
        }
        return array();
    }

    /**
     * get the product ID
     * @param $product - woocommerce product object
     * @return null
     */
    static function getProductId($product)
    {
        if (method_exists($product, 'get_id')) {
            return $product->get_id();
        } elseif (isset($product->id)) {
            $product_id = $product->id;
            if (isset($product->variation_id)) {
                $product_id = $product->variation_id;
            }
            return $product_id;
        } else {
            return NULL;
        }
    }

    /**
     * get product id from cart item id
     * */
    public static function getProductIdFromCartItem($cart_item){
        $product_id = null;
        if(isset($cart_item['product_id'])){
            $product_id = $cart_item['product_id'];
            if(isset($cart_item['variation_id']) && !empty($cart_item['variation_id'])){
                $product_id = $cart_item['variation_id'];
            }
        } else if(isset($cart_item['data'])){
            $product_id = self::getProductId($cart_item['data']);
        }

        return $product_id;
    }

    /**
     * Get the product from product id
     * @param $product_id
     * @return bool|false|WC_Product|null
     */
    static function getProduct($product_id)
    {
        if(isset(self::$products[$product_id])){
            return self::$products[$product_id];
        } else if (function_exists('wc_get_product')) {
            self::$products[$product_id] = apply_filters('advanced_woo_discount_rules_get_wc_product', wc_get_product($product_id), $product_id);

            return self::$products[$product_id];
        }
        return false;
    }

    static function is_ajax()
    {
        if (function_exists('is_ajax')) {
            return is_ajax();
        }
        return false;
    }

    /**
     * Get the product from Cart item data/product id
     * @param $cart_item object
     * @param $product_id int
     * @return mixed
     */
    static function getProductFromCartItem($cart_item, $product_id = 0)
    {
        $product = isset($cart_item['data']) ? $cart_item['data'] : $cart_item;
        if (!is_a($product, 'WC_Product')) {
            $product = self::getProduct($product_id);
        }
        if (is_a($product, 'WC_Product')) {
            return apply_filters('advanced_woo_discount_rules_get_product_from_cart_item', $product, $cart_item);
        }
        return false;
    }

    /**
     * Get the sale price of the product
     * @param $product
     * @return bool
     */
    static function getProductSalePrice($product)
    {
        if (self::isProductInSale($product)) {
            if (method_exists($product, 'get_sale_price')) {
                $price = $product->get_sale_price();
                return apply_filters('advanced_woo_discount_rules_get_sale_price', $price, $product);
            }
            return false;
        }
        return false;
    }

    /**
     * Check the produt in sale
     * @param $product
     * @return bool
     */
    static function isProductInSale($product)
    {
        if (method_exists($product, 'is_on_sale') && method_exists($product, 'get_sale_price')) {
            if($product->is_on_sale('')){
                if($product->get_sale_price()){
                    return apply_filters('advanced_woo_discount_rules_is_on_sale', true, $product);
                }else{
                    return apply_filters('advanced_woo_discount_rules_is_on_sale', false, $product);
                }
            }
        }
        return false;
    }

    /**
     * Get the regular price of the product
     * @param $product
     * @return bool
     */
    static function getProductRegularPrice($product)
    {
        if (method_exists($product, 'get_regular_price')) {
            $price = $product->get_regular_price();
            return apply_filters('advanced_woo_discount_rules_get_regular_price', $price, $product);
        }
        return false;
    }

    /**
     * Get the actual price of the product
     * @param $product
     * @return bool
     */
    static function getProductPrice($product)
    {
        if (method_exists($product, 'get_price')) {
            $price = $product->get_price();
            return apply_filters('advanced_woo_discount_rules_get_price', $price, $product);
        }
        return false;
    }

    /**
     * Get the categories of the product
     * @param $product
     * @return array
     */
    static function getProductCategories($product)
    {
        $categories = $variant = array();
        if (method_exists($product, 'get_category_ids')) {
            if (self::productTypeIs($product, 'variation')) {
                $variant = $product;
                $parent_id = self::getProductParentId($product);
                $product = self::getProduct($parent_id);
            }
            $categories = $product->get_category_ids();
        }
        return apply_filters('advanced_woo_discount_rules_get_product_categories', $categories, $product, $variant);
    }

    /**
     * Get product tags
     * @param $product
     * @return array
     */
    static function getProductTags($product)
    {
        if (method_exists($product, 'get_tag_ids')) {
            return $product->get_tag_ids();
        }
        return array();
    }

    /**
     * Get product attributes
     * @param $product
     * @return array
     */
    static function getProductAttributes($product)
    {
        if (method_exists($product, 'get_attributes')) {
            return $product->get_attributes();
        }
        return array();
    }

    /**
     * Get product attributes
     * @param $product
     * @return array
     */
    static function getProductChildren($product)
    {
        if (method_exists($product, 'get_children')) {
            return $product->get_children();
        }
        return array();
    }

    /**
     * Get product SKU
     * @param $product
     * @return bool
     */
    static function getProductSku($product)
    {
        if (method_exists($product, 'get_sku')) {
            return $product->get_sku();
        }
        return NULL;
    }

    /**
     * Get product price suffix
     * @param $product
     * @param $price
     * @param $discount_prices
     * @return bool
     */
    static function getProductPriceSuffix($product, $price = '', $discount_prices = array())
    {
        if (method_exists($product, 'get_price_suffix')) {
            return apply_filters('advanced_woo_discount_rules_price_suffix', $product->get_price_suffix($price), $product, $price, $discount_prices);
        }
        return NULL;
    }

    /**
     * Get attribute Name
     * @param $attribute
     * @return array
     */
    static function getAttributeName($attribute)
    {
        if (method_exists($attribute, 'get_name')) {
            return $attribute->get_name();
        }
        return NULL;
    }

    /**
     * Get attribute Option
     * @param $attribute
     * @return array
     */
    static function getAttributeOption($attribute)
    {
        if (method_exists($attribute, 'get_options')) {
            return $attribute->get_options();
        }
        return array();
    }

    /**
     * Get attribute Option
     * @param $attribute
     * @return array
     */
    static function getAttributeVariation($attribute)
    {
        if (method_exists($attribute, 'get_variation')) {
            return $attribute->get_variation();
        }
        return true;
    }

    /**
     * Get product custom taxonomy
     * @return array|null
     */
    static function getCustomProductTaxonomies()
    {
        if (!empty(self::$custom_taxonomies)) {
            return self::$custom_taxonomies;
        }
        if (function_exists('get_taxonomies')) {
            self::$custom_taxonomies = array_filter(get_taxonomies(array(
                'show_ui' => true,
                'show_in_menu' => true,
                'object_type' => array('product'),
            ), 'objects'), function ($tax) {
                return !in_array($tax->name, array('product_cat', 'product_tag'));
            });
            self::$custom_taxonomies = apply_filters('advanced_woo_discount_rules_get_custom_taxonomies', self::$custom_taxonomies);
        }
        return self::$custom_taxonomies;
    }

    /**
     * Format the sale price
     * @param $price1
     * @param $price2
     * @return string|null
     */
    static function formatSalePrice($price1, $price2)
    {
        if (function_exists('wc_format_sale_price')) {
            return apply_filters('advanced_woo_discount_rules_format_sale_price', wc_format_sale_price($price1, $price2), $price1, $price2);
        }
        return NULL;
    }

    /**
     * format the price //range
     * @param $min_price
     * @param $max_price
     * @param $original_html
     * @return string
     */
    static function formatPriceRange($min_price, $max_price, $original_html = false)
    {
        if (function_exists('wc_format_price_range')) {
            $html = wc_format_price_range($min_price, $max_price);
        } else {
            $html = self::formatPrice($min_price) . ' - ' . self::formatPrice($max_price);
        }

        return apply_filters('advanced_woo_discount_rules_format_sale_price_range', $html, $min_price, $max_price, $original_html);
    }

    /**
     * format the price
     * @param $price
     * @param $args
     * @return string
     */
    static function formatPrice($price, $args = array())
    {
        if (function_exists('wc_price')) {
            return wc_price($price, $args);
        }
        return $price;
    }

    /**
     * format currency code
     * @return string
     */
    static function get_currency_symbol($code = '')
    {
        if (function_exists('get_woocommerce_currency_symbol')) {
            return get_woocommerce_currency_symbol($code);
        }
        return $code;
    }

    /**
     * format given string to upper
     * @param string $string String to format.
     * @return string
     */
    static function formatStringToUpper($string)
    {
        if (function_exists('wc_strtoupper')) {
            return wc_strtoupper($string);
        } else{
            return strtoupper($string);
        }
    }

    /**
     * format given string to lower case
     * @param string $string String to format.
     * @return string
     */
    static function formatStringToLower($string)
    {
        if (function_exists('wc_strtolower')) {
            return wc_strtolower($string);
        } else {
            return strtolower($string);
        }
    }

    /**
     * get cart items
     * @return array
     */
    static function getCart($recalculate_total = false)
    {
        if($recalculate_total){
            if(apply_filters('advanced_woo_discount_rules_do_recalculate_total', $recalculate_total)){
                self::reCalculateCartTotal();
            }
        }
        $cart = array();
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null){
                if (method_exists(WC()->cart, 'get_cart')) {
                    $cart = WC()->cart->get_cart();
                }
            }
        }
        return apply_filters('advanced_woo_discount_rules_get_cart', $cart);
    }

    /**
     * calculate totals
     * @return array
     */
    static function calculateCartTotals()
    {
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null){
                if (method_exists(WC()->cart, 'calculate_totals')) {
                    WC()->cart->calculate_totals();
                }
            }
        }
    }

    static function reCalculateCartTotal(){
        remove_action('woocommerce_before_calculate_totals', array(Router::$manage_discount, 'applyCartProductDiscount'), 1000);
        self::calculateCartTotals();
        add_action('woocommerce_before_calculate_totals', array(Router::$manage_discount, 'applyCartProductDiscount'), 1000);
    }

    /**
     * get shipping packages
     * @return array
     */
    static function get_shipping_packages()
    {
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'get_shipping_packages')) {
                    return WC()->cart->get_shipping_packages();
                }
            }
        }
        return null;
    }

    static function round($value){
        if(function_exists('wc_get_price_decimals')){
            return round( $value, wc_get_price_decimals() );
        } else {
            return round( $value, get_option( 'woocommerce_price_num_decimals', 2 ) );
        }
    }

    /**
     * Add cart item
     *
     * @access public
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @param array $variation
     * @param array $cart_item_data
     * @return boolean
     */
    public static function add_to_cart($product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array())
    {
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'add_to_cart')) {
                    return WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
                }
            }
        }

        return false;
    }

    /**
     * set quantity
     *
     * @access public
     * @param string $cart_item_key
     * @param int $quantity
     * @param boolean $refresh_totals
     * @return boolean
     */
    public static function set_quantity( $cart_item_key, $quantity = 1, $refresh_totals = true ){
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'set_quantity')) {
                    return WC()->cart->set_quantity($cart_item_key, $quantity, $refresh_totals);
                }
            }
        }

        return false;
    }

    /**
     * Remove cart item
     *
     * @access public
     * @return boolean
     */
    public static function remove_cart_item($_cart_item_key)
    {
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'remove_cart_item')) {
                    return WC()->cart->remove_cart_item($_cart_item_key);
                }
            }
        }

        return false;
    }

    /**
     * Remove coupon from cart
     *
     * @access public
     * @param string $code
     * @return boolean
     */
    public static function remove_coupon($code)
    {
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'remove_coupon')) {
                    return WC()->cart->remove_coupon($code);
                }
            }
        }

        return false;
    }

    /**
     * Add notice
     *
     * @access public
     * @param $message string
     * @param $type string
     * @param $data array
     */
    public static function wc_add_notice($message, $type = 'success', $data = array())
    {
        if (function_exists('wc_add_notice')) {
            wc_add_notice( $message, $type,  $data);
        }
    }

    /**
     * Remove specific notice
     *
     * @access public
     * @param $remove_message string
     * @param $type string
     */
    public static function removeSpecificNoticeFromSession($remove_message, $type = 'success')
    {
        $all_notices  = self::getSession('wc_notices', array());
        if(!empty($all_notices)){
            foreach ($all_notices as $key => $messages){
                if($key == $type){
                    if(!empty($messages)){
                        foreach ($messages as $msg_key => $message){
                            if(isset($message['notice'])){
                                if($message['notice'] == $remove_message){
                                    unset($all_notices[$key][$msg_key]);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        self::setSession('wc_notices', $all_notices);
    }


    /**
     * get the parent id of the particular product
     * @param $product
     * @return int
     */
    static function getProductParentId($product)
    {
        $parent_id = 0;
        if (is_int($product)) {
            $product = self::getProduct($product);
        }
        if (method_exists($product, 'get_parent_id')) {
            $parent_id = $product->get_parent_id();
        }
        return apply_filters('advanced_woo_discount_rules_get_product_parent_id', $parent_id, $product);
    }

    /**
     * get cart items
     * @param $cart
     * @return array
     */
    static function getCartItems($cart)
    {
        $cart_items = array();
        if (method_exists($cart, 'get_cart_contents')) {
            $cart_items = $cart->get_cart_contents();
        }
        return apply_filters('advanced_woo_discount_rules_get_cart_items', $cart_items, $cart);
    }

    /**
     * get cart items
     * @return array
     */
    static function getCartSubtotal()
    {
        if (function_exists('WC')) {
            $subtotal = 0;
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'get_subtotal')) {
                    $subtotal = WC()->cart->get_subtotal();
                } elseif (isset(WC()->cart->subtotal)) {
                    $subtotal = WC()->cart->subtotal;
                }
            }
            return apply_filters('advanced_woo_discount_rules_get_cart_subtotal', $subtotal);
        }

        return 0;
    }

    /**
     * get line item subtotal
     * @return array
     */
    static function getCartLineItemSubtotal($cart_item)
    {
        $tax_display_type = get_option('woocommerce_tax_display_cart');
        if ($tax_display_type === 'excl') {
            $line_subtotal = (isset($cart_item['line_subtotal'])) ? $cart_item['line_subtotal'] : 0;
        } else {
            $line_subtotal = (isset($cart_item['line_subtotal'])) ? $cart_item['line_subtotal'] : 0;
            $line_subtotal_tax = (isset($cart_item['line_subtotal_tax'])) ? $cart_item['line_subtotal_tax'] : 0;
            $line_subtotal = $line_subtotal+$line_subtotal_tax;
        }

        return apply_filters('advanced_woo_discount_rules_line_item_subtotal', $line_subtotal, $cart_item, $tax_display_type);
    }

    /**
     * Add cart fee
     * @param $cart
     * @param $name
     * @param $fee
     * @return array
     */
    static function addCartFee($cart, $name, $fee)
    {
        if (method_exists($cart, 'add_fee')) {
            if(apply_filters('advanced_discount_rules_do_add_fee', true, $cart)){
                if(!apply_filters('advanced_discount_rules_calculate_tax_with_fee', true, $name, $cart)){
                    add_filter('woocommerce_cart_totals_get_fees_from_cart_taxes', function ($fee_taxes, $fee, $cart) use ($name) {
                        if(isset($fee->object->name)){
                            if($fee->object->name == $name) {
                                $fee_taxes = array();
                            }
                        }

                        return $fee_taxes;
                    }, 10, 3);
                }
                $fee = apply_filters('advanced_discount_rules_discount_fee_amount', $fee, $name, $cart);
                $name = __($name, 'woo-discount-rules');
                return $cart->add_fee($name, $fee);
            }
        }
        return array();
    }

    static function isTaxEnabled(){
        if(get_option( 'woocommerce_calc_taxes' ) === 'yes'){
            return true;
        } else {
            return false;
        }
    }

    static function isEnteredPriceIncludeTax(){
        if(get_option('woocommerce_prices_include_tax', 'no') == 'no'){
            return false;
        } else {
            return true;
        }
    }

    /**
     * get coupon code from coupon object
     * @param $coupon
     * @return null
     */
    static function getCouponCode($coupon)
    {
        if (method_exists($coupon, 'get_code')) {
            return $coupon->get_code();
        }
        return NULL;
    }

    /**
     * get coupon code from coupon object
     * @return null
     */
    static function getAppliedCoupons()
    {
        if (function_exists('WC')) {
            if(isset(WC()->cart) && WC()->cart != null) {
                if (method_exists(WC()->cart, 'get_applied_coupons')) {
                    return WC()->cart->get_applied_coupons();
                }
            }
        }
        return NULL;
    }

    /**
     * Add cart fee
     * @param $cart
     * @param $code
     * @return array
     */
    static function addCouponDiscount($cart, $code)
    {
        if (method_exists($cart, 'add_discount')) {
            return $cart->add_discount($code);
        }
        return array();
    }

    /**
     * Check the coupon already found in cart
     * @param $cart
     * @param $code
     * @return array
     */
    static function hasCouponInCart($cart, $code)
    {
        if (method_exists($cart, 'has_discount')) {
            return $cart->has_discount($code);
        }
        return array();
    }

    /**
     * Set the cart item price
     * @param $cart_item_object
     * @param $price
     * @return mixed
     */
    static function setCartProductPrice($cart_item_object, $price)
    {
        if (method_exists($cart_item_object, 'set_price')) {
            return $cart_item_object->set_price($price);
        }
        return false;
    }

    /**
     * print the notice
     * @param $message
     * @param $type
     */
    static function printNotice($message, $type)
    {
        if (function_exists('wc_print_notice')) {
            wc_print_notice($message, $type);
        }
    }

    /**
     * Calculate including tax for product of price
     * @param $product
     * @param $original_price
     * @param $quantity
     * @return float
     */
    static function getIncludingTaxPrice($product, $original_price, $quantity)
    {
        if (function_exists('wc_get_price_including_tax')) {
            $price = wc_get_price_including_tax($product, array('qty' => $quantity, 'price' => $original_price));
        } else if (method_exists($product, 'get_price_including_tax')) {
            $price = $product->get_price_including_tax($quantity, $original_price);
        } else {
            $price = $original_price;
        }

        return apply_filters('advanced_woo_discount_rules_get_price_including_tax', $price, $product, $original_price);
    }

    /**
     * Calculate including tax for product of price
     * @param $product
     * @param $original_price
     * @param $quantity
     * @return float
     */
    static function getExcludingTaxPrice($product, $original_price, $quantity)
    {
        if (function_exists('wc_get_price_excluding_tax')) {
            $price = wc_get_price_excluding_tax($product, array('qty' => $quantity, 'price' => $original_price));
        } else if (method_exists($product, 'get_price_excluding_tax')) {
            $price = $product->get_price_excluding_tax($quantity, $original_price);
        } else {
            $price = $original_price;
        }

        return apply_filters('advanced_woo_discount_rules_get_price_excluding_tax', $price, $product, $original_price);
    }

    /**
     * get user roles
     * @return array
     */
    static function getUserRolesList()
    {
        global $wp_roles;
        if (isset($wp_roles->roles)) {
            return $wp_roles->roles;
        }
        return array();
    }

    /**
     * get countries from WC
     * @return array
     */
    static function getCountriesList()
    {
        if (function_exists('WC')) {
            if (isset(WC()->countries) && method_exists(WC()->countries, 'get_countries')) {
                return WC()->countries->get_countries();
            }
        }
        return array();
    }

    /**
     * get States from WC
     * @return array
     */
    static function getStatesList()
    {
        if (function_exists('WC')) {
            if (isset(WC()->countries) && method_exists(WC()->countries, 'get_states')) {
                return WC()->countries->get_states();
            }
        }
        return array();
    }

    /**
     * Get Payment Gateway Methods from WC
     * @return array
     */
    static function getPaymentMethodList()
    {
        if (function_exists('WC')) {
            if (method_exists(WC()->payment_gateways, 'payment_gateways')) {
                return WC()->payment_gateways->payment_gateways();
            }
        }
        return array();
    }

    /**
     * Build week days
     * @return array
     */
    static function getWeekDaysList()
    {
        return array(
            'sunday' => __('Sunday', 'woo-discount-rules'),
            'monday' => __('Monday', 'woo-discount-rules'),
            'tuesday' => __('Tuesday', 'woo-discount-rules'),
            'wednesday' => __('Wednesday', 'woo-discount-rules'),
            'thursday' => __('Thursday', 'woo-discount-rules'),
            'friday' => __('Friday', 'woo-discount-rules'),
            'saturday' => __('Saturday', 'woo-discount-rules'),
        );
    }

    /**
     * Build Banner position
     * @return array
     */
    static function getBannerPositionList()
    {
        $banner_hooks = array(
            'woocommerce_before_main_content' => __('Woocommerce before main content(Archive / Shop / Cat Pages / single product)', 'woo-discount-rules'),
            'woocommerce_archive_description' => __('Woocommerce archive description(Archive / Shop / Cat Pages)', 'woo-discount-rules'),
            'woocommerce_before_shop_loop' => __('Woocommerce before shop loop(Archive / Shop / Cat Pages)', 'woo-discount-rules'),
            'woocommerce_after_shop_loop' => __('Woocommerce after shop loop(Archive / Shop / Cat Pages)', 'woo-discount-rules'),
            'woocommerce_after_main_content' => __('Woocommerce after main content(Archive / Shop / Cat Pages / single product)', 'woo-discount-rules'),
            'woocommerce_before_single_product' => __('Woocommerce before single product', 'woo-discount-rules'),
            'woocommerce_before_single_product_summary' => __('Woocommerce before single product summary', 'woo-discount-rules'),
            'woocommerce_after_single_product_summary' => __('Woocommerce after single product summary', 'woo-discount-rules'),
            'woocommerce_after_single_product' => __('Woocommerce after single product', 'woo-discount-rules'),
            'woocommerce_before_cart' => __('Woocommerce before cart', 'woo-discount-rules'),
            'woocommerce_before_cart_table' => __('Woocommerce before cart table', 'woo-discount-rules'),
            'woocommerce_before_cart_contents' => __('Woocommerce before cart contents', 'woo-discount-rules'),
            'woocommerce_cart_contents' => __('Woocommerce cart contents', 'woo-discount-rules'),
            'woocommerce_after_cart_contents' => __('Woocommerce after cart contents', 'woo-discount-rules'),
            'woocommerce_after_cart_table' => __('Woocommerce after cart table', 'woo-discount-rules'),
            'woocommerce_after_cart' => __('Woocommerce after cart', 'woo-discount-rules'),
            'woocommerce_before_checkout_form' => __('Woocommerce before checkout form', 'woo-discount-rules'),
            //'woocommerce_checkout_before_customer_details' => __('Woocommerce checkout before customer details', 'woo-discount-rules'),
            'woocommerce_before_checkout_billing_form' => __('Woocommerce before checkout billing form', 'woo-discount-rules'),
            'woocommerce_after_checkout_billing_form' => __('Woocommerce after checkout billing form', 'woo-discount-rules'),
            'woocommerce_before_checkout_shipping_form' => __('Woocommerce before checkout shipping form', 'woo-discount-rules'),
            'woocommerce_after_checkout_shipping_form' => __('Woocommerce after checkout shipping form', 'woo-discount-rules'),
            'woocommerce_before_order_notes' => __('Woocommerce before order notes', 'woo-discount-rules'),
            'woocommerce_after_order_notes' => __('Woocommerce after order notes', 'woo-discount-rules'),
            //'woocommerce_checkout_after_customer_details' => __('Woocommerce checkout after customer details', 'woo-discount-rules'),
            //'woocommerce_checkout_before_order_review' => __('Woocommerce checkout before order review', 'woo-discount-rules'),
            //'woocommerce_checkout_after_order_review' => __('Woocommerce checkout after order review', 'woo-discount-rules'),
        );

        return apply_filters('advanced_woo_discount_rules_get_banner_position_events', $banner_hooks);
    }

    /**
     * get weight of the item
     * @param $item
     * @return int
     */
    static function getWeight($item)
    {
        if (!empty($item)) {
            if (method_exists($item, 'get_weight')) {
                return $item->get_weight();
            }
        }
        return 0;
    }

    /**
     * get woocommerce plugin url
     */
    static function getWooPluginUrl()
    {
        if (function_exists('WC')) {
            return WC()->plugin_url();
        }
        return NULL;
    }

    /**
     * get the user selected payment method
     * @return array|string|null
     */
    static function getUserSelectedPaymentMethod()
    {
        return self::getSession('chosen_payment_method', NULL);
    }

    /**
     * get the session value by key
     * @param $key
     * @param null $default
     * @return array|string|null
     */
    static function getSession($key, $default = NULL)
    {
        if (function_exists('WC')) {
            if (method_exists(WC()->session, 'get')) {
                return WC()->session->get($key);
            }
        }
        return $default;
    }

    /**
     * set the session value by key
     * @param $key
     * @param $value mixed
     */
    static function setSession($key, $value)
    {
        if (function_exists('WC')) {
            if (method_exists(WC()->session, 'set')) {
                WC()->session->set($key, $value);
            }
        }
    }

    /**
     * get the user role from user obj
     * @param $user
     * @return array
     */
    static function getRole($user)
    {
        if (!empty($user) && isset($user->user_login)) {
            return $user->roles;
        }
        return array();
    }

    /**
     * get the shipping country of customer
     * @return string|null
     */
    static function getShippingCountry()
    {
        if (function_exists('WC') && WC()->customer) {
            if (method_exists(WC()->customer, 'get_shipping_country')) {
                return WC()->customer->get_shipping_country();
            }
        }
        return NULL;
    }

    /**
     * get the shipping state of customer
     * @return string|null
     */
    static function getShippingState()
    {
        if (function_exists('WC') && WC()->customer) {
            if (method_exists(WC()->customer, 'get_shipping_state')) {
                return WC()->customer->get_shipping_state();
            }
        }
        return NULL;
    }

    /**
     * get the shipping city of customer
     * @return string|null
     */
    static function getShippingCity()
    {
        if (function_exists('WC') && WC()->customer) {
            if (method_exists(WC()->customer, 'get_shipping_city')) {
                return WC()->customer->get_shipping_city();
            }
        }
        return NULL;
    }

    /**
     * get the Billing city of customer
     * @return string|null
     */
    static function getBillingCity()
    {
        if (function_exists('WC') && WC()->customer) {
            if (method_exists(WC()->customer, 'get_billing_city')) {
                return WC()->customer->get_billing_city();
            }
        }
        return NULL;
    }

    /**
     * get the shipping city of customer
     * @return string|null
     */
    static function getShippingZipCode()
    {
        if (function_exists('WC') && WC()->customer) {
            if (method_exists(WC()->customer, 'get_shipping_postcode')) {
                return WC()->customer->get_shipping_postcode();
            }
        }
        return NULL;
    }

    /**
     * get orders list by condition
     * @param array $conditions
     * @return int[]|WP_Post[]
     */
    static function getOrdersByConditions($conditions = array())
    {
        $default_conditions = array(
            'numberposts' => -1,
            'post_type' => self::getOrderPostType(),
            'post_status' => array_keys(self::getOrderStatusList()),
            'orderby' => 'ID',
            'order' => 'DESC'
        );
        if (is_object($conditions)) {
            $conditions = (array)$conditions;
        } elseif (!is_array($conditions)) {
            $conditions = array();
        }
        $final_conditions = array_merge($default_conditions, $conditions);
        return get_posts($final_conditions);
    }

    /**
     * Get all order status lists
     * @param bool $key_only
     * @return array
     */
    static function getOrderPostType($key_only = false)
    {
        if (function_exists('wc_get_order_types')) {
            if ($key_only) {
                return array_keys(wc_get_order_types());
            }
            return wc_get_order_types();
        }
        return NULL;
    }

    /**
     * get woocommerce order status
     * @return array
     */
    static function getOrderStatusList()
    {
        if (function_exists('wc_get_order_statuses')) {
            return wc_get_order_statuses();
        }
        return array();
    }

    /**
     * get item ids of the particular order
     * @param $order
     * @return array
     */
    static function getOrderItemsId($order)
    {
        $order_items = self::getOrderItems($order);
        $order_items_id = array();
        if (!empty($order_items)) {
            foreach ($order_items as $item) {
                $order_items_id[] = self::getItemId($item);
            }
        }
        return array_filter($order_items_id);
    }

    /**
     * get quantities of the particular order
     * @param $order
     * @return array
     */
    static function getOrderItemsQty($order)
    {
        $order_items = self::getOrderItems($order);
        $productIds = array();
        if (!empty($order_items)) {
            foreach ($order_items as $item) {
                $product_id = $item->get_product_id();
                $variant_id = $item->get_variation_id();
                $quantity = $item->get_quantity();
                if($variant_id){
                    $productId = $variant_id;
                } else {
                    $productId = $product_id;
                }
                if(isset($productIds[$productId])){
                    $productIds[$productId] = $productIds[$productId]+$quantity;
                } else {
                    $productIds[$productId] = $quantity;
                }
            }
        }

        return $productIds;
    }

    /**
     * get all items of the order
     * @param $order
     * @return array
     */
    static function getOrderItems($order)
    {
        if (method_exists($order, 'get_items')) {
            return $order->get_items();
        }
        return array();
    }

    /**
     * get the order currency
     * @param $order
     * @return array
     */
    static function getOrderCurrency($order)
    {
        if (method_exists($order, 'get_currency')) {
            return $order->get_currency();
        }
        return NULL;
    }

    /**
     * Set order item meta
     * @param $item
     * @param $key
     * @param $value
     * @return array
     */
    static function setOrderItemMeta($item, $key, $value)
    {
        if (method_exists($item, 'add_meta_data')) {
            return $item->add_meta_data($key, $value, true);
        }
        return NULL;
    }

    /**
     * Set order item meta
     * @param $item
     * @param $key
     * @return array
     */
    static function getOrderItemMeta($item, $key)
    {
        if (method_exists($item, 'get_meta')) {
            return $item->get_meta($key);
        }
        return NULL;
    }

    /**
     * get item id from the item object
     * @param $item
     * @return null
     */
    static function getItemId($item)
    {
        if (method_exists($item, 'get_product_id') && method_exists($item, 'get_variation_id')) {
            if ($product_id = $item->get_variation_id()) {
                return $product_id;
            } else {
                return $item->get_product_id();
            }
        }
        return NULL;
    }

    /**
     * get term slug from category id
     * @param $id
     * @return bool
     */
    static function getCategorySlugByID($id)
    {
        if (function_exists('get_term_by')) {
            $term = get_term_by('id', $id, 'product_cat', 'ARRAY_A');
            return isset($term['slug']) ? $term['slug'] : NULL;
        }
        return NULL;
    }

    /**
     * get term slug from tag id
     * @param $id
     * @return bool
     */
    static function getTagSlugByID($id)
    {
        $slug = false;
        if (function_exists('get_term_by')) {
            $term = get_term_by('id', $id, 'product_tag', 'ARRAY_A');
            $slug = $term['slug'];
        }
        return $slug;
    }

    /**
     * get custom term slug from custom tag id
     * @param $id
     * @param $term_name
     * @return boo
     */
    static function getTermSlugByID($id, $term_name)
    {
        $slug = false;
        if (function_exists('get_term_by')) {
            $term = get_term_by('id', $id, $term_name, 'ARRAY_A');
            $slug = $term['slug'];
        }
        return $slug;
    }

    /**
     * get product id by using sku
     * @param $sku
     * @return bool|int
     */
    static function getProductsBySku($sku)
    {
        if (empty($sku)) {
            return false;
        }
        if (function_exists('wc_get_product_id_by_sku')) {
            $id = wc_get_product_id_by_sku($sku);
            if (!empty($id)) {
                $product_id = Woocommerce::getProductParentId($id);
                if (empty($product_id)) {
                    return $id;
                } else {
                    return $product_id;
                }
            }
        }
        return false;
    }

    /**
     * get on sale products ids
     * @return array|bool
     */
    static function getOnSaleProductsIds()
    {
        if (function_exists('wc_get_product_ids_on_sale')) {
            $on_sale_product_ids = wc_get_product_ids_on_sale();
            return apply_filters('advanced_woo_discount_rules_get_on_sale_product_ids', $on_sale_product_ids);
        }

        return false;
    }

    /**
     * get all available attributes details
     * @return array
     */
    static function getAllAvailableAttributeDetails()
    {
        global $wc_product_attributes;
        $attributes = array();
        $available_attr = array();
        if (function_exists('get_terms') && isset($wc_product_attributes) && is_array($wc_product_attributes) && !empty($wc_product_attributes)) {
            foreach ($wc_product_attributes as $attr_tax => $attr_value) {
                $terms = get_terms(array(
                    'taxonomy' => $attr_tax,
                    'hide_empty' => false,
                ));
                $attributes[$attr_tax] = $terms;
                if (is_array($terms) && !empty($terms)) {
                    foreach ($terms as $term_detail) {
                        if (is_object($term_detail)) {
                            $term_id = isset($term_detail->term_id) ? $term_detail->term_id : '';
                            if (!empty($term_id)) {
                                $available_attr[$attr_tax]['id'][] = $term_id;
                                $available_attr[$attr_tax]['slug'][$term_id] = $term_detail->slug;
                            }
                        }
                    }
                }
            }
        }
        return array('terms' => $attributes, 'attr' => $available_attr);
    }

    /**
     * get current cart quantities
     * @return int|mixed
     */
    static function getCartTotalQuantities()
    {
        $cart_items = self::getCart();
        $quantity = 0;
        if (!empty($cart_items)) {
            foreach ($cart_items as $cart_item) {
                $quantity += $cart_item['quantity'];
            }
        }
        return apply_filters('advanced_woo_discount_rules_get_cart_total_quantities', $quantity, $cart_items);
    }

    /**
     * define product loop has started
     * @param bool $echo
     * @return string|null
     */
    static function productLoopStart($echo = true)
    {
        if (function_exists('woocommerce_product_loop_start')) {
            return woocommerce_product_loop_start($echo);
        }
        return NULL;
    }

    /**
     * Set product loop as end
     * @param bool $echo
     * @return string|null
     */
    static function productLoopEnd($echo = true)
    {
        if (function_exists('woocommerce_product_loop_end')) {
            return woocommerce_product_loop_end($echo);
        }
        return NULL;
    }

    /**
     * set product loop properties
     * @param $option
     * @param $value
     * @return void|null
     */
    static function setLoopProperties($option, $value)
    {
        if (function_exists('wc_set_loop_prop')) {
            return wc_set_loop_prop($option, $value);
        }
        return NULL;
    }

    /**
     * Validate coupon
     *
     * @param string $coupon_name
     * @return boolean
     * */
    static function checkCouponAlreadyExistsInWooCommerce($coupon_name)
    {
        $coupon_args = array(
            'name' => $coupon_name,
            'post_type' => 'shop_coupon'
        );
        $posts = get_posts($coupon_args);
        if (!empty($posts) && count($posts) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Validate coupon
     *
     * @param string $coupon_name
     * @return array
     * */
    static function validateDynamicCoupon($coupon_name)
    {
        $result['status'] = false;
        if (!empty($coupon_name)) {
            $coupon_exists = self::checkCouponAlreadyExistsInWooCommerce($coupon_name);
            if ($coupon_exists) {
                $result['status'] = false;
                $result['message'] = esc_html__('Coupon already exists in WooCommerce. Please select another name', 'woo-discount-rules');
            } else {
                $result['status'] = true;
            }
        }
        $result['coupon'] = $coupon_name;
        return $result;
    }

    /**
     * get current product price html
     * @param $product
     * @return bool
     */
    static function getPriceHtml($product){
        $html = false;
        if (method_exists($product, 'get_price_html')) {
            $html = $product->get_price_html();
        }
        return apply_filters('advanced_woo_discount_rules_get_price_html', $html, $product);
    }

    /**
     * get parent product of current product
     * @param $product
     * @return bool|false|WC_Product|null
     */
    static function getParentProduct($product){

        if (self::productTypeIs($product, 'variation')) {
            $parent_id = self::getProductParentId($product);
            $product = self::getProduct($parent_id);
        }
        return $product;
    }

    public static function getCheckOutPostData(){
        if(self::$checkout_post === null){
            $input = new Input();
            $postData = $input->post('post_data', null, 'raw');
            $postDataArray = array();
            if($postData != ''){
                parse_str($postData, $postDataArray);
            }
            self::$checkout_post = $postDataArray;
        }
        return self::$checkout_post;
    }

    /**
     * Get billing email from post data
     *
     * @return string
     * */
    public static function getBillingEmailFromPost(){
        $user_email = '';
        $postData = self::getCheckOutPostData();
        if(isset($postData['billing_email']) && !empty($postData['billing_email'])){
            $user_email = $postData['billing_email'];
        }
        if(empty($user_email)){
            if(function_exists('WC')){
                $session = WC()->session;
                if(!empty($session)){
                    if(method_exists($session, 'get')){
                        $customer = $session->get('customer');
                        if(isset($customer['email']) && !empty($customer['email'])){
                            $user_email = $customer['email'];
                        }
                    }
                }
            }
        }
        return $user_email;
    }

    /**
     * remove Html Tags
     *
     * @param $html
     * @return string
     */
    public static function removeHtmlTags($html){
        if (function_exists('wp_strip_all_tags')) {
            $html = wp_strip_all_tags($html);
        }else{
            $html = strip_tags($html);
        }
        return $html;
    }

    /**
     * check is rtl function
     * @return bool
     */
    public static function isRTLEnable(){
        if(function_exists('is_rtl') && is_rtl()){
            return true;
        }
        return false;
    }

    /**
     * Get the min or max variation regular price.
     *
     * @param  object  $product
     * @param  string  $min_or_max Min or max price.
     * @param  boolean $for_display If true, prices will be adapted for display based on the `woocommerce_tax_display_shop` setting (including or excluding taxes).
     * @return string
     */
    public static function get_variation_regular_price($product, $min_or_max = 'min', $for_display = false){
        if(method_exists($product, 'get_variation_regular_price')){
            return  $product->get_variation_regular_price($min_or_max, $for_display);
        }
        return 0;
    }

    /**
     * Load discount table if any one variant has discount
     * */
    public static function displayTableIfAnyOneVariantHasDiscount(){
        return apply_filters('advanced_woo_discount_rules_display_bulk_table_if_any_one_variant_has_discount', false);
    }

    /**
     * get available product variations
     * @param $product
     * @return array
     */
   public static function availableProductVariations($product){
       $available_variations = array();
       $is_variable_product = self::productTypeIs($product, 'variable');
       if ($is_variable_product && method_exists($product, 'get_available_variations')){
           $available_variations = $product->get_available_variations();
       }
       return $available_variations;
   }
}