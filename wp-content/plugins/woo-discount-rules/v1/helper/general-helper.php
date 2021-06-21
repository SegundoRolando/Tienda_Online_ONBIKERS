<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
include_once(WOO_DISCOUNT_DIR . '/helper/purchase.php');
include_once(WOO_DISCOUNT_DIR . '/helper/woo-function.php');
/**
 * Class FlycartWooDiscountRulesGeneralHelper
 */
if ( ! class_exists( 'FlycartWooDiscountRulesGeneralHelper' ) ) {
    global $styles_woo_discount;
    class FlycartWooDiscountRulesGeneralHelper
    {

        public $isPro;

        /**
         * @var string
         */
        public $default_page = 'pricing-rules';

        protected static $sub_categories = array();

        /**
         * To Process the View.
         *
         * @param $path
         * @param $data
         * @return bool|string
         */
        public function processBaseView($path, $data, $tab)
        {
            if (!file_exists($path)) return false;
            $this->checkPluginState();
            $purchase = new FlycartWooDiscountRulesPurchase();
            $suffix = $purchase->getSuffix();
            ob_start();
            $config = $data;
            $pro = $this->isPro;
            $category = $this->getCategoryList();
            $coupons = array();
            if(in_array($tab, array('pricing-rules-new', 'pricing-rules-view', 'cart-rules-new', 'cart-rules-view'))){
                $has_large_no_of_coupon = FlycartWooDiscountBase::hasLargeNumberOfCoupon();
                if($pro && (!$has_large_no_of_coupon)) $coupons = $this->getCouponsList();
            }

            //$users = $this->getUserList();
            FlycartWoocommerceVersion::wcVersion('3.0')? $flycart_wdr_woocommerce_version = 3: $flycart_wdr_woocommerce_version = 2;
            $userRoles = $this->getUserRoles();
            $userRoles['woo_discount_rules_guest'] = esc_html__('Guest', 'woo-discount-rules');
            $countries = $this->getAllCountries();
            
            if (!isset($config)) return false;
            if (!isset($path) or is_null($config)) return false;
            include($path);
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }

        public function checkPluginState()
        {
            $purchase = new FlycartWooDiscountRulesPurchase();
            $this->isPro = $purchase->isPro();
            return $this->isPro;
        }

        /**
         * To Retrieve the list of Users.
         *
         * @return array
         */
        public function getUserList()
        {
            $result = array();
            foreach (get_users() as $user) {
                $result[$user->ID] = '#' . $user->ID . ' ' . $user->user_email;
            }
            return $result;
        }

        /**
         * Get all coupons
         * */
        public function getCouponsList(){
            $args = array(
                'posts_per_page'   => -1,
                'orderby'          => 'title',
                'order'            => 'asc',
                'post_type'        => 'shop_coupon',
                'post_status'      => 'publish',
            );
            $coupon_list = array();
            $coupons = get_posts( $args );
            if(!empty($coupons)){
                foreach ($coupons as $coupon){
                    //$coupon_list[$coupon->post_name] = $coupon->post_title;
                    if(function_exists('wc_strtolower')){
                        $coupon_name = wc_strtolower($coupon->post_title);
                    } else {
                        $coupon_name = strtolower($coupon->post_title);
                    }

                    $coupon_list[$coupon_name] = $coupon->post_title;
                }
            }

            return $coupon_list;
        }

        /**
         * To Retrieve the active tab.
         *
         * @return string
         */
        public function getCurrentTab()
        {
            $postData = \FlycartInput\FInput::getInstance();
            $tab = $this->default_page;
            $empty_tab = $postData->get('tab', null);
            if (!empty($empty_tab) && $postData->get('tab', '') != '') {
                $tab = sanitize_text_field($postData->get('tab', ''));
            }
            return $tab;
        }

        /**
         * To Get All Countries.
         *
         * @return array
         */
        public function getAllCountries()
        {
            $countries = new WC_Countries();

            if ($countries && is_array($countries->countries)) {
                return array_merge(array(), $countries->countries);
            } else {
                return array();
            }
        }

        /**
         * To Get All Capabilities list.
         *
         * @return array
         */
        public function getCapabilitiesList()
        {
            $capabilities = array();

            if (class_exists('Groups_User') && class_exists('Groups_Wordpress') && function_exists('_groups_get_tablename')) {

                global $wpdb;
                $capability_table = _groups_get_tablename('capability');
                $all_capabilities = $wpdb->get_results('SELECT capability FROM ' . $capability_table);

                if ($all_capabilities) {
                    foreach ($all_capabilities as $capability) {
                        $capabilities[$capability->capability] = $capability->capability;
                    }
                }
            } else {
                global $wp_roles;

                if (!isset($wp_roles)) {
                    get_role('administrator');
                }

                $roles = $wp_roles->roles;

                if (is_array($roles)) {
                    foreach ($roles as $rolename => $atts) {
                        if (isset($atts['capabilities']) && is_array($atts['capabilities'])) {
                            foreach ($atts['capabilities'] as $capability => $value) {
                                if (!in_array($capability, $capabilities)) {
                                    $capabilities[$capability] = $capability;
                                }
                            }
                        }
                    }
                }
            }

            return array_merge(array(), $capabilities);
        }

        /**
         * @return array
         */
        public function getUserRoles()
        {
            global $wp_roles;

            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }
            $roles = array();
            foreach ($wp_roles->get_names() as $key => $wp_role ){
                if(function_exists('translate_user_role')) $roles[$key] = translate_user_role($wp_role);
                else $roles[$key] = $wp_role;
            }

            return $roles;
        }

        /**
         * Get list of roles assigned to current user
         *
         * @access public
         * @return array
         */
        public static function getCurrentUserRoles()
        {
            $current_user = wp_get_current_user();
            $userRoles = $current_user->roles;
            if(get_current_user_id() == 0){
                $userRoles[] = 'woo_discount_rules_guest';
            }
            return $userRoles;
        }

        /**
         * @return array
         */
        public function getCategoryList()
        {
            $result = array();
            $taxonomies = apply_filters('woo_discount_rules_accepted_taxonomy_for_category', array('product_cat'));
            $post_categories_raw = get_terms($taxonomies, array('hide_empty' => 0));
            if(is_array($post_categories_raw)){
                $post_categories_raw_count = count($post_categories_raw);

                foreach ($post_categories_raw as $post_cat_key => $post_cat) {
                    if(isset($post_cat->name)){
                        $category_name = $post_cat->name;

                        if ($post_cat->parent) {
                            $parent_id = $post_cat->parent;
                            $has_parent = true;

                            // Make sure we don't have an infinite loop here (happens with some kind of "ghost" categories)
                            $found = false;
                            $i = 0;

                            while ($has_parent && ($i < $post_categories_raw_count || $found)) {

                                // Reset each time
                                $found = false;
                                $i = 0;

                                foreach ($post_categories_raw as $parent_post_cat_key => $parent_post_cat) {

                                    $i++;

                                    if ($parent_post_cat->term_id == $parent_id) {
                                        $category_name = $parent_post_cat->name . ' &rarr; ' . $category_name;
                                        $found = true;

                                        if ($parent_post_cat->parent) {
                                            $parent_id = $parent_post_cat->parent;
                                        } else {
                                            $has_parent = false;
                                        }

                                        break;
                                    }
                                }
                            }
                        }

                        $result[$post_cat->term_id] = $category_name;
                    }
                }
            }

            return $result;
        }

        /**
         * get taxonomy list
         */
        public static function getTaxonomyList()
        {
            $skip_default_taxonomies = array('category', 'post_tag', 'nav_menu', 'link_category', 'post_format', 'action-group', 'product_type', 'product_visibility', 'product_shipping_class', 'product_cat');
            $args = array();
            $output = 'objects';
            $taxonomies = get_taxonomies($args, $output);
            $additional_taxonomies = array();
            foreach ($taxonomies as $taxonomy){
                if(!in_array($taxonomy->name, $skip_default_taxonomies)){
                    $load_attributes_in_categories = apply_filters('woo_discount_rules_load_attributes_in_categories', false);
                    if(!(substr( $taxonomy->name, 0, 3 ) === "pa_") || $load_attributes_in_categories){
                        $additional_taxonomies[$taxonomy->name] = $taxonomy->label;
                    }
                }
            }

            return $additional_taxonomies;
        }

        /**
         * Get Category by passing product ID or Product.
         *
         * @param $item
         * @param bool $is_id
         * @return array
         */
        public static function getCategoryByPost($item, $is_id = false)
        {
            if ($is_id) {
                $id = $item;
            } else {
                $id = FlycartWoocommerceProduct::get_id($item['data']);
            }
            $product = FlycartWoocommerceProduct::wc_get_product($id);
            $categories = FlycartWoocommerceProduct::get_category_ids($product);

            return $categories;
        }

        /**
         * To Parsing the Array from String to Int.
         *
         * @param array $array
         */
        public static function toInt(array &$array)
        {
            foreach ($array as $index => $item) {
                $array[$index] = intval($item);
            }
        }

        /**
         * @param $html
         * @return bool|mixed
         */
        static function makeString($html)
        {
            if (is_null($html) || empty($html) || !isset($html)) return false;
            $out = $html;
            // This Process only helps, single level array.
            if (is_array($html)) {
                foreach ($html as $id => $value) {
                    self::escapeCode($value);
                    $html[$id] = $value;
                }
                return $out;
            } else {
                self::escapeCode($html);
                return $html;
            }
        }

        /**
         * Re-Arrange the Index of Array to Make Usable.[2-D Array Only]
         * @param $rules
         */
        public static function reArrangeArray(&$rules)
        {
            $result = array();
            foreach ($rules as $index => $item) {
                foreach ($item as $id => $value) {
                    if(!in_array($id, array('product_variants'))){
                        $result[$id] = $value;
                    }
                }
            }
            $rules = $result;
        }

        /**
         * @param $value
         */
        static function escapeCode(&$value)
        {
            if(is_string($value)){
                // Four Possible tags for PHP to Init.
                $value = preg_replace(array('/^<\?php.*\?\>/', '/^<\%.*\%\>/', '/^<\?.*\?\>/', '/^<\?=.*\?\>/'), '', $value);
                $value = self::delete_all_between('<?php', '?>', $value);
                $value = self::delete_all_between('<?', '?>', $value);
                $value = self::delete_all_between('<?=', '?>', $value);
                $value = self::delete_all_between('<%', '%>', $value);
                $value = str_replace(array('<?php', '<?', '<?=', '<%', '?>'), '', $value);
            }
        }


        /**
         * @param $beginning
         * @param $end
         * @param $string
         * @return mixed
         */
        static function delete_all_between($beginning, $end, $string)
        {

            if (!is_string($string)) return false;

            $beginningPos = strpos($string, $beginning);
            $endPos = strpos($string, $end);
            if ($beginningPos === false || $endPos === false) {
                return $string;
            }

            $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

            return str_replace($textToDelete, '', $string);
        }

        /**
         * To get slider content through curl
         * */
        public static function getSideBarContent(){
            $html = '';
            if(is_callable('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.flycart.org/updates/woo-discount-rules.json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $contents = curl_exec($ch);
                $contents_decode = json_decode($contents);
                if(isset($contents_decode['0']->promo_html)){
                    $html = $contents_decode['0']->promo_html;
                }
            }

            return $html;
        }

        /**
         * Check do the discount rules need to execute
         * */
        public static function doIHaveToRun(){
            $status = true;
            if(is_admin()){
                $status = false;
            }
            if(defined('DOING_AJAX') && DOING_AJAX){
                $status = true;
                $postData = \FlycartInput\FInput::getInstance();
                $action = $postData->get('action', '');
                $form = $postData->get('from', '');
                if($action == 'saveCartRule' || $action == 'savePriceRule'){
                    $status = false;
                } else if(($action == 'UpdateStatus' || $action == 'RemoveRule') && ($form == 'cart-rules' || $form == 'pricing-rules')){
                    $status = false;
                } else if($action == 'saveConfig' && $form == 'settings'){
                    $status = false;
                }
            }
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                $status = false;
            }

            return apply_filters('woo_discount_rules_apply_rules', $status);
        }

        public static function haveToApplyTheRules(){
            $status = true;
            $config = new FlycartWooDiscountBase();
            $do_not_run_while_have_third_party_coupon = $config->getConfigData('do_not_run_while_have_third_party_coupon', 0);
            if($do_not_run_while_have_third_party_coupon == '1'){
                $hasCoupon = self::hasCouponInCart();
                if($hasCoupon){
                    self::resetTheCartValues();
                    $status = false;
                }
            }

            return apply_filters('woo_discount_rules_apply_rules', $status);
        }

        /**
         * Check has coupon - not related to woo discount rules
         * */
        public static function hasCouponInCart(){
            $result = false;
            $cartRules = new FlycartWooDiscountRulesCartRules();
            $coupon_code = $cartRules->getCouponCode();
            $coupon_code = strtolower($coupon_code);
            global $woocommerce;
            if (!empty($woocommerce->cart->applied_coupons)) {
                $appliedCoupons = $woocommerce->cart->applied_coupons;
                if(is_array($appliedCoupons) && count($appliedCoupons) > 0){
                    $used_coupons = self::getUsedCouponsInRules();
                    $used_coupons[] = $coupon_code;
                    $used_coupons = apply_filters('woo_discount_rules_coupons_to_skip_while_apply_third_party_coupon_and_disable_rules', $used_coupons);
                    if(!empty($used_coupons)){
                        $used_coupons = array_map('strtolower', $used_coupons);
                    }
                    foreach ($appliedCoupons as $appliedCoupon){
                        if(!in_array($appliedCoupon, $used_coupons)){
                            $result = true;
                            break;
                        }
                    }
                }
            }

            return $result;
        }
        
        protected static function resetTheCartValues(){
            global $woocommerce;
            // Make sure item exists in cart
            if (!isset($woocommerce->cart)) return;
            if (!isset($woocommerce->cart->cart_contents)) return;
            if(!empty($woocommerce->cart->cart_contents) && count($woocommerce->cart->cart_contents) > 0){
                foreach ($woocommerce->cart->cart_contents as $key => $cart){
                    if(isset($woocommerce->cart->cart_contents[$key]['woo_discount'])){
                        unset($woocommerce->cart->cart_contents[$key]['woo_discount']);
                    }
                }
            }
        }

        /**
         * get Used Coupons
         * */
        public static function getUsedCouponsInRules(){
            $coupons = array();
            $post_args = array('post_type' => array('woo_discount', 'woo_discount_cart'), 'numberposts' => '-1', 'post_status' => 'publish');
            $post_args['meta_key'] = 'used_coupon';
            $post_args['meta_query'] = array(
                array(
                    'key' => 'status',
                    'value' => 'publish',
                )
            );
            $posts = get_posts($post_args);
            if(!empty($posts) && count($posts) > 0){
                foreach ($posts as $post){
                    $used_coupons = get_post_meta($post->ID, 'used_coupon');
                    if(!empty($used_coupons)){
                        $coupons = array_merge($coupons, $used_coupons);
                    }
                }
            }

            return $coupons;
        }

        /**
         * get Used Coupons
         * */
        public static function getDynamicUsedCouponsInRules(){
            $coupons = array();
            $post_args = array('post_type' => array('woo_discount', 'woo_discount_cart'), 'numberposts' => '-1', 'post_status' => 'publish');
            $post_args['meta_key'] = 'dynamic_coupons_to_apply';
            $post_args['meta_query'] = array(
                array(
                    'key' => 'status',
                    'value' => 'publish',
                )
            );
            $posts = get_posts($post_args);
            if(!empty($posts) && count($posts) > 0){
                foreach ($posts as $post){
                    $used_coupons = get_post_meta($post->ID, 'dynamic_coupons_to_apply');
                    if(!empty($used_coupons)){
                        $coupons = array_merge($coupons, $used_coupons);
                    }
                }
            }

            return $coupons;
        }

        /**
         * Get Current date and time based on Wordpress time zone
         *
         * @param string $date
         * @return string
         * */
        public static function getCurrentDateAndTimeBasedOnTimeZone($date = ''){
            if(empty($date)){
                $current_date = new DateTime('now', new DateTimeZone('UTC'));
                $date = $current_date->format('Y-m-d H:i:s');
            }
            $offset = get_option('gmt_offset');
            if(empty($offset)){
                $offset = 0;
            }
            //$time_zone = get_option('timezone_string');
            return date("Y-m-d H:i:s", strtotime($date) + (3600 * $offset) );
        }

        /**
         * Validate the start and end date
         *
         * @param string $date_from
         * @param string $date_to
         * @return boolean
         * */
        public static function validateDateAndTime($date_from, $date_to){
            $valid = true;
            $current_date = self::getCurrentDateAndTimeBasedOnTimeZone();
            if($date_from != ''){
                if(!(strtotime($date_from) <= strtotime($current_date))) $valid = false;
            }
            if($date_to != ''){
                if(!(strtotime($date_to) >= strtotime($current_date))) $valid = false;
            }

            return $valid;
        }

        /**
         * Validate the start and end date
         *
         * @param string $date_from
         * @param string $date_to
         * @return boolean
         * */
        public static function validateDateAndTimeWarningText($date_from, $date_to){
            $string = '';
            $current_date = self::getCurrentDateAndTimeBasedOnTimeZone();
            if($date_from != ''){
                if(!(strtotime($date_from) <= strtotime($current_date))) $string = esc_html__('Start date and time is set in the future date', 'woo-discount-rules');
            }
            if($date_to != ''){
                if(!(strtotime($date_to) >= strtotime($current_date))) $string = esc_html__('Validity expired', 'woo-discount-rules');;
            }

            return $string;
        }

        /**
         * Validate the start and end date
         *
         * @param string $date_from
         * @param string $date_to
         * @return boolean
         * */
        public static function validateDateAndTimeWarningTextForListing($date_from, $date_to){
            $string = '';
            $current_date = self::getCurrentDateAndTimeBasedOnTimeZone();
            if($date_from != ''){
                if(!(strtotime($date_from) <= strtotime($current_date))) $string = esc_html__('Will run in future', 'woo-discount-rules');
            }
            if($date_to != ''){
                if(!(strtotime($date_to) >= strtotime($current_date))) $string = esc_html__('Not running - validity expired', 'woo-discount-rules');;
            }

            return $string;
        }

        /**
         * Validate the start and end date
         *
         * @param string $date_from
         * @param string $date_to
         * @return boolean
         * */
        public static function validateDateAndTimeWarningTextForListingHTML($date_from, $date_to){
            $string = '';
            $validate_date_string = self::validateDateAndTimeWarningTextForListing($date_from, $date_to);
            if(empty($validate_date_string)){
                $string .= " - <span class='wdr_status_active_text text-success'>(".esc_html__('Running', 'woo-discount-rules').")</span>";
            } else {
                $current_date_and_time = self::getCurrentDateAndTimeBasedOnTimeZone();
                $current_date_and_time = date('m/d/Y H:i', strtotime($current_date_and_time));
                $string .= " - <span class='wdr_status_active_text'>(".$validate_date_string.")</span>";
                $string .= "<br><span class='wdr_status_active_text text-warning'><b>".esc_html__('Your server current date and time: ', 'woo-discount-rules')." </b>".$current_date_and_time."</span>";
            }

            return $string;
        }

        /**
         * Reorder the rule if order id already exists
         *
         * @param int $id
         * @param int $order_id
         * @param string $post_type
         * @return int
         * */
        public static function reOrderRuleIfExists($id, $order_id, $post_type){
            $posts = get_posts(array('post_type' => $post_type, 'numberposts' => '-1', 'exclude' => array($id)));
            $greaterId = $alreadyExists = 0;
            if (!empty($posts) && count($posts) > 0) {
                foreach ($posts as $index => $item) {
                    $orderId = get_post_meta($item->ID, 'rule_order', true);
                    if(!empty($orderId)){
                        if((int)$order_id == (int)$orderId){
                            $alreadyExists = 1;
                        }
                        if($orderId > $greaterId){
                            $greaterId = $orderId;
                        }
                    }
                }
            }
            if($alreadyExists){
                $greaterId++;
                return $greaterId;
            }

            return $order_id;
        }

        /**
         * Get all sub categories
         * */
        public static function getAllSubCategories($cat){
            $taxonomies = apply_filters('woo_discount_rules_accepted_taxonomy_for_category', array('product_cat'));
            $category_with_sub_cat = $cat;
            foreach ($taxonomies as $taxonomy){
                $category_with_sub = self::getAllSubCategoriesRecursive($cat, $taxonomy);
                $category_with_sub_cat = array_merge($category_with_sub_cat, $category_with_sub);
            }

            return $category_with_sub_cat;
//            $category_with_sub_cat = $cat;
//            foreach($cat as $c) {
//                $args = array('hierarchical' => 1,
//                    'show_option_none' => '',
//                    'hide_empty' => 0,
//                    'parent' => $c,
//                    'taxonomy' => 'product_cat');
//                $categories = get_categories( $args );
//                foreach($categories as $category) {
//                    //$category_with_sub_cat[] = $category->term_id;
//                    $category_with_sub_cat = array_merge($category_with_sub_cat, self::getAllSubCategories(array($category->term_id)));
//                }
//            }
//            $category_with_sub_cat = array_unique($category_with_sub_cat);
//
//            return $category_with_sub_cat;
        }

        /**
         * Get all sub categories
         * */
        protected static function getAllSubCategoriesRecursive($cat, $taxonomy = 'product_cat'){
            $category_with_sub_cat = $cat;
            foreach($cat as $c) {
                if(isset(self::$sub_categories[$c])){
                    $category_with_sub_cat = array_merge($category_with_sub_cat, self::$sub_categories[$c]);
                } else {
                    $args = array('hierarchical' => 1,
                        'show_option_none' => '',
                        'hide_empty' => 0,
                        'parent' => $c,
                        'taxonomy' => $taxonomy);
                    $categories = get_categories( $args );
                    foreach($categories as $category) {
                        //$category_with_sub_cat[] = $category->term_id;
                        $category_with_sub_cat = array_merge($category_with_sub_cat, self::getAllSubCategoriesRecursive(array($category->term_id), $taxonomy));
                    }

                    self::$sub_categories[$c] = $category_with_sub_cat;
                }
            }
            $category_with_sub_cat = array_unique($category_with_sub_cat);

            return $category_with_sub_cat;
        }

        /**
         * Docs HTML
         * @param string $url
         * @param string $utm_term
         * @return string
         * */
        public static function docsURL($url, $utm_term){
            $url_prefix = 'https://docs.flycart.org/woocommerce-discount-rules/';
            $utm = '?utm_source=woo-discount-rules&utm_campaign=doc&utm_medium=text-click&utm_content=';
            $url = trim($url, '/');

            return $url_prefix.$url.$utm.$utm_term;
        }

        /**
         * Docs HTML
         * @param string $url
         * @param string $utm_term
         * @return string
         * */
        public static function docsDirectURL($url, $utm_term){
            $utm = '?utm_source=woo-discount-rules&utm_campaign=doc&utm_medium=text-click&utm_content=';
            $url = trim($url, '/');

            return $url.$utm.$utm_term;
        }

        /**
         * Docs HTML
         * @param string $url
         * @param string $utm_term
         * @return string
         * */
        public static function docsURLHTML($url, $utm_term, $additional_class = '', $text = ''){
            if(!empty($additional_class)){
                $additional_class = 'wdr_read_doc '.$additional_class;
            } else {
                $additional_class = 'wdr_read_doc';
            }
            if(empty($text)) $text = esc_html__('Read Docs', 'woo-discount-rules');
            $html = '<a class="'.$additional_class.'" href="'.self::docsURL($url, $utm_term).'" target="_blank">'.$text.'</a>';

            return $html;
        }

        /**
         * Docs HTML
         * @param string $url
         * @param string $utm_term
         * @return string
         * */
        public static function docsURLHTMLForDocumentation($url, $utm_term, $text, $description_text = '', $additional_class = ''){
            if(!empty($additional_class)){
                $additional_class = 'wdr_read_documentation '.$additional_class;
            } else {
                $additional_class = 'wdr_read_documentation';
            }
            $html = '<div class="wdr_read_documentation_con">';
            $html .= '<a class="'.$additional_class.'" href="'.self::docsURL($url, $utm_term).'" target="_blank">'.$text.'</a>';
            if(!empty($description_text))
            $html .= '<p class="">'.$description_text.'</p>';
            $html .= '</div>';

            return $html;
        }

        /**
         * Remove Coupon price in cart
         * @param array $coupons
         * */
        public static function removeCouponPriceInCart($coupons = array()){
            $config = new FlycartWooDiscountBase();
            $remove_zero_coupon_price = $config->getConfigData('remove_zero_coupon_price', 1);
            if($remove_zero_coupon_price){
                $has_coupon = false;
                $styles = '';
                foreach ($coupons as $coupon){
                    $wc = WC();
                    if(!empty($wc) && !empty($wc->cart)){
                        if(method_exists($wc->cart, 'has_discount')){
                            if($wc->cart->has_discount($coupon)){
                                $coupon_amount = $wc->cart->get_coupon_discount_amount($coupon);
                                if($coupon_amount == 0){
                                    $has_coupon = true;
                                    $coupon_code_class = esc_attr( sanitize_title( strtolower($coupon) ) );
                                    $style = '.coupon-'.$coupon_code_class.' .amount{display: none}.coupon-'.$coupon_code_class.' td::first-letter{font-size: 0}';
                                    $styles .= apply_filters('woo_discount_rules_apply_style_for_zero_price_coupon', $style, $coupon);
                                }
                            }
                        }
                    }
                }
                if($has_coupon){
                    if(!empty($styles)){
                        global $styles_woo_discount;
                        if(!empty($styles_woo_discount)){
                            $styles_woo_discount .= $styles;
                        } else {
                            $styles_woo_discount = $styles;
                        }
                        $is_ajax = is_ajax();
                        $wc_ajax = isset($_REQUEST['wc-ajax'])? $_REQUEST['wc-ajax']: false;
                        if(!$is_ajax){
                            global $flycart_woo_discount_rules_hide_zero_coupon;
                            $flycart_woo_discount_rules_hide_zero_coupon = "<style>".$styles."</style>";
                            //echo "<style>".$styles."</style>";
                            add_action('woocommerce_before_checkout_form', 'FlycartWooDiscountRulesGeneralHelper::woo_discount_rules_custom_styles');
                            add_action('woocommerce_before_cart', 'FlycartWooDiscountRulesGeneralHelper::woo_discount_rules_custom_styles');
                            add_action('wp_head', 'FlycartWooDiscountRulesGeneralHelper::woo_discount_rules_custom_styles', 100);
                        } else if(in_array($wc_ajax, array('apply_coupon'))){
                            echo "<style>".$styles."</style>";
                        }
                        add_action('woocommerce_before_cart_totals', 'FlycartWooDiscountRulesGeneralHelper::woo_discount_rules_custom_styles');
                        add_action('woocommerce_review_order_before_cart_contents', 'FlycartWooDiscountRulesGeneralHelper::woo_discount_rules_custom_styles');
                    }
                }
            }
        }

        public static function woo_discount_rules_custom_styles(){
            global $styles_woo_discount;
            echo "<style>".$styles_woo_discount."</style>";
        }

        /**
         * Get WPML Language
         * */
        public static function getWPMLLanguage(){
            $wpml_language = '';
            if(defined('WOO_DISCOUNT_AVAILABLE_WPML')){
                if(WOO_DISCOUNT_AVAILABLE_WPML){
                    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                        $wpml_language = ICL_LANGUAGE_CODE;
                    }
                }
            }

            return $wpml_language;
        }

        public static function applyDiscountFromRegularPrice(){
            $config = FlycartWooDiscountBase::get_instance();
            $apply_discount_from = $config->getConfigData('do_discount_from_regular_price', 'sale');
            if($apply_discount_from == 'regular'){
                return true;
            } else {
                return false;
            }
        }

        /**
         * Reset used coupon in post meta
         * */
        public static function resetUsedCoupons($post_id, $coupons_used){
            delete_post_meta($post_id, 'used_coupon');
            if(is_array($coupons_used) && !empty($coupons_used)){
                foreach ($coupons_used as $coupon){
                    add_post_meta($post_id, 'used_coupon', $coupon);
                }
            }
        }

        /**
         * Is countable object or array
         * */
        public static function is_countable($data){
            if((is_array($data) || is_object($data))){
                if(is_object($data)) $data = (array)$data;
                if(count($data)) return true;
            }
            return false;
        }

        /**
         * Is same string
         * */
        public static function is_same_string($first, $second){
            $first = str_replace("&ndash;", '-', $first);
            $first = strip_tags($first);
            $second = str_replace("&ndash;", '-', $second);
            $second = strip_tags($second);
            if($first == $second){
                return true;
            }
            return false;
        }

        /**
         * Get billing email from post data
         *
         * @return string
         * */
        public static function get_billing_email_from_post(){
            $user_email = '';
            $postDataObject = \FlycartInput\FInput::getInstance();
            $postData = $postDataObject->get('post_data', '', 'raw');
            $postDataArray = array();
            if($postData != ''){
                parse_str($postData, $postDataArray);
            }
            $postBillingEmail = $postDataObject->get('billing_email', '', 'raw');
            if($postBillingEmail != ''){
                $postDataArray['billing_email'] = $postBillingEmail;
            }
            if(!get_current_user_id()){
                $order_id = $postDataObject->get('order-received', 0);
                if($order_id){
                    $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                    $postDataArray['billing_email'] = FlycartWoocommerceOrder::get_billing_email($order);
                }
            }
            if(isset($postDataArray['billing_email']) && $postDataArray['billing_email'] != ''){
                $user_email = $postDataArray['billing_email'];
            }
            if(empty($user_email)){
                if(function_exists('WC')){
                    $session = WC()->session;
                    if(!empty($session)){
                        if(method_exists($session, 'get')){
                            $customer = $session->get('customer');
                            if(isset($customer['email']) && !empty($customer['email'])){
                                $user_email = $customer['email'];
                            }
                        }
                    }
                }
            }

            return $user_email;
        }

        /**
         * Add a quantity for product strikeout
         *
         * @return boolean
         * */
        public static function addAQuantityForProductStrikeOut(){
            $config = FlycartWooDiscountBase::get_instance();
            $show_price_discount = $config->getConfigData('show_price_discount_on_product_page', 'show');
            $add = true;
            if($show_price_discount == 'show_after_rule_matches'){
                $add = false;
            }

            return $add;
        }

        /**
         * Show discount on product page
         *
         * @return boolean
         * */
        public static function showDiscountOnProductPage(){
            $config = FlycartWooDiscountBase::get_instance();
            $show_price_discount = $config->getConfigData('show_price_discount_on_product_page', 'show');
            $show = false;
            if(in_array($show_price_discount, array('show', 'show_after_rule_matches', 'show_on_qty_update'))){
                $show = true;
            }

            return $show;
        }

        /**
         * Validate coupon
         *
         * @param string $coupon_name
         * @return array
         * */
        public static function validateDynamicCoupon($coupon_name){
            $result['status'] = false;
            $coupon_name = self::filterDynamicCoupon($coupon_name);
            if(!empty($coupon_name)){
                $coupon_exists = self::checkCouponAlreadyExistsInWooCommerce($coupon_name);
                if($coupon_exists){
                    $result['status'] = false;
                    $result['message'] = esc_html__('Coupon already exists in WooCommerce. Please select another name', 'woo-discount-rules');
                } else {
                    $result['status'] = true;
                }
            }
            $result['coupon'] = $coupon_name;

            return $result;
        }

        /**
         * Validate coupon
         *
         * @param string $coupon_name
         * @return boolean
         * */
        public static function checkCouponAlreadyExistsInWooCommerce($coupon_name){
            $coupon_args = array(
                'name'   => wc_strtolower($coupon_name),
                'post_type'    => 'shop_coupon'
            );
            $posts = get_posts($coupon_args);
            if(!empty($posts) && count($posts) > 0){
                return true;
            }

            return false;
        }

        /**
         * Filter Coupon name
         *
         * @param string $coupon_name
         * @return string
         * */
        public static function filterDynamicCoupon($coupon_name){
            $coupon_name = trim($coupon_name);
            $coupon_name = str_replace(' ', '', $coupon_name);
            $coupon_name = apply_filters('woocommerce_coupon_code', $coupon_name);
            $coupon_name = FlycartWoocommerceProduct::wc_strtoupper($coupon_name);

            return $coupon_name;
        }

        /**
         * Virtually add Coupon to apply the Discount.
         *
         * @param array $response
         * @param mixed $coupon_data
         * @return mixed
         */
        public static function addVirtualCoupon($response, $coupon_data)
        {
            if(is_string($coupon_data)){
                $used_coupon_codes = self::getDynamicUsedCouponsInRules();
                if (in_array(FlycartWoocommerceProduct::wc_strtoupper($coupon_data), $used_coupon_codes)) {
                    $discount_type = 'percent';
                    $amount = 0;

                    $coupon = array(
                        'id' => time() . rand(2, 9),
                        'amount' => $amount,
                        'individual_use' => false,
                        'product_ids' => array(),
                        'exclude_product_ids' => array(),
                        'usage_limit' => '',
                        'usage_limit_per_user' => '',
                        'limit_usage_to_x_items' => '',
                        'usage_count' => '',
                        'expiry_date' => '',
                        'apply_before_tax' => 'yes',
                        'free_shipping' => false,
                        'product_categories' => array(),
                        'exclude_product_categories' => array(),
                        'exclude_sale_items' => false,
                        'minimum_amount' => '',
                        'maximum_amount' => '',
                        'customer_email' => '',
                    );
                    if(FlycartWoocommerceVersion::wcVersion('3.2')) {
                        $coupon['discount_type'] = $discount_type;
                    } else {
                        $coupon['type'] = $discount_type;
                    }
                    return $coupon;
                }
            }

            return $response;
        }

        /**
         * Get promotion messages
         * */
        public static function getPromotionMessages(){
            return FlycartWoocommerceSession::getSession('woo_discount_promotion_messages_cart', array());
        }

        /**
         * Clear promotion messages
         * */
        public static function clearCartPromotionMessages(){
            FlycartWoocommerceSession::setSession('woo_discount_promotion_messages_cart', array());
        }

        /**
         * Set messages
         * */
        public static function setPromotionMessage($message, $rule_id = '', $type = 'cart'){
            $messages = FlycartWoocommerceSession::getSession('woo_discount_promotion_messages_'.$type, array());
            if(!is_array($messages)) $messages = array();
            if(!empty($messages) && in_array($message, $messages)){
            } else {
                if(empty($rule_id)){
                    $messages[] = $message;
                } else {
                    $messages[$rule_id] = $message;
                }
            }

            FlycartWoocommerceSession::setSession('woo_discount_promotion_messages_'.$type, $messages);
        }

        /**
         * Set messages
         * */
        public static function removePromotionMessage($rule_id, $type = 'cart'){
            $messages = FlycartWoocommerceSession::getSession('woo_discount_promotion_messages_'.$type, array());
            if(is_array($messages) && !empty($messages)){
                if(isset($messages[$rule_id])){
                    unset($messages[$rule_id]);
                }
            }

            FlycartWoocommerceSession::setSession('woo_discount_promotion_messages_'.$type, $messages);
        }

        /**
         * Display promotional messages
         * */
        public static function displayPromotionMessages(){
            $messages = self::getPromotionMessages();
            if(!empty($messages) && is_array($messages)){
                foreach ($messages as $message){
                    wc_print_notice($message, "notice");
                }
            }
        }

        /**
         * Display promotional message in check out while processing order review
         * */
        public static function displayPromotionMessagesInCheckout(){
            echo "<div id='wdr_checkout_promotion_messages_data'>";
            self::displayPromotionMessages();
            echo "</div>";
            echo "<script>";
            echo "jQuery('#wdr_checkout_promotion_messages').html(jQuery('#wdr_checkout_promotion_messages_data').html());jQuery('#wdr_checkout_promotion_messages_data').remove()";
            echo "</script>";
        }

        /**
         * Load outer div for displaying promotional message in check out
         * */
        public static function displayPromotionMessagesInCheckoutContainer(){
            echo "<div id='wdr_checkout_promotion_messages'>";
            echo "</div>";
        }

        /**
         * Get calculated item subtotal manually
         *
         * @param $rule array
         * @return int/float
         * */
        public static function get_calculated_item_subtotal_manually($rule = array()){
            global $woocommerce;
            $sub_total = 0;
            if(count($woocommerce->cart->cart_contents)){
                foreach ($woocommerce->cart->cart_contents as $key => $cartItem) {
                    $calculate_sub_total = apply_filters('woo_discount_rules_skip_item_to_calculate_subtotal_for_price_rules', true, $cartItem, $rule);
                    if($calculate_sub_total === true){
                        $sub_total += FlycartWooDiscountRulesCartRules::getSubTotalOfCartItem($cartItem);
                    }
                }
            }

            return $sub_total;
        }

        /**
         * Create nonce for v1
         * @param int $action
         * @return mixed
         */
        public static function createNonce($action = -1){
            return wp_create_nonce($action);
        }

        /**
         * Verify nonce
         * @param $nonce
         * @param int $action
         * @return bool
         */
        protected static function verifyNonce($nonce, $action = -1 ){
            if (wp_verify_nonce($nonce, $action)){
                return true;
            } else {
                return false;
            }
        }

        /**
         * check valid nonce for v1
         * @param $method
         * @param null $wdr_nonce
         * @return bool
         */
        public static function validateRequest($method, $wdr_nonce = null){
            if($wdr_nonce === null){
                if(isset($_REQUEST['wdr_nonce']) && !empty($_REQUEST['wdr_nonce'])){
                    if(self::verifyNonce(wp_unslash($_REQUEST['wdr_nonce']), $method)){
                        return true;
                    }
                }
            } else {
                if(self::verifyNonce(wp_unslash($wdr_nonce), $method)){
                    return true;
                }
            }

            die(__('Invalid token', 'woo-discount-rules'));
        }

        /**
         * Has admin privilage to change rule data
         * @return bool
         */
        public static function hasAdminPrivilege(){
            if (current_user_can( 'manage_woocommerce' )) {
                return true;
            } else {
                return false;
            }
        }
    }
}
