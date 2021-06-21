<?php

namespace Wdr\App\Controllers;

use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Language;
use Wdr\App\Helpers\Template;
use Wdr\App\Helpers\Woocommerce;
use Wdr\App\Helpers\Input;
use Wdr\App\Models\DBTable;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base
{
    public static $db, $config, $woocommerce_helper, $template_helper, $language_helper;
    public $default_rule = NULL, $input, $filter_types = array(), $discount_types = array(), $available_conditions = array();

    /**
     * Base constructor.
     */
    function __construct()
    {
        self::$db = (!empty(self::$db)) ? self::$db : new DBTable();
        self::$config = (empty(self::$config)) ? new Configuration() : self::$config;
        self::$woocommerce_helper = (empty(self::$woocommerce_helper)) ? new Woocommerce() : self::$woocommerce_helper;
        $this->filter_types = (!empty($this->filter_types)) ? $this->filter_types : $this->filtersTypes();
        $this->available_conditions = (!empty($this->available_conditions)) ? $this->available_conditions : $this->getAvailableConditions();
        $this->discount_types = (!empty($this->discount_types)) ? $this->discount_types : $this->discountElements();
        self::$template_helper = (!empty(self::$template_helper)) ? self::$template_helper : new Template();
        self::$language_helper = (!empty(self::$language_helper)) ? self::$language_helper : new Language();
        $this->input = new Input();
    }

    /**
     * Product filter types
     * @return mixed
     */
    function filtersTypes()
    {
        $this->filter_types['all_products'] = array(
            'label' => __('All Products', 'woo-discount-rules'),
            'group' => __('Product', 'woo-discount-rules'),
            'template' => WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Filters/AllProducts.php',
        );
        $this->filter_types['products'] = array(
            'label' => __('Products', 'woo-discount-rules'),
            'group' => __('Product', 'woo-discount-rules'),
            'template' => WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Filters/Products.php',
        );

        $this->filter_types = apply_filters( 'advanced_woo_discount_rules_filters', $this->filter_types);
        return $this->filter_types;
    }

    /**
     * get template content
     * @return array
     */
    function getFilterTemplatesContent()
    {
        $templates = array_map(function ($item) {
            ob_start();
            if(isset($item['template']) && !empty($item['template'])){
                include $item['template'];
            }
            $content = ob_get_clean();
            return $content;
        }, $this->filter_types);
        return $templates;
    }

    /**
     * get filter type array
     * @return array
     */
    function getProductFilterTypes()
    {
        $ret = array();
        foreach ($this->filter_types as $filter_key => $filter_item) {
            $group = $filter_item['group'];
            $ret[$group][$filter_key] = $filter_item;
        }
        return $ret;
    }

    /**
     * availableConditions elements
     * @return array
     */
    public function getAvailableConditions()
    {
        //Read the conditions directory and create condition object
        if (file_exists(WDR_PLUGIN_PATH . 'App/Conditions/')) {
            $conditions_list = array_slice(scandir(WDR_PLUGIN_PATH . 'App/Conditions/'), 2);
            if (!empty($conditions_list)) {
                foreach ($conditions_list as $condition) {
                    $class_name = basename($condition, '.php');
                    if (!in_array($class_name, array('Base'))) {
                        $condition_class_name = 'Wdr\App\Conditions\\' . $class_name;
                        if (class_exists($condition_class_name)) {
                            $condition_object = new $condition_class_name();
                            if ($condition_object instanceof \Wdr\App\Conditions\Base) {
                                $rule_name = $condition_object->name();
                                if (!empty($rule_name)) {
                                    $this->available_conditions[$rule_name] = array(
                                        'object' => $condition_object,
                                        'label' => $condition_object->label,
                                        'group' => $condition_object->group,
                                        'template' => $condition_object->template,
                                        'extra_params' => $condition_object->extra_params,
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->available_conditions = apply_filters( 'advanced_woo_discount_rules_conditions', $this->available_conditions);
        return $this->available_conditions;
    }

    /**
     * get conditions template content
     * @return array
     */
    public function getConditionsTemplatesContent()
    {
        $condition_templates = array();
        if (!empty($this->available_conditions)) {
            foreach ($this->available_conditions as $item) {
                $extra_params['render_saved_condition'] = false;
                $extra_params = isset($item['extra_params']) && is_array($item['extra_params']) ? $item['extra_params'] : array();
                $extra_params['render_saved_condition'] = false;
                if(isset($item['template']) && !empty($item['template'])){
                    $condition_templates[] = self::$template_helper->setData($extra_params)->setPath($item['template'])->render();
                }
            }
        }
        return $condition_templates;
    }

    /**
     * get filter type array
     * @return array
     */
    public function getProductConditionsTypes()
    {
        $sortedConditionsTypes = $cart_item_condition = $billing = $customer = array();
        $ruleConditionsTypes = array();

        foreach ($this->available_conditions as $condition_key => $condition_item) {
            $group = empty($condition_item['group']) ? 'remove_index' : $condition_item['group'];
            $ruleConditionsTypes[$group][$condition_key] = $condition_item;
        }
        if(isset($ruleConditionsTypes['remove_index'])){
            unset($ruleConditionsTypes['remove_index']);
        }
        foreach ($ruleConditionsTypes as $key => $options){
            if(!empty($options) && is_array($options)) {
                if ($key === "Cart") {
                    $default_first = $options['cart_subtotal'];
                    $default_second = $options['cart_items_quantity'];
                    $default_thired = $options['cart_coupon'];
                    $default_last = $options['cart_line_items_count'];
                    unset($options['cart_subtotal'], $options['cart_items_quantity'], $options['cart_coupon'], $options['cart_line_items_count']);
                    $options = array(
                                'cart_subtotal' => $default_first,
                                'cart_items_quantity' => $default_second,
                                'cart_coupon' => $default_thired,
                                )+$options+array('cart_line_items_count' => $default_last);
                    $cart_item_condition[$key] = $options;
                } elseif ($key === "Billing"){
                    $billing[$key] = $options;
                } elseif ($key === "Customer"){
                    $customer[$key] = $options;
                } else {
                    $sortedConditionsTypes[$key] = $options;
                }
            }
        }
        $sortedConditionsTypes =  $cart_item_condition+$sortedConditionsTypes+$billing+$customer;
        return $sortedConditionsTypes;
    }

    /**
     * Available Discount elements
     */
    function discountElements()
    {
        $this->discount_types['wdr_simple_discount'] = array(
            'class' => '',
            'label' => __('Product Adjustment', 'woo-discount-rules'),
            'group' => __('Simple Discount', 'woo-discount-rules'),
            'template' => WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Discounts/simple.php',
        );
        $this->discount_types['wdr_cart_discount'] = array(
            'class' => '',
            'label' => __('Cart Adjustment', 'woo-discount-rules'),
            'group' => __('Simple Discount', 'woo-discount-rules'),
            'template' => WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Discounts/Cart.php',
        );
        $this->discount_types['wdr_bulk_discount'] = array(
            'class' => '',
            'label' => __('Bulk Discount', 'woo-discount-rules'),
            'group' => __('Bulk Discount', 'woo-discount-rules'),
            'template' => WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Discounts/Bulk.php',
        );

        $this->discount_types = apply_filters('advanced_woo_discount_rules_adjustment_type', $this->discount_types);

        return $this->discount_types;
    }

    /**
     * get discount template content
     * @return array
     */
    function getDiscountTemplatesContent()
    {
        $discount_templates = array_map(function ($item) {
            ob_start();
            if(isset($item['template']) && !empty($item['template'])){
                include $item['template'];
            }
            $content = ob_get_clean();
            return $content;
        }, $this->discount_types);
        return $discount_templates;
    }

    /**
     * get filter type array
     * @return array
     */
    function getDiscountTypes()
    {
        $processed_discount_types = array();
        foreach ($this->discount_types as $discount_key => $discount_item) {
            $group = $discount_item['group'];
            $processed_discount_types[$group][$discount_key] = $discount_item;
        }
        return $processed_discount_types;
    }
}