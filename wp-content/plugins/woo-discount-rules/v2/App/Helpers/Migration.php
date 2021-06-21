<?php

namespace Wdr\App\Helpers;

use Wdr\App\Controllers\Configuration;

class Migration
{
    protected $form;
    protected $rule;
    protected $migrated_option_key = 'awdr_migration_info';
    protected $migration_count_on_a_set = 2;

    public function __construct()
    {
        $this->form = new RuleForm();
        $this->rule = new Rule();
    }

    public static function init(){
        $do_migration = true;
        if($do_migration === true){
            return (new self())->processMigrationV1ToV2();
        }
        return false;
    }
    public static function hasSwitchBackOption(){
        // For v2 phase 1 switch back should display by default
        //$has_switch_back_option = true;
        $has_migration = (new self())->getMigrationInfoOf('has_migration', null);
        $has_switch_back_option = false;
        if($has_migration == 1){
            $has_switch_back_option = true;
        }

        return apply_filters('advanced_woo_discount_rules_has_switch_back_option', $has_switch_back_option);
    }

    /**
     * Check and create sample rules if no rules exists
     * */
    public static function checkAndCreateSampleRules(){
        $database = new \Wdr\App\Models\DBTable();
        $rows = $database->getRulesCount();
        if(empty($rows) || $rows <= 0){
            self::createSampleRules();
        }
    }

    protected static function getSampleRules(){
        $rules[] = array(
            'title' => "Store wide discount - sample",
            'type' => "wdr_simple_discount",
            'has_condition' => false,
            'discount_type' => "percentage",
            'discount_value' => 10,
        );
        $rules[] = array(
            'title' => "Bulk/tiered discount - sample",
            'type' => "wdr_bulk_discount",
            'has_condition' => false,
            'discount_ranges' => array(
                array("from" => 1, "to" => 5, "discount_type" => "percentage", "discount_value" => 5),
                array("from" => 6, "to" => 10, "discount_type" => "percentage", "discount_value" => 10),
                array("from" => 11, "to" => 15, "discount_type" => "percentage", "discount_value" => 15),
                array("from" => 16, "to" => 20, "discount_type" => "percentage", "discount_value" => 20),
                array("from" => 21, "to" => '', "discount_type" => "percentage", "discount_value" => 25),
            )
        );
        $rules[] = array(
            'title' => "Cart discount - sample",
            'type' => "wdr_cart_discount",
            'has_condition' => true,
            'discount_type' => "percentage",
            'discount_value' => 20,
            'discount_label' => "Subtotal discount",
            'condition_type' => "cart_subtotal",
            'condition_option' => array(
                'operator' => 'greater_than_or_equal',
                'value' => 500,
                'calculate_from' => 'from_cart',
            ),
        );

        return $rules;
    }

    protected static function createSampleRules(){
        $current_obj = new self();
        $rules = self::getSampleRules();
        foreach ($rules as $key => $rule){
            $current_obj->form->reset();
            $current_obj->form->title = $rule['title'];
            $current_obj->form->enabled = 0;
            $current_obj->form->additional = array('condition_relationship' => 'and');
            $current_obj->form->usage_limits = 0;
            $current_obj->form->date_from = '';
            $current_obj->form->date_to = '';
            $current_obj->form->discount_type = $rule['type'];
            $current_obj->form->setFilter('all_products');

            if($rule['has_condition']){
                $current_obj->form->setConditions($rule['condition_type'], $rule['condition_option']);
            }
            if($rule['type'] == 'wdr_cart_discount'){
                $current_obj->form->setCartAdjustment($rule['discount_value'], $rule['discount_type'], $rule['discount_label']);
            } else {
                $current_obj->form->setCumulativeOption();
                if($rule['type'] == 'wdr_simple_discount'){
                    $current_obj->form->setProductAdjustment($rule['discount_type'], $rule['discount_value']);
                } elseif ($rule['type'] == 'wdr_bulk_discount'){
                    foreach ($rule['discount_ranges'] as $discount_range){
                        $current_obj->form->setBulkRange($discount_range['from'], $discount_range['to'], $discount_range['discount_value'], $discount_range['discount_type']);
                    }
                }
            }
            $form_data = $current_obj->form->getForm();
            $current_obj->rule->save($form_data);
        }
    }

    /**
     * Migrate licence key
     * */
    protected function migrateLicenceKey(){
        $v2_config = new Configuration();
        $licence_key = $v2_config->getConfig('licence_key', null);
        if(empty($licence_key)){
            $v1_config = get_option("woo-discount-config");
            if (is_string($v1_config)) $v1_config = json_decode($v1_config, true);
            $v1_licence_key = isset($v1_config["license_key"])? $v1_config["license_key"] : null;
            if(!empty($v1_licence_key)){
                self::updateLicenceKeyInSettings($v1_licence_key);
            }
        }
    }

    /**
     * Update Licence key with settings
     * */
    protected static function updateLicenceKeyInSettings($licence_key){
        $config = get_option(Configuration::DEFAULT_OPTION);
        $config['licence_key'] = $licence_key;
        update_option(Configuration::DEFAULT_OPTION, $config);
    }

    /**
     * check for migration
     * */
    public static function checkForMigration(){
        $current_obj = new self();
        $has_migration = $current_obj->getMigrationInfoOf('has_migration', null);
        if($has_migration === null){
            $has_migration_status = 0;
            $price_rules = $current_obj->getV1Rules();
            if(!empty($price_rules)){
                $has_migration_status = 1;
            } else {
                $cart_rules = $current_obj->getV1Rules('woo_discount_cart');
                if(!empty($cart_rules)){
                    $has_migration_status = 1;
                }
            }
            $current_obj->updateMigrationInfo(array('has_migration' => $has_migration_status));
        }
    }

    /**
     * Process migration
     * */
    protected function processMigrationV1ToV2(){
        $total_count = Woocommerce::getSession('awdr_v1_to_v2_total_migration');
        if(empty($total_count)){
            $total_count = 0;
            $price_rules = $this->getV1Rules('woo_discount', 1);
            $cart_rules = $this->getV1Rules('woo_discount_cart', 1);
            if(!empty($price_rules)){
                $total_count += count($price_rules);
            }
            if(!empty($cart_rules)){
                $total_count += count($cart_rules);
            }
            Woocommerce::setSession('awdr_v1_to_v2_total_migration', $total_count);
            Woocommerce::setSession('awdr_v1_to_v2_total_migrated', 0);
        }
        $current_migrated_count = 0;
        $price_rules = $this->getV1Rules();
        $cart_rules = $this->getV1Rules('woo_discount_cart');
        if(!empty($price_rules)){
            $this->processPriceRules($price_rules);
            $current_migrated_count += count($price_rules);
        }
        if(!empty($cart_rules)){
            $this->processCartRules($cart_rules);
            $current_migrated_count += count($cart_rules);
        }
        $total_migrated = Woocommerce::getSession('awdr_v1_to_v2_total_migrated', 0);
        $total_migrated += $current_migrated_count;
        $return_status = array(
            'total_count' => $total_count,
            'completed_count' => $total_migrated,
            'status' => 'processing',
        );
        if($total_migrated >= $total_count || $current_migrated_count == 0){
            $this->updateMigrationInfo(array('migration_completed' => 1));
            $return_status['status'] = 'completed';
            $percentage = 100;
            Woocommerce::setSession('awdr_v1_to_v2_total_migration', null);
            $this->migrateLicenceKey();
        } else {
            $percentage = ($total_migrated/$total_count)*100;
            $percentage = round($percentage);
            Woocommerce::setSession('awdr_v1_to_v2_total_migrated', $total_migrated);
        }
        $percentage = $percentage.'%';
        $return_status['display_text'] = sprintf(esc_html__('%s Completed. Please wait..', 'woo-discount-rules'), $percentage);

        return $return_status;
    }

    /**
     * get migration data
     * */
    protected function getMigrationInfo(){
        return get_option($this->migrated_option_key, array());
    }

    /**
     * get migration data
     * */
    public function getMigrationInfoOf($key, $default = ''){
        $config = self::getMigrationInfo();
        if(isset($config[$key])){
            return $config[$key];
        } else {
            return $default;
        }
    }

    /**
     * Update migration data
     * */
    public function updateMigrationInfo($data){
        $config = $this->getMigrationInfo();
        if(!is_array($config)) $config = array();
        $config = array_merge($config, $data);
        update_option($this->migrated_option_key, $config);
    }

    /**
     * Process cart rules
     * @param $rules
     * */
    protected function processCartRules($rules){
        foreach ($rules as $key => $rule){
            $form_data = $this->convertV1CartRuleToV2($rule);
            $this->rule->save($form_data);
            $this->updateMigrationInfo(array('v1_last_migrated_cart_rule_id' => $rule->ID));
        }
    }

    /**
     * Process price rules
     * @param $rules
     * */
    protected function processPriceRules($rules){
        foreach ($rules as $key => $rule){
            $split_rule_types = $this->splitRuleTypes($rule);
            if(!empty($split_rule_types) && count($split_rule_types)){
                foreach ($split_rule_types as $split_rule_type){
                    $rule->meta['discount_range'][0] = json_encode($split_rule_type);
                    $this->processPriceRulesBasedOnType($rule);
                }
            } else {
                $this->processPriceRulesBasedOnType($rule);
            }
        }
    }

    protected function processPriceRulesBasedOnType($rule){
        $form_data = $this->convertV1PriceRuleToV2($rule);
        $this->rule->save($form_data);
        $this->updateMigrationInfo(array('v1_last_migrated_price_rule_id' => $rule->ID));
    }

    protected function splitRuleTypes($price_rule){
        $return = array();
        if($price_rule->rule_method == 'qty_based'){
            $discount_ranges = $this->getDiscountRange($price_rule);
            foreach ($discount_ranges as $key => $discount_range){
                if(in_array($discount_range->discount_type, array('percentage_discount', 'price_discount', 'fixed_price'))){
                    $return['bulk_discounts'][] = $discount_range;
                } else if($discount_range->discount_type == 'set_discount'){
                    $return['set_discount'][] = $discount_range;
                } else if($discount_range->discount_type == 'buy_x_get_x'){
                    $return['buy_x_get_x'][] = $discount_range;
                } else if($discount_range->discount_type == 'buy_x_get_y'){
                    $return['buy_x_get_y'][] = $discount_range;
                } else if($discount_range->discount_type == 'more_than_one_cheapest'){
                    $return['more_than_one_cheapest'][] = $discount_range;
                } else if($discount_range->discount_type == 'more_than_one_cheapest_from_cat'){
                    $return['more_than_one_cheapest_from_cat'][] = $discount_range;
                } else if($discount_range->discount_type == 'more_than_one_cheapest_from_all'){
                    $return['more_than_one_cheapest_from_all'][] = $discount_range;
                } else if($discount_range->discount_type == 'product_discount'){
                    if($discount_range->discount_product_option == 'same_product'){
                        $discount_range->discount_type = 'buy_x_get_x';
                        $return['buy_x_get_x'][] = $discount_range;
                    } else if($discount_range->discount_product_option == 'all'){
                        $discount_range->discount_type = 'buy_x_get_y';
                        $return['buy_x_get_y'][] = $discount_range;
                    } else if($discount_range->discount_product_option == 'more_than_one_cheapest'){
                        $discount_range->discount_type = 'more_than_one_cheapest';
                        $return['more_than_one_cheapest'][] = $discount_range;
                    } else if($discount_range->discount_product_option == 'more_than_one_cheapest_from_cat'){
                        $discount_range->discount_type = 'more_than_one_cheapest_from_cat';
                        $return['more_than_one_cheapest_from_cat'][] = $discount_range;
                    } else if($discount_range->discount_product_option == 'more_than_one_cheapest_from_all'){
                        $discount_range->discount_type = 'more_than_one_cheapest_from_all';
                        $return['more_than_one_cheapest_from_all'][] = $discount_range;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Convert the v1 rule to v2 format for cart rules
     * */
    protected function convertV1CartRuleToV2($rule){
        $this->form->reset();
        $this->processCommonData($rule);
        $this->processDiscountType($rule, 'cart');
        $this->processFilters($rule, 'cart');
        $this->processConditions($rule, 'cart');
        $this->processCartDiscounts($rule);
        return $this->form->getForm();
    }

    /**
     * Convert the v1 rule to v2 format
     * */
    protected function convertV1PriceRuleToV2($price_rule){
        $this->form->reset();
        $this->processCommonData($price_rule);
        $this->processDiscountType($price_rule);
        $this->processFilters($price_rule);
        $this->processCumulativeOption($price_rule);
        $this->processRanges($price_rule);
        $this->processConditions($price_rule);
        $this->processAdvanceTable($price_rule);

        return $this->form->getForm();
    }

    /**
     * Process cumulative option
     * */
    protected function processAdvanceTable($price_rule){
        $advance_table_format = $price_rule->advance_table_format;
        if(!empty($advance_table_format)){

        }
    }

    /**
     * Process cumulative option
     * */
    protected function processRanges($price_rule){
        if($price_rule->rule_method == "qty_based"){
            $discount_ranges = $this->getDiscountRange($price_rule);
            if(!empty($discount_ranges)){
                foreach ($discount_ranges as $discount_range){
                    $discount_type = $discount_range->discount_type;
                    if(in_array($discount_type, array('percentage_discount', 'price_discount', 'fixed_price'))){
                        if($discount_type == 'percentage_discount'){
                            $type = 'percentage';
                        } else if($discount_type == 'price_discount'){
                            $type = 'flat';
                        } else {
                            $type = 'fixed_price';
                        }
                        $this->form->setBulkRange($discount_range->min_qty, $discount_range->max_qty, $discount_range->to_discount, $type);
                    } elseif ($discount_type == 'set_discount'){
                        $this->form->setSetRange($discount_range->min_qty, $discount_range->to_discount);
                    } elseif ($discount_type == 'buy_x_get_x'){
                        $type = ($discount_range->discount_product_discount_type == '')? 'free_product': 'percentage';
                        $min_qty = $discount_range->min_qty;
                        $max_qty = $discount_range->max_qty;
                        $discount_bogo_qty = $discount_range->discount_bogo_qty;
                        if($type == "free_product"){
                            $new_min_qty = $min_qty-$discount_bogo_qty;
                            $new_max_qty = $max_qty-$discount_bogo_qty;
                            if($new_min_qty > 0) $min_qty = $new_min_qty;
                            if($new_max_qty > 0) $max_qty = $new_max_qty;
                        }
                        $value = (isset($discount_range->discount_product_percent))? $discount_range->discount_product_percent: '';
                        $this->form->setBuyXGetXRange($min_qty, $discount_bogo_qty, $max_qty, $type, $value);
                    } else{
                        if ($discount_type == 'buy_x_get_y'){
                            $discount_product = (isset($discount_range->discount_product))? $this->getData($discount_range->discount_product): array();
                            $additional_values = array('products' => $discount_product);
                            $discount_bogo_qty = $discount_range->discount_bogo_qty;
                        } elseif ($discount_type == 'more_than_one_cheapest'){
                            $discount_product = (isset($discount_range->discount_product))? $this->getData($discount_range->discount_product): array();
                            $additional_values = array('products' => $discount_product);
                            $discount_bogo_qty = $discount_range->discount_product_qty;
                        }  elseif ($discount_type == 'more_than_one_cheapest_from_cat'){
                            $discount_category = (isset($discount_range->discount_category))? $this->getData($discount_range->discount_category): array();
                            $additional_values = array('categories' => $discount_category);
                            $discount_bogo_qty = $discount_range->discount_product_qty;
                        }  else {
                            $additional_values = array();
                            $discount_bogo_qty = $discount_range->discount_product_qty;
                        }
                        $type = ($discount_range->discount_product_discount_type == '')? 'free_product': 'percentage';
                        $value = (isset($discount_range->discount_product_percent))? $discount_range->discount_product_percent: '';
                        $this->form->setBuyXGetyRange($discount_range->min_qty, $discount_bogo_qty, $additional_values, $discount_range->max_qty, $type, $value);
                    }
                }
            }
        } else {
            $condition = $this->getData($price_rule->product_based_condition);
            $discount = $this->getData($price_rule->product_based_discount);
            $has_set = false;
            if($discount->discount_type == "percentage_discount"){
                $type = 'percentage';
            } else {
                $type = 'flat';
            }
            if($condition->get_discount_type == "product"){
                if(isset($condition->product_to_apply_count_option) && $condition->product_to_apply_count_option == "apply_first"){
                    $this->form->setSetRange($condition->product_to_apply_count, $discount->discount_value, $type);
                    $has_set = true;
                }
            }
            if($has_set == false){
                $this->form->setProductAdjustment($type, $discount->discount_value);
            }
        }
    }

    /**
     * Process cumulative option
     * */
    protected function processCumulativeOption($price_rule){
        if($price_rule->rule_method == "qty_based"){
            $has_cumulative = false;
            $field_name = '';
            if(in_array($price_rule->apply_to, array('all_products', 'specific_category', 'specific_attribute'))) {
                if ($price_rule->apply_to == 'all_products') {
                    $field_name = 'is_cumulative_for_products';
                } else if ($price_rule->apply_to == 'specific_category') {
                    $field_name = 'is_cumulative';
                } else if ($price_rule->apply_to == 'specific_attribute') {
                    $field_name = 'is_cumulative_attribute';
                }
            }
            if($field_name != ''){
                if(isset($price_rule->$field_name)){
                    if($price_rule->$field_name){
                        $has_cumulative = true;
                    }
                }
            }
            if($has_cumulative){
                $this->form->setCumulativeOption();
            } else {
                $this->form->setCumulativeOption('product');
            }
        }
    }
    
    /**
     * Process cart discounts
     * */
    protected function processCartDiscounts($rule){
        $discount_value = $rule->to_discount;
        if($rule->discount_type == "percentage_discount"){
            $this->form->setCartAdjustment($discount_value);
        } elseif ($rule->discount_type == "price_discount"){
            $this->form->setCartAdjustment($discount_value, 'flat_in_subtotal');
        } elseif ($rule->discount_type == "product_discount"){
            $this->form->setBuyXGetYOption();
            $additional_values = array('products' => $this->getData($rule->cart_discounted_products));
            $this->form->setBuyXGetyRange(1, $rule->product_discount_quantity, $additional_values, 9999, 'free_product');
        }
    }

    protected function processPriceRulesQuantityBasedConditions($rule){
        if($rule->customer == 'only_given'){
            $option = array('operator' => 'in_list', 'value' => $this->getData($rule->users_to_apply));
            $this->form->setConditions('user_list', $option);
        }
        if(isset($rule->user_roles_to_apply) && !empty($rule->user_roles_to_apply)){
            $user_roles_to_apply = $this->getData($rule->user_roles_to_apply);
            if(!empty($user_roles_to_apply)){
                $option = array('operator' => 'in_list', 'value' => $user_roles_to_apply);
                $this->form->setConditions('user_role', $option);
            }
        }
        if(isset($rule->coupons_to_apply_option) && $rule->coupons_to_apply_option != "none"){
            if($rule->coupons_to_apply_option == 'create_dynamic_coupon'){
                $option = array('operator' => 'custom_coupon', 'custom_value' => $rule->dynamic_coupons_to_apply);
            } else if($rule->coupons_to_apply_option == 'any_selected'){
                $coupon = $this->getData($rule->coupons_to_apply);
                if(is_string($coupon)){
                    $coupon = explode(',', $coupon);
                }
                $option = array('operator' => 'at_least_one', 'value' => $coupon);
            } else {
                $coupon = $this->getData($rule->coupons_to_apply);
                if(is_string($coupon)){
                    $coupon = explode(',', $coupon);
                }
                $option = array('operator' => 'all', 'value' => $coupon);
            }
            $this->form->setConditions('cart_coupon', $option);
        }
        if(isset($rule->subtotal_to_apply_option) && $rule->subtotal_to_apply_option != "none"){
            if($rule->subtotal_to_apply_option == 'atleast'){
                $option = array('operator' => 'greater_than_or_equal', 'value' => $rule->subtotal_to_apply);
                $this->form->setConditions('cart_subtotal', $option);
            }
        }
        if(isset($rule->based_on_purchase_history) && $rule->based_on_purchase_history != "0"){
            if($rule->based_on_purchase_history == 'first_order'){
                $option = array('value' => 1);
                $this->form->setConditions('purchase_first_order', $option);
            } elseif ($rule->based_on_purchase_history == 1){
                $history_type = ($rule->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $this->getData($rule->purchase_history_status_list);
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($rule->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($rule->purchased_history_duration, $rule->purchased_history_duration_days);
                }
                $option = array('operator' => $history_type, 'amount' => $rule->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $this->form->setConditions('purchase_spent', $option);
            } elseif ($rule->based_on_purchase_history == 2){
                $history_type = ($rule->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $this->getData($rule->purchase_history_status_list);
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($rule->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($rule->purchased_history_duration, $rule->purchased_history_duration_days);
                }
                $option = array('operator' => $history_type, 'count' => $rule->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $this->form->setConditions('purchase_previous_orders', $option);
            } else{
                //for 3 and 4
                $history_type = ($rule->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $this->getData($rule->purchase_history_status_list);
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($rule->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($rule->purchased_history_duration, $rule->purchased_history_duration_days);
                }
                $products = $this->getData($rule->purchase_history_products);
                $option = array('products'=> $products, 'operator' => $history_type, 'count' => $rule->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $this->form->setConditions('purchase_previous_orders_for_specific_product', $option);
            }
        }
    }

    protected function getPurchaseHistoryDuration($duration, $custom_days){
        if($duration == 'all_time'){
            $return = 'all_time';
        } elseif ($duration == 'custom_days'){
            $return = '-'.$custom_days.'_days';
        } else {
            if($duration == '7_days'){
                $return = '-1_week';
            } else if($duration == '14_days'){
                $return = '-2_week';
            } else if($duration == '30_days'){
                $return = '-1_month';
            } else if($duration == '60_days'){
                $return = '-2_months';
            } else if($duration == '90_days'){
                $return = '-3_months';
            } else if($duration == '180_days'){
                $return = '-9_months';
            } else {
                $return = '-12_months';
            }
        }
        return $return;
    }

    protected function getStateInFormat($states){
        if(is_array($states)){
            $new_states = array();
            foreach ($states as $state){
                $new_state = null;
                $available_states = Woocommerce::getStatesList();
                foreach ($available_states as $key => $val) {
                    foreach ($val as $key2 => $value){
                        if(strtolower($state) == strtolower($value)){
                            $new_state = $key2;
                        }
                    }
                }
                if($new_state !== null){
                    $new_states[] = $new_state;
                } else {
                    $new_states[] = $state;
                }
            }

            return $new_states;
        }

        return array();
    }

    protected function processCartRulesConditions($rule){
        $discount_rule_conditions = $this->getData($rule->discount_rule);
        foreach ($discount_rule_conditions as $discount_rule_condition){
            $has_condition = false;
            if(isset($discount_rule_condition->subtotal_less)){
                $condition_type = 'cart_subtotal';
                $option = array(
                    'operator' => 'less_than_or_equal',
                    'value' => $discount_rule_condition->subtotal_less,
                    'calculate_from' => 'from_filter',
                );
                $has_condition = true;
            }
            if(isset($discount_rule_condition->subtotal_least)){
                $condition_type = 'cart_subtotal';
                $option = array(
                    'operator' => 'greater_than_or_equal',
                    'value' => $discount_rule_condition->subtotal_least,
                    'calculate_from' => 'from_filter',
                );
                if(isset($rule->promotion_subtotal_from) && isset($rule->promotion_message)){
                    $promotion_subtotal_from = $this->getData($rule->promotion_subtotal_from);
                    $promotion_message = $rule->promotion_message;
                    $option["subtotal_promotion_from"] = $promotion_subtotal_from;
                    $option["subtotal_promotion_message"] = $promotion_message;
                }
                $has_condition = true;
            }
            if(isset($discount_rule_condition->item_count_least)){
                $condition_type = 'cart_line_items_count';
                $option = array(
                    'operator' => 'greater_than_or_equal',
                    'value' => $discount_rule_condition->item_count_least,
                    'calculate_from' => 'from_filter',
                );
                $has_condition = true;
            }
            if(isset($discount_rule_condition->item_count_less)){
                $condition_type = 'cart_line_items_count';
                $option = array(
                    'operator' => 'less_than_or_equal',
                    'value' => $discount_rule_condition->item_count_less,
                    'calculate_from' => 'from_filter',
                );
                $has_condition = true;
            }
            if(isset($discount_rule_condition->quantity_least)){
                $condition_type = 'cart_items_quantity';
                $option = array(
                    'operator' => 'greater_than_or_equal',
                    'value' => $discount_rule_condition->quantity_least,
                    'calculate_from' => 'from_filter',
                );
                $has_condition = true;
            }
            if(isset($discount_rule_condition->quantity_less)){
                $condition_type = 'cart_items_quantity';
                $option = array(
                    'operator' => 'less_than_or_equal',
                    'value' => $discount_rule_condition->quantity_less,
                    'calculate_from' => 'from_filter',
                );
                $has_condition = true;
            }
            if(isset($discount_rule_condition->users_in)){
                $condition_type = 'user_list';
                $option = array('operator' => 'in_list', 'value' => $discount_rule_condition->users_in);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->roles_in)){
                $condition_type = 'user_role';
                $option = array('operator' => 'in_list', 'value' => $discount_rule_condition->roles_in);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_email_tld)){
                $condition_type = 'user_email';
                $option = array('operator' => 'user_email_tld', 'value' => $discount_rule_condition->customer_email_tld);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_email_domain)){
                $condition_type = 'user_email';
                $option = array('operator' => 'user_email_domain', 'value' => $discount_rule_condition->customer_email_domain);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_shipping_city)){
                $condition_type = 'shipping_city';
                $option = array('operator' => 'in_list', 'value' => explode(",", $discount_rule_condition->customer_shipping_city));
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_billing_city)){
                $condition_type = 'billing_city';
                $option = array('operator' => 'in_list', 'value' => explode(",", $discount_rule_condition->customer_billing_city));
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_shipping_state)){
                $condition_type = 'shipping_state';
                $shipping_state = $this->getStateInFormat(explode(",", $discount_rule_condition->customer_shipping_state));
                $option = array('operator' => 'in_list', 'value' => $shipping_state);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->shipping_countries_in)){
                $condition_type = 'shipping_country';
                $option = array('operator' => 'in_list', 'value' => $discount_rule_condition->shipping_countries_in);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_shipping_zip_code)){
                $condition_type = 'shipping_zipcode';
                $option = array('operator' => 'in_list', 'value' => $discount_rule_condition->customer_shipping_zip_code);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_based_on_first_order)){
                $condition_type = 'purchase_first_order';
                $option = array('value' => 1);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_based_on_purchase_history)){
                $purchase_history_value = $discount_rule_condition->customer_based_on_purchase_history;
                $condition_type = 'purchase_spent';
                $history_type = ($purchase_history_value->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $purchase_history_value->purchase_history_order_status;
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($purchase_history_value->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($purchase_history_value->purchased_history_duration, $purchase_history_value->purchased_history_duration_days);
                }
                $option = array('operator' => $history_type, 'amount' => $rule->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->customer_based_on_purchase_history)){
                $purchase_history_value = $discount_rule_condition->customer_based_on_purchase_history;
                $condition_type = 'purchase_spent';
                $history_type = ($purchase_history_value->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $purchase_history_value->purchase_history_order_status;
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($purchase_history_value->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($purchase_history_value->purchased_history_duration, $purchase_history_value->purchased_history_duration_days);
                }
                $option = array('operator' => $history_type, 'amount' => $rule->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->customer_based_on_purchase_history_order_count)){
                $purchase_history_value = $discount_rule_condition->customer_based_on_purchase_history_order_count;
                $condition_type = 'purchase_previous_orders';
                $history_type = ($purchase_history_value->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $purchase_history_value->purchase_history_order_status;
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($purchase_history_value->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($purchase_history_value->purchased_history_duration, $purchase_history_value->purchased_history_duration_days);
                }
                $option = array('operator' => $history_type, 'count' => $purchase_history_value->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->customer_based_on_purchase_history_product_order_count)){
                $purchase_history_value = $discount_rule_condition->customer_based_on_purchase_history_product_order_count;
                $condition_type = 'purchase_previous_orders_for_specific_product';
                $history_type = ($purchase_history_value->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $purchase_history_value->purchase_history_order_status;
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($purchase_history_value->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($purchase_history_value->purchased_history_duration, $purchase_history_value->purchased_history_duration_days);
                }
                $products = $purchase_history_value->purchase_history_products;
                $option = array('products'=> $products, 'operator' => $history_type, 'count' => $purchase_history_value->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $has_condition = true;
            }
            if(isset($discount_rule_condition->customer_based_on_purchase_history_product_quantity_count)){
                $purchase_history_value = $discount_rule_condition->customer_based_on_purchase_history_product_quantity_count;
                $condition_type = 'purchase_quantities_for_specific_product';
                $history_type = ($purchase_history_value->purchased_history_type == 'atleast')? 'greater_than_or_equal': 'less_than_or_equal';
                $status = $purchase_history_value->purchase_history_order_status;
                $status = (empty($status))? array(): $status;
                $duration = 'all_time';
                if(isset($purchase_history_value->purchased_history_duration)){
                    $duration = $this->getPurchaseHistoryDuration($purchase_history_value->purchased_history_duration, $purchase_history_value->purchased_history_duration_days);
                }
                $products = $purchase_history_value->purchase_history_products;
                $option = array('products'=> $products, 'operator' => $history_type, 'count' => $purchase_history_value->purchased_history_amount, 'status' => $status, 'time' => $duration);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->create_dynamic_coupon)){
                $condition_type = 'cart_coupon';
                $option = array('operator' => 'custom_coupon', 'custom_value' => $discount_rule_condition->create_dynamic_coupon);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->coupon_applied_all_selected)){
                $condition_type = 'cart_coupon';
                $option = array('operator' => 'at_least_one', 'custom_value' => $discount_rule_condition->coupon_applied_all_selected);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->coupon_applied_any_one)){
                $condition_type = 'cart_coupon';
                $option = array('operator' => 'all', 'custom_value' => $discount_rule_condition->coupon_applied_any_one);
                $has_condition = true;
            }

            if(isset($discount_rule_condition->in_each_category)){
                $condition_type_cat = 'cart_item_category_combination';
                $option_cat = array('combination' => 'each', 'category' => $discount_rule_condition->in_each_category);
                $this->setCategoryCombinationConditions($condition_type_cat, $discount_rule_conditions, $option_cat);
            }

            if($has_condition) $this->form->setConditions($condition_type, $option);
        }
    }

    protected function setCategoryCombinationConditions($condition_type_cat, $discount_rule_conditions, $option_cat){
        foreach ($discount_rule_conditions as $discount_rule_condition) {
            if (isset($discount_rule_condition->subtotal_less)) {
                $option_cat_temp = $option_cat;
                $option_cat_temp['type'] = 'cart_subtotal';
                $option_cat_temp['operator'] = 'less_than_or_equal';
                $option_cat_temp['from'] = $discount_rule_condition->subtotal_less;
                $this->form->setConditions($condition_type_cat, $option_cat_temp);
            }
            if (isset($discount_rule_condition->subtotal_least)) {
                $option_cat_temp = $option_cat;
                $option_cat_temp['type'] = 'cart_subtotal';
                $option_cat_temp['operator'] = 'greater_than_or_equal';
                $option_cat_temp['from'] = $discount_rule_condition->subtotal_least;
                $this->form->setConditions($condition_type_cat, $option_cat_temp);
            }
            if (isset($discount_rule_condition->item_count_least)) {
                $option_cat_temp = $option_cat;
                $option_cat_temp['type'] = 'cart_line_item';
                $option_cat_temp['operator'] = 'greater_than_or_equal';
                $option_cat_temp['from'] = $discount_rule_condition->item_count_least;
                $this->form->setConditions($condition_type_cat, $option_cat_temp);
            }
            if (isset($discount_rule_condition->item_count_less)) {
                $option_cat_temp = $option_cat;
                $option_cat_temp['type'] = 'cart_line_item';
                $option_cat_temp['operator'] = 'less_than_or_equal';
                $option_cat_temp['from'] = $discount_rule_condition->item_count_less;
                $this->form->setConditions($condition_type_cat, $option_cat_temp);
            }
            if (isset($discount_rule_condition->quantity_least)) {
                $option_cat_temp = $option_cat;
                $option_cat_temp['type'] = 'cart_quantity';
                $option_cat_temp['operator'] = 'greater_than_or_equal';
                $option_cat_temp['from'] = $discount_rule_condition->quantity_least;
                $this->form->setConditions($condition_type_cat, $option_cat_temp);
            }
            if (isset($discount_rule_condition->quantity_less)) {
                $option_cat_temp = $option_cat;
                $option_cat_temp['type'] = 'cart_quantity';
                $option_cat_temp['operator'] = 'less_than_or_equal';
                $option_cat_temp['from'] = $discount_rule_condition->quantity_less;
                $this->form->setConditions($condition_type_cat, $option_cat_temp);
            }
        }
    }
    
    /**
     * Process common data
     * */
    protected function processConditions($rule, $type = 'price'){
        if($type == 'price'){
            if($rule->rule_method == "qty_based"){
                $this->processPriceRulesQuantityBasedConditions($rule);
            } else {
                $condition = $this->getData($rule->product_based_condition);
                if($condition->product_quantity_rule == "more"){
                    $operator = 'greater_than_or_equal';
                } elseif($condition->product_quantity_rule == "less"){
                    $operator = 'less_than_or_equal';
                } elseif($condition->product_quantity_rule == "equal"){
                    $operator = 'equal_to';
                } else {
                    $operator = 'in_range';
                }
                $option = array('type' => $condition->product_buy_type, 'product' => $condition->product_to_buy,
                    'operator' => $operator, 'from' => $condition->product_quantity_from, 'to' => $condition->product_quantity_to);
                $this->form->setConditions('cart_item_product_combination', $option);
            }
        } else {
            $this->processCartRulesConditions($rule);
        }
    }
    
    /**
     * Process common data
     * */
    protected function processFilters($price_rule, $type = 'price'){
        if($type == 'price'){
            if($price_rule->rule_method == "qty_based"){
                if(in_array($price_rule->apply_to, array('all_products', 'specific_category', 'specific_attribute'))){
                    if($price_rule->apply_to == 'all_products'){
                        $this->form->setFilter('all_products');
                    } else if($price_rule->apply_to == 'specific_category'){
                        $this->form->setFilter('product_category', 'in_list', $this->getData($price_rule->category_to_apply));
                    } else if($price_rule->apply_to == 'specific_attribute'){
                        $this->form->setFilter('product_attributes','in_list', $this->getData($price_rule->attribute_to_apply));
                    }

                    $product_to_exclude = $this->getData($price_rule->product_to_exclude);
                    if(!empty($product_to_exclude)){
                        $this->form->setFilter('products', 'not_in_list', $product_to_exclude);
                    }
                } else if($price_rule->apply_to == 'specific_products'){
                    $product_to_apply = $this->getData($price_rule->product_to_apply);
                    if(!empty($product_to_apply)){
                        $this->form->setFilter('products', 'in_list', $product_to_apply);
                    }
                }
                if(isset($price_rule->exclude_sale_items)){
                    if($price_rule->exclude_sale_items){
                        $this->form->setFilter('product_on_sale', 'not_in_list');
                    }
                }
            } else {
                $condition = $this->getData($price_rule->product_based_condition);
                if($condition->get_discount_type == "product"){
                    $this->form->setFilter('products', 'in_list', $condition->product_to_apply);
                } else {
                    $this->form->setFilter('product_category', 'in_list', $condition->category_to_apply);
                }
            }
        } else {
            $discount_rule = $this->getData($price_rule->discount_rule);
            $has_no_filters = true;
            foreach ($discount_rule as $condition_type){
                if(isset($condition_type->products_in_list)){
                    $this->form->setFilter('products', 'in_list', $condition_type->products_in_list);
                    $has_no_filters = false;
                }
                if(isset($condition_type->products_not_in_list)){
                    $this->form->setFilter('products', 'not_in_list', $condition_type->products_not_in_list);
                }
                if(isset($condition_type->exclude_sale_products)){
                    $this->form->setFilter('product_on_sale', 'not_in_list');
                }
                if(isset($condition_type->categories_in)){
                    $this->form->setFilter('product_category', 'in_list', $condition_type->categories_in);
                    $has_no_filters = false;
                }
                if(isset($condition_type->atleast_one_including_sub_categories)){
                    $this->form->setFilter('product_category', 'in_list', $condition_type->atleast_one_including_sub_categories);
                    $has_no_filters = false;
                }
                if(isset($condition_type->in_each_category)){
                    $this->form->setFilter('product_category', 'in_list', $condition_type->in_each_category);
                    $has_no_filters = false;
                }
                if(isset($condition_type->exclude_categories)){
                    $this->form->setFilter('product_category', 'not_in_list', $condition_type->exclude_categories);
                }
            }
            if($has_no_filters){
                $this->form->setFilter('all_products');
            }
        }
    }

    /**
     * Get data in right format
     * */
    protected function getData($data){
        if(is_array($data)){
            return $data;
        } else if(is_serialized($data)){
            return unserialize($data);
        } else {
            return json_decode($data);
        }
    }

    /**
     * Process common data
     * */
    protected function processDiscountType($price_rule, $type = 'price'){
        if($type == 'price'){
            if($price_rule->rule_method == 'qty_based'){
                $discount_range = $this->getDiscountRange($price_rule);
                $discount_type = $this->getDiscountTypeFromV1($discount_range);
                $this->form->discount_type = 'wdr_bulk_discount';
                if(in_array($discount_type, array('percentage_discount', 'price_discount', 'fixed_price'))){
                    $this->form->discount_type = 'wdr_bulk_discount';
                } elseif ($discount_type == 'set_discount'){
                    $this->form->discount_type = 'wdr_set_discount';
                } elseif ($discount_type == 'buy_x_get_x'){
                    $this->form->discount_type = 'wdr_buy_x_get_x_discount';
                } elseif ($discount_type == 'buy_x_get_y'){
                    $this->form->discount_type = 'wdr_buy_x_get_y_discount';
                    $this->form->setBuyXGetYOption();
                } elseif ($discount_type == 'more_than_one_cheapest'){
                    $this->form->discount_type = 'wdr_buy_x_get_y_discount';
                    $this->form->setBuyXGetYOption('bxgy_product', 'cheapest');
                }  elseif ($discount_type == 'more_than_one_cheapest_from_cat'){
                    $this->form->discount_type = 'wdr_buy_x_get_y_discount';
                    $this->form->setBuyXGetYOption('bxgy_category', 'cheapest');
                }  else {
                    $this->form->discount_type = 'wdr_buy_x_get_y_discount';
                    $this->form->setBuyXGetYOption('bxgy_all', 'cheapest');
                }
            } else {
                $condition = $this->getData($price_rule->product_based_condition);
                $has_set = false;
                if($condition->get_discount_type == "product"){
                    if(isset($condition->product_to_apply_count_option) && $condition->product_to_apply_count_option == "apply_first"){
                        $this->form->discount_type = 'wdr_set_discount';
                        $has_set = true;
                    }
                }
                if($has_set == false){
                    $this->form->discount_type = 'wdr_simple_discount';
                }
            }
        } else {
            if($price_rule->discount_type == "product_discount"){
                $this->form->discount_type = 'wdr_buy_x_get_y_discount';
            } elseif ($price_rule->discount_type == "shipping_price"){
                $this->form->discount_type = 'wdr_free_shipping';
            } else {
                $this->form->discount_type = 'wdr_cart_discount';
            }
        }
    }

    protected function getDiscountRange($rule){
        if(isset($rule->meta['discount_range'][0])){
            return $this->getData($rule->meta['discount_range'][0]);
        }

        return $rule->discount_range;
    }
    
    protected function getDiscountTypeFromV1($discount_range){
        if(isset($discount_range[0])){
            $discount_type = $discount_range[0]->discount_type;
        } else {
            $discount_type = $discount_range->{0}->discount_type;
        }

        return $discount_type;
    }

    /**
     * Process common data
     * */
    protected function processCommonData($price_rule){
        $this->form->title = $price_rule->post_title;
        $this->form->enabled = ($price_rule->status == 'disable')? 0: 1;
        $this->form->additional = array('condition_relationship' => 'and');
        $this->form->usage_limits = 0;
        $this->form->date_from = isset($price_rule->date_from)? $price_rule->date_from: '';
        $this->form->date_to = isset($price_rule->date_to)? $price_rule->date_to: '';
        if(isset($price_rule->wpml_language)){
            if(!empty($price_rule->wpml_language)){
                $this->form->rule_language = array($price_rule->wpml_language);
            }
        }
    }

    /**
     * Alter query
     * */
    public function filter_where( $where = '', $object = '' ) {
        if(is_object($object) && !empty($object)){
            global $wpdb;
            $query_array = $object->query;
            if(isset($query_array['awdr_last_upgrade_id'])){
                if($query_array['awdr_last_upgrade_id'] > 0){
                    $last_id = intval($query_array['awdr_last_upgrade_id']);
                    $where .= " AND ".$wpdb->posts.".ID > ".$last_id." ";
                }
            }
        }
        return $where;
    }

    /**
     * Get v1 rules
     * @param $post_type string
     * @return mixed
     * */
    public function getV1Rules($post_type = 'woo_discount', $count = 0){
        if($post_type == 'woo_discount'){
            $last_id = $this->getMigrationInfoOf('v1_last_migrated_price_rule_id', 0);
        } else {
            $last_id = $this->getMigrationInfoOf('v1_last_migrated_cart_rule_id', 0);
        }

        $post_args = array('post_type' => $post_type);
        if($count){
            $post_args['numberposts'] = '-1';
        } else {
            $post_args['numberposts'] = $this->migration_count_on_a_set;
        }
        $post_args['orderby'] = 'ID';
        $post_args['order'] = 'ASC';
        $post_args['suppress_filters'] = false;
        $post_args['awdr_last_upgrade_id'] = $last_id;

        add_filter( 'posts_where', array($this, 'filter_where'), 10, 2);
        $posts = get_posts($post_args);
        remove_filter( 'posts_where', array($this, 'filter_where'), 10);
        
        if (!empty($posts) && count($posts) > 0) {
            foreach ($posts as $index => $item) {
                $posts[$index]->meta = get_post_meta($posts[$index]->ID);
            }
        }

        return $posts;
    }
}