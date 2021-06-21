<?php

namespace Wdr\App\Conditions;

use Wdr\App\Helpers\Filter;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Input;
use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit;

abstract class Base
{
    public static $woocommerce_helper, $filter;
    public $name = NULL, $rule = null, $label = NULL, $group = NULL, $template = NULL, $input, $extra_params = array('render_saved_condition' => false);

    function __construct()
    {
        self::$woocommerce_helper = (!empty(self::$woocommerce_helper)) ? self::$woocommerce_helper : new Woocommerce();
        self::$filter = (!empty(self::$filter)) ? self::$filter : new Filter();
        $this->input = new Input();
    }

    abstract function check($cart, $options);

    /**
     * return the name of the condition. If condition does not have name, then the condition will not get consider.
     * @return null
     */
    function name()
    {
        return $this->name;
    }

    /**
     * compare cart items with the product filter helper
     * @param $cart
     * @param $options
     * @param $type
     * @return bool
     */
    function doCartItemsCheck($cart, $options, $type)
    {
        if(empty($cart)){
            return false;
        }
        $comparision_operator = isset($options->cartqty) ? $options->cartqty : 'less_than_or_equal';
        $comparision_quantity = isset($options->qty) ? $options->qty : 0;
        if (empty($comparision_quantity)) {
            return true;
        }
       // $comparision_method = isset($options->method) ? $options->method : 'in_list';
        $comparision_method = isset($options->operator) ? $options->operator : 'in_list';
        $comparision_value = (array)isset($options->value) ? $options->value : array();
        $cart_items = array();
        if ($cart instanceof \WC_Cart) {
            $cart_items = self::$woocommerce_helper->getCartItems($cart);
        } elseif (is_array($cart)) {
            $cart_items = $cart;
        }
        $quantity = $not_in_list_quantity = 0;
        foreach ($cart_items as $cart_item) {
            $product = isset($cart_item['data']) ? $cart_item['data'] : array();
            if(Helper::isCartItemConsideredForCalculation(true, $cart_item, $type)){
                if (self::$filter->match($product, $type, $comparision_method, $comparision_value, $options)) {
                    if ($type != 'products') {
                        $quantity += (int)$cart_item['quantity'];
                    }else{
                        if($comparision_method == 'not_in_list'){
                            continue;
                        }
                        $quantity += (int)$cart_item['quantity'];
                        /*$quantity = (int)$item['quantity'];
                        $product_parant_id = Woocommerce::getProductParentId($product);
                        if(!empty($product_parant_id)){
                            $quantity = $this->getChildVariantCountInCart($options, $product_parant_id, $quantity, $cart_items);
                        }*/
                    }
                }else{
                    $not_in_list_product = $this->findNotInListProduct($product, $cart_item, $comparision_value, $type, $options);
                    if($comparision_method == 'not_in_list' && $not_in_list_product){
                        if ($type != 'products') {
                            $not_in_list_quantity += (int)$cart_item['quantity'];
                        }else{
                            $not_in_list_quantity += (int)$cart_item['quantity'];
                        }
                    }
                }
            }
        }
        $cart_in_list = array();
        $cart_not_in_list = array();
        foreach ($cart_items as $item) {
            $product = isset($item['data']) ? $item['data'] : array();
            if(Helper::isCartItemConsideredForCalculation(true, $item, $type)){
                if (self::$filter->match($product, $type, $comparision_method, $comparision_value, $options)) {
                    if($comparision_method == 'not_in_list'){
                        $cart_in_list[] = 'yes';
                        continue;
                    }
                    switch ($comparision_operator) {
                        case 'less_than':
                            if ($quantity < $comparision_quantity) {
                                $cart_in_list[] = 'yes';
                            }else{
                                $cart_in_list[] = 'no';
                            }
                            break;
                        case 'greater_than_or_equal':
                            if ($quantity >= $comparision_quantity) {
                                $cart_in_list[] = 'yes';
                            }else{
                                $cart_in_list[] = 'no';
                            }
                            break;
                        case 'greater_than':
                            if ($quantity > $comparision_quantity) {
                                $cart_in_list[] = 'yes';
                            }else{
                                $cart_in_list[] = 'no';
                            }
                            break;
                        default:
                        case 'less_than_or_equal':
                            if ($quantity <= $comparision_quantity) {
                                $cart_in_list[] = 'yes';
                            }else{
                                $cart_in_list[] = 'no';
                            }
                            break;
                    }
                }else{
                    $not_in_list_product = $this->findNotInListProduct($product, $item, $comparision_value, $type, $options);
                    if($comparision_method == 'not_in_list' && $not_in_list_product){
                       /* if($type == 'products'){
                            $not_in_list_quantity = 0;
                            $not_in_list_quantity = (int)$item['quantity'];
                            $product_parant_id = Woocommerce::getProductParentId($product);
                            if(!empty($product_parant_id)){
                                $not_in_list_quantity = $this->getChildVariantCountInCart($options, $product_parant_id, $not_in_list_quantity, $cart_items);
                            }
                        }*/
                        switch ($comparision_operator) {
                            case 'less_than':
                                if ($not_in_list_quantity < $comparision_quantity) {
                                    $cart_not_in_list[] = 'no';
                                }else{
                                    $cart_not_in_list[] = 'yes';
                                }
                                break;
                            case 'greater_than_or_equal':
                                if ($not_in_list_quantity >= $comparision_quantity) {
                                    $cart_not_in_list[] = 'no';
                                }else{
                                    $cart_not_in_list[] = 'yes';
                                }
                                break;
                            case 'greater_than':
                                if ($not_in_list_quantity > $comparision_quantity) {
                                    $cart_not_in_list[] = 'no';
                                }else{
                                    $cart_not_in_list[] = 'yes';
                                }
                                break;
                            default:
                            case 'less_than_or_equal':
                                if ($not_in_list_quantity <= $comparision_quantity) {
                                    $cart_not_in_list[] = 'no';
                                }else{
                                    $cart_not_in_list[] = 'yes';
                                }
                                break;
                        }
                    }
                }
            }
        }
        if(!empty($cart_not_in_list) && in_array('no', $cart_not_in_list)){
            return false;
        }elseif (!empty($cart_in_list) && in_array('no', $cart_in_list)){
            return false;
        } else if((!empty($cart_in_list) && in_array('yes', $cart_in_list)) || (!empty($cart_not_in_list) && in_array('yes', $cart_not_in_list))){
            return true;
        }
        return false;
    }

    /**
     * get the date by passing days
     * @param $value string; Example- +1 day,-1 month, now
     * @param $format string
     * @return bool|string
     */
    function getDateByString($value, $format = 'Y-m-d H:i:s')
    {
        if (!empty($value)) {
            $value = str_replace('_', ' ', $value);
            try {
                $date = new \DateTime(current_time('mysql'));
                $date->modify($value);
                return $date->format($format);
            } catch (\Exception $e) {
            }
        }
        return false;
    }

    /**
     * Do the mathematical Comparision operation
     * @param $operation
     * @param $operand1 - user data
     * @param $operand2 - admin condition data 1
     * @param $operand3 - admin condition data 2, if range
     * @return bool
     */
    function doComparisionOperation($operation, $operand1, $operand2, $operand3 = NULL)
    {
        $result = false;
        switch ($operation) {
            case 'equal_to':
                $result = ($operand1 == $operand2);
                break;
            case 'not_equal_to';
                $result = ($operand1 != $operand2);
                break;
            case 'greater_than';
                $result = ($operand1 > $operand2);
                break;
            case 'less_than';
                $result = ($operand1 < $operand2);
                break;
            case 'greater_than_or_equal';
                $result = ($operand1 >= $operand2);
                break;
            case 'less_than_or_equal';
                $result = ($operand1 <= $operand2);
                break;
            case 'in_range';
                if (!empty($operand3)) {
                    $result = (($operand1 >= $operand2) && ($operand1 <= $operand3));
                }
                break;
            default:
                break;
        }
        return $result;
    }

    /**
     * check the data is present in loop
     * @param $operation
     * @param $key
     * @param $list
     * @return bool
     */
    function doCompareInListOperation($operation, $key, $list)
    {
        if (!is_array($list))
            return false;
        switch ($operation) {
            case 'not_in_list':
                if (is_array($key) || is_object($key)) {
                    $key = (array)$key;
                    return !array_intersect($key, $list);
                } else {
                    $result = !in_array($key, $list);
                }
                break;
            default:
            case 'in_list';
                if (is_array($key) || is_object($key)) {
                    $key = (array)$key;
                    return array_intersect($key, $list);
                } else {
                    $result = in_array($key, $list);
                }
                break;
        }
        return $result;
    }

    /**
     * @param $options
     * @param $parant_id
     * @param $quantity
     * @param $cart_items
     * @return int
     */
    function getChildVariantCountInCart($options, $parant_id, $quantity, $cart_items){
        $filter_value = (is_object($options) && isset($options->value)) ? $options->value : 0;
        if(in_array($parant_id,$filter_value)){
            $count_quantity = 0;
            foreach ($cart_items as $cart_item){
                $product = isset($cart_item['data']) ? $cart_item['data'] : 0;
                $product_parant_id = Woocommerce::getProductParentId($product);
                if($parant_id == $product_parant_id){
                    $count_quantity += (int)$cart_item['quantity'];
                }
            }
            return $count_quantity;
        }else{
            return $quantity;
        }
    }

    /**
     * Find product that in product not in list condition
     *
     * @param $product
     * @param $cart_item
     * @param $comparision_value
     * @param $type
     * @param $options
     * @return bool
     */
    function findNotInListProduct($product, $cart_item, $comparision_value, $type, $options){
        $filter_helper = new Filter();
        $product_id = $product->get_id();
        $not_in_list_product = false;
        switch ($type){
            case 'product_category':
                $categories = Woocommerce::getProductCategories($product);
                $not_in_list_product = count(array_intersect($categories, $comparision_value)) > 0;
                break;
            case 'products':
                $apply_discount_to_child = apply_filters('advanced_woo_discount_rules_apply_discount_to_child', true, $product);
                if ($apply_discount_to_child) {
                    if (isset($options->product_variants) && !empty($options->product_variants) && is_array($options->product_variants)) {
                        $comparision_value = Helper::combineProductArrays($comparision_value, $options->product_variants);
                    }
                }
                $not_in_list_product = in_array($product_id, $comparision_value);
                break;
            case 'product_attributes':
                $attrs = Woocommerce::getProductAttributes($product);
                $attr_ids = array();
                if (Woocommerce::productTypeIs($product, 'variation')) {
                    if (count(array_filter($attrs)) < count($attrs)) {
                        if (isset($cart_item['variation'])) {
                            $attrs = array();
                            foreach ($cart_item['variation'] as $attribute_name => $value) {
                                $attrs[str_replace('attribute_', '', $attribute_name)] = $value;
                            }
                        }
                    }
                    $product_variation = Woocommerce::getProduct(Woocommerce::getProductParentId($product));
                    foreach ($attrs as $taxonomy => $value) {
                        if ($value) {
                            $term_obj = get_term_by('slug', $value, $taxonomy);
                            if (!is_wp_error($term_obj) && $term_obj && $term_obj->name) {
                                $attr_ids = array_merge($attr_ids, (array)($term_obj->term_id));
                            }
                        } else {
                            $attrs_variation = Woocommerce::getProductAttributes($product_variation);
                            foreach ($attrs_variation as $attr) {
                                if ($taxonomy == Woocommerce::getAttributeName($attr))
                                    $attr_ids = array_merge($attr_ids, Woocommerce::getAttributeOption($attr));
                            }
                        }
                    }
                    if(!empty($product_variation)){
                        $attributes_parent = Woocommerce::getProductAttributes($product_variation);
                        foreach ($attributes_parent as $attributes){
                            if(!empty($attributes) && is_object($attributes)){
                                $variation = Woocommerce::getAttributeVariation($attributes);
                                if(!(int)$variation){
                                    $options = Woocommerce::getAttributeOption($attributes);

                                    if(!empty($options) && is_array($options)){
                                        $attr_ids = array_merge($attr_ids, $options);
                                    }
                                }
                            } else {
                                $options = Woocommerce::getAttributeOption($attributes);
                                if(!empty($options) && is_array($options)){
                                    $attr_ids = array_merge($attr_ids, $options);
                                }
                            }
                        }
                    }
                } else {
                    foreach ($attrs as $attr) {
                        $attr_ids = array_merge($attr_ids, Woocommerce::getAttributeOption($attr));
                    }
                }
                $attr_ids = array_unique($attr_ids);
                $not_in_list_product = count(array_intersect($attr_ids, $comparision_value)) > 0;
                break;
            case 'product_sku':
                $product_sku = Woocommerce::getProductSku($product);
                $not_in_list_product = in_array($product_sku, $comparision_value);
                break;
            case 'product_tags':
                $product_parentId = Woocommerce::getProductParentId($product);
                if(!empty($product_parentId)){
                    $parent_product = Woocommerce::getProduct($product_parentId);
                    $tag_ids = Woocommerce::getProductTags($parent_product);
                }else{
                    $tag_ids = Woocommerce::getProductTags($product);
                }
                $not_in_list_product = count(array_intersect($tag_ids, $comparision_value)) > 0;
                break;
            default:
                ///for custom taxonomy
                if(isset($options->custom_taxonomy) && $options->custom_taxonomy == $type){
                    if(in_array($type, array_keys(Woocommerce::getCustomProductTaxonomies()))){
                        $product_parent = Woocommerce::getProductParentId($product_id);
                        $product_id = !empty($product_parent) ? $product_parent : $product_id;
                        $term_ids = wp_get_post_terms($product_id, $type, array("fields" => "ids"));
                        $not_in_list_product = count(array_intersect($term_ids, $comparision_value)) > 0;
                    }
                }
                break;
        }
        return $not_in_list_product;
    }
}