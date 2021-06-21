<?php

namespace Wdr\App\Conditions;

use Wdr\App\Controllers\DiscountCalculator;

if (!defined('ABSPATH')) exit;

class CartLineItemsCount extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->name = 'cart_line_items_count';
        $this->label = __('Line Item Count', 'woo-discount-rules');
        $this->group = __('Cart', 'woo-discount-rules');
        $this->template = WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Conditions/Cart/LineItemCount.php';
    }

    function check($cart, $options)
    {
        if(empty($cart)){
            return false;
        }
        if (isset($options->operator) && $options->value) {
            $operator = sanitize_text_field($options->operator);
            $value = $options->value;
            if($options->calculate_from == 'from_filter'){
                $line_items = DiscountCalculator::getFilterBasedCartQuantities('cart_line_items_count', $this->rule);
            }else{
                $line_items = (is_array($cart)) ? count($cart) : 0;
            }
            return $this->doComparisionOperation($operator, $line_items, $value);
        }
        return false;
    }
}