<?php

namespace Wdr\App\Helpers;

use Valitron\Validator;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Validation
 * https://github.com/vlucas/valitron
 * Class Validation
 * @package Wdr\App\Helpers
 */
class Validation
{
    static $is_condition_value_valid = NULL;

    /**
     * validate input against the alpha numeric and spaces
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateAlphaNumWithSpace($field, $value, array $params, array $fields)
    {
        return (bool)preg_match('/^[\p{L}\p{Nd} .-]+$/', $value);
    }

    /**
     * validate Input Text Html Tags
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateBasicHtmlTags($field, $value, array $params, array $fields)
    {
        $value = stripslashes($value);
        $value = html_entity_decode($value);
        $invalid_tags = array("script", "iframe", "style");
        foreach ($invalid_tags as $tag_name) {
            $pattern = "#<\s*?$tag_name\b[^>]*>(.*?)</$tag_name\b[^>]*>#s";;
            preg_match($pattern, $value, $matches);
            //script or iframe found
            if (!empty($matches)) {
                return false;
            }
        }
        return true;
    }

    /**
     * validate Plain Input Text
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validatePlainInputText($field, $value, array $params, array $fields)
    {
        if (!empty($value)) {
            $value = html_entity_decode($value);
            $html = Woocommerce::removeHtmlTags($value);
            return ($html === trim($value));
        } else {
            return true;
        }
    }

    /**
     * validate input against the alpha numeric and spaces
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateColor($field, $value, array $params, array $fields)
    {
        return (bool)preg_match('/^#(([0-9a-fA-F]{2}){3}|([0-9a-fA-F]){3})$/', $value);
    }

    /**
     * validate the value is float or not
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateFloat($field, $value, array $params, array $fields)
    {
        return (is_numeric($value) || is_float($value));
    }

    /**
     * validate the value is 0 or 1
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateChecked($field, $value, array $params, array $fields)
    {
        return (in_array($value, array('0', '1')));
    }

    /**
     * validate the conditional values
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateConditionFields($field, $value, array $params, array $fields)
    {
        if (is_array($value)) {
            foreach ($value as $input_val) {
                if (!self::validateConditionFields($field, $input_val, $params, $fields)) {
                    return false;
                }
            }
        } else {
            return self::validatePlainInputText($field, $value, $params, $fields);
        }
        return true;
    }

    /**
     * validate rules while saving data
     * @param $post_values
     * @return array|bool
     */
    static function validateRules($post_values)
    {
        $input_validator = new Validator($post_values);
        Validator::addRule('basicTags', array(__CLASS__, 'validateBasicHtmlTags'), __('Invalid characters', 'woo-discount-rules'));
        Validator::addRule('checkPlainInputText', array(__CLASS__, 'validatePlainInputText'), __('Accepts only letters a-z, numbers 0-9 and spaces with special characters', 'woo-discount-rules'));
        Validator::addRule('float', array(__CLASS__, 'validateFloat'), __('Accepts only numbers 0-9 and one dot', 'woo-discount-rules'));
        Validator::addRule('checked', array(__CLASS__, 'validateChecked'), __('Accepts only 0 or 1', 'woo-discount-rules'));
        Validator::addRule('color', array(__CLASS__, 'validateColor'), __('Accepts only hex color code', 'woo-discount-rules'));
        Validator::addRule('conditionValues', array(__CLASS__, 'validateConditionFields'), __('Invalid characters', 'woo-discount-rules'));
        //may contain
        $input_validator->rule('checkPlainInputText',
            array(
                'title',
                'product_adjustments.cart_label',
                'cart_adjustments.label',
                'bulk_adjustments.ranges.*.label',
                'set_adjustments.ranges.*.label',
                'conditions.*.options.custom_value',
            )
        );
        //Discount bar accept br, strong, span,div, p tags only
        $input_validator->rule('basicTags',
            array(
                'discount_badge.badge_text',
                'conditions.*.options.subtotal_promotion_message', //regex for exclude our tags - > '/^[a-zA-Z0-9 _}?{-]*$/'
            )
        );
        //Validation condition values
        $input_validator->rule('conditionValues',
            array(
                'conditions.*.options.value',
                'filters.*.value.*',
            )
        );
        //exclude our short code
        $input_validator->rule('regex', array(
            'conditions.*.options.time',
            'conditions.*.options.to',
            'conditions.*.options.from',
        ), '/^[\p{L}\p{Nd} :._-]+$/');
        //validate slug may contains a-zA-Z0-9_-
        $input_validator->rule('slug',
            array(
                'discount_type',
                'product_adjustments.type',
                'filters.*.type',
                'filters.*.method',
                'cart_adjustments.type',
                'bulk_adjustments.operator',
                'bulk_adjustments.ranges.*.type',
                'set_adjustments.operator',
                'set_adjustments.ranges.*.type',
                'buyx_getx_adjustments.ranges.*.free_type',
                'buyx_gety_adjustments.type',
                'buyx_gety_adjustments.operator',
                'buyx_gety_adjustments.mode',
                'buyx_gety_adjustments.ranges.*.free_type',
                'additional.condition_relationship',
                'conditions.*.type',
                'conditions.*.options.operator',
                'conditions.*.options.calculate_from',
                'conditions.*.options.status.*',
                'conditions.*.options.combination',
                'conditions.*.options.type',
                'conditions.*.options.cartqty',
            )
        );
        //only numbers, not accepts float also
        $input_validator->rule('integer',
            array(
                'usage_limits',
                'bulk_adjustments.ranges.*.from',
                'bulk_adjustments.ranges.*.to',
                'set_adjustments.ranges.*.from',
                'buyx_getx_adjustments.ranges.*.from',
                'buyx_getx_adjustments.ranges.*.to',
                'buyx_getx_adjustments.ranges.*.free_qty',
                'buyx_gety_adjustments.ranges.*.from',
                'buyx_gety_adjustments.ranges.*.to',
                'buyx_gety_adjustments.ranges.*.products.*',
                'buyx_gety_adjustments.ranges.*.free_qty',
                'buyx_gety_adjustments.ranges.*.categories.*',
                'conditions.*.options.products.*',
                'conditions.*.options.product.*',
                'conditions.*.options.category.*',
                'conditions.*.options.qty',
            )
        );
        //may contain flot or number
        $input_validator->rule('float',
            array(
                'product_adjustments.value',
                'cart_adjustments.value',
                'bulk_adjustments.ranges.*.value',
                'set_adjustments.ranges.*.value',
                'conditions.*.options.amount',
            )
        );
        // must 0 or 1
        $input_validator->rule('checked',
            array(
                'enabled',
                'exclusive',
                'product_adjustments.apply_as_cart_rule',
                'bulk_adjustments.apply_as_cart_rule',
                'set_adjustments.apply_as_cart_rule',
                'set_adjustments.ranges.*.recursive',
                'buyx_getx_adjustments.ranges.*.recursive',
                'buyx_gety_adjustments.ranges.*.recursive',
                'discount_badge.display')
        );
        // format date
        $input_validator->rule('dateFormat', array(
            'date_from',
            'date_to'
        ), 'Y-m-d H:i');
        //validate only hex color code #000000 or #fff
        $input_validator->rule('color', array(
            'discount_badge.badge_color_picker',
            'discount_badge.badge_text_color_picker',
        ));
        if ($input_validator->validate()) {
            return true;
        } else {
            return $input_validator->errors();
        }
    }

    /**
     * validate Radio Button And Select Box
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateRadioButtonAndSelectBox($field, $value, array $params, array $fields)
    {
        $acceptable = array('yes', 'on', 1, '1', true, 0, '0');
        return in_array($value, $acceptable, true);
    }

    /**
     * validate Radio Button And Select Box
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateCrossSellOrdering($field, $value, array $params, array $fields)
    {
        $acceptable = array('desc', 'asc');
        return in_array($value, $acceptable, true);
    }

    /**
     * validate Order bY
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateCrossSellOrderBy($field, $value, array $params, array $fields)
    {
        $acceptable = array('rand', 'menu_order', 'price');
        return in_array($value, $acceptable, true);
    }

    /**
     * Check alphaNum values for selected array values (multi select box)
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateSelectedArrayValues($field, $value, array $params, array $fields)
    {
        $result = false;
        if (is_array($value) && !empty($value)) {
            $result = true;
            foreach ($value as $val) {
                //Validate that a field contains only alpha-numeric characters, dashes, and underscores
                if (!preg_match('/^([-a-z0-9_-])+$/i', $val)) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * Validate discount rules settings tab input fields
     * @param $post_values
     * @return bool
     */
    static function validateSettingsTabFields($post_values)
    {
        $settings_fields_validator = new Validator($post_values);
        Validator::addRule('basicTags', array(__CLASS__, 'validateBasicHtmlTags'), __('Invalid characters', 'woo-discount-rules'));
        Validator::addRule('radioButtonAndSelectBox', array(__CLASS__, 'validateRadioButtonAndSelectBox'), __('Accept only yes, on, 1, true', 'woo-discount-rules'));
        Validator::addRule('plainInputText', array(__CLASS__, 'validatePlainInputText'), __('Should not contain any tags', 'woo-discount-rules'));
        Validator::addRule('selectedArrayValues', array(__CLASS__, 'validateSelectedArrayValues'), __('Should not contain any tags and special characters', 'woo-discount-rules'));
        Validator::addRule('crossSellOrdering', array(__CLASS__, 'validateCrossSellOrdering'), __('Invalid inputs', 'woo-discount-rules'));
        Validator::addRule('crossSellOrderBy', array(__CLASS__, 'validateCrossSellOrderBy'), __('Invalid inputs', 'woo-discount-rules'));
        $settings_fields_validator->rule('crossSellOrdering',
            array(
                'cross_sell_on_cart_order',
            )
        );
        $settings_fields_validator->rule('crossSellOrderBy',
            array(
                'cross_sell_on_cart_order_by',
            )
        );
        //allow br, strong, span,div, p tags only
        $settings_fields_validator->rule('basicTags',
            array(
                'on_sale_badge_html',
                'applied_rule_message',
            )
        );
        //Should not allow any tags
        $settings_fields_validator->rule('plainInputText',
            array(
                'discount_label_for_combined_discounts',
                'free_shipping_title',
                'you_saved_text',
                'table_title_column_name',
                'table_discount_column_name',
                'table_range_column_name',
            )
        );
        //validate yes, on, 1, true, 0 , '0'
        $settings_fields_validator->rule('radioButtonAndSelectBox',
            array(
                'apply_discount_subsequently',
                'refresh_order_review',
                'suppress_other_discount_plugins',
                'customize_on_sale_badge',
                'force_override_on_sale_badge',
                'show_bulk_table',
                'table_column_header',
                'table_title_column',
                'table_range_column',
                'table_discount_column_value',
                'table_discount_column',
                'modify_price_at_shop_page',
                'modify_price_at_product_page',
                'modify_price_at_category_page',
                'show_strikeout_on_cart',
                'combine_all_cart_discounts',
                'show_subtotal_promotion',
                'show_promo_text_con',
                'show_applied_rules_message_on_cart',
                'show_cross_sell_on_cart',
                'wdr_override_custom_price',
                'disable_recalculate_total',
                'disable_recalculate_total_when_coupon_apply'
            )
        );
        //validate slug may contains a-zA-Z0-9_-
        $settings_fields_validator->rule('slug',
            array(
                'calculate_discount_from',
                'apply_product_discount_to',
                'disable_coupon_when_rule_applied',
                'show_on_sale_badge',
                'position_to_show_bulk_table',
                'position_to_show_discount_bar',
                'show_strikeout_when',
                'display_saving_text',
                'apply_cart_discount_as',
            )
        );
        //validate integer 0,1,2..
        $settings_fields_validator->rule('integer',
            array(
                'customize_bulk_table_title',
                'customize_bulk_table_discount',
                'customize_bulk_table_range',
                'cross_sell_on_cart_limit',
                'cross_sell_on_cart_column',
            )
        );
        //validate array
        $settings_fields_validator->rule('selectedArrayValues',
            array(
                'awdr_rebuild_on_sale_rules',
                'show_promo_text',
            )
        );
        //LicenceKey
        $settings_fields_validator->rule('alphaNum',
            array(
                'licence_key',
            )
        );
        if ($settings_fields_validator->validate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validate discount rules licence key input
     * @param $post_values
     * @return bool
     */
    static function validateLicenceKay($post_values)
    {
        $rules = [
            'alphaNum' => 'licence_key',
        ];
        $v = new Validator(array('licence_key' => $post_values));
        $v->rules($rules);
        return $v->validate();
    }

    /**
     * Validate advanced option section
     * @param $post_values
     * @return bool
     */
    static function validateAdvancedOptionKey($post_values)
    {
        $advanced_option_validator = new Validator($post_values);
        $advanced_option_validator->rule('integer',
            array(
                'wdr_override_custom_price',
                'wdr_recalculate_total_before_cart',
                'wdr_recalculate_total_when_coupon_apply',
            )
        );
        if ($advanced_option_validator->validate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *validate Report Fields
     *
     * @param $field
     * @param $value
     * @param array $params
     * @param array $fields
     * @return bool
     */
    static function validateReportFields($field, $value, array $params, array $fields)
    {
        return (bool)preg_match('/^[a-zA-Z0-9 :_-]+$/', $value);
    }

    /**
     * validate Report Tab Fields
     *
     * @param $post_values
     * @return bool
     */
    static function validateReportTabFields($post_values)
    {
        $report_fields_validator = new Validator($post_values);
        Validator::addRule('reportFields', array(__CLASS__, 'validateReportFields'), __('Validation error', 'woo-discount-rules'));
        //Validation condition values
        $report_fields_validator->rule('reportFields',
            array(
                'period',
                'from',
                'to',
                'type',
            )
        );
        if ($report_fields_validator->validate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * validate state country condition
     *
     * @param $post_values
     * @return bool
     */
    static function validateStateCountryCondition($post_values)
    {
        $state_country_validator = new Validator($post_values);
        Validator::addRule('conditionValues', array(__CLASS__, 'validateConditionFields'), __('Invalid characters', 'woo-discount-rules'));
        //Validation condition values
        $state_country_validator->rule('conditionValues',
            array(
                'selected_country',
                'selected_index',
                'selected_state',
            )
        );
        if ($state_country_validator->validate()) {
            return true;
        } else {
            return false;
        }
    }
}