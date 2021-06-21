<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $woocommerce;

/**
 * Class FlycartWooDiscountBase
 */
if (!class_exists('FlycartWooDiscountBase')) {
    class FlycartWooDiscountBase
    {
        /**
         * @var string
         */
        public $default_page = 'pricing-rules';

        /**
         * @var string
         */
        public $default_option = 'woo-discount-config';
        public $taxonomy_option = 'woo-discount-taxonomy';

        /**
         * @var array
         */
        private $instance = array();

        public $has_free_shipping = 0;
        public $has_free_shipping_rule = 0;

        protected static $self_instance;

        /**
         * FlycartWooDiscountBase constructor.
         */
        public function __construct() {}

        /**
         * Singleton Instance maker.
         *
         * @param $name
         * @return bool
         */
        public function getInstance($name)
        {
            if (!isset($this->instance[$name])) {
                if (class_exists($name)) {
                    $this->instance[$name] = new $name;
                    $instance = $this->instance[$name];
                } else {
                    $instance = false;
                }
            } else {
                $instance = $this->instance[$name];
            }
            return $instance;
        }

        public static function get_instance(){
            if (empty(self::$self_instance))
            {
                self::$self_instance = new self();
            }
            return self::$self_instance;
        }

        /**
         * Managing discount of Price and Cart.
         */
        public function handleDiscount()
        {
            global $woocommerce;

            $price_discount = $this->getInstance('FlycartWooDiscountRulesPricingRules');
            $cart_discount = $this->getInstance('FlycartWooDiscountRulesCartRules');

            $price_discount->analyse($woocommerce);
            $cart_discount->analyse($woocommerce);
        }

        /**
         * Managing discount of Cart.
         */
        public function handleCartDiscount($free_shipping_check = 0)
        {
            global $woocommerce;
            $cart_discount = $this->getInstance('FlycartWooDiscountRulesCartRules');
            $cart_discount->analyse($woocommerce, $free_shipping_check);
            $this->has_free_shipping_rule = $cart_discount->has_free_shipping_rule;
            if($free_shipping_check){
                $this->has_free_shipping = $cart_discount->has_free_shipping;
            }
        }

        /**
         * Managing discount of Price.
         */
        public function handlePriceDiscount()
        {
            global $woocommerce;
            $price_discount = $this->getInstance('FlycartWooDiscountRulesPricingRules');
            $price_discount->analyse($woocommerce);
        }

        /**
         * For adding script in checkout page
         * */
        public function addScriptInCheckoutPage(){
            $additional_class_to_add = '';
            $additional_class = apply_filters('woo_discount_rules_add_additional_class_to_refresh_the_checkout_review_on_blur', $additional_class_to_add);
            if(!empty($additional_class) && is_string($additional_class)){
                $additional_class_to_add = $additional_class;
            }
            $on_blur_event_for_items  = apply_filters('woo_discount_rules_class_to_refresh_the_checkout_review_on_blur', 'input#billing_email, select#billing_state');
            $script = '<script type="text/javascript">
                    jQuery( function( $ ) {
                        $(document).ready(function() {
                            $( document.body ).on( "blur", "'.$on_blur_event_for_items.$additional_class_to_add.'", function() {
                                $(document.body).trigger("update_checkout");
                            });
                        }); 
                    });
                </script>';
            echo $script;
        }

        /**
         * WooCommerce hook to change the name of a product.
         *
         * @param $title
         * @return mixed
         */
        public function modifyName($title)
        {
            //
            return $title;
        }

        /**
         * Finally, on triggering the "Thank You" hook by WooCommerce,
         * Overall session data's are stored to the order's meta as "woo_discount_log".
         *
         * @param integer $order_id Order ID.
         */
        public function storeLog($order_id)
        {
            if (function_exists('WC')) {
                if (!empty(WC()->session)) {
                    if (method_exists(WC()->session, 'get')) {
                        $log['price_discount'] = WC()->session->get('woo_price_discount', array());
                        $log['cart_discount'] = WC()->session->get('woo_cart_discount', array());

                        add_post_meta($order_id, 'woo_discount_log', json_encode($log), 1);

                        // Reset the Coupon Status.
                        WC()->session->set('woo_coupon_removed', '');
                    }
                }
            }
        }

        /**
         * Create New Menu On WooCommerce.
         */
        public function adminMenu()
        {
            if (!is_admin()) return;

            global $submenu;
            if (isset($submenu['woocommerce'])) {
                add_submenu_page(
                    'woocommerce',
                    'Woo Discount Rules',
                    'Woo Discount Rules',
                    'edit_posts',
                    'woo_discount_rules',
                    array($this, 'viewManager')
                );
            }
        }

        /**
         * Update the Status of the Rule Set.
         */
        public function updateStatus()
        {
            $postData = \FlycartInput\FInput::getInstance();
            $id = $postData->get('id', false);
            $wdr_nonce = $postData->get('wdr_nonce', false);
            if(!empty($wdr_nonce)){
                if(FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if(FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_rule_listing', $wdr_nonce)){
                        if ($id) {
                            $status = get_post_meta($id, 'status', false);
                            if (isset($status[0])) {
                                $state = ($status[0] == 'publish') ? 'disable' : 'publish';
                                update_post_meta($id, 'status', $state);
                            } else {
                                add_post_meta($id, 'status', 'disable');
                                $state = 'disable';
                            }
                            $meta = get_post_meta($id, '', true);
                            $status_string = '';
                            if(!empty($meta)){
                                if(!isset($meta['status'][0])) $meta['status'][0] = 'disable';
                                if($meta['status'][0] == 'publish'){
                                    $status_string .= "<span class='wdr_status_active_text alert alert-success'>".esc_html__('Active', 'woo-discount-rules')."</span>";
                                } else {
                                    $status_string .= "<span class='wdr_status_disabled_text alert alert-danger'>".esc_html__('Disabled', 'woo-discount-rules')."</span>";
                                }
                                if($meta['status'][0] == 'publish'){
                                    $date_from = (isset($meta['date_from'][0]) ? $meta['date_from'][0] : false);
                                    $date_to = (isset($meta['date_to'][0]) ? $meta['date_to'][0] : false);
                                    $validate_date_string = FlycartWooDiscountRulesGeneralHelper::validateDateAndTimeWarningTextForListingHTML($date_from, $date_to);
                                    $status_string .= $validate_date_string;
                                }
                            }
                            $return_value = array('status' => ucfirst($state), 'status_html' => $status_string);

                            echo json_encode($return_value);
                        }
                    }
                }
            }
            die();
        }

        /**
         * Remove the Rule Set.
         */
        public function removeRule()
        {
            $postData = \FlycartInput\FInput::getInstance();
            $id = $postData->get('id', false);
            $wdr_nonce = $postData->get('wdr_nonce', false);
            if(!empty($wdr_nonce)) {
                if (FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if (FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_rule_listing', $wdr_nonce)) {
                        if ($id) {
                            try {
                                $id = intval($id);
                                if (!$id) return false;
                                wp_delete_post($id);
                            } catch (Exception $e) {
                                //
                            }
                        }
                    }
                }
            }
            die();
        }
//    -------------------------------------- PRICE RULES ---------------------------------------------------------------
        /**
         * Saving the Price Rule.
         *
         * @return bool
         */
        public function savePriceRule()
        {
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            $params = array();
            if (!isset($request['data'])) return false;
            parse_str($request['data'], $params);
            if(isset($params['wdr_nonce']) && !empty($params['wdr_nonce'])){
                if(FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if(FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_save_price_rule', $params['wdr_nonce'])){
                        $pricing_rule = $this->getInstance('FlycartWooDiscountRulesPricingRules');
                        $pricing_rule->save($params);
                    }
                }
            }
            die();
        }

//    -------------------------------------- CART RULES ----------------------------------------------------------------
        /**
         * Saving the Cart Rule.
         *
         * @return bool
         */
        public function saveCartRule()
        {

            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            $params = array();
            if (!isset($request['data'])) return false;
            parse_str($request['data'], $params);
            if(isset($params['wdr_nonce']) && !empty($params['wdr_nonce'])){
                if(FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if(FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_save_cart_rule', $params['wdr_nonce'])){
                        $this->parseFormWithRules($params, true);
                        $pricing_rule = $this->getInstance('FlycartWooDiscountRulesCartRules');
                        $pricing_rule->save($params);
                    }
                }
            }
            die();
        }

        /**
         * load product select box
         *
         * @return bool
         */
        public function loadProductSelectBox() {
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            if(isset($request['wdr_nonce']) && !empty($request['wdr_nonce'])) {
                if (FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if (FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_ajax_search_field', $request['wdr_nonce'])) {
                        if (!isset($request['name'])) return false;
                        echo FlycartWoocommerceProduct::getProductAjaxSelectBox(array(), $request['name']);
                    }
                }
            }
            die();
        }

        /**
         * load coupons
         *
         * @return bool
         */
        public function loadCoupons() {
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            $posts = array();
            if(isset($request['wdr_nonce']) && !empty($request['wdr_nonce'])){
                if(FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if(FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_ajax_search_field', $request['wdr_nonce'])){
                        $page = isset($request['page'])? $request['page']: 1;
                        $query = isset($request['q'])? $request['q']: '';
                        $args = array( 'posts_per_page' => 30, 's' => $query,
                            'paged' => $page, 'post_type' => 'shop_coupon' );
                        $posts = get_posts($args);
                        if(!empty($posts) && count($posts) > 0){
                            foreach ($posts as $post){
                                if(function_exists('wc_strtolower')){
                                    $coupon_name = wc_strtolower($post->post_title);
                                } else {
                                    $coupon_name = strtolower($post->post_title);
                                }
                                $post->post_name = $coupon_name;
                            }
                        }
                    }
                }
            }

            echo json_encode($posts);
            die();
        }

        /**
         * Get coupon options
         *
         * @param array $selected
         * @return string
         * */
        public static function loadSelectedCouponOptions($selected){
            $html_options = '';
            if(!empty($selected) && is_array($selected)){
//                $args = array( 'numberposts' => '-1', 'post_type' => 'shop_coupon', 'post_name__in' => $selected);
//                $posts = get_posts($args);
                $in_data = "'".implode("','", $selected)."'";
                global $wpdb;
                $query = "SELECT `post_title`, `post_name` FROM $wpdb->posts WHERE `post_type` = 'shop_coupon' AND `post_status` <> 'trash' AND `post_title` IN (".$in_data.")";
                $posts = $wpdb->get_results($query);
                if(!empty($posts)){
                    if(count($posts)){
                        foreach ($posts as $post){
                            if(function_exists('wc_strtolower')){
                                $coupon_name = wc_strtolower($post->post_title);
                            } else {
                                $coupon_name = strtolower($post->post_title);
                            }
                            $html_options .= '<option value="'.$coupon_name.'" selected="selected">'.$post->post_title.'</option>';
                        }
                    }
                }
            }

            return $html_options;
        }

        /**
         * Get coupon count
         *
         * @return int
         * */
        public static function getWooCommerceCouponCount(){
            $count = wp_count_posts( 'shop_coupon' );
            $coupon_count = 0;
            if(!empty($count) && isset($count->publish)){
                if(!empty($count->publish)){
                    $coupon_count = $count->publish;
                }
            }

            return $coupon_count;
        }

        /**
         * Has large number of coupons
         * */
        public static function hasLargeNumberOfCoupon(){
            $load_coupon_by_search = apply_filters('woo_discount_rules_load_coupon_value_by_search', false);
            if($load_coupon_by_search){
                return true;
            } else {
                $count = self::getWooCommerceCouponCount();
                if($count > 50) return true;
                return false;
            }
        }

        /**
         * Making the reliable end data to store.
         *
         * @param $cart_rules
         * @param bool $isCartRules
         */
        public function parseFormWithRules(&$cart_rules, $isCartRules = false)
        {
            $cart_rules['discount_rule'] = $this->generateFormData($cart_rules, $isCartRules);
        }

        /**
         * @param $cart_rules
         * @param bool $isCartRules
         * @return array
         */
        public function generateFormData($cart_rules, $isCartRules = false)
        {
            $link = $this->fieldLink();

            $discount_list = array();
            // Here, Eliminating the Cart's rule with duplicates.
            $discount_rule = (isset($cart_rules['discount_rule']) ? $cart_rules['discount_rule'] : array());
            if ($isCartRules) {
                if(is_array($discount_rule) && count($discount_rule)){
                    foreach ($discount_rule as $index => $value) {

                        // The Type of Option should get value from it's native index.
                        // $link[$value['type']] will gives the native index of the "type"

                        if (isset($link[$value['type']])) {
                            if(is_array($link[$value['type']])){
                                foreach ($link[$value['type']] as $fields){
                                    $discount_list[$index][$value['type']][$fields] = $value[$fields];
                                }
                            } else if (isset($value[$link[$value['type']]])) {
                                $discount_list[$index][$value['type']] = $value[$link[$value['type']]];
                            }
                        } else {
                            $discount_list[$index][$value['type']] = $value['option_value'];
                        }
                    }
                }
            }

            return $discount_list;

        }

        /**
         * @return array
         */
        public function fieldLink()
        {
            // TODO: Check Subtotal Link
            return array(
                'products_atleast_one' => 'product_to_apply',
                'products_not_in' => 'product_to_apply',

                'categories_atleast_one' => 'category_to_apply',
                'exclude_categories' => 'category_to_apply',
                'categories_in' => 'category_to_apply',
                'in_each_category' => 'category_to_apply',
                'atleast_one_including_sub_categories' => 'category_to_apply',

                'coupon_applied_any_one' => 'coupon_to_apply',
                'coupon_applied_all_selected' => 'coupon_to_apply',

                'products_in_list' => 'products',
                'products_not_in_list' => 'products',

                'users_in' => 'users_to_apply',
                'roles_in' => 'user_roles_to_apply',
                'shipping_countries_in' => 'countries_to_apply',
                'customer_based_on_purchase_history' => array('purchase_history_order_status', 'purchased_history_amount', 'purchased_history_type', 'purchased_history_duration', 'purchased_history_duration_days'),
                'customer_based_on_purchase_history_order_count' => array('purchase_history_order_status', 'purchased_history_amount', 'purchased_history_type', 'purchased_history_duration', 'purchased_history_duration_days'),
                'customer_based_on_purchase_history_product_order_count' => array('purchase_history_order_status', 'purchased_history_amount', 'purchased_history_type', 'purchase_history_products', 'purchased_history_duration', 'purchased_history_duration_days'),
                'customer_based_on_purchase_history_product_quantity_count' => array('purchase_history_order_status', 'purchased_history_amount', 'purchased_history_type', 'purchase_history_products', 'purchased_history_duration', 'purchased_history_duration_days'),
            );
        }

        // ----------------------------------------- CART RULES END --------------------------------------------------------


        // -------------------------------------------SETTINGS--------------------------------------------------------------

        /**
         *
         */
        public function saveConfig($licenceValidate = 0)
        {
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            $params = array();
            $type = 'default';
            if (isset($request['from'])) {
                if(in_array($request['from'], array('taxonomy'))){
                    $type = $request['from'];
                }
            }
            if($type == 'taxonomy'){
                $option_type = $this->taxonomy_option;
            } else {
                $option_type = $this->default_option;
            }

            if (isset($request['data'])) {
                parse_str($request['data'], $params);
            }

            if(isset($params['wdr_nonce']) && !empty($params['wdr_nonce'])){
                if(FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if(FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_save_rule_config', $params['wdr_nonce'])){
                        if(isset($params['coupon_name'])){
                            $params['coupon_name'] = trim($params['coupon_name']);
                        }

                        if (is_array($request)) {
                            if(isset($params['show_draft']) && $params['show_draft']){
                                $params['show_draft'] = 1;
                            } else {
                                $params['show_draft'] = 0;
                            }
                            foreach ($params as $index => $item) {
//                $params[$index] = FlycartWooDiscountRulesGeneralHelper::makeString($item);
                                $params[$index] = $item;
                            }
                            $params = json_encode($params);
                        }

                        if (get_option($option_type)) {
                            update_option($option_type, $params);
                        } else {
                            add_option($option_type, $params);
                        }
                    }
                }
            }

            if(!$licenceValidate)
                die();
        }

        public function resetWDRCache(){
            $postData = \FlycartInput\FInput::getInstance();
            $wdr_nonce = $postData->get('wdr_nonce', false);
            if(!empty($wdr_nonce)) {
                if (FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if (FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_save_rule_config', $wdr_nonce)) {
                        $price_discount = $this->getInstance('FlycartWooDiscountRulesPricingRules');
                        $result = $price_discount->updateLastUpdateTimeOfRule();
                        if($result){
                            esc_html_e('Cache cleared successfully', 'woo-discount-rules');
                        } else {
                            esc_html_e('Failed to clear cache', 'woo-discount-rules');
                        }
                    }
                }
            }
            die();
        }

        /**
         * @return array
         */
        public function getBaseConfig($type = 'default')
        {
            if($type == 'taxonomy'){
                $option_type = $this->taxonomy_option;
            } else {
                $option_type = $this->default_option;
            }
            $option = get_option($option_type);
            if (!$option || is_null($option)) {
                return array();
            } else {
                return $option;
            }
        }

        /**
         * Get Config data
         *
         * @param String $key
         * @param mixed $default
         * @return mixed
         * */
        public function getConfigData($key, $default = '', $type = "default"){
            $config = $this->getBaseConfig($type);
            if (is_string($config)) $config = json_decode($config, true);
            return isset($config[$key])? $config[$key] : $default;
        }

        // -------------------------------------------SETTINGS END----------------------------------------------------------

        /**
         * @param $request
         * @return bool
         */
        public function checkSubmission($request)
        {
            if (isset($request['form']) && !empty($request['form'])) {
                $form = sanitize_text_field($request['form']);
                if (strpos($form, '_save') === false) return false;
                // For Saving Form
                $form = str_replace('_save', '', $form);
                // To Verify, the submitted form is in the Registered List or Not
                if (in_array($form, $this->formList())) {
                    if (isset($request['page'])) {
                        switch ($form) {
                            case 'pricing_rules':
                                die(123);
                                $pricing_rule = $this->getInstance('FlycartWooDiscountRulesPricingRules');
                                $pricing_rule->save($request);
                                break;
                            case 'cart_rules':
                                $cart_rules = $this->getInstance('FlycartWooDiscountRulesCartRules');
                                $cart_rules->save($request);
                                break;
                            case 'settings':
                                $this->save($request);
                                break;
                            default:
                                // Invalid Submission.
                                break;
                        }
                    }
                }
            }
        }

        /**
         * @param $option
         */
        public function checkAccess(&$option)
        {
            $postData = \FlycartInput\FInput::getInstance();
            // Handling View
            if ($postData->get('view', false)) {
                $option = $option . '-view';
                // Type : Price or Cart Discounts.
            } elseif ($postData->get('type', false)) {
                if ($postData->get('tab', false)) {
                    if ($postData->get('tab', '') == 'cart-rules') {
                        $option = 'cart-rules-new';
                        if ($postData->get('type', '') == 'view') $option = 'cart-rules-view';
                    }
                } else {
                    $option = $option . '-' . $postData->get('type', '');
                }
            }
        }

        /**
         * @param $request
         */
        public function save($request)
        {
            // Save General Settings of the Plugin.
        }

        /**
         * Do bulk action
         * */
        public function doBulkAction(){
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            if(isset($request['wdr_nonce']) && !empty($request['wdr_nonce'])){
                if(FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if(FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_rule_listing', $request['wdr_nonce'])){
                        if(empty($request['bulk_action'])){
                            echo esc_html__('Failed to do action', 'woo-discount-rules');exit;
                        }
                        $result = array();
                        $had_action = 0;
                        if(!empty($request['post'])){
                            foreach ($request['post'] as $key => $id){
                                $had_action = 1;
                                $result[$key] = 0;
                                if($id){
                                    switch ($request['bulk_action']){
                                        case 'unpublish':
                                            $status = get_post_meta($id, 'status', true);
                                            if (!empty($status)) {
                                                $result[$key] = update_post_meta($id, 'status', 'disable');
                                            }
                                            break;
                                        case 'delete':
                                            try {
                                                $id = intval($id);
                                                if ($id) $result[$key] = wp_delete_post($id);
                                            } catch (Exception $e) {
                                            }
                                            break;
                                        default:
                                            $status = get_post_meta($id, 'status', true);

                                            if (!empty($status)) {
                                                $result[$key] = update_post_meta($id, 'status', 'publish');
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                        if(!$had_action){
                            echo esc_html__('Failed to do action', 'woo-discount-rules');
                        } else{
                            switch ($request['bulk_action']){
                                case 'unpublish':
                                    echo esc_html__('Disabled successfully', 'woo-discount-rules');
                                    break;
                                case 'delete':
                                    echo esc_html__('Deleted successfully', 'woo-discount-rules');
                                    break;
                                default:
                                    echo esc_html__('Enabled successfully', 'woo-discount-rules');
                                    break;
                            }
                        }
                    }
                }
            }
            die();
        }

        /**
         * Create a duplicate rule
         * */
        public function createDuplicateRule(){
            $purchase = new FlycartWooDiscountRulesPurchase();
            $isPro = $purchase->isPro();
            if(!$isPro) return false;
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            if(isset($request['wdr_nonce']) && !empty($request['wdr_nonce'])) {
                if (FlycartWooDiscountRulesGeneralHelper::hasAdminPrivilege()) {
                    if (FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_rule_listing', $request['wdr_nonce'])) {
                        if(!empty($request['id']) && (int)$request['id'] && !empty($request['type'])){
                            $post = get_post( (int)$request['id'] );
                            if(!empty($post)){
                                $post_new = array(
                                    'post_title' => $post->post_title.' - '.esc_html__('copy', 'woo-discount-rules'),
                                    'post_name' => $post->post_title.' - '.esc_html__('copy', 'woo-discount-rules'),
                                    'post_content' => 'New Rule',
                                    'post_type' => $post->post_type,
                                    'post_status' => 'publish'
                                );
                                $id = wp_insert_post($post_new);
                                if($id){
                                    /*
                                     * duplicate all post meta just in two SQL queries
                                     */
                                    global $wpdb;
                                    $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post->ID");
                                    if (count($post_meta_infos)!=0) {
                                        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                                        foreach ($post_meta_infos as $meta_info) {
                                            $meta_key = $meta_info->meta_key;
                                            if( $meta_key == 'rule_order' ) $meta_info->meta_value = FlycartWooDiscountRulesGeneralHelper::reOrderRuleIfExists($id, 1, $post->post_type);
                                            if( $meta_key == 'rule_name' ) $meta_info->meta_value = $meta_info->meta_value.' - '.esc_html__('copy', 'woo-discount-rules');
                                            if( $meta_key == 'status' ) $meta_info->meta_value = 'disable';
                                            $meta_value = addslashes($meta_info->meta_value);
                                            $sql_query_sel[]= "SELECT $id, '$meta_key', '$meta_value'";
                                        }
                                        $sql_query.= implode(" UNION ALL ", $sql_query_sel);
                                        $wpdb->query($sql_query);
                                    }
                                    echo esc_html__('Duplicate rule created successfully', 'woo-discount-rules'); die();
                                }
                            }
                        }
                    }
                }
            }
            echo esc_html__('Failed to create duplicate rule', 'woo-discount-rules'); die();
        }

        /**
         * @return array
         */
        public function formList()
        {
            return array(
                'pricing_rules',
                'cart_rules',
                'settings'
            );
        }

        /**
         *
         */
        public function viewManager()
        {
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            $this->checkSubmission($request);

            // Adding Plugin Page Script
            //$this->woo_discount_adminPageScript();

            // Loading Instance.
            $generalHelper = $this->getInstance('FlycartWooDiscountRulesGeneralHelper');
            // Sanity Check.
            if (!$generalHelper) return;
            // Getting Active Tab.
            $tab = $generalHelper->getCurrentTab();

            $path = $this->getPath($tab);

            // Manage Tab.
            $tab = (isset($tab) ? $tab : $this->default_page);
            $html = '';
            // File Check.
            if (file_exists($path)) {
                $data = array();
                $this->fetchData($tab, $data);
                // Processing View.
                $html = $generalHelper->processBaseView($path, $data, $tab);
            }
            echo $html;
        }

        /**
         * @param $tab
         * @return mixed
         */
        public function getPath(&$tab)
        {
            $this->checkAccess($tab);
            $pages = $this->adminPages();
            // Default tab.
            $path = $pages[$this->default_page];

            // Comparing Available Tab with Active Tab.
            if (isset($pages[$tab])) {
                $path = $pages[$tab];
            }
            return $path;
        }

        /**
         * @param $type
         * @param $data
         */
        public function fetchData($type, &$data)
        {
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();

            $helper = new FlycartWooDiscountRulesGeneralHelper();
            $isPro = $helper->checkPluginState();
            $this->checkForWPMLAndSetCookie($type);
            switch ($type) {
                // Managing Price Rules View.
                case 'pricing-rules':
                    $pricing_rule = $this->getInstance('FlycartWooDiscountRulesPricingRules');
                    $data = $pricing_rule->getRules();
                    break;
                // Managing Cart Rules View.
                case 'cart-rules':
                    $cart_rule = $this->getInstance('FlycartWooDiscountRulesCartRules');
                    $data = $cart_rule->getRules();
                    break;
                // Managing View of Settings.
                case 'settings':
                    $data = $this->getBaseConfig();
                    break;
                case 'taxonomy':
                    $data = $this->getBaseConfig('taxonomy');
                    break;
                case 'documentation':
                    break;

                // Managing View of Pricing Rules.
                case 'pricing-rules-new':
                    $data = new stdClass();
                    $data->form = 'pricing_rules_save';
                    if (!$isPro) {
                        $pricing_rule = $this->getInstance('FlycartWooDiscountRulesPricingRules');
                        $data = $pricing_rule->getRules();
                        if (count($data) >= 6) die('You are restricted to process this action.');
                    }
                    break;

                // Managing View of Pricing Rules.
                case 'pricing-rules-view':

                    $view = false;
                    // Handling View
                    if (isset($request['view'])) {
                        $view = $request['view'];
                    }
                    $html = $this->getInstance('FlycartWooDiscountRulesPricingRules');
                    $out = $html->view($type, $view);
                    if (isset($out) && !empty($out)) {
                        $data = $out;
                    }
                    $data->form = 'pricing_rules_save';
                    break;

                // Managing View of Cart Rules.
                case 'cart-rules-view':
                    $view = false;
                    // Handling View
                    if (isset($request['view'])) {
                        $view = $request['view'];
                    } else {

                        if (!$isPro) {
                            $cart_rule = $this->getInstance('FlycartWooDiscountRulesCartRules');
                            $total_record = $cart_rule->getRules(true);
                            if ($total_record >= 6) wp_die('You are restricted to process this action.');
                        }
                    }

                    $html = $this->getInstance('FlycartWooDiscountRulesCartRules');
                    $out = $html->view($type, $view);
                    if (isset($out) && !empty($out)) {
                        $data[] = $out;
                    }
                    break;
                // Managing View of Cart Rules.
                case 'cart-rules-new':
                    if (!$isPro) {
                        $cart_rule = $this->getInstance('FlycartWooDiscountRulesCartRules');
                        $total_record = $cart_rule->getRules(true);
                        if ($total_record >= 6) wp_die('You are restricted to process this action.');
                    }
                    break;

                default:
                    $data = array();

                    break;
            }

        }

        /**
         * Check for WPML available and set cookie if available
         * */
        protected function checkForWPMLAndSetCookie($layout){
            $set_wpml_lang = apply_filters('woo_discount_rules_set_wpml_language_for_loading_in_product_select_box', true);
            if($set_wpml_lang){
                $wpml_language = FlycartWooDiscountRulesGeneralHelper::getWPMLLanguage();
                if(!empty($wpml_language)){
                    if(in_array($layout, array('pricing-rules-new', 'pricing-rules-view', 'cart-rules-view', 'cart-rules-new'))){
                        setcookie('_wcml_dashboard_order_language', $wpml_language, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
                    } else {
                        if(!isset($_COOKIE['_wcml_dashboard_order_language']) && !empty($_COOKIE['_wcml_dashboard_order_language'])) {
                            setcookie('_wcml_dashboard_order_language', '', time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
                        }
                    }
                }
            }
        }

        /**
         * @return array
         */
        public function adminPages()
        {
            return array(
                $this->default_page => WOO_DISCOUNT_DIR . '/view/pricing-rules.php',
                'cart-rules' => WOO_DISCOUNT_DIR . '/view/cart-rules.php',
                'settings' => WOO_DISCOUNT_DIR . '/view/settings.php',
                'documentation' => WOO_DISCOUNT_DIR . '/view/documentation.php',
                'taxonomy' => WOO_DISCOUNT_DIR . '/view/settings_taxonomy.php',

                // New Rule also access the same "View" to process
                'pricing-rules-new' => WOO_DISCOUNT_DIR . '/view/view-pricing-rules.php',
                'cart-rules-new' => WOO_DISCOUNT_DIR . '/view/view-cart-rules.php',

                // Edit Rules
                'pricing-rules-view' => WOO_DISCOUNT_DIR . '/view/view-pricing-rules.php',
                'cart-rules-view' => WOO_DISCOUNT_DIR . '/view/view-cart-rules.php'
            );
        }

        /**
         *
         */
        public function getOption()
        {

        }

        /**
         * Adding Admin Page Script.
         */
        function woo_discount_adminPageScript()
        {
            $status = false;
            $postData = \FlycartInput\FInput::getInstance();
            // Plugin scripts should run only in plugin page.
            if (is_admin()) {
                if ($postData->get('page', false) == 'woo_discount_rules') {
                    $status = true;
                }
                // By Default, the landing page also can use this script.
            } elseif (!is_admin()) {
                //  $status = true;
            }

            if ($status) {

                $config = $this->getBaseConfig();
                if (is_string($config)) $config = json_decode($config, true);
                $enable_bootstrap = isset($config['enable_bootstrap'])? $config['enable_bootstrap']: 1;

                wp_register_style('woo_discount_style', WOO_DISCOUNT_URI . '/assets/css/style.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_style');

                wp_register_style('woo_discount_style_custom', WOO_DISCOUNT_URI . '/assets/css/custom.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_style_custom');

                wp_register_style('woo_discount_style_tab', WOO_DISCOUNT_URI . '/assets/css/tabbablePanel.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_style_tab');

                // For Implementing Select Picker Library.
                wp_register_style('woo_discount_style_select', WOO_DISCOUNT_URI . '/assets/css/bootstrap.select.min.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_style_select');

                wp_enqueue_script('woo_discount_script_select', WOO_DISCOUNT_URI . '/assets/js/bootstrap.select.min.js', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_script('woo_discount_script_select2', WOO_DISCOUNT_URI . '/assets/js/select2.min.js', array(), WOO_DISCOUNT_VERSION);

                wp_register_style('woo_discount_bootstrap', WOO_DISCOUNT_URI . '/assets/css/bootstrap.min.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_bootstrap');

                if($enable_bootstrap){
                    wp_register_script('woo_discount_jquery_ui_js_2', WOO_DISCOUNT_URI . '/assets/js/bootstrap.min.js', array(), WOO_DISCOUNT_VERSION);
                    wp_enqueue_script('woo_discount_jquery_ui_js_2');
                }

                wp_register_style('woo_discount_jquery_ui_css', WOO_DISCOUNT_URI . '/assets/css/jquery-ui.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_jquery_ui_css');
                wp_register_style('woo_discount_datetimepicker_css', WOO_DISCOUNT_URI . '/assets/css/bootstrap-datetimepicker.min.css', array(), WOO_DISCOUNT_VERSION);
                wp_enqueue_style('woo_discount_datetimepicker_css');

                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script( 'woocommerce_admin' );
                wp_enqueue_script( 'wc-enhanced-select' );

                wp_enqueue_script('woo_discount_datetimepicker_js', WOO_DISCOUNT_URI . '/assets/js/bootstrap-datetimepicker.min.js', array('woocommerce_admin', 'wc-enhanced-select'), WOO_DISCOUNT_VERSION, true);
                wp_enqueue_script('woo_discount_script', WOO_DISCOUNT_URI . '/assets/js/app.js', array(), WOO_DISCOUNT_VERSION);
                $localization_data = $this->getLocalizationData();
                wp_localize_script( 'woo_discount_script', 'woo_discount_localization', $localization_data);

                //To load woocommerce product select
                wp_enqueue_style( 'woocommerce_admin_styles' );
            }
        }

        /**
         * Get localisation script
         * */
        protected function getLocalizationData(){
            return array(
                'please_fill_this_field' => esc_html__('Please fill this field', 'woo-discount-rules'),
                'please_enter_the_rule_name' => esc_html__('Please Enter the Rule Name to Create / Save.', 'woo-discount-rules'),
                'saving' => esc_html__('Saving...', 'woo-discount-rules'),
                'save_rule' => esc_html__('Save Rule', 'woo-discount-rules'),
                'please_enter_a_key' => esc_html__('Please enter a Key', 'woo-discount-rules'),
                'min_quantity' => esc_html__('Min Quantity', 'woo-discount-rules'),
                'max_quantity' => esc_html__('Max Quantity', 'woo-discount-rules'),
                'set_quantity' => esc_html__('Quantity', 'woo-discount-rules'),
                'place_holder_ex_1' => esc_html__('ex. 1', 'woo-discount-rules'),
                'place_holder_ex_10' => esc_html__('ex. 10', 'woo-discount-rules'),
                'place_holder_ex_50' => esc_html__('ex. 50', 'woo-discount-rules'),
                'place_holder_search_for_a_user' => esc_html__('Search for a user', 'woo-discount-rules'),
                'adjustment_type' => esc_html__('Adjustment Type', 'woo-discount-rules'),
                'percentage_discount' => esc_html__('Discount percentage', 'woo-discount-rules'),
                'percentage_discount_in_adjustment_type' => esc_html__('Percentage Discount', 'woo-discount-rules'),
                'price_discount' => esc_html__('Price Discount', 'woo-discount-rules'),
                'product_discount' => esc_html__('BOGO Product Discount', 'woo-discount-rules'),
                'product_discount_not_work_on_subtotal_based' => esc_html__('Product Discount - Not support for subtotal based rule', 'woo-discount-rules'),
                'value_text' => esc_html__('Value', 'woo-discount-rules'),
                'apply_for' => esc_html__('receive discount for', 'woo-discount-rules'),
                'all_selected' => esc_html__('Auto add all selected products', 'woo-discount-rules'),
                'same_product' => esc_html__('Same product', 'woo-discount-rules'),
                'any_one_cheapest_from_selected' => esc_html__('Any one cheapest from selected', 'woo-discount-rules'),
                'any_one_cheapest_from_all_products' => esc_html__('Any one cheapest from all products', 'woo-discount-rules'),
                'more_than_one_cheapest_from_selected_category' => esc_html__('Buy X Get Y - Selected Categories (Cheapest in cart)', 'woo-discount-rules'),
                'more_than_one_cheapest_from_selected' => esc_html__('Buy X Get Y - Selected item(s) (Cheapest in cart)', 'woo-discount-rules'),
                'more_than_one_cheapest_from_all' => esc_html__('Buy X get Y - Cheapest among all items in cart', 'woo-discount-rules'),
                'free_quantity' => esc_html__('Free quantity', 'woo-discount-rules'),
                'number_of_quantities_in_each_products' => esc_html__('Number of quantity(ies) in each selected product(s)', 'woo-discount-rules'),
                'fixed_item_count' => esc_html__('Fixed item count (not recommended)', 'woo-discount-rules'),
                'dynamic_item_count' => esc_html__('Dynamic item count', 'woo-discount-rules'),
                'fixed_item_count_tooltip' => esc_html__('Fixed item count - You need to provide item count manually. Dynamic item count - System will choose dynamically based on cart', 'woo-discount-rules'),
                'item_count' => esc_html__('Item count', 'woo-discount-rules'),
                'discount_number_of_item_tooltip' => esc_html__('Discount for number of item(s) in cart', 'woo-discount-rules'),
                'discount_number_of_each_item_tooltip' => esc_html__('Discount for number of quantity(ies)', 'woo-discount-rules'),
                'item_quantity' => esc_html__('Item quantity', 'woo-discount-rules'),
                'place_holder_search_for_products' => esc_html__('Search for a products', 'woo-discount-rules'),
                'and_text' => esc_html__('and', 'woo-discount-rules'),
                'percent_100' => esc_html__('100% percent', 'woo-discount-rules'),
                'limited_percent' => esc_html__('Limited percent', 'woo-discount-rules'),
                'percentage_tooltip' => esc_html__('Percentage', 'woo-discount-rules'),
                'as_discount' => esc_html__('as discount', 'woo-discount-rules'),
                'remove_text' => esc_html__('Remove', 'woo-discount-rules'),
                'duplicate_text' => esc_html__('Duplicate', 'woo-discount-rules'),
                'none_text' => esc_html__('none', 'woo-discount-rules'),
                'are_you_sure_to_remove_this' => esc_html__('Are you sure to remove this ?', 'woo-discount-rules'),
                'enable_text' => esc_html__('Enable', 'woo-discount-rules'),
                'disable_text' => esc_html__('Disable', 'woo-discount-rules'),
                'are_you_sure_to_remove' => esc_html__('Are you sure to remove ?', 'woo-discount-rules'),
                'type_text' => esc_html__('Type', 'woo-discount-rules'),
                'cart_subtotal' => esc_html__('Cart Subtotal', 'woo-discount-rules'),
                'subtotal_at_least' => esc_html__('Subtotal at least', 'woo-discount-rules'),
                'subtotal_less_than' => esc_html__('Subtotal less than', 'woo-discount-rules'),
                'cart_item_count' => esc_html__('Cart Item Count', 'woo-discount-rules'),
                'number_of_line_items_in_cart_at_least' => esc_html__('Number of line items in the cart (not quantity) at least', 'woo-discount-rules'),
                'number_of_line_items_in_cart_less_than' => esc_html__('Number of line items in the cart (not quantity) less than', 'woo-discount-rules'),
                'quantity_sum' => esc_html__('Quantity Sum', 'woo-discount-rules'),
                'total_number_of_quantities_in_cart_at_least' => esc_html__('Total number of quantities in the cart at least', 'woo-discount-rules'),
                'total_number_of_quantities_in_cart_less_than' => esc_html__('Total number of quantities in the cart less than', 'woo-discount-rules'),
                'categories_in_cart' => esc_html__('Categories in cart', 'woo-discount-rules'),
                'atleast_one_including_sub_categories' => esc_html__('Including sub-categories in cart', 'woo-discount-rules'),
                'customer_details_must_be_logged_in' => esc_html__('Customer Details (must be logged in)', 'woo-discount-rules'),
                'user_in_list' => esc_html__('User in list', 'woo-discount-rules'),
                'user_role_in_list' => esc_html__('User role in list', 'woo-discount-rules'),
                'shipping_country_list' => esc_html__('Shipping country in list', 'woo-discount-rules'),
                'customer_email' => esc_html__('Customer Email', 'woo-discount-rules'),
                'customer_email_tld' => esc_html__('Email with TLD (Ege: edu)', 'woo-discount-rules'),
                'customer_email_domain' => esc_html__('Email with Domain (Eg: gmail.com)', 'woo-discount-rules'),
                'customer_billing_details' => esc_html__('Customer Billing Details', 'woo-discount-rules'),
                'customer_billing_city' => esc_html__('Billing city', 'woo-discount-rules'),
                'customer_shipping_details' => esc_html__('Customer Shipping Details', 'woo-discount-rules'),
                'customer_shipping_state' => esc_html__('Shipping state', 'woo-discount-rules'),
                'customer_shipping_city' => esc_html__('Shipping city', 'woo-discount-rules'),
                'customer_shipping_zip_code' => esc_html__('Shipping zip code', 'woo-discount-rules'),
                'purchase_history' => esc_html__('Purchase History', 'woo-discount-rules'),
                'purchased_amount' => esc_html__('Purchased amount', 'woo-discount-rules'),
                'number_of_order_purchased' => esc_html__('Number of previous orders made', 'woo-discount-rules'),
                'number_of_order_purchased_in_product' => esc_html__('Number of previous orders made with following products', 'woo-discount-rules'),
                'number_of_order_quantity_purchased_in_product' => esc_html__('Number of quantity(s) in previous orders made with following products', 'woo-discount-rules'),
                'coupon_applied' => esc_html__('Coupon applied', 'woo-discount-rules'),
                'create_a_coupon' => esc_html__('Create your own coupon', 'woo-discount-rules'),
                'atleast_any_one' => esc_html__('Atleast any one (Select from WooCommerce)', 'woo-discount-rules'),
                'all_selected_coupon' => esc_html__('All selected (Select from WooCommerce)', 'woo-discount-rules'),
                'greater_than_or_equal_to' => esc_html__('Greater than or equal to', 'woo-discount-rules'),
                'less_than_or_equal_to' => esc_html__('Less than or equal to', 'woo-discount-rules'),
                'in_order_status' => esc_html__('and the order status should be', 'woo-discount-rules'),
                'action_text' => esc_html__('Action', 'woo-discount-rules'),
                'save_text' => esc_html__('Save', 'woo-discount-rules'),
                'saved_successfully' => esc_html__('Saved Successfully!', 'woo-discount-rules'),
                'none_selected' => esc_html__('None selected', 'woo-discount-rules'),
                'in_each_category_cart' => esc_html__('In each category', 'woo-discount-rules'),
                'show_text' => esc_html__('Show', 'woo-discount-rules'),
                'hide_text' => esc_html__('Hide', 'woo-discount-rules'),
                'please_select_at_least_one_checkbox' => esc_html__('Please select at least one rule', 'woo-discount-rules'),
                'please_select_bulk_action' => esc_html__('Please select an action to apply', 'woo-discount-rules'),
                'are_you_sure_to_delete' => esc_html__('Are you sure to remove the selected rules', 'woo-discount-rules'),
                'choose_products' => esc_html__('Choose product(s)', 'woo-discount-rules'),
                'choose_categories' => esc_html__('Choose category(ies)', 'woo-discount-rules'),
                'coupon_select_box_placeholder' => esc_html__('Search for a coupon', 'woo-discount-rules'),
                'percentage_discount_amount_tool_tip_text' => esc_attr__('Enter only numeric values. Eg: <b>50</b> for 50% discount', 'woo-discount-rules'),
                'price_discount_amount_tool_tip_text' => sprintf(esc_attr__('Enter the discount price. Eg: <b>10</b> for %s discount', 'woo-discount-rules'), strip_tags(FlycartWoocommerceProduct::wc_price(10))),
                'fixed_price_discount_amount_tool_tip_text' => sprintf(esc_attr__('Enter the discounted price per unit. Eg: <b>10</b> for %s as unit price', 'woo-discount-rules'), strip_tags(FlycartWoocommerceProduct::wc_price(10))),
                'set_discount_amount_tool_tip_text' => sprintf(esc_attr__('Enter the price for selected quantity. Eg: <b>10</b> then %s as total price for selected quantity', 'woo-discount-rules'), strip_tags(FlycartWoocommerceProduct::wc_price(10))),
                'discount_product_option_tooltip' => esc_html__("Auto add all selected products - Automatically added to the cart <br> Same product - get discount in same product <br> Any one cheapest from selected - Get discount in one selected product <br> Any one cheapest from all products - Get discount in one cheapest product  in cart <br> Cheapest in cart - selected category(ies) - cheapest product from the selected category will be discounted <br> Cheapest in cart - selected item(s) - get discount in chosen no.of quantities", 'woo-discount-rules'),
                'products_in_cart' => esc_html__('Products', 'woo-discount-rules'),
                'products_in_list' => esc_html__('Products in cart', 'woo-discount-rules'),
                'products_not_in_list' => esc_html__('Exclude products', 'woo-discount-rules'),
                'exclude_sale_products' => esc_html__('Exclude on sale products', 'woo-discount-rules'),
                'exclude_sale_products_desc' => esc_html__('This will exclude the on sale products from discount', 'woo-discount-rules'),
                'from_all_previous_orders' => esc_html__('From all previous orders', 'woo-discount-rules'),
                'last_7_days' => esc_html__('Last 7 days', 'woo-discount-rules'),
                'last_14_days' => esc_html__('Last 14 days', 'woo-discount-rules'),
                'last_30_days' => esc_html__('Last 30 days', 'woo-discount-rules'),
                'last_60_days' => esc_html__('Last 60 days', 'woo-discount-rules'),
                'last_90_days' => esc_html__('Last 90 days', 'woo-discount-rules'),
                'last_180_days' => esc_html__('Last 180 days', 'woo-discount-rules'),
                'last_1_year' => esc_html__('Last 1 year', 'woo-discount-rules'),
                'custom_days' => esc_html__('Custom', 'woo-discount-rules'),
                'in_days' => esc_html__('in days', 'woo-discount-rules'),
                'fixed_price' => esc_html__('Fixed Price Per Unit', 'woo-discount-rules'),
                'set_discount' => esc_html__('Bundle (Set) Discount', 'woo-discount-rules'),
                'first_order_discount' => esc_html__('First Order discount', 'woo-discount-rules'),
                'exclude_categories_in_cart' => esc_html__('Exclude categories in cart', 'woo-discount-rules'),
                'enable_fixed_item_count_in_bogo' => apply_filters('woo_discount_rules_enable_fixed_item_count_in_bogo', false),
                'buy_x_get_x' => esc_html__('Buy X get X (Same product)', 'woo-discount-rules'),
                'buy_x_get_y' => esc_html__('Buy X get Y (Auto add all selected products)', 'woo-discount-rules'),
                'buy_x_get_x_tool_tip_text' => esc_html__('The customer gets the same product free (Buy 2 get 1 free) or a limited percentage (Buy 2 and get 1 at 50% discount)', 'woo-discount-rules'),
                'buy_x_get_y_tool_tip_text' => __('Provide a specific product free when purchasing another product.<br><br>Example: Buy Product A and get Product B free. Product B will be automatically added to cart.', 'woo-discount-rules'),
                'more_than_one_cheapest_tool_tip_text' => __('Provide a specific product free when purchasing another product.<br><br>Instead of automatically adding, if you wish to choose the free product, you can select this option.<br><br>Note : Product will be discounted only when the user manually adds the product to cart.', 'woo-discount-rules'),
                'more_than_one_cheapest_from_cat_tool_tip_text' => __('Used to provide BOGO discount within categories.<br><br>Example 1: Buy 2 from Category A and get 1 free from the same Category A.<br>Example 2: Buy any items from Category A and get 20% (limited percent) discount on Category B.', 'woo-discount-rules'),
                'more_than_one_cheapest_from_all_tool_tip_text' => __('This allows you to offer the cheapest product in cart for free (or at a limited percentage like 50%)', 'woo-discount-rules'),
                'apply_to_hint_all_products' => sprintf(__('<span class="wdr_desc_text">Useful for providing a discount on store wide or on all products.</span> <span class="wdr_desc_text"> <a href="%s">Read docs</a>.</span>', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1459869-storewide-global-discount-for-all-products', 'discount_for_all_products')),
                'apply_to_hint_specific_products' => sprintf(__('<span class="wdr_desc_text">Useful for providing a discount on selected products. Check the box to count quantities together across products.</span><br><span class="wdr_desc_text"><b>Note:</b> For variable products, you can auto include variants by enabling it in the settings. <a href="%s">Read docs</a>.</span>', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1459887-product-specific-discount-get-discount-in-t-shirts', 'discount_for_specific_products')),
                'apply_to_hint_specific_category' => sprintf(__('<span class="wdr_desc_text">Useful for providing a discount on a specific category or multiple categories.</span><br><span class="wdr_desc_text"><b>Example:</b> 10%% discount for products from category A or category B. <a href="%s">Read docs</a>.</span>', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1459878-category-specific-discount', 'discount_for_specific_products')),
                'apply_to_hint_specific_attribute' => sprintf(__('<span class="wdr_desc_text">Useful to offer discount based on attributes. <b>Example:</b> 10%% discount on Small Size T-shirts.</span><br><span class="wdr_desc_text"><b>Note:</b> Please make sure that the attributes are pre-defined in WooCommerce -> Attributes. <a href="%s">Read docs</a>.</span>', 'woo-discount-rules'), FlycartWooDiscountRulesGeneralHelper::docsDirectURL('https://docs.flycart.org/en/articles/1993883-specific-attribute-based-discount', 'discount_for_specific_attributes')),
            );
        }

        /**
         * Remove applied message for the coupon we are going to remove
         * */
        function removeAppliedMessageOfThirdPartyCoupon($msg, $msg_code, $coupon){
            if(!empty($coupon)){
                if(method_exists($coupon, 'get_code')){
                    $coupon_code = $coupon->get_code(); //Applied COUPON
                    $do_not_run_while_have_third_party_coupon = $this->getConfigData('do_not_run_while_have_third_party_coupon', 0);
                    if($do_not_run_while_have_third_party_coupon == 'remove_coupon'){
                        $remove_coupon_message = apply_filters('woo_discount_rules_remove_coupon_message_on_apply_discount', true, $coupon_code);
                        if($remove_coupon_message){
                            $has_price_rules = $this->hasPriceRules();
                            $has_cart_rules = $this->hasCartRules();
                            $used_coupons = FlycartWooDiscountRulesGeneralHelper::getUsedCouponsInRules();
                            $cartRules = new FlycartWooDiscountRulesCartRules();
                            $coupon_code_by_cart_rule = $cartRules->getCouponCode();
                            $coupon_code_by_cart_rule = strtolower($coupon_code_by_cart_rule);
                            $used_coupons[] = $coupon_code_by_cart_rule;
                            $applied_coupon = FlycartWooDiscountRulesCartRules::getAppliedCoupons();
                            if(!empty($used_coupons) && is_array($used_coupons)){
                                $applied_coupon = array_merge($applied_coupon, $used_coupons);
                            }
                            if(!empty($applied_coupon)){
                                $applied_coupon = array_map('strtolower', $applied_coupon);
                            }
                            $skip_coupons = apply_filters('woo_discount_rules_coupons_to_skip_while_apply_rules_and_remove_third_party_coupon', $applied_coupon);
                            $cart_discount = $this->getInstance('FlycartWooDiscountRulesCartRules');
                            $skip_coupons[] = $cart_discount->coupon_code;
                            if($has_price_rules || $has_cart_rules){
                                if(!in_array($coupon_code, $skip_coupons)){
                                    $msg = '';
                                }
                            } else {
                                global $woocommerce;
                                if(!empty($woocommerce->cart)){
                                    if(!empty($woocommerce->cart->applied_coupons)){
                                        $coupons_applied = $woocommerce->cart->applied_coupons;
                                        if(!empty($coupons_applied)){
                                            $used_coupon_in_woo_discount = array_intersect($coupons_applied, $skip_coupons);
                                        }
                                        if(!empty($used_coupon_in_woo_discount)){
                                            if(!in_array($coupon_code, $used_coupon_in_woo_discount)){
                                                $msg = '';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $msg;
        }

        /**
         * Remove third party coupon
         * */
        public function removeThirdPartyCoupon(){
            $removed_coupon = false;
            $do_not_run_while_have_third_party_coupon = $this->getConfigData('do_not_run_while_have_third_party_coupon', 0);
            if($do_not_run_while_have_third_party_coupon == 'remove_coupon'){
                $has_price_rules = $this->hasPriceRules();
                $has_cart_rules = $this->hasCartRules();
                if($has_price_rules || $has_cart_rules){
                    $used_coupons = FlycartWooDiscountRulesGeneralHelper::getUsedCouponsInRules();
                    $applied_coupon = FlycartWooDiscountRulesCartRules::getAppliedCoupons();
                    if(!empty($used_coupons) && is_array($used_coupons)){
                        $applied_coupon = array_merge($applied_coupon, $used_coupons);
                    }
                    if(!empty($applied_coupon)){
                        $applied_coupon = array_map('strtolower', $applied_coupon);
                    }
                    $skip_coupons = apply_filters('woo_discount_rules_coupons_to_skip_while_apply_rules_and_remove_third_party_coupon', $applied_coupon);
                    global $woocommerce;
                    if(!empty($woocommerce->cart)){
                        if(!empty($woocommerce->cart->applied_coupons)){
                            foreach ($woocommerce->cart->applied_coupons as $code) {
                                if(!in_array($code, $skip_coupons)){
                                    $removed_coupon = true;
                                    FlycartWoocommerceCart::remove_coupon($code);
                                    if(function_exists('wc_add_notice')){
                                        $msg = sprintf(__('Sorry, it is not possible to apply coupon <b>"%s"</b> as you already have a discount applied in cart.'), $code);
                                        $msg = apply_filters('woo_discount_rules_notice_on_remove_coupon_while_having_a_discount', $msg, $code);
                                        wc_add_notice( $msg, 'notice' );
                                    }
                                }
                            }
                            if($removed_coupon){
                                WC()->cart->calculate_totals();
                            }
                        }
                    }
                }
            }
        }

        /**
         * Check has price rules
         * */
        protected function hasPriceRules(){
            $has_price_rules = false;
            $cart = FlycartWoocommerceCart::get_cart();
            if(FlycartWooDiscountRulesGeneralHelper::is_countable($cart)){
                foreach ($cart as $key => $cart_item){
                    if(isset($cart_item['woo_discount'])){
                        if(!empty($cart_item['woo_discount'])){
                            $has_price_rules = true;
                            break;
                        }
                    }
                }
            }

            return $has_price_rules;
        }

        /**
         * Check has cart rules
         * */
        protected function hasCartRules(){
            $applied_coupon = FlycartWooDiscountRulesCartRules::getAppliedCoupons();
            if(empty($applied_coupon)){
                return false;
            }

            return true;
        }

        /**
         * To display settings link in plugin page
         *
         * @param array $links
         * @return array
         * */
        public static function addActionLinksInPluginPage($links){
            $new_links = array(
                '<a href="' . admin_url("admin.php?page=woo_discount_rules&tab=settings"). '">'.esc_html__('Settings', 'woo-discount-rules').'</a>',
            );
            return array_merge($new_links, $links);
        }

        /**
         * Remove hooks set by other plugins
         * */
        public static function removeHooksSetByOtherPlugins() {
            global $wp_filter;

            $allowed_hooks = array(
                //Filters
                "woocommerce_sale_flash"            => array( "FlycartWooDiscountRulesPricingRules|replaceSaleTagText" ),
            );

            foreach ( $wp_filter as $hook_name => $hook_obj ) {
                if ( preg_match( '#^woocommerce_#', $hook_name ) ) {
                    if ( isset( $allowed_hooks[ $hook_name ] ) ) {
                        $wp_filter[ $hook_name ] = self::remove_wrong_callbacks( $hook_obj, $allowed_hooks[ $hook_name ] );
                    } else {
                    }
                }
            }
        }

        /**
         * Remove other plugin hooks
         *
         * @param $hook_obj object
         * @param $allowed_hooks string
         * @return string
         * */
        public static function remove_wrong_callbacks( $hook_obj, $allowed_hooks ) {
            $new_callbacks = array();
            foreach ( $hook_obj->callbacks as $priority => $callbacks ) {
                $priority_callbacks = array();
                foreach ( $callbacks as $idx => $callback_details ) {
                    if ( self::is_callback_match( $callback_details, $allowed_hooks ) ) {
                        $priority_callbacks[ $idx ] = $callback_details;
                    }
                }
                if ( $priority_callbacks ) {
                    $new_callbacks[ $priority ] = $priority_callbacks;
                }
            }
            $hook_obj->callbacks = $new_callbacks;

            return $hook_obj;
        }

        /**
         * Is hook matches
         *
         * @param $callback_details array
         * @param $allowed_hooks array
         * @return boolean
         * */
        public static function is_callback_match( $callback_details, $allowed_hooks ) {
            $result = false;
            foreach ( $allowed_hooks as $callback_name ) {
                list( $class_name, $func_name ) = explode( "|", $callback_name );
                if(isset($callback_details['function']) && is_array($callback_details['function'])){
                    if ( count( $callback_details['function'] ) != 2 ) {
                        continue;
                    }
                    if ( $class_name == get_class( $callback_details['function'][0] ) AND $func_name == $callback_details['function'][1] ) {
                        $result = true;
                        break;// done!
                    }
                }
            }

            return $result;
        }

        /**
         * Change the default template for sale badge
         *
         * @param $located string
         * @param $template_name string
         * @param $args array
         * @param $template_path string
         * @param $default_path string
         * @return string
         * */
        public static function changeTemplateForSaleTag($located, $template_name, $args, $template_path, $default_path){
            if($template_name == 'single-product/sale-flash.php'){
                $located = self::getTemplatePath('sale-flash.php', WOO_DISCOUNT_DIR . '/view/template/single-product/sale-flash.php', 'single-product');
            } else if($template_name == 'loop/sale-flash.php'){
                $located = self::getTemplatePath('sale-flash.php', WOO_DISCOUNT_DIR . '/view/template/loop/sale-flash.php', 'loop');
            }

            return $located;
        }

        /**
         * Get template path
         *
         * @param $template_name string
         * @param $default_path string
         * @param $folder string
         * @return string
         * */
        protected static function getTemplatePath($template_name, $default_path, $folder = ''){
            $pricing_rules = self::get_instance()->getInstance('FlycartWooDiscountRulesPricingRules');
            $path_from_template = $pricing_rules->getTemplateOverride($template_name, $folder);
            if($path_from_template) $default_path = $path_from_template;

            return $default_path;
        }
    }
}