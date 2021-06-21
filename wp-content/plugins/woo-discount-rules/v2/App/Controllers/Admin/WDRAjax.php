<?php

namespace Wdr\App\Controllers\Admin;

use WC_Data_Store;
use Wdr\App\Controllers\Base;
use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\ManageDiscount;
use Wdr\App\Controllers\OnSaleShortCode;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Migration;
use Wdr\App\Helpers\Rule;
use Wdr\App\Helpers\Validation;
use Wdr\App\Helpers\Woocommerce;
use Wdr\App\Models\Recipes;
use Wdr\App\Router;

if (!defined('ABSPATH')) exit;

class WDRAjax extends Base
{
    public static $wdr_rules_table;
    public static $manage_discount;
    public $search_result_limit = 20;

    /**
     * WDRAjax constructor.
     */
    public function __construct()
    {
        parent::__construct();
        self::$manage_discount = (isset(self::$manage_discount) && !empty(self::$manage_discount)) ? self::$manage_discount : new ManageDiscount();
        self::$wdr_rules_table = (isset(self::$wdr_rules_table) && !empty(self::$wdr_rules_table)) ? self::$wdr_rules_table : WDR_PLUGIN_PREFIX.'rules';
        $this->search_result_limit = apply_filters('advanced_woo_discount_rules_select_search_limit', $this->search_result_limit);
    }

    protected function frontEndMethods(){
        return array(
            'get_price_html',
            'get_variable_product_bulk_table'
        );
    }

    /**
     * Ajax Controller
     */
    public function wdr_ajax_requests()
    {
        $result = null;
        $method = $this->input->post('method', '');
        if(Helper::hasAdminPrivilege() || in_array($method, $this->frontEndMethods())){
            $method_name = 'wdr_ajax_' . $method;
            if (method_exists($this, $method_name)) {
                $result = $this->$method_name();
            }else{
                $result = __('Authentication required', 'woo-discount-rules');
            }
        } else {
            $result = __('Authentication required', 'woo-discount-rules');
        }
        wp_send_json_success($result);
    }

    /**
     * Process v1 to v2 migration
     * */
    public function wdr_ajax_do_v1_v2_migration(){
        Helper::validateRequest('awdr_v2_migration');
        $status = \Wdr\App\Helpers\Migration::init();
        wp_send_json_success($status);
    }

    public function wdr_ajax_rebuild_onsale_list(){
        Helper::validateRequest('wdr_ajax_rule_build_index');
        $shortcode_manager = new OnSaleShortCode();
        $rules = $this->input->post('rules', array());
        $status = $shortcode_manager->rebuildOnSaleList($rules);
        wp_send_json_success($status);
    }

    /**
     * Process v1 to v2 migration
     * */
    public function wdr_ajax_skip_v1_v2_migration(){
        wp_send_json_success(true);
       /*$migration = new Migration(); //Removed for now
       $migration->updateMigrationInfo(array('skipped_migration' => 1));
       wp_send_json_success(true);*/
    }

    /**
     * Get discount price for a product
     * */
    public function awdr_get_discount_of_a_product(){
        Helper::validateRequest('awdr_ajax_front_end');
        $product_id = $this->input->post('product_id', '');
        $product_id = intval($product_id);
        $result = false;
        if($product_id){
            $quantity = $this->input->post('qty', '');
            $quantity = intval($quantity);
            $product = Woocommerce::getProduct($product_id);
            if($product){
                $price = Woocommerce::getProductPrice($product);
                $custom_price = $this->input->post('custom_price', '');
                if(function_exists('wc_get_price_thousand_separator')){
                    $price_thousand_separator = wc_get_price_thousand_separator();
                    $custom_price = str_replace($price_thousand_separator, "", $custom_price);
                }
                $custom_price = floatval($custom_price);
                $result = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', $price, $product, $quantity, $custom_price, 'all', true);
                if(!empty($result)){
                    $result = Helper::formatAllPrices($result);
                }
            }
        }

        wp_send_json_success($result);
    }

    /**
     * search Product
     * @return array
     * @throws \Exception
     */
    public function wdr_ajax_products()
    {
        Helper::validateRequest('wdr_ajax_select2');
        $query = $this->input->post('query', '');
        //to disable other search classes
        remove_all_filters('woocommerce_data_stores');
        $data_store = WC_Data_Store::load('product');
        $ids = $data_store->search_products($query, '', true, false, $this->search_result_limit);
            return array_values(array_map( function ( $post_id ) {
                return array(
                    'id'   => (string) $post_id,
                    'text' => '#' . $post_id . ' ' . get_the_title( $post_id ),
                );
            }, array_filter( $ids ) ));
    }

    /**
     * search product category
     * @return array
     */
    public function wdr_ajax_product_category()
    {
        Helper::validateRequest('wdr_ajax_select2');
        $taxonomy = apply_filters('advanced_woo_discount_rules_category_taxonomies', array('product_cat'));
        if(!is_array($taxonomy)){
            $taxonomy = array('product_cat');
        }
        //For loading all language categories in select box.
        global $sitepress;
        if(!empty($sitepress)){
            remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ), 10 );
            remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
            remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10 );
        }
        $query = $this->input->post('query', '');
        $terms = get_terms(array('taxonomy' => $taxonomy, 'name__like' => $query, 'hide_empty' => false, 'number' => $this->search_result_limit));

        return array_map(function ($term) {
            $parant_name = '';
            if(!empty($term->parent)){
                if (function_exists('get_the_category_by_ID')) {
                    $parant_names = get_the_category_by_ID((int)$term->parent);
                    $parant_name = $parant_names . ' -> ';
                    $parant_category = get_term( (int)$term->parent );
                    if(is_object($parant_category) && !empty($parant_category->parent)){
                        $grant_parant_names = get_the_category_by_ID((int)$parant_category->parent);
                        $parant_name = $grant_parant_names . ' -> '.$parant_names .  ' -> ';
                        $grant_parant_category = get_term( (int)$parant_category->parent );
                        if(is_object($grant_parant_category) && !empty($grant_parant_category->parent)){
                            $grant_grant_parant_names = get_the_category_by_ID((int)$grant_parant_category->parent);
                            $parant_name = $grant_grant_parant_names. ' -> '.$grant_parant_names . ' -> '.$parant_names . ' -> ';
                        }
                    }

                }
            }
            return array(
                'id' => (string)$term->term_id,
                'text' => $parant_name.$term->name,
            );
        }, $terms);
    }

    /**
     * search product tags
     * @return array
     */
    public function wdr_ajax_product_tags()
    {
        Helper::validateRequest('wdr_ajax_select2');
        $query = $this->input->post('query', '');
        $terms = get_terms(array('taxonomy' => 'product_tag', 'name__like' => $query, 'hide_empty' => false, 'number' => $this->search_result_limit));
        return array_map(function ($term) {
            return array(
                'id' => (string)$term->term_id,
                'text' => $term->name,
            );
        }, $terms);
    }

    /**
     * search taxonomies product
     * @return array
     */
    public function wdr_ajax_product_taxonomies()
    {
        Helper::validateRequest('wdr_ajax_select2');
        $query = $this->input->post('query', '');
        $taxonomy_name = $this->input->post('taxonomy', '');
        $terms = get_terms(array('taxonomy' => $taxonomy_name,
            'name__like' => $query,
            'hide_empty' => false,
            'number' => $this->search_result_limit,
        ));

        return array_map(function ($term) {
            $parant_name = '';
            if(!empty($term->parent)){
                if (function_exists('get_the_category_by_ID')) {
                    $parant_names = get_the_category_by_ID((int)$term->parent);
                    $parant_name = $parant_names . ' -> ';
                }
            }
            return array(
                'id' => (string)$term->term_id,
                'text' => $parant_name.$term->name,
            );
        }, $terms);
    }

    /**
     * search product sku
     * @return array
     */
    public function wdr_ajax_product_sku()
    {
        Helper::validateRequest('wdr_ajax_select2');
        global $wpdb;
        $query = $this->input->post('query', '');
        $query = Helper::filterSelect2SearchQuery($query);
        $results = $wpdb->get_results("
			SELECT DISTINCT meta_value
			FROM $wpdb->postmeta
			WHERE meta_key = '_sku' AND meta_value  like '%$query%'
		");

        return array_map(function ($result) {
            $p_title = '';
            if(function_exists('wc_get_product_id_by_sku') && function_exists('get_the_title')  ){
                $p_id = wc_get_product_id_by_sku( $result->meta_value );
                if( $p_id > 0){
                    $p_title = $p_id.': '.get_the_title( $p_id );
                    if(!empty($p_title)){
                        $p_title =  'SKU: ' . $result->meta_value . ' ( ' . $p_title . ' )';
                    }
                }
            }
           if(empty( $p_title )){
                $p_title =  'SKU: ' . $result->meta_value;
           }
            return array(
                'id' => (string)$result->meta_value,
                'text' => $p_title,
            );
        }, $results);
    }

    /**
     * search product attributes
     * @return array
     */
    public function wdr_ajax_product_attributes()
    {
        Helper::validateRequest('wdr_ajax_select2');

        global $wc_product_attributes, $wpdb;
        //return $wc_product_attributes;
        $query = $this->input->post('query', '');
        $query = Helper::filterSelect2SearchQuery($query);
        $taxonomies = array_map(function ($item) {
            return "'$item'";
        }, array_keys($wc_product_attributes));
        $taxonomies = implode(', ', $taxonomies);
        $items = $wpdb->get_results("
			SELECT $wpdb->terms.term_id, $wpdb->terms.name, taxonomy
			FROM $wpdb->term_taxonomy INNER JOIN $wpdb->terms USING (term_id)
			WHERE taxonomy in ($taxonomies)
			AND $wpdb->terms.name  like '%$query%' 
		");
        return array_map(function ($term) use ($wc_product_attributes) {
            $attribute = $wc_product_attributes[$term->taxonomy]->attribute_label;
            return array(
                'id' => (string)$term->term_id,
                'text' => $attribute . ': ' . $term->name,
            );
        }, $items);
    }

    /**
     * search user list
     * @return array
     */
    public function wdr_ajax_users_list()
    {
        Helper::validateRequest('wdr_ajax_select2');
        $query = $this->input->post('query', '');
        $query = "*$query*";
        $users = get_users(array('fields' => array('ID', 'user_nicename'), 'search' => $query, 'orderby' => 'user_nicename'));
        return array_map(function ($user) {
            return array(
                'id' => (string)$user->ID,
                'text' => $user->user_nicename,
            );
        }, $users);
    }

    /**
     * search coupon
     * @return array
     */
    public function wdr_ajax_cart_coupon()
    {
        Helper::validateRequest('wdr_ajax_select2');
        $posts_raw = get_posts(array(
            'posts_per_page' => '-1',
            'post_type' => 'shop_coupon',
            'post_status' => array('publish'),
            'fields' => 'ids',
        ));
        $items = array_map(function ($post_id) {
            $code = get_the_title($post_id);
            return array(
                'id' => strtolower($code),
                'text' => $code
            );
        }, $posts_raw);
        $query = $this->input->post('query');
        if (!empty($query)) {
            $items = array_filter($items, function ($item) use ($query) {
                return stripos($item['text'], $query) !== FALSE;
            });
        }
        return array_values($items);
    }

    /**
     * save settings
     * @return array
     */
    public function wdr_ajax_save_configuration()
    {
        Helper::validateRequest('wdr_ajax_save_configuration');
        $save_alert = $this->input->post('customizer_save_alert', 0) ;
        if($save_alert == "1"){
            $save_alert = "alert_in_popup";
        } else {
            $save_alert = "alert_in_normal";
        }
        if(!Validation::validateSettingsTabFields($_POST)){
            return array('result' => false, 'save_popup' => $save_alert, 'security_pass' => 'fails' );
        }
        $save_config = $this->input->post();
        $save_config['modify_price_at_shop_page'] = $this->input->post('modify_price_at_shop_page', 0);
        $save_config['modify_price_at_product_page'] = $this->input->post('modify_price_at_product_page', 0);
        $save_config['modify_price_at_category_page'] = $this->input->post('modify_price_at_category_page', 0);
        $save_config['customize_bulk_table_title'] = $this->input->post('customize_bulk_table_title', 0);
        $save_config['customize_bulk_table_discount'] = $this->input->post('customize_bulk_table_discount', 1);
        $save_config['customize_bulk_table_range'] = $this->input->post('customize_bulk_table_range', 2);
        $save_config['table_title_column'] = $this->input->post('table_title_column', 0);
        $save_config['table_discount_column'] = $this->input->post('table_discount_column', 0);
        $save_config['table_range_column'] = $this->input->post('table_range_column', 0);
        $save_config['licence_key'] = $this->input->post('licence_key', '');
        $save_config['on_sale_badge_html'] = (isset($_POST['on_sale_badge_html'])) ? stripslashes($_POST['on_sale_badge_html']) : '';
        $save_config['on_sale_badge_html'] = Rule::validateHtmlBeforeSave($_POST['on_sale_badge_html']);
        $save_config['you_saved_text'] = Rule::validateHtmlBeforeSave($this->input->post('you_saved_text'));
        $save_config['applied_rule_message'] = Rule::validateHtmlBeforeSave($this->input->post('applied_rule_message'));
        $save_config['discount_label_for_combined_discounts'] = Rule::validateHtmlBeforeSave($this->input->post('discount_label_for_combined_discounts'));
        $save_config['free_shipping_title'] = Rule::validateHtmlBeforeSave($this->input->post('free_shipping_title'));
        return array('result' => Configuration::saveConfig(configuration::DEFAULT_OPTION, $save_config), 'save_popup' => $save_alert, 'security_pass' => 'passed');
    }

    /**
     * save Advanced option
     * @return array
     */
    public function wdr_ajax_save_advanced_option()
    {
        Helper::validateRequest('wdr_ajax_save_advanced_option_config');
        if(!Validation::validateAdvancedOptionKey($_POST)){
            return array('result' => false, 'security_pass' => 'fails' );
        }
        $advanced_option_config = $this->input->post();
        return array('result' => Configuration::saveConfig(configuration::ADVANCED_OPTION, $advanced_option_config), 'security_pass' => 'passed');
    }

    /**
     * save rules
     */
    public function wdr_ajax_save_rule()
    {
        Helper::validateRequest('wdr_ajax_save_rule');
        $validation_result = Validation::validateRules($_POST);
        if ($validation_result === true) {
            $post = $this->input->post();
            $rule_helper = new Rule();
            $post['title'] = (isset($_POST['title'])) ? stripslashes(sanitize_text_field($_POST['title'])) : '';
            $post['product_adjustments']['cart_label'] = (isset($_POST['product_adjustments']) && isset($_POST['product_adjustments']['cart_label'])) ? stripslashes(sanitize_text_field($_POST['product_adjustments']['cart_label'])) : '';
            $post['bulk_adjustments']['cart_label'] = (isset($_POST['bulk_adjustments']) && isset($_POST['bulk_adjustments']['cart_label'])) ? stripslashes(sanitize_text_field($_POST['bulk_adjustments']['cart_label'])) : '';
            $post['set_adjustments']['cart_label'] = (isset($_POST['set_adjustments']) && isset($_POST['set_adjustments']['cart_label'])) ? stripslashes(sanitize_text_field($_POST['set_adjustments']['cart_label'])) : '';
            $rule_id = $rule_helper->save($post);
            if (isset($rule_id['coupon_exists'])) {
                $coupon_message = $rule_id['coupon_exists'];
                wp_send_json_error(array('coupon_message' => $coupon_message));
                die;
            }
            $redirect_url = false;
            if (!empty($this->input->post('wdr_save_close', ''))) {
                $redirect_url = admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules')));
            } elseif (empty($this->input->post('edit_rule', ''))) {
                $redirect_url = admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules', 'task' => 'view', 'id' => $rule_id)));
            }
            $build_index = array();
            if ($rule_id) {
                $build_index = OnSaleShortCode::getOnPageReBuildOption($rule_id);
            }
            wp_send_json_success(array('rule_id' => $rule_id, 'redirect' => $redirect_url, 'build_index' => $build_index));
        } else {
            wp_send_json_error($validation_result);
        }
    }

    /**
     * Delete rule
     */
    public function wdr_ajax_delete_rule()
    {
        global $wpdb;
        $deleted = 'failed';
        $row_id = $this->input->post('rowid', '');
        $row_id = intval($row_id);
        if (!empty($row_id)) {
            Helper::validateRequest('wdr_ajax_delete_rule'.$row_id);
            $deleted = $wpdb->update($wpdb->prefix . self::$wdr_rules_table,
                array(
                    'deleted' => 1
                ),
                array(
                    'id' => $row_id
                ),
                array(
                    '%d'
                ),
                array(
                    '%d'
                )
            );
        }
        wp_send_json($deleted);
    }

    /**
     * Duplicate rule
     */
    public function wdr_ajax_duplicate_rule()
    {
        global $wpdb;
        $duplicated_id = 'failed';
        $row_id = $this->input->post('rowid', '');
        $row_id = intval($row_id);
        if (!empty($row_id)) {
            Helper::validateRequest('wdr_ajax_duplicate_rule'.$row_id);
            $rule_title = $wpdb->get_row("SELECT title FROM " . $wpdb->prefix . self::$wdr_rules_table . " WHERE id=" . $row_id);
            $rule_priority = $wpdb->get_row("SELECT max(priority) as priority FROM " . $wpdb->prefix . self::$wdr_rules_table);
            $priority = 1;
            if (!empty($rule_priority) && $rule_priority->priority) {
                $priority = intval($rule_priority->priority) + 1;
            }
            $rule_title = !empty($rule_title) && isset($rule_title->title) ? $rule_title->title : '';
            $rule_title = addslashes($rule_title);
            $sql = "INSERT INTO " . $wpdb->prefix . self::$wdr_rules_table . " (enabled, exclusive, title, priority, filters, conditions, product_adjustments, cart_adjustments, buy_x_get_x_adjustments, buy_x_get_y_adjustments, bulk_adjustments, set_adjustments, other_discounts, date_from, date_to, usage_limits, rule_language, additional, max_discount_sum, advanced_discount_message, discount_type, used_coupons ) 
                    SELECT 0, exclusive, '" . $rule_title . " - copy'," . $priority . ", filters, conditions, product_adjustments, cart_adjustments, buy_x_get_x_adjustments, buy_x_get_y_adjustments, bulk_adjustments, set_adjustments, other_discounts, date_from, date_to, usage_limits, rule_language,  additional, max_discount_sum, advanced_discount_message, discount_type, used_coupons   
                    FROM " . $wpdb->prefix . self::$wdr_rules_table . " 
                    WHERE id = " . $row_id;
            $wpdb->query($sql);
            $duplicated_id = $wpdb->insert_id;
        }
        wp_send_json($duplicated_id);
    }

    /**
     * Disable rule
     */
    public function wdr_ajax_manage_status()
    {
        global $wpdb;
        $rule_status = 'failed';
        $row_id = $this->input->post('rowid', '');
        $row_id = intval($row_id);
        $status = $this->input->post('changeto', 0);
        $status = intval($status);
        if (!empty($row_id) && ($status == 1 || $status == 0)) {
            Helper::validateRequest('wdr_ajax_manage_status'.$row_id);
            $rule_status = $wpdb->update($wpdb->prefix . self::$wdr_rules_table,
                array(
                    'enabled' => $status
                ),
                array(
                    'id' => $row_id
                ),
                array(
                    '%d'
                ),
                array(
                    '%d'
                )
            );
        }
        wp_send_json($rule_status);
    }

    /**
     * bulk action
     * @return bool|mixed
     */
    public function wdr_ajax_bulk_action()
    {
        global $wpdb;
        $action_type = $this->input->post('wdr_bulk_action', '');
        $saved_rules = $this->input->post('saved_rules', '');
        Helper::validateRequest('awdr_ajax_rule_bulk_actions');
        if ($action_type == 'enable') {
            if (!empty($saved_rules) && is_array($saved_rules)) {
                foreach ($saved_rules as $saved_rule_id) {
                    $wpdb->update($wpdb->prefix . self::$wdr_rules_table,
                        array(
                            'enabled' => 1
                        ),
                        array(
                            'id' => intval($saved_rule_id)
                        ),
                        array(
                            '%d'
                        ),
                        array(
                            '%d'
                        )
                    );
                }
            }
            wp_send_json(
                array(
                    'delete' => '',
                    'disable' => '',
                    'enable' => 'enabled'
                )
            );
        } elseif ($action_type == 'disable') {
            if (!empty($saved_rules) && is_array($saved_rules)) {
                foreach ($saved_rules as $saved_rule_id) {
                    $wpdb->update($wpdb->prefix . self::$wdr_rules_table,
                        array(
                            'enabled' => 0
                        ),
                        array(
                            'id' => intval($saved_rule_id)
                        ),
                        array(
                            '%d'
                        ),
                        array(
                            '%d'
                        )
                    );
                }
            }
            wp_send_json(
                array(
                    'delete' => '',
                    'disable' => 'disabled',
                    'enable' => ''
                )
            );
        } elseif ($action_type == 'delete') {
            if (!empty($saved_rules) && is_array($saved_rules)) {
                foreach ($saved_rules as $saved_rule_id) {
                    $wpdb->update($wpdb->prefix . self::$wdr_rules_table,
                        array(
                            'deleted' => 1
                        ),
                        array(
                            'id' => intval($saved_rule_id)
                        ),
                        array(
                            '%d'
                        ),
                        array(
                            '%d'
                        )
                    );
                }
                wp_send_json(
                    array(
                        'delete' => 'deleted',
                        'disable' => '',
                        'enable' => ''
                    )
                );
            }
        } else {
            return false;
        }
        return $this->input->post();
    }

    /**
     * drag & drop priority
     */
    public function wdr_ajax_update_priority_order()
    {
        Helper::validateRequest('awdr_rule_list');
        global $wpdb;
        $new_priority_order = $this->input->post('position', '');
        $priority = 1;
        $priority_updated = false;
        foreach ($new_priority_order as $key => $value) {
            $priority_updated = $wpdb->update($wpdb->prefix . self::$wdr_rules_table,
                array(
                    'priority' => $priority
                ),
                array(
                    'id' => intval($value)
                ),
                array(
                    '%d'
                ),
                array(
                    '%d'
                )
            );
            $priority++;
        }
        wp_send_json($priority_updated);
    }

    /**
     * Update discounted price when update quantity in product page
     */
    public function wdr_ajax_get_price_html()
    {
        Helper::validateRequest('awdr_ajax_front_end');
        $product = $this->input->post('product_id', '');
        $product = intval($product);
        $product_qty = $this->input->post('qty', '');
        $product_qty = intval($product_qty);
        $price_html = $original_html = '';
        if($product){
            $product = self::$woocommerce_helper->getProduct($product);
            if($product){
                $price_html = "<div class='price'></div>";
                $price_html = self::$manage_discount->getPriceHtml($price_html, $product, $product_qty, true);
                remove_filter('woocommerce_get_price_html', array(Router::$manage_discount, 'getPriceHtml'), 100);
                $original_html = self::$woocommerce_helper->getPriceHtml($product);
            }
        }

        wp_send_json(array('price_html'=>$price_html, 'original_price_html' => $original_html));
    }

    /**
     * get variable product bulk table
     */
    public function wdr_ajax_get_variable_product_bulk_table(){
        $product_id = $this->input->post('product_id', '');
        Helper::validateRequest('awdr_ajax_front_end');
        $product_id = intval($product_id);
        if($product_id){
            $product = self::$woocommerce_helper->getProduct($product_id);
            $bulk_table = self::$manage_discount->showBulkTableInPositionManually($product, $get_variable_product_table = true);
            wp_send_json(array('bulk_table' => $bulk_table));
        }else{
            wp_send_json_error();
        }

    }

    /**
     * @return get state select box
     */
    public function wdr_ajax_get_state_details(){
        Helper::validateRequest('wdr_ajax_select2');
        $validation_result = Validation::validateStateCountryCondition($_POST);
        if (!$validation_result) {
            wp_send_json_error();
        }
        $state_options = '';
        $selected_countries = $this->input->post('selected_country', '');
        $selected_state = $this->input->post('selected_state', '');
        $selected_index = $this->input->post('selected_index', '');
        $getStatesList = self::$woocommerce_helper->getStatesList();
        $state_options .= '<select multiple
                   class="get_awdr_shipping_state append-preloaded-values edit-preloaded-values"
                   data-list="states"
                   data-field="preloaded"
                   data-placeholder="'. __('Search State', 'woo-discount-rules') .'"
                   name="conditions['.$selected_index.'][options][value][]">';
        if(!empty($selected_countries)){
            foreach ($selected_countries as $country) {
                if(isset($getStatesList[$country])){
                    $states = $getStatesList[$country];
                    foreach ($states as $id => $text) {
                        if( is_array($selected_state) && !empty($selected_state) && in_array($id, $selected_state)){
                            $state_options .= "<option value={$id} selected>{$text}</option>";
                        }else{
                            $state_options .= "<option value={$id}>{$text}</option>";
                        }

                    }
                }
            }
        }
        $state_options .= "</select>";
        return $state_options;
    }

    function wdr_ajax_create_rule_recipe()
    {
        Helper::validateRequest('common_recipe_nonce');
        $recipes_object = new Recipes();
        $recipes_data = $recipes_object->recipeDetails();
        $awdr_recipe_type = isset($_POST['awdr_recipe_type']) ? $_POST['awdr_recipe_type'] : array();
        $recipes_data_keys = array_keys($recipes_data);
        if(in_array($awdr_recipe_type, $recipes_data_keys)){
            $rule_data = $recipes_data[$awdr_recipe_type];
            $rule_id = $recipes_object->save($rule_data);
            if (!empty($rule_id)) {
                $redirect_url = admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules', 'task' => 'view', 'id' => $rule_id)));
            } else {
                $redirect_url = '';
            }

            wp_send_json_success(array('rule_id' => $rule_id, 'redirect' => $redirect_url, 'success' => true));
        } else {
            wp_send_json_error(array('message' => __('Invalid request', 'woo-discount-rules'), 'success' => false));
        }
    }
}