<?php

namespace Wdr\App\Conditions;

use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\DiscountCalculator;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit;

class CartSubTotal extends Base
{
    function __construct()
    {
        parent::__construct();
        $this->name = 'cart_subtotal';
        $this->label = __('Subtotal', 'woo-discount-rules');
        $this->group = __('Cart', 'woo-discount-rules');
        $this->template = WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Conditions/Cart/Subtotal.php';
    }

    public function check($cart, $options)
    {
        if(empty($cart)){
            return false;
        }
        $sub_total_recalculate = false;
        $cart_sub_total = 0;
        if($options->calculate_from == 'from_filter'){
            $cart_sub_total = DiscountCalculator::getFilterBasedCartQuantities('cart_subtotal', $this->rule);
            $cart_sub_total = Woocommerce::round($cart_sub_total);
        }else{
            if(apply_filters('advanced_woo_discount_rules_calculate_cart_subtotal_manually', false)){
                $cart = Woocommerce::getCart(true);
            } else {
                $cart_sub_total = self::$woocommerce_helper->getCartSubtotal();
            }
            $sub_total_recalculate = true;
        }
        if(!empty($cart) && $cart_sub_total == 0 && $sub_total_recalculate){
            foreach ($cart as $cart_product){
                $cart_sub_total += self::$woocommerce_helper->getCartLineItemSubtotal($cart_product);
            }
            $cart_sub_total = Woocommerce::round($cart_sub_total);
        }
        if (isset($options->operator) && $options->value) {
            $operator = sanitize_text_field($options->operator);
            $value = self::$woocommerce_helper->getConvertedFixedPrice($options->value, 'subtotal_condition');
            $status = $this->doComparisionOperation($operator, $cart_sub_total, $value);
            if(!$status){
                $config = new Configuration();
                if($config->getConfig('show_subtotal_promotion', '') == 1){
                    $this->processPromotion($operator, $options, $cart_sub_total, $value);
                }
            }
            return $status;
        }

        return false;
    }

    /**
     * Process promotion
     */
    function processPromotion($operator, $options, $cart_sub_total, $min_value)
    {
        if(in_array($operator, array('greater_than', 'greater_than_or_equal'))){
            if(!empty($options->subtotal_promotion_from) && !empty($options->subtotal_promotion_message)){
                if($options->subtotal_promotion_from <= $cart_sub_total){
                    $difference_amount = $min_value - $cart_sub_total;
                    if($difference_amount > 0){
                        $message = __($options->subtotal_promotion_message, 'woo-discount-rules');
                        $difference_amount = Woocommerce::formatPrice($difference_amount);
                        $message = str_replace('{{difference_amount}}', $difference_amount, $message);
                        Helper::setPromotionMessage($message, $this->rule->rule->id);
                    }
                }
            }
        }
    }
}