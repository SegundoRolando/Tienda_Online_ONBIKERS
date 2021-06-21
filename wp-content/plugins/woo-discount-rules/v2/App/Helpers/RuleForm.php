<?php

namespace Wdr\App\Helpers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Helps to create a rule while migration/manually
 * */
class RuleForm
{
    protected $form = array();

    /**
     * Get form
     * */
    public function getForm(){
        return $this->form;
    }

    public function __get( $key )
    {
        return $this->form[ $key ];
    }

    public function __set( $key, $value )
    {
        $this->form[ $key ] = $value;
    }

    /**
     * reset
     * */
    public function reset(){
        $this->form = array();
    }

    /**
     * Set filter value
     *
     * @param $type string
     * @param $method mixed
     * @param $value mixed
     * @param $additional_data array
     * */
    public function setFilter($type, $method = null, $value = array(), $additional_data = array()){
        $filters = isset($this->form['filters'])? $this->form['filters']: array();
        $filter['type'] = $type;
        if($method !== null){
            $filter['method'] = $method;
        }
        if(!empty($value)){
            $filter['value'] = $value;
        }
        if(!empty($additional_data)){
            foreach ($additional_data as $key => $values){
                $filter[$key] = $values;
            }
        }
        $filters[] = $filter;
        $this->form['filters'] = $filters;
    }

    /**
     * Set cumulative option
     * @param $type string
     * */
    public function setCumulativeOption($type = 'product_cumulative'){
        if(isset($this->form['discount_type'])){
            $discount_type = $this->form['discount_type'];
            $field_name = '';
            if($discount_type == 'wdr_bulk_discount'){
                $field_name = 'bulk_adjustments';
            } elseif ($discount_type == 'wdr_set_discount'){
                $field_name = 'set_adjustments';
            } elseif ($discount_type == 'wdr_buy_x_get_y_discount'){
                $field_name = 'buyx_gety_adjustments';
            }
            if($field_name != ''){
                $bulk_adjustments = isset($this->form[$field_name])? $this->form[$field_name]: array();
                $bulk_adjustments['operator'] = $type;
                $this->form[$field_name] = $bulk_adjustments;
            }
        }
    }

    /**
     * Set buy x get y option
     * @param $type string
     * */
    public function setBuyXGetYOption($type = 'bxgy_product', $mode = 'auto_add'){
        $adjustments = isset($this->form['buyx_gety_adjustments'])? $this->form['buyx_gety_adjustments']: array();
        $adjustments['type'] = $type;
        $adjustments['mode'] = $mode;
        $this->form['buyx_gety_adjustments'] = $adjustments;
    }

    /**
     * Set bulk range
     * @param $from int
     * @param $to int
     * @param $value mixed
     * @param $type string
     * @param $label string
     * */
    public function setBulkRange($from, $to, $value, $type = 'percentage', $label = ''){
        $bulk_adjustments = isset($this->form['bulk_adjustments'])? $this->form['bulk_adjustments']: array();
        $bulk_adjustments_ranges = isset($bulk_adjustments['ranges'])? $bulk_adjustments['ranges']: array();
        $bulk_adjustments_ranges[] = array(
            "from" => $from,
            "to" => $to,
            "type" => $type,
            "value" => $value,
            "label" => $label,
        );
        $bulk_adjustments['ranges'] = $bulk_adjustments_ranges;
        $this->form['bulk_adjustments'] = $bulk_adjustments;
    }

    public function setProductAdjustment($type, $value, $apply_as_cart_rule = ''){
        $this->form['product_adjustments'] = array(
            "type" => $type,
            "value" => $value,
            "apply_as_cart_rule" => $apply_as_cart_rule,
        );
    }

    public function setSetRange($from, $value, $type = 'fixed_set_price', $label = ''){
        $adjustments = isset($this->form['set_adjustments'])? $this->form['set_adjustments']: array();
        $adjustments_ranges = isset($adjustments['ranges'])? $adjustments['ranges']: array();
        $adjustments_ranges[] = array(
            "from" => $from,
            "type" => $type,
            "value" => $value,
            "label" => $label,
        );
        $adjustments['ranges'] = $adjustments_ranges;
        $this->form['set_adjustments'] = $adjustments;
    }

    public function setBuyXGetXRange($from, $free_qty, $to = '', $type = 'free_product', $value = '', $recursive = 0){
        $adjustments = isset($this->form['buyx_getx_adjustments'])? $this->form['buyx_getx_adjustments']: array();
        $adjustments_ranges = isset($adjustments['ranges'])? $adjustments['ranges']: array();
        $adjustments_ranges[] = array(
            "from" => $from,
            "to" => $to,
            "free_type" => $type,
            "free_qty" => $free_qty,
            "free_value" => $value,
            "recursive" => $recursive,
        );
        $adjustments['ranges'] = $adjustments_ranges;
        $this->form['buyx_getx_adjustments'] = $adjustments;
    }

    public function setBuyXGetYRange($from, $free_qty, $additional_values = array(), $to = '', $type = 'free_product', $value = '', $recursive = 0){
        $adjustments = isset($this->form['buyx_gety_adjustments'])? $this->form['buyx_gety_adjustments']: array();
        $adjustments_ranges = isset($adjustments['ranges'])? $adjustments['ranges']: array();
        $adjustments_range_value = array(
            "from" => $from,
            "to" => $to,
            "free_type" => $type,
            "free_qty" => $free_qty,
            "free_value" => $value,
            "recursive" => $recursive,
        );
        if(!empty($additional_values) && is_array($additional_values)){
            $adjustments_range_value = array_merge($adjustments_range_value, $additional_values);
        }
        $adjustments_ranges[] = $adjustments_range_value;
        $adjustments['ranges'] = $adjustments_ranges;
        $this->form['buyx_gety_adjustments'] = $adjustments;
    }

    /**
     * Set discount badge
     * @param $badge_text string
     * @param $display boolean
     * @param $badge_color_picker string
     * @param $badge_text_color_picker string
     * */
    public function setDiscountBadge($badge_text, $display = true, $badge_color_picker = '#6aaef6', $badge_text_color_picker = '#ffffff'){
        $this->form['discount_badge'] = array(
            'display' => $display,
            'badge_color_picker' => $badge_color_picker,
            'badge_text_color_picker' => $badge_text_color_picker,
            'badge_text' => $badge_text,
        );
    }

    /**
     * Set conditions
     * @param $type string
     * @param $options array
     * @param $additional_data array
     * */
    public function setConditions($type, $options = array(), $additional_data = array()){
        $conditions = isset($this->form['conditions'])? $this->form['conditions']: array();
        $condition['type'] = $type;
        $condition['options'] = $options;
        if(!empty($additional_data)){
            foreach ($additional_data as $key => $values){
                $condition[$key] = $values;
            }
        }
        $conditions[] = $condition;
        $this->form['conditions'] = $conditions;
    }

    /**
     * Set cart adjustment
     * @param $value mixed
     * @param $type string
     * @param $label string
     * */
    public function setCartAdjustment($value, $type = 'percentage', $label = ''){
        $cart_adjustments = isset($this->form['cart_adjustments'])? $this->form['cart_adjustments']: array();
        $cart_adjustments['type'] = $type;
        $cart_adjustments['value'] = $value;
        $cart_adjustments['label'] = $label;
        $this->form['cart_adjustments'] = $cart_adjustments;
    }
}