<?php

namespace Wdr\App\Helpers;
use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\ManageDiscount;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class Helper
{
    public static $available_coupon_names = null;
    /**
     * Combine two array with unique values
     *
     * @param $products array
     * @param $additional_products array
     * @return array
     * */
    public static function combineProductArrays($products, $additional_products)
    {
        $products = array_merge($products, $additional_products);
        $products = array_unique($products);
        return $products;
    }

    /**
     * Check has pro version
     *
     * @return boolean
     * */
    public static function hasPro()
    {
        if (defined('WDR_PRO'))
            if (WDR_PRO === true) return true;
        return false;
    }

    /**
     * Format price
     *
     * @param $data mixed
     * @return mixed
     * */
    public static function formatAllPrices($data)
    {
        if (is_array($data)) {
            if (isset($data['initial_price']) && !isset($data['initial_price_html'])) {
                $data['initial_price_html'] = Woocommerce::formatPrice($data['initial_price']);
            }
            if (isset($data['discounted_price']) && !isset($data['discounted_price_html'])) {
                $data['discounted_price_html'] = Woocommerce::formatPrice($data['discounted_price']);
            }
            if (isset($data['initial_price_with_tax']) && !isset($data['initial_price_with_tax_html'])) {
                $data['initial_price_with_tax_html'] = Woocommerce::formatPrice($data['initial_price_with_tax']);
            }
            if (isset($data['discounted_price_with_tax']) && !isset($data['discounted_price_with_tax_html'])) {
                $data['discounted_price_with_tax_html'] = Woocommerce::formatPrice($data['discounted_price_with_tax']);
            }
            if (!isset($data['currency_symbol'])) {
                $data['currency_symbol'] = Woocommerce::get_currency_symbol();
            }
        }
        return $data;
    }

    /**
     * Get template override
     * @param string $template_name
     * @param string $folder
     * @return string
     * */
    public static function getTemplateOverride($template_name, $folder = '')
    {
        if (!empty($folder)) {
            $path = trailingslashit('woo-discount-rules') . $folder . "/" . $template_name;
        } else {
            $path = trailingslashit('woo-discount-rules') . $template_name;
        }
        $template = locate_template(
            array(
                $path,
                $template_name,
            )
        );
        return $template;
    }

    /**
     * Get template path
     *
     * @param $template_name string
     * @param $default_path string
     * @param $folder string
     * @return string
     * */
    public static function getTemplatePath($template_name, $default_path, $folder = '')
    {
        $path_from_template = self::getTemplateOverride($template_name, $folder);
        if ($path_from_template) $default_path = $path_from_template;
        return $default_path;
    }

    /**
     * Is Cart item is consider for discount calculation
     *
     * @param $status bool
     * @param $cart_item array
     * @param $type string
     * @return bool
     * */
    public static function isCartItemConsideredForCalculation($status, $cart_item, $type)
    {
        return apply_filters('advanced_woo_discount_rules_include_cart_item_to_count_quantity', $status, $cart_item, $type);
    }

    /**
     * Set promotion messages
     * @param $message
     * @param string $rule_id
     * @param $promotion_type
     */
    public static function setPromotionMessage($message, $rule_id = '')
    {
        $messages = Woocommerce::getSession('awdr_promotion_messages', array());
        if (!is_array($messages)) $messages = array();
        if (!empty($messages) && in_array($message, $messages)) {
        } else {
            if (empty($rule_id)) {
                $messages[] = $message;
            } else {
                $messages[$rule_id] = $message;
            }
        }
        Woocommerce::setSession('awdr_promotion_messages', $messages);
    }

    /**
     * Get promotion messages
     * */
    public static function getPromotionMessages()
    {
        return Woocommerce::getSession('awdr_promotion_messages', array());
    }

    /**
     * Clear promotion messages
     * */
    public static function clearPromotionMessages()
    {
        Woocommerce::setSession('awdr_promotion_messages', array());
    }

    /**
     * ruleConditionDescription
     */
    public static function ruleConditionDescription()
    {
        return $content = "<p>" . __('Include additional conditions (if necessary)', 'woo-discount-rules') . "</p>
        <b>" . __('Popular conditions:', 'woo-discount-rules') . "</b>
        <span style='width: 100%; display: flex;'>
            <span style='width: 45%; padding-right: 5px;'>
                <ul  class='awdr-bullet-style'>
                    <li ><a href='https://docs.flycart.org/en/articles/3977542-subtotal-based-free-product-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=subtotal_documentation' target='_blank'>" . __('Subtotal', 'woo-discount-rules') . "</a></li>
                    <li ><a href='https://docs.flycart.org/en/articles/4203313-user-role-based-discount-rules-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=user_role_documentation' target='_blank'>" . __('User role', 'woo-discount-rules') . "</a></li>
                    <li >" . __('Days & Time', 'woo-discount-rules') . "</li>
                    <li ><a href='https://docs.flycart.org/en/articles/4206683-how-to-provide-first-order-discount-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=purchase_history' target='_blank'>" . __('Purchase History', 'woo-discount-rules') . "</a></li>
                    <li >" . __('Payment Method', 'woo-discount-rules') . "</li>
                </ul>
            </span>
            <span style='width: 45%;'>
                <ul  class='awdr-bullet-style'>
                    <li ><a href='https://docs.flycart.org/en/articles/4268595-activate-discount-rule-using-a-coupon-code-in-woocommerce-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=coupon_documentation' target='_blank'>" . __('Coupon', 'woo-discount-rules') . "</a></li>
                    <li ><a href='https://docs.flycart.org/en/articles/4214869-customer-shipping-address-based-discount-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=shipping_address_documentation' target='_blank'>" . __('Shipping Address', 'woo-discount-rules') . "</a></li>
                    <li><a href='https://docs.flycart.org/en/articles/4279899-category-combination-get-discount-only-when-category-a-b-c-are-in-the-cart-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=category_combination' target='_blank'>" . __('Category Combination', 'woo-discount-rules') . "</a></li>
                    <li><a href='https://docs.flycart.org/en/articles/4164153-buy-product-a-b-and-get-discount-in-product-c-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=product_combination' target='_blank'>" . __('Product Combination', 'woo-discount-rules') . "</a></li>
                    <li ><a href='https://docs.flycart.org/en/articles/4280177-discounts-based-on-cart-line-items?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=quantity_or_line_items' target='_blank'>" . __('Quantities/Line items', 'woo-discount-rules') . "</a></li>
                    
                    
                </ul>
            </span>
        </span>";
    }

    /**
     * bogoToolTipDescriptionForIndividualProduct
     * @return mixed
     */
    public static function bogoToolTipDescriptionForIndividualProduct()
    {
        return $content = __("Individual Product : 
            This counts the total quantity of each product / line item separately.
            Example:
             If a customer wanted to buy 2 quantities of Product A,  3 quantities of Product B, then count will be maintained at the product level.
            2 - count of Product A
            3 - Count of Product B
            In case of variable products, the count will be based on each variant because WooCommerce considers a variant as a product itself.", 'woo-discount-rules');
    }

    /**
     * bogoToolTipDescriptionForFilterTogether
     * @return mixed
     */
    public static function bogoToolTipDescriptionForFilterTogether()
    {
        return $content = __("Filter set above :
             This will count the quantities of products set in the 'Filter' section.
             Example: If you selected a few categories there, it will count the quantities of products in those categories added in cart. If you selected a few products in the filters section, then it will count the quantities together.
            Example: Let’s say, you wanted to offer a Bulk Quantity discount for Category A and chosen Category A in the filters. So when a customer adds 1 quantity each of X, Y and Z from Category A, then the count here is 3.", 'woo-discount-rules');
    }

    /**
     * bogoToolTipDescriptionForvariants
     * @return mixed
     */
    public static function bogoToolTipDescriptionForvariants()
    {
        return $content = __('All variants in each product together :
            Useful when applying discounts based on variable products and you want the quantity to be counted based on the parent product.
            Example:
            Say, you have Product A - Small, Medium, Large.
            If a customer buys  2 of Product A - Small,  4 of Product A - Medium,  6 of Product A - Large, then the count will be: 6+4+2 = 12
', 'woo-discount-rules');
    }

    /**
     * Get available coupon names
     * */
    public static function getAvailableCouponNameFromRules(){
        if(self::$available_coupon_names === null){
            $available_rules = ManageDiscount::$available_rules;
            $coupon_names = array();
            if(!empty($available_rules)){
                foreach ($available_rules as $available_rule){
                    $discount_type = $available_rule->getRuleDiscountType();
                    if($discount_type == 'wdr_bulk_discount'){
                        $adjustment = $available_rule->getBulkAdjustments();
                        if(isset($adjustment->apply_as_cart_rule) && $adjustment->apply_as_cart_rule == 1){
                            if(!empty($adjustment->cart_label)){
                                $coupon_names[] = $adjustment->cart_label;
                            } else {
                                $coupon_names[] = $available_rule->getTitle();
                            }
                        }
                    } else if($discount_type == 'wdr_simple_discount'){
                        $adjustment = $available_rule->getProductAdjustments();
                        if(isset($adjustment->apply_as_cart_rule) && $adjustment->apply_as_cart_rule == 1){
                            if(!empty($adjustment->cart_label)){
                                $coupon_names[] = $adjustment->cart_label;
                            } else {
                                $coupon_names[] = $available_rule->getTitle();
                            }
                        }
                    } else if($discount_type == 'wdr_cart_discount'){
                        $adjustment = $available_rule->getCartAdjustments();
                        if(!empty($adjustment->label)){
                            $coupon_names[] = $adjustment->label;
                        } else {
                            $coupon_names[] = $available_rule->getTitle();
                        }
                    } else if($discount_type == 'wdr_set_discount'){
                        $adjustment = json_decode($available_rule->rule->set_adjustments);
                        if(isset($adjustment->apply_as_cart_rule) && $adjustment->apply_as_cart_rule == 1){
                            if(!empty($adjustment->cart_label)){
                                $coupon_names[] = $adjustment->cart_label;
                            } else {
                                $coupon_names[] = $available_rule->getTitle();
                            }
                        }
                    }
                }
            }
            $coupon_name_from_config = Configuration::getInstance()->getConfig('discount_label_for_combined_discounts', __('Cart discount', 'woo-discount-rules'));
            $coupon_names[] = (empty($coupon_name_from_config))? __('Cart discount', 'woo-discount-rules'): $coupon_name_from_config;
            foreach ($coupon_names as $key => $coupon_name){
                $coupon_names[$key] = apply_filters('woocommerce_coupon_code', $coupon_name);
            }
            self::$available_coupon_names = $coupon_names;
        }

        return self::$available_coupon_names;
    }

    /**
     * Remove error message for our coupons as sometime the coupon doesn't validate because of event calls before calculate totals
     * */
    public static function removeErrorMessageForOurCoupons($err, $err_code, $coupon){
        if($err_code == 101){
            if(!empty($coupon)){
                $coupon_code = Woocommerce::getCouponCode($coupon);
                if(in_array($coupon_code, self::getAvailableCouponNameFromRules())){
                    $err = '';
                }
            }
        }

        return $err;
    }

    public static function create_nonce($action = -1)
    {
        return wp_create_nonce($action);
    }

    protected static function verify_nonce($nonce, $action = -1)
    {
        if (wp_verify_nonce($nonce, $action)) {
            return true;
        } else {
            return false;
        }
    }

    public static function validateRequest($method, $awdr_nonce = null)
    {
        if ($awdr_nonce === null) {
            if (isset($_REQUEST['awdr_nonce']) && !empty($_REQUEST['awdr_nonce'])) {
                if (self::verify_nonce(wp_unslash($_REQUEST['awdr_nonce']), $method)) {
                    return true;
                }
            }
        } else {
            if (self::verify_nonce(wp_unslash($awdr_nonce), $method)) {
                return true;
            }
        }
        die(__('Invalid token', 'woo-discount-rules'));
    }

    public static function filterSelect2SearchQuery($query)
    {
        return esc_sql(stripslashes($query));
    }

    public static function displayCompatibleCheckMessages()
    {
        if (version_compare(WDR_VERSION, '2.3.7', '>=')) {
            if (defined('WDR_PRO_VERSION')) {
                if (version_compare(WDR_PRO_VERSION, '2.3.7', '<')) {
                    $url = admin_url() . "plugins.php";
                    $plugin_page = '<a target="_blank" href="' . $url . '">' . __('Update now', 'woo-discount-rules') . '</a>';
                    ?>
                    <br>
                    <div class="notice inline notice notice-warning notice-alt awdr-rule-limit-disabled">
                        <p class="rule_limit_msg_future">
                            <?php echo sprintf(__('You are using a lower version of our <b>Woo Discount Rules PRO 2.0</b> plugin. Please update the plugin to latest version to run smoothly. %s', 'woo-discount-rules'), $plugin_page); ?>
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }

    public static function hasAdminPrivilege()
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        } else {
            return false;
        }
    }

    public static function getCleanHtml($html)
    {
        try {
            $html = html_entity_decode($html);
            $html = preg_replace('/(<(script|style|iframe)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $html);
            $allowed_html = array(
                'br' => array(),
                'strong' => array(),
                'span' => array('class' => array()),
                'div' => array('class' => array()),
                'p' => array('class' => array()),
            );
            return wp_kses($html, $allowed_html);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * check the given content is json or not
     * @param $string
     * @return bool
     */
    static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * sanitize the json data
     * @param $data
     * @return bool|false|mixed|string
     */
    static function sanitizeJson($data)
    {
        $arr = array();
        if (is_array($data)) {
            $arr = $data;
        } elseif (is_object($data)) {
            $encoded = wp_json_encode($data);
            $arr = json_decode($encoded, true);
        } elseif (self::isJson($data)) {
            $arr = json_decode($data, true);
        }
        $result = array();
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = self::sanitizeJson($value);
                    $result[sanitize_key($key)] = $value;
                } else {
                    if (is_string($value)) {
                        $value = sanitize_text_field($value);
                    } elseif (is_int($value)) {
                        $value = intval($value);
                    } elseif (is_float($value)) {
                        $value = floatval($value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                    $result[sanitize_key($key)] = $value;
                }
            }
        }
        return $result;
    }
}