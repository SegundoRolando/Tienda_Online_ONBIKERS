<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
include_once(WOO_DISCOUNT_DIR . '/helper/general-helper.php');
include_once(WOO_DISCOUNT_DIR . '/includes/discount-base.php');

/**
 * Class FlycartWooDiscountRulesCartRules
 */
if (!class_exists('FlycartWooDiscountRulesCartRules')) {
    class FlycartWooDiscountRulesCartRules
    {
        /**
         * @var string
         */
        private $option_name = 'woo_discount_cart_option';

        /**
         * @var string
         */
        public $post_type = 'woo_discount_cart';

        /**
         * @var bool
         */
        public $discount_applied = false;

        /**
         * @var
         */
        private $rules;

        /**
         * @var
         */
        public $rule_sets;

        /**
         * @var array
         */
        public $cart_items;

        /**
         * @var
         */
        public $sub_total;

        /**
         * @var int
         */
        public $discount_total = 0;
        public $product_discount_total = 0;

        public $has_category_in_rule = false;
        public $has_product_specific_rule = false;

        /**
         * @var array
         */
        public $coupon_list;

        /**
         * @var string
         */
        public $coupon_code;

        /**
         * @var
         */
        public $matched_sets;

        public $matched_discounts;

        public $postData;

        public static $rules_loaded = 0;
        public static $cartRules;

        public $has_free_shipping = 0;
        public $has_free_shipping_rule = 0;
        public $bogo_coupon_codes = array();
        public static $applied_coupon = array();

        protected $checked_for_categories_and_product_match = false;
        protected $categories_and_product_match_value = false;

        protected static $added_bogo_product_ids = array();

        /**
         * FlycartWooDiscountRulesCartRules constructor.
         */
        public function __construct()
        {
            global $woocommerce;

            $this->postData = \FlycartInput\FInput::getInstance();
            $this->cart_items = (isset($woocommerce->cart->cart_contents) ? $woocommerce->cart->cart_contents : array());
            $this->calculateCartSubtotal();
            $this->coupon_list = (isset($woocommerce->cart->applied_coupons) ? $woocommerce->cart->applied_coupons : array());

            // Check for Remove Coupon Request.
            if (!is_null($this->postData->get('remove_coupon', null))) $this->removeWoocommerceCoupon($this->postData->get('remove_coupon'));

            // Update Coupon Code
            $this->coupon_code = strtolower($this->getCouponCode());
        }

        /**
         * Save Cart Configs.
         *
         * @param array $request bulk request data.
         * @return bool
         */
        public function save($request)
        {
            $result['status'] = 1;
            $result['message'] = esc_html__('Saved successfully', 'woo-discount-rules');
            foreach ($request as $index => $value) {
                if ($index !== 'discount_rule') {
                    $request[$index] = FlycartWooDiscountRulesGeneralHelper::makeString($value);
                }
            }

            $id = (isset($request['rule_id']) ? $request['rule_id'] : false);

            $id = intval($id);
            if (!$id && $id != 0) return false;
            $title = $request['rule_name'] = (isset($request['rule_name']) ? str_replace('\'', '', $request['rule_name']) : 'New');
            $slug = str_replace(' ', '-', strtolower($title));

            // To Lowercase.
            $slug = strtolower($slug);

            // Encoding String with Space.
            $slug = str_replace(' ', '-', $slug);

            $request['rule_descr'] = (isset($request['rule_descr']) ? str_replace('\'', '', $request['rule_descr']) : '');
            $request['cart_discounted_products'] = (isset($request['cart_discounted_products'])) ? json_encode($request['cart_discounted_products']) : '{}';
            $form = array(
                'rule_name',
                'rule_descr',
                'date_from',
                'date_to',
                'apply_to',
                'discount_type',
                'cart_discounted_products',
                'product_discount_quantity',
                'to_discount',
                'discount_rule',
                'dynamic_coupons_to_apply',
                'rule_order',
                'status',
                'promotion_subtotal_from',
                'promotion_message',
                'wpml_language'
            );
            $coupons_used = array();
            if($request['discount_type'] == 'product_discount'){
                if(!empty($request['cart_discounted_products']) && !empty($request['product_discount_quantity'])){
                    $coupon_code_text = apply_filters('woo_discount_rules_cart_bogo_coupon_code', '{{product_name}} X {{quantity}}');
                    $cart_discounted_products = $request['cart_discounted_products'];
                    if(is_string($request['cart_discounted_products'])){
                        $cart_discounted_products = json_decode($request['cart_discounted_products']);
                    }
                    if(!empty($cart_discounted_products) && is_array($cart_discounted_products)){
                        foreach ($cart_discounted_products as $product_id){
                            $product = FlycartWoocommerceProduct::wc_get_product($product_id);
                            if($product){
                                $product_name = FlycartWoocommerceProduct::get_name($product);
                                $coupons_used[] = self::formatBOGOCouponCode($product_name, $request['product_discount_quantity'], $product, $coupon_code_text);
                            }
                        }
                    }
                }
            }
            $request['dynamic_coupons_to_apply'] = '';
            $coupon_keys = array('coupon_applied_any_one','coupon_applied_all_selected', 'create_dynamic_coupon');
            foreach ($request['discount_rule'] as $index => $value) {
                foreach ($coupon_keys as $coupon_key){
                    if($coupon_key === 'create_dynamic_coupon' && !empty($value['create_dynamic_coupon'])){
                        $validate_dynamic_coupon = FlycartWooDiscountRulesGeneralHelper::validateDynamicCoupon($value[$coupon_key]);
                        $request['dynamic_coupons_to_apply'] = $result['create_dynamic_coupon'] = $validate_dynamic_coupon['coupon'];
                        if($validate_dynamic_coupon['status'] === true){
                            $request['discount_rule'][$index][$coupon_key] = $value[$coupon_key] = $validate_dynamic_coupon['coupon'];
                            $coupons_used[] = $request['dynamic_coupons_to_apply'];
                        } else {
                            $result['status'] = 0;
                            $result['message'] = esc_html__('Failed to save', 'woo-discount-rules');
                            $result['error_message'] = $validate_dynamic_coupon['message'];
                            $result['invalid_field'] = 'create_dynamic_coupon';
                            if(defined('JSON_UNESCAPED_UNICODE')){
                                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                            } else {
                                echo json_encode($result);
                            }
                            die();
                        }
                    } else {
                        if(isset($value[$coupon_key]) && !empty($value[$coupon_key])){
                            if(is_array($value[$coupon_key])){
                                $coupons_used = array_merge($coupons_used, $value[$coupon_key]);
                            }
                        }
                    }

                }
                $request['discount_rule'][$index] = FlycartWooDiscountRulesGeneralHelper::makeString($value);
            }
            if ($id) {
                $post = array(
                    'ID' => $id,
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_content' => 'New Rule',
                    'post_type' => $this->post_type,
                    'post_status' => 'publish'
                );
                wp_update_post($post);
            } else {
                $post = array(
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_content' => 'New Rule',
                    'post_type' => $this->post_type,
                    'post_status' => 'publish'
                );
                $id = wp_insert_post($post);
                $request['status'] = 'publish';
            }
            $request['rule_order'] = FlycartWooDiscountRulesGeneralHelper::reOrderRuleIfExists($id, $request['rule_order'], $this->post_type);

            $product_keys = array('products_in_list','products_not_in_list');
            foreach ($request['discount_rule'] as $index => $value) {
                foreach ($product_keys as $product_key){
                    if(isset($value[$product_key]) && !empty($value[$product_key])){
                        if(is_array($value[$product_key])){
                            $request['discount_rule'][$index]['product_variants'] = FlycartWooDiscountRulesPricingRules::getVariantsOfProducts($value[$product_key]);
                        }
                    }
                }
            }

            if (isset($request['discount_rule'])){
                if(defined('JSON_UNESCAPED_UNICODE')){
                    $request['discount_rule'] = json_encode($request['discount_rule'], JSON_UNESCAPED_UNICODE);
                } else {
                    $request['discount_rule'] = json_encode($request['discount_rule']);
                }

            }

            if (is_null($id) || !isset($id)) return false;
            FlycartWooDiscountRulesGeneralHelper::resetUsedCoupons($id, $coupons_used);
            $request['wpml_language'] = FlycartWooDiscountRulesGeneralHelper::getWPMLLanguage();

            foreach ($request as $index => $value) {
                //$value = sanitize_text_field($value);
                if (in_array($index, $form)) {
                    if (get_post_meta($id, $index)) {
                        update_post_meta($id, $index, $value);
                    } else {
                        add_post_meta($id, $index, $value);
                    }
                }
            }
            if(defined('JSON_UNESCAPED_UNICODE')){
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode($result);
            }
        }

        /**
         * Load View Data.
         *
         * @param $option
         * @param integer $id to load post.
         * @return string mixed response.
         */
        public function view($option, $id)
        {
            $id = intval($id);
            if (!$id) return false;

            $post = get_post($id, 'OBJECT');
            if (isset($post)) {
                if (isset($post->ID)) {
                    $post->meta = get_post_meta($post->ID);
                }
            }
            return $post;
        }

        /**
         * List of Checklist.
         */
        public function checkPoint()
        {
            // Apply rules with products.
            // NOT YET USED.
            if ($this->discount_applied) return true;
        }

        /**
         * Load List of Rules.
         *
         * @return mixed
         */
        public function getRules($onlyCount = false)
        {
            if(self::$rules_loaded) return $this->rules = self::$cartRules;

            $post_args = array('post_type' => $this->post_type, 'numberposts' => '-1');
            $postData = \FlycartInput\FInput::getInstance();
            $request = $postData->getArray();
            if(is_admin() && isset($request['page']) && $request['page'] == 'woo_discount_rules'){
                $post_args['meta_key'] = 'rule_order';
                $post_args['orderby'] = 'meta_value_num';
                $post_args['order'] = 'DESC';
                if(isset($request['order']) && in_array($request['order'], array('asc', 'desc'))){
                    if($request['order'] == 'asc') $post_args['order'] = 'ASC';
                }
            }
            $posts = get_posts($post_args);

            if ($onlyCount) return count($posts);
            if (isset($posts) && count($posts) > 0) {
                $wpml_language = FlycartWooDiscountRulesGeneralHelper::getWPMLLanguage();
                foreach ($posts as $index => $item) {
                    $posts[$index]->meta = get_post_meta($posts[$index]->ID);
                    if(!empty($wpml_language) && $wpml_language != 'all'){
                        if(isset($posts[$index]->meta['wpml_language'])){
                            if(isset($posts[$index]->meta['wpml_language']['0'])){
                                if($posts[$index]->meta['wpml_language']['0'] != $wpml_language && $posts[$index]->meta['wpml_language']['0'] != '' && $posts[$index]->meta['wpml_language']['0'] != 'all') unset($posts[$index]);
                            }
                        }
                    }
                }

                $this->rules = $posts;
            }

            self::$rules_loaded = 1;
            self::$cartRules = $posts;

            return $posts;
        }

        /**
         * To Analyzing the Pricing Rules to Apply the Discount in terms of price.
         */
        public function analyse($woocommerce, $free_shipping_check = 0)
        {
            global $woocommerce;
            if (!FlycartWooDiscountRulesGeneralHelper::haveToApplyTheRules()) return false;
            // Re-arranging the Rules.
            $this->organizeRules();
            // Apply Group of Rules.
            $this->applyRules();
            // Get Overall Discounts.
            $this->getDiscountAmount();

            //run an event
            do_action('woo_discount_rules_after_fetching_discount', $this);
            global $flycart_woo_discount_rules;
            $flycart_woo_discount_rules->cart_rules = $this;

            // Add a Coupon Virtually (Temporary access).
            if(!$free_shipping_check)
                if ($this->discount_total != 0) {
                    add_filter('woocommerce_get_shop_coupon_data', array($this, 'addVirtualCoupon'), 10, 2);
                    add_action('woocommerce_after_calculate_totals', array($this, 'applyFakeCoupons'), 10);
                }
            if($this->product_discount_total) {
                add_action('woocommerce_after_calculate_totals', array($this, 'applyFakeCouponsForBOGO'));
                add_filter('woocommerce_get_shop_coupon_data', array($this, 'addVirtualCouponForBOGO'), 10, 2);
            }
        }

        /**
         * To Make record of discount changes.
         *
         * @return bool
         */
        public function makeLog()
        {
            if (is_null($this->coupon_code) || empty($this->coupon_code)) return false;

            $discount_log = array(
                'coupon_name' => $this->coupon_code,
                'discount' => $this->discount_total,
            );
            WC()->session->set('woo_cart_discount', json_encode($discount_log));
        }

        /**
         * Virtually add Coupon to apply the Discount.
         *
         * @param array $response
         * @param string $coupon_data Existing Coupon
         * @return mixed
         */
        public function addVirtualCoupon($response, $coupon_data)
        {
            $coupon_code = $this->coupon_code;
            // Getting Coupon Remove status from Session.
            if(isset(WC()->session) && is_object(WC()->session) && method_exists(WC()->session, 'get')) {
                $is_removed = WC()->session->get('woo_coupon_removed', '');
                // If Both are same, then it won't added.
                if(!empty($is_removed)){
                    if ($coupon_code == $is_removed) return $response;
                }
            }

            if ($coupon_data == $coupon_code || wc_strtolower($coupon_data) == wc_strtolower($coupon_code)) {

                if ($this->postData->get('remove_coupon', false) == $coupon_code) return false;
                $this->makeLog();
                $discount_type = 'fixed_cart';
                $amount = $this->discount_total;
                if(FlycartWoocommerceVersion::wcVersion('3.2')){
//                    if(!$this->has_category_in_rule){
                        $discount_type = 'percent';
                        //To calculate the percent from total
                        if($this->sub_total > 0) {
                            $amount = ((100 * $this->discount_total) / $this->sub_total);
                        }
//                    }
                }

                $coupon = array(
                    'id' => 321123 . rand(2, 9),
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

            return $response;
        }

        public function addVirtualCouponForBOGO($response, $old_coupon_code)
        {
            $bogo_coupon_codes = $this->bogo_coupon_codes;
            $coupon_codes = array_keys($bogo_coupon_codes);
            // Getting Coupon Remove status from Session.
            if(isset(WC()->session) && is_object(WC()->session) && method_exists(WC()->session, 'get')) {
                $is_removed = WC()->session->get('woo_coupon_removed', '');
                if(!empty($is_removed)){
                    // If Both are same, then it won't added.
                    if (in_array($is_removed, $coupon_codes)) return false;
                }
            }

            if (in_array($old_coupon_code, $coupon_codes) || in_array(wc_strtolower($old_coupon_code), $coupon_codes)) {
                if (in_array($this->postData->get('remove_coupon', false), $coupon_codes)) return false;
                $this->makeLog();
                $discount_type = 'fixed_product';
                $amount = $bogo_coupon_codes[wc_strtolower($old_coupon_code)]['amount'];

                $coupon = array(
                    'id' => 321123 . rand(2, 9),
                    'amount' => $amount,
                    'individual_use' => false,
                    'product_ids' => array($bogo_coupon_codes[wc_strtolower($old_coupon_code)]['product_id']),
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

            return $response;
        }

        /**
         * To Get the Coupon code that already specified.
         *
         * @return string
         */
        public function getCouponCode()
        {
            $config = new FlycartWooDiscountBase();
            $config = $config->getBaseConfig();

            if (is_string($config)) $config = json_decode($config, true);

            // Pre-Defined alternative Coupon Code.
            $coupon = 'Discount';

            // Verify and overwrite the Coupon Code.
            if (isset($config['coupon_name']) && $config['coupon_name'] != '') $coupon = $config['coupon_name'];
            return $coupon;
        }

        /**
         * Apply fake coupon to cart
         *
         * @access public
         * @return void
         */
        public function applyFakeCoupons()
        {
            global $woocommerce;

            // 'newyear' is a temporary coupon for validation.
            $coupon_code = apply_filters('woocommerce_coupon_code', $this->coupon_code);

            // Getting New Instance with the Coupon Code.
            $the_coupon = FlycartWoocommerceCoupon::wc_get_coupon($coupon_code);
            if($the_coupon->is_valid()){
                self::setAppliedCoupon($coupon_code);
            }
            // Validating the Coupon as Valid and discount status.
            if ($the_coupon->is_valid() && !$woocommerce->cart->has_discount($coupon_code)) {

                // Do not apply coupon with individual use coupon already applied
                if ($woocommerce->cart->applied_coupons) {
                    foreach ($woocommerce->cart->applied_coupons as $code) {
                        $coupon = FlycartWoocommerceCoupon::wc_get_coupon($code);
                        if (FlycartWoocommerceCoupon::get_individual_use($coupon) == true) {
                            return false;
                        }
                    }
                }

                // Add coupon
                $woocommerce->cart->applied_coupons[] = $coupon_code;
                $trigger_applied_coupon_before_load_cart = apply_filters('woo_discount_rules_trigger_applied_coupon_before_load_cart', false);
                if($trigger_applied_coupon_before_load_cart){
                    add_action('woocommerce_before_cart', array($this, 'trigger_event_woocommerce_applied_coupon'));
                    add_action('woocommerce_review_order_before_cart_contents', array($this, 'trigger_event_woocommerce_applied_coupon'));
                } else {
                    remove_action('woocommerce_after_calculate_totals', array($this, 'applyFakeCoupons'));
                    do_action('woocommerce_applied_coupon', $coupon_code);
                    add_action('woocommerce_after_calculate_totals', array($this, 'applyFakeCoupons'));
                }

                return true;
            }
        }

        public function trigger_event_woocommerce_applied_coupon(){
            global $woocommerce;
            $coupon_code = apply_filters('woocommerce_coupon_code', $this->coupon_code);
            if(in_array($coupon_code, $woocommerce->cart->applied_coupons)){
                do_action('woocommerce_applied_coupon', $coupon_code);
            }
            if(!empty($this->bogo_coupon_codes)) {
                foreach ($this->bogo_coupon_codes as $coupon_code => $coupon_data) {
                    $coupon_code = apply_filters('woocommerce_coupon_code', $coupon_code);
                    if(in_array($coupon_code, $woocommerce->cart->applied_coupons)){
                        do_action('woocommerce_applied_coupon', $coupon_code);
                    }
                }
            }
        }

        public function applyFakeCouponsForBOGO()
        {
            global $woocommerce;
            if(!empty($this->bogo_coupon_codes)){
                foreach ($this->bogo_coupon_codes as $coupon_code => $coupon_data){
                    // 'newyear' is a temporary coupon for validation.
                    $coupon_code = apply_filters('woocommerce_coupon_code', $coupon_code);
                    // Getting New Instance with the Coupon Code.
                    $the_coupon = FlycartWoocommerceCoupon::wc_get_coupon($coupon_code);
                    if($the_coupon->is_valid()){
                        self::setAppliedCoupon($coupon_code);
                    }
                    // Validating the Coupon as Valid and discount status.
                    if ($the_coupon->is_valid() && !$woocommerce->cart->has_discount($coupon_code)) {

                        // Do not apply coupon with individual use coupon already applied
                        if ($woocommerce->cart->applied_coupons) {
                            foreach ($woocommerce->cart->applied_coupons as $code) {
                                $coupon = FlycartWoocommerceCoupon::wc_get_coupon($code);
                                if (FlycartWoocommerceCoupon::get_individual_use($coupon) == true) {
                                    return false;
                                }
                            }
                        }

                        // Add coupon
                        $woocommerce->cart->applied_coupons[] = $coupon_code;
                        $trigger_applied_coupon_before_load_cart = apply_filters('woo_discount_rules_trigger_applied_coupon_before_load_cart', false);
                        if($trigger_applied_coupon_before_load_cart){
                            add_action('woocommerce_before_cart', array($this, 'trigger_event_woocommerce_applied_coupon'));
                            add_action('woocommerce_review_order_before_cart_contents', array($this, 'trigger_event_woocommerce_applied_coupon'));
                        } else {
                            remove_action('woocommerce_after_calculate_totals', array($this, 'applyFakeCouponsForBOGO'));
                            do_action('woocommerce_applied_coupon', $coupon_code);
                            add_action('woocommerce_after_calculate_totals', array($this, 'applyFakeCouponsForBOGO'));
                        }
                    }
                }
            }
            return true;
        }

        /**
         * Simply remove or reset the virtual coupon by set "empty" as value
         * to "Woo's" session "woo_coupon_removed".
         *
         * @param $coupon
         */
        public function removeWoocommerceCoupon($coupon)
        {
            WC()->session->set('woo_coupon_removed', $coupon);
        }

        /**
         * @return string
         */
        public function woocommerceEnableCoupons()
        {
            return 'true';
        }

        /**
         *
         */
        public function organizeRules()
        {
            // Loads the Rules to Global.
            $this->getRules();
            // Validate and Re-Assign the Rules.
            $this->filterRules();
        }

        /**
         * @return bool
         */
        public function applyRules()
        {
            global $woocommerce;
            // If there is no rules, then return false.
            if (!isset($this->rules)) return false;

            // Check point having list of checklist to apply.
            if ($this->checkPoint()) return false;
            FlycartWooDiscountRulesGeneralHelper::clearCartPromotionMessages();
            // To Generate Valid Rule sets.
            $this->generateRuleSets();
        }

        /**
         *
         */
        public function filterRules()
        {
            $rules = $this->rules;

            if (is_null($rules) || !isset($rules)) return false;
            // Start with empty set.
            $rule_set = array();
            foreach ($rules as $index => $rule) {
                $status = (isset($rule->status) ? $rule->status : false);

                // To Check as Plugin Active - InActive.
                if ($status == 'publish') {
                    $date_from = (isset($rule->date_from) ? $rule->date_from : '');
                    $date_to = (isset($rule->date_to) ? $rule->date_to : '');
                    $validateDate = FlycartWooDiscountRulesGeneralHelper::validateDateAndTime($date_from, $date_to);
                    // Validating Rule with Date of Expiry.
                    if ($validateDate) {
                        // Validating the Rule with its Order ID.
                        if (isset($rule->rule_order)) {
                            $load_rule = apply_filters('woo_discount_rules_run_cart_rule', true, $rule);
                            if($load_rule){
                                // If Order ID is '-', then this rule not going to implement.
                                if ($rule->rule_order !== '-') {
                                    $rule_set[] = $rule;
                                    if(!empty($rule->discount_type)){
                                        if ($rule->discount_type == 'shipping_price') {
                                            $this->has_free_shipping_rule = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $this->rules = $rule_set;

            // To Order the Rules, based on its order ID.
            $this->orderRules();
        }

        /**
         * @return bool
         */
        public function orderRules()
        {
            if (empty($this->rules)) return false;

            $ordered_rules = array();

            // Make associative array with Order ID.
            foreach ($this->rules as $index => $rule) {
                if (isset($rule->rule_order)) {
                    if ($rule->rule_order != '') {
                        $ordered_rules[$rule->rule_order] = $rule;
                    }
                }
            }
            // Order the Rules with it's priority.
            ksort($ordered_rules);

            $this->rules = $ordered_rules;
        }

        /**
         * @return bool
         */
        public function generateRuleSets()
        {
            global $woocommerce;
            $rule_sets = array();

            if (!isset($this->rules)) return false;

            // Loop the Rules set to collect matched rules.
            foreach ($this->rules as $index => $rule) {
                // General Rule Info.
                $rule_sets[$index]['discount_type'] = 'price_discount';
                $rule_sets[$index]['name'] = (isset($rule->rule_name) ? $rule->rule_name : 'Rule_' . $index);
                $rule_sets[$index]['descr'] = (isset($rule->rule_descr) ? $rule->rule_descr : '');
                $rule_sets[$index]['method'] = (isset($rule->rule_method) ? $rule->rule_method : 'qty_based');
                $rule_sets[$index]['qty_based_on'] = (isset($rule->qty_based_on) ? $rule->qty_based_on : 'each_product');
                $rule_sets[$index]['date_from'] = (isset($rule->date_from) ? $rule->date_from : false);
                $rule_sets[$index]['date_to'] = (isset($rule->date_to) ? $rule->date_to : false);
                $rule_sets[$index]['discount_rule'] = (isset($rule->discount_rule) ? $rule->discount_rule : false);
                $rule_sets[$index]['discount_type'] = (isset($rule->discount_type) ? $rule->discount_type : false);
                $rule_sets[$index]['to_discount'] = (isset($rule->to_discount) ? $rule->to_discount : false);
                $rule_sets[$index]['cart_discounted_products'] = isset($rule->cart_discounted_products) ? json_decode($rule->cart_discounted_products) : array();
                $rule_sets[$index]['product_discount_quantity'] = isset($rule->product_discount_quantity) ? $rule->product_discount_quantity : 1;
                $rule_sets[$index]['promotion_qty_from'] = isset($rule->promotion_qty_from) ? $rule->promotion_qty_from : 0;
                $rule_sets[$index]['promotion_subtotal_from'] = isset($rule->promotion_subtotal_from) ? $rule->promotion_subtotal_from : 0;
                $rule_sets[$index]['promotion_message'] = isset($rule->promotion_message) ? $rule->promotion_message : '';
                $rule_sets[$index]['rule_id'] = isset($rule->ID) ? $rule->ID : '';
                $rule_sets[$index]['rule_order_id'] = isset($rule->rule_order) ? $rule->rule_order : '';
                if (in_array($rule->discount_type, array('product_discount'))) {
                    $rule_sets[$index]['enabled'] = $this->validateBOGOCart($rule_sets[$index]['discount_rule'],$rule, $rule_sets[$index]);
                }else{
                    $rule_sets[$index]['enabled'] = $this->validateCart($rule_sets[$index]['discount_rule'], $rule_sets[$index]);
                }
            }
            $rule_sets = apply_filters('woo_discount_rules_cart_rule_sets_to_apply', $rule_sets);
            $this->rule_sets = $rule_sets;
        }

        /**
         * Get Overall discount amount across allover the rules that available.
         *
         * @return integer Total Discount Amount.
         */
        public function getDiscountAmount()
        {
            $discount = 0;
            $discounts = array();
            if (!isset($this->rule_sets)) return false;

            // Get settings
            $config = new FlycartWooDiscountBase();
            $config = $config->getBaseConfig();
            if (is_string($config)) $config = json_decode($config, true);
            if(isset($config['cart_setup'])){
                $cart_setup = $config['cart_setup'];
            } else {
                $cart_setup = 'all';
            }

            if(count($this->rule_sets)){
                if(in_array($cart_setup, array('first', 'all'))){
                    if($cart_setup == 'first'){
                        // Processing the Totals.
                        foreach ($this->rule_sets as $index => $rule) {
                            if ($rule['enabled'] == true) {
                                do_action('woo_discount_rules_before_apply_cart_discount', $rule, $cart_setup);
                                $discounts['name'][$index] = $rule['name'];
                                $discounts['type'][$index] = $rule['discount_type'];
                                //we will have to re-calculate the sub-total if it has category selected
                                $this->has_category_in_rule = $this->is_category_specific($rule);
                                $this->has_product_specific_rule = $this->is_product_specific($rule);
                                if ($rule['discount_type'] == 'shipping_price') {
                                    $this->has_free_shipping = 1;
                                } else if ($rule['discount_type'] == 'price_discount') {
                                    // Getting the Flat Rate of Discount.
                                    $discounts['to_discount'][$index] = $this->calculateDiscount($this->sub_total, array('type' => 'price', 'value' => $rule['to_discount']));
                                } else if($rule['discount_type'] == 'product_discount'){
                                    // Calculate product discount
                                    if(FlycartWooDiscountRulesGeneralHelper::is_countable($rule['cart_discounted_products'])){
                                        $this->calculateProductDiscount($rule['cart_discounted_products'],$rule['product_discount_quantity']);
                                    }
                                } else {
                                    if($this->has_category_in_rule || $this->has_product_specific_rule) {
                                        if(!empty($this->cart_items)){
                                            if(!did_action('woocommerce_before_calculate_totals')){
                                                do_action('woocommerce_before_calculate_totals', FlycartWoocommerceCart::get_cart_object());
                                            }
                                        }
                                        //re-calculate the sub-total
                                        $subtotal = $this->calculate_conditional_subtotal($this->get_discounted_categories_from_json($rule), $rule);
                                    } else {
                                        $subtotal = $this->sub_total;
                                    }
                                    $subtotal = apply_filters('woo_discount_rules_before_calculate_discount_from_subtotal_in_cart', $subtotal, $rule['discount_type'], $rule);
                                    // Getting the Percentage level of Discount.
                                    $discounts['to_discount'][$index] = $this->calculateDiscount($subtotal, array('type' => 'percentage', 'value' => $rule['to_discount']));
                                }
                                if(isset($discounts['to_discount']) && isset($discounts['to_discount'][$index])) {
                                    // Sum of Available discount list.
                                    $discount += $discounts['to_discount'][$index];
                                }
                                // Update the status of the status of the discount rule.
                                $discounts['is_enabled'][$index] = $rule['enabled'];
                                break;
                            }
                        }
                    } else {
                        // Processing the Totals.
                        foreach ($this->rule_sets as $index => $rule) {
                            if ($rule['enabled'] == true) {
                                do_action('woo_discount_rules_before_apply_cart_discount', $rule, $cart_setup);
                                $discounts['name'][$index] = $rule['name'];
                                $discounts['type'][$index] = $rule['discount_type'];
                                //we will have to re-calculate the sub-total if it has category selected
                                $this->has_category_in_rule = $this->is_category_specific($rule);
                                $this->has_product_specific_rule = $this->is_product_specific($rule);
                                if ($rule['discount_type'] == 'shipping_price') {
                                    $this->has_free_shipping = 1;
                                } else if ($rule['discount_type'] == 'price_discount') {
                                    // Getting the Flat Rate of Discount.
                                    $discounts['to_discount'][$index] = $this->calculateDiscount($this->sub_total, array('type' => 'price', 'value' => $rule['to_discount']));
                                } else if($rule['discount_type'] == 'product_discount'){
                                    // Calculate product discount
                                    if(FlycartWooDiscountRulesGeneralHelper::is_countable($rule['cart_discounted_products'])){
                                        $this->calculateProductDiscount($rule['cart_discounted_products'],$rule['product_discount_quantity']);
                                    }
                                } else {
                                    if($this->has_category_in_rule || $this->has_product_specific_rule) {
                                        //re-calculate the sub-total
                                        $subtotal = $this->calculate_conditional_subtotal($this->get_discounted_categories_from_json($rule), $rule);
                                    }else {
                                        $subtotal = $this->sub_total;
                                    }
                                    $subtotal = apply_filters('woo_discount_rules_before_calculate_discount_from_subtotal_in_cart', $subtotal, $rule['discount_type'], $rule);
                                    // Getting the Percentage level of Discount.
                                    $discounts['to_discount'][$index] = $this->calculateDiscount($subtotal, array('type' => 'percentage', 'value' => $rule['to_discount']));
                                }
                                if(isset($discounts['to_discount']) && isset($discounts['to_discount'][$index])){
                                    // Sum of Available discount list.
                                    $discount += $discounts['to_discount'][$index];
                                }

                                // Update the status of the status of the discount rule.
                                $discounts['is_enabled'][$index] = $rule['enabled'];
                            }
                        }
                    }
                } else if($cart_setup == 'biggest'){
                    $biggestDiscount = $newDiscount = 0;
                    // Processing the Totals.
                    foreach ($this->rule_sets as $index => $rule) {
                        if ($rule['enabled'] == true) {
                            do_action('woo_discount_rules_before_apply_cart_discount', $rule, $cart_setup);
                            //we will have to re-calculate the sub-total if it has category selected
                            $this->has_category_in_rule = $this->is_category_specific($rule);
                            $this->has_product_specific_rule = $this->is_product_specific($rule);
                            if ($rule['discount_type'] == 'shipping_price') {
                                $this->has_free_shipping = 1;
                                $newDiscount = 0;
                            } else if ($rule['discount_type'] == 'price_discount') {
                                // Getting the Flat Rate of Discount.
                                $newDiscount = $this->calculateDiscount($this->sub_total, array('type' => 'price', 'value' => $rule['to_discount']));
                            } else if($rule['discount_type'] == 'product_discount'){
                                // Calculate product discount
                                if(FlycartWooDiscountRulesGeneralHelper::is_countable($rule['cart_discounted_products'])){
                                    $this->calculateProductDiscount($rule['cart_discounted_products'],$rule['product_discount_quantity']);
                                }
                            } else {
                                if($this->has_category_in_rule || $this->has_product_specific_rule) {
                                    //re-calculate the sub-total
                                    $subtotal = $this->calculate_conditional_subtotal($this->get_discounted_categories_from_json($rule), $rule);
                                }else {
                                    $subtotal = $this->sub_total;
                                }
                                $subtotal = apply_filters('woo_discount_rules_before_calculate_discount_from_subtotal_in_cart', $subtotal, $rule['discount_type'], $rule);
                                // Getting the Percentage level of Discount.
                                $newDiscount = $this->calculateDiscount($subtotal, array('type' => 'percentage', 'value' => $rule['to_discount']));
                            }
                            if($newDiscount > $biggestDiscount){
                                $biggestDiscount = $newDiscount;
                                $discounts['name'][1] = $rule['name'];
                                $discounts['type'][1] = $rule['discount_type'];
                                $discounts['to_discount'][1] = $newDiscount;
                                $discount = $newDiscount;
                                // Update the status of the status of the discount rule.
                                $discounts['is_enabled'][1] = $rule['enabled'];
                            }
                        }
                    }
                }
            }

            $this->discount_total = $discount;
            $this->matched_discounts = $discounts;
            return $discounts;
        }

        /**
         * Check is specific to category
         * */
        public function is_category_specific($rule) {
            if(count($this->get_discounted_categories_from_json($rule))) {
                return true;
            }
            return false;
        }

        /**
         * Check has product specific
         * */
        public function is_product_specific($rule) {
            $result = false;
            if ( ! empty( $rule['discount_rule'] ) )
            {
                if(!is_object($rule['discount_rule'])) {
                    //assume it is a json string and parse
                    $rules = json_decode($rule['discount_rule'], true);
                }
                if(count($rules)) {
                    foreach($rules as $rule) {
                        if(array_key_exists('products_in_list', $rule)) {
                            $result = true;
                            break;
                        }
                        if(array_key_exists('products_not_in_list', $rule)) {
                            $result = true;
                            break;
                        }
                        if(array_key_exists('exclude_sale_products', $rule)) {
                            $result = true;
                            break;
                        }
                    }
                }
            }

            return $result;
        }

        /**
         * get discount categories from rule
         * */
        public function get_discounted_categories_from_json($rule)
        {
            $categories = array();
            if ( ! empty( $rule['discount_rule'] ) )
            {
                if(!is_object($rule['discount_rule'])) {
                    //assume it is a json string and parse
                    $rules = json_decode($rule['discount_rule'], true);
                }

                if(count($rules)) {
                    foreach($rules as $rule) {
                        if(array_key_exists('exclude_categories', $rule)) {
                            $categories = $rule['exclude_categories'];
                            break;
                        }
                        if(array_key_exists('categories_in', $rule)) {
                            $categories = $rule['categories_in'];
                            break;
                        }
                        if(array_key_exists('in_each_category', $rule)) {
                            $categories = $rule['in_each_category'];
                            break;
                        }
                        if(array_key_exists('atleast_one_including_sub_categories', $rule)) {
                            $categories = FlycartWooDiscountRulesGeneralHelper::getAllSubCategories($rule['atleast_one_including_sub_categories']);
                            break;
                        }
                    }
                }
            }
            return $categories;
        }

        /**
         * Comparing the Rules with the each line item to check
         * and return as, matched or not.
         *
         * @param array $rules
         * @return bool true|false
         */
        public function validateCart($rules, $rule_sets)
        {
            $this->checked_for_categories_and_product_match = false;
            $this->calculateCartSubtotal();
            $rules = (is_string($rules) ? json_decode($rules, true) : array());
            $rules_with_all_data = $rules;
            // Simple array helper to re-arrange the structure.
            FlycartWooDiscountRulesGeneralHelper::reArrangeArray($rules);
            if(is_array($rules) && count($rules)){
                foreach ($rules as $index => $rule) {
                    // Validating the Rules one by one.
                    if ($this->applyRule($index, $rule, $rules, $rules_with_all_data, $rule_sets) == false) {
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Process promotion message
         *
         * @param $rule_sets array
         * @param $rule_value mixed
         * @param $type string
         * */
        public function processPromotionMessage($rule_sets, $rule_value, $type, $additional_data = array()){
            if($type == "subtotal_least"){
                if(isset($rule_sets["promotion_subtotal_from"]) && isset($rule_sets["promotion_message"])){
                    $message = trim($rule_sets["promotion_message"]);
                    if(!empty($message)){
                        $sub_total = $this->sub_total;
                        if(isset($additional_data['subtotal'])){
                            $sub_total = $additional_data['subtotal'];
                        }
                        $promotion_subtotal_from = (float)$rule_sets["promotion_subtotal_from"];
                        if($sub_total >= $promotion_subtotal_from){
                            $difference_amount = $rule_value - $sub_total;
                            if($difference_amount > 0){
                                $difference_amount = FlycartWoocommerceProduct::wc_price($difference_amount);
                                $message = str_replace('{{difference_amount}}', $difference_amount, $message);
                                FlycartWooDiscountRulesGeneralHelper::setPromotionMessage($message, $rule_sets['rule_id']);
                            }
                        }
                    }
                }
            }
        }

        /**
         * Applying bunch amount of rules with the line item.
         *
         * @param string $index Index of the Rule
         * @param array $rule array of rule info.
         * @return bool true|false as matched or not.
         */
        public function applyRule($index, $rule, $rules, $rules_with_all_data, $rule_sets)
        {
            $skipRuleType = array('categories_in', 'exclude_categories', 'in_each_category', 'atleast_one_including_sub_categories', 'products_in_list', 'products_not_in_list', 'exclude_sale_products');
            $availableRuleToSkip = array_intersect($skipRuleType, array_keys($rules));
            switch ($index) {

                // Cart Subtotal.
                case 'subtotal_least':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($this->sub_total < $rule) {
                        $this->processPromotionMessage($rule_sets, $rule, $index);
                        return false;
                    }
                    return true;
                    break;
                case 'subtotal_less':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($this->sub_total >= $rule) {
                        return false;
                    }
                    return true;
                    break;

                // Cart Item Count.
                case 'item_count_least':
                    if(!empty($availableRuleToSkip)){
                    } elseif (count($this->cart_items) < $rule) {
                        return false;
                    }
                    return true;
                    break;
                case 'item_count_less':
                    if(!empty($availableRuleToSkip)){
                    } elseif (count($this->cart_items) >= $rule) {
                        return false;
                    }
                    return true;
                    break;

                // Quantity Count.
                case 'quantity_least':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($this->cartItemQtyTotal() < $rule) {
                        return false;
                    }
                    return true;
                    break;
                case 'quantity_less':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($this->cartItemQtyTotal() >= $rule) {
                        return false;
                    }
                    return true;
                    break;

                // Logged In Users.
                case 'users_in':
                    $rule = FlycartWoocommerceVersion::backwardCompatibilityStringToArray($rule);
                    if (get_current_user_id() == 0 || !in_array(get_current_user_id(), $rule)) {
                        return false;
                    }
                    return true;
                    break;
                case 'shipping_countries_in':
                    $postCalcShippingCountry = $this->postData->get('calc_shipping_country', '');
                    if(!empty($postCalcShippingCountry)){
                        $shippingCountry = $postCalcShippingCountry;
                    }
                    if(empty($shippingCountry)){
                        $shippingCountry = WC()->customer->get_shipping_country();
                    }
                    if (empty($shippingCountry) || !in_array($shippingCountry, $rule)) {
                        return false;
                    }
                    return true;
                    break;
                case 'roles_in':
                    if (count(array_intersect(FlycartWooDiscountRulesGeneralHelper::getCurrentUserRoles(), $rule)) == 0) {
                        return false;
                    }
                    return true;
                    break;
                case ($index == 'customer_email_tld' || $index == 'customer_email_domain'):
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = trim($r);
                            $rule[$key] = trim($rule[$key], '.');
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    $postBillingEmail = $this->postData->get('billing_email', '', 'raw');
                    if($postBillingEmail != ''){
                        $postDataArray['billing_email'] = $postBillingEmail;
                    }
                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['billing_email'] = FlycartWoocommerceOrder::get_billing_email($order);
                        }
                    }
                    if(isset($postDataArray['billing_email']) && $postDataArray['billing_email'] != ''){
                        $user_email = $postDataArray['billing_email'];
                        if(get_current_user_id()){
                            update_user_meta(get_current_user_id(), 'billing_email', $user_email);
                        }
                        if($index == 'customer_email_tld')
                            $tld = $this->getTLDFromEmail($user_email);
                        else
                            $tld = $this->getDomainFromEmail($user_email);
                        if($this->validateTLD($tld, $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $user_email = get_user_meta( get_current_user_id(), 'billing_email', true );
                        if($user_email != '' && !empty($user_email)){
                            if($index == 'customer_email_tld')
                                $tld = $this->getTLDFromEmail($user_email);
                            else
                                $tld = $this->getDomainFromEmail($user_email);
                            if($this->validateTLD($tld, $rule)){
                                return true;
                            }
                        } else {
                            $user_details = get_userdata( get_current_user_id() );
                            if(isset($user_details->data->user_email) && $user_details->data->user_email != ''){
                                $user_email = $user_details->data->user_email;
                                if($index == 'customer_email_tld')
                                    $tld = $this->getTLDFromEmail($user_email);
                                else
                                    $tld = $this->getDomainFromEmail($user_email);
                                if($this->validateTLD($tld, $rule)){
                                    return true;
                                }
                            }
                        }
                    }
                    return false;
                    break;

                case 'customer_billing_city':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    $postBillingEmail = $this->postData->get('billing_city', '', 'raw');
                    if($postBillingEmail != ''){
                        $postDataArray['billing_city'] = $postBillingEmail;
                    }
                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['billing_city'] = FlycartWoocommerceOrder::get_billing_city($order);
                        }
                    }
                    if(isset($postDataArray['billing_city']) && $postDataArray['billing_city'] != ''){
                        $billingCity = $postDataArray['billing_city'];
                        if(in_array(strtolower($billingCity), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $billingCity = get_user_meta( get_current_user_id(), 'billing_city', true );
                        if($billingCity != '' && !empty($billingCity)){
                            if(in_array(strtolower($billingCity), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'customer_shipping_state':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    if(isset($postDataArray['ship_to_different_address']) && $postDataArray['ship_to_different_address']){
                        $shippingFieldName = 'shipping_state';
                    } else {
                        $shippingFieldName = 'billing_state';
                    }
                    $postShippingState = $this->postData->get($shippingFieldName, '', 'raw');
                    if($postShippingState != ''){
                        $postDataArray[$shippingFieldName] = $postShippingState;
                    }

                    $postCalcShippingState = $this->postData->get('calc_shipping_state', '', 'raw');
                    if(!empty($postCalcShippingState)){
                        $postDataArray[$shippingFieldName] = $postCalcShippingState;
                    }

                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['shipping_state'] = FlycartWoocommerceOrder::get_shipping_state($order);
                        }
                    }

                    $customer_from_session = FlycartWoocommerceSession::getSession('customer');
                    if(!empty($customer_from_session)){
                        if(isset($customer_from_session[$shippingFieldName])){
                            $postDataArray[$shippingFieldName] = $customer_from_session[$shippingFieldName];
                        } else if(isset($customer_from_session['shipping_state'])){
                            $postDataArray['shipping_state'] = $customer_from_session['shipping_state'];
                            if(empty($postDataArray[$shippingFieldName])){
                                $postDataArray[$shippingFieldName] = $postDataArray['shipping_state'];
                            }
                        }
                    }

                    if(isset($postDataArray[$shippingFieldName]) && $postDataArray[$shippingFieldName] != ''){
                        $shippingState = $postDataArray[$shippingFieldName];
                        if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $shippingState = get_user_meta( get_current_user_id(), 'shipping_state', true );
                        if($shippingState != '' && !empty($shippingState)){
                            if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'customer_shipping_city':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    if(isset($postDataArray['ship_to_different_address']) && $postDataArray['ship_to_different_address']){
                        $shippingFieldName = 'shipping_city';
                    } else {
                        $shippingFieldName = 'billing_city';
                    }
                    $postShippingState = $this->postData->get($shippingFieldName, '', 'raw');
                    if($postShippingState != ''){
                        $postDataArray[$shippingFieldName] = $postShippingState;
                    }

                    $postCalcShippingCity = $this->postData->get('calc_shipping_city', '', 'raw');
                    if(!empty($postCalcShippingCity)){
                        $postDataArray[$shippingFieldName] = $postCalcShippingCity;
                    }

                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['shipping_city'] = FlycartWoocommerceOrder::get_shipping_city($order);
                        }
                    }

                    $customer_from_session = FlycartWoocommerceSession::getSession('customer');
                    if(!empty($customer_from_session)){
                        if(isset($customer_from_session[$shippingFieldName])){
                            $postDataArray[$shippingFieldName] = $customer_from_session[$shippingFieldName];
                        } else if(isset($customer_from_session['shipping_city'])){
                            $postDataArray['shipping_city'] = $customer_from_session['shipping_city'];
                            if(empty($postDataArray[$shippingFieldName])){
                                $postDataArray[$shippingFieldName] = $postDataArray['shipping_city'];
                            }
                        }
                    }

                    if(isset($postDataArray[$shippingFieldName]) && $postDataArray[$shippingFieldName] != ''){
                        $shippingState = $postDataArray[$shippingFieldName];
                        if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $shippingState = get_user_meta( get_current_user_id(), 'shipping_city', true );
                        if($shippingState != '' && !empty($shippingState)){
                            if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'customer_shipping_zip_code':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    if(isset($postDataArray['ship_to_different_address']) && $postDataArray['ship_to_different_address']){
                        $shippingFieldName = 'shipping_postcode';
                    } else {
                        $shippingFieldName = 'billing_postcode';
                    }
                    $postShippingState = $this->postData->get($shippingFieldName, '', 'raw');
                    if($postShippingState != ''){
                        $postDataArray[$shippingFieldName] = $postShippingState;
                    }
                    $post_calc_shipping_postcode = $this->postData->get('calc_shipping_postcode', '', 'raw');
                    if(!empty($post_calc_shipping_postcode)){
                        $postDataArray[$shippingFieldName] = $post_calc_shipping_postcode;
                    }
                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['shipping_postcode'] = FlycartWoocommerceOrder::get_shipping_city($order);
                        }
                    }

                    $customer_from_session = FlycartWoocommerceSession::getSession('customer');
                    if(!empty($customer_from_session)){
                        if(isset($customer_from_session[$shippingFieldName])){
                            $postDataArray[$shippingFieldName] = $customer_from_session[$shippingFieldName];
                        } else if(isset($customer_from_session['shipping_postcode'])){
                            $postDataArray['shipping_postcode'] = $customer_from_session['shipping_postcode'];
                            if(empty($postDataArray[$shippingFieldName])){
                                $postDataArray[$shippingFieldName] = $postDataArray['shipping_postcode'];
                            }
                        }
                    }

                    if(isset($postDataArray[$shippingFieldName]) && $postDataArray[$shippingFieldName] != ''){
                        $shippingState = $postDataArray[$shippingFieldName];
                        if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $shippingState = get_user_meta( get_current_user_id(), 'shipping_postcode', true );
                        if($shippingState != '' && !empty($shippingState)){
                            if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'products_in_list':
                case 'products_not_in_list':
                case 'exclude_sale_products':
                case 'categories_in':
                case 'exclude_categories':
                case 'atleast_one_including_sub_categories':
                case 'in_each_category':
                    $ruleSuccess = $this->validateCartItemsInSelectedProductsAndCategories($index, $rule, $rules, $rules_with_all_data, $rule_sets);
                    if($ruleSuccess){
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'customer_based_on_first_order':
                case 'customer_based_on_purchase_history':
                case 'customer_based_on_purchase_history_order_count':
                case 'customer_based_on_purchase_history_product_order_count':
                case 'customer_based_on_purchase_history_product_quantity_count':
                    if($index == 'customer_based_on_first_order'){
                        $rule = array();
                    }
                    if($index == 'customer_based_on_first_order' || count($rule)){
                        $ruleSuccess = $this->validateCartItemsBasedOnPurchaseHistory($index, $rule, $rules);
                        if($ruleSuccess){
                            return true;
                        }
                    }
                    return false;
                    break;
                case 'create_dynamic_coupon':
                case 'coupon_applied_any_one':
                    if(!empty($rule)){
                        $ruleSuccess = $this->validateCartCouponAppliedAnyOne($index, $rule, $rules);
                        if($ruleSuccess){
                            if(is_string($rule)){
                            $coupons = explode(',', $rule);
                            } elseif (is_array($rule)){
                                $coupons = $rule;
                            } else {
                                return false;
                            }
                            if($index == 'coupon_applied_any_one') {
                                FlycartWooDiscountRulesGeneralHelper::removeCouponPriceInCart($coupons);
                            }
                            if($index == 'create_dynamic_coupon'){
                                $apply_discount_in_same_coupon = apply_filters('woo_discount_rules_apply_discount_in_same_coupon_code_which_created', true, $rule, $rules);
                                if($apply_discount_in_same_coupon){
                                    $this->coupon_code = $rule;
                                }
                            }
                            return true;
                        }
                    }
                    return false;
                    break;
                case 'coupon_applied_all_selected':
                    if(!empty($rule)){
                        $ruleSuccess = $this->validateCartCouponAppliedAllSelected($index, $rule, $rules);
                        if($ruleSuccess){
                            if(is_string($rule)){
                            $coupons = explode(',', $rule);
                            } elseif (is_array($rule)){
                                $coupons = $rule;
                            } else {
                                return false;
                            }
                            FlycartWooDiscountRulesGeneralHelper::removeCouponPriceInCart($coupons);
                            return true;
                        }
                    }
                    return false;
                    break;
            }

        }

        /**
         * check the any one of the selected coupon applied
         * */
        protected function validateCartCouponAppliedAnyOne($index, $rule, $rules){
            global $woocommerce;
            $allowed = 0;
            if(is_string($rule)){
            $coupons = explode(',', $rule);
            } elseif (is_array($rule)){
                $coupons = $rule;
            } else {
                return 0;
            }
            if(is_array($coupons) && count($coupons)){
                foreach ($coupons as $coupon){
                    if(!empty($coupon)) {
                        if (!empty($woocommerce->cart)) {
                            if (method_exists($woocommerce->cart, 'has_discount')) {
                                if($woocommerce->cart->has_discount($coupon)){
                                    $allowed = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            return $allowed;
        }

        /**
         * check the all the selected coupon applied
         * */
        protected function validateCartCouponAppliedAllSelected($index, $rule, $rules){
            global $woocommerce;
            $allowed = 0;
            if(is_string($rule)){
            $coupons = explode(',', $rule);
            } elseif (is_array($rule)){
                $coupons = $rule;
            } else {
                return 0;
            }
            if(is_array($coupons) && count($coupons)){
                foreach ($coupons as $coupon){
                    if(!empty($coupon)) {
                        if (!empty($woocommerce->cart)) {
                            if (method_exists($woocommerce->cart, 'has_discount')) {
                                if(!$woocommerce->cart->has_discount($coupon)){
                                    $allowed = 0;
                                    break;
                                } else {
                                    $allowed = 1;
                                }
                            }
                        }
                    }

                }
            }

            return $allowed;
        }

        /**
         * check the cart items satisfies purchase history rule
         * */
        protected function validateCartItemsBasedOnPurchaseHistory($index, $rule, $rules){
            $allowed = 0;
            $user = get_current_user_id();
            $email = FlycartWooDiscountRulesGeneralHelper::get_billing_email_from_post();
            if($user || !empty($email)){
                if($index == 'customer_based_on_first_order'){
                    $rule['purchased_history_amount'] = 0;
                    $rule['purchased_history_type'] = 'less_than_or_equal';
                    $rule['purchased_history_duration'] = 'all_time';
                }
                $purchase_history_status_list = isset($rule['purchase_history_order_status'])? $rule['purchase_history_order_status']: array('wc-completed');
                if(isset($rule['purchased_history_amount'])){
                    if($rule['purchased_history_amount'] >= 0){
                        $purchased_history_duration = isset($rule['purchased_history_duration'])? $rule['purchased_history_duration']: 'all_time';
                        $purchased_history_duration_days = isset($rule['purchased_history_duration_days'])? $rule['purchased_history_duration_days']: '';
                        $query = array(
                            'numberposts' => -1,
                            'meta_value'  => $email,
                            'post_type'   => wc_get_order_types(),
                            'post_status' => $purchase_history_status_list,
                        );
                        if($index == 'customer_based_on_first_order'){
                            $query['post_status'] = 'any';
                            $allowed = 1;
                        }
                        if($user){
                            $query['meta_value'] = $user;
                            $query['meta_key'] = '_customer_user';
                        } else {
                            $query['meta_key'] = '_billing_email';
                        }

                        if($purchased_history_duration != 'all_time'){
                            $days = false;
                            if(in_array($purchased_history_duration, array('7_days', '14_days', '30_days', '60_days', '90_days', '180_days'))){
                                $split_days = explode('_', $purchased_history_duration);
                                if(isset($split_days['0'])){
                                    if(((int)$split_days['0']) > 0){
                                        $days = '- '.(int)$split_days['0'].' days';
                                    }
                                }

                            } else if($purchased_history_duration == '1_year'){
                                $days = '- 1 years';
                            } else if($purchased_history_duration == 'custom_days'){
                                if($purchased_history_duration_days > 0 ){
                                    $purchased_history_duration_days = (int)$purchased_history_duration_days;
                                    $days = '- '.$purchased_history_duration_days.' days';
                                }
                            }
                            if($days !== false){
                                $query['date_query'] = array(
                                    'column'  => 'post_date',
                                    'after'   => $days
                                );
                            }
                        }

                        $customerOrders = get_posts( $query );
                        $totalPurchasedAmount = $totalOrder = $totalQuantityPurchased = 0;
                        if(!empty($customerOrders)){
                            foreach ($customerOrders as $customerOrder) {
                                if($index == 'customer_based_on_first_order'){
                                    if(!in_array($customerOrder->post_status, array('wc-failed'))){
                                        return 0;
                                    } else{
                                        continue;
                                    }
                                }
                                $order = FlycartWoocommerceOrder::wc_get_order($customerOrder->ID);
                                $total = FlycartWoocommerceOrder::get_total($order);
                                if(($index == 'customer_based_on_purchase_history_product_order_count' || $index == 'customer_based_on_purchase_history_product_quantity_count') && isset($rule['purchase_history_products'])){
                                    $products = $this->getProductsFromRule($rule['purchase_history_products']);
                                    $product_ids = FlycartWoocommerceOrder::get_product_ids($order);
                                    if(!empty($products)){
                                        if (!count(array_intersect($products, $product_ids)) > 0) {
                                            continue;
                                        }
                                    }
                                    $product_quantities = FlycartWoocommerceOrder::get_product_quantities($order);
                                    if(!empty($product_quantities) && !empty($products)){
                                        foreach ($products as $product_id){
                                            if(isset($product_quantities[$product_id])){
                                                $totalQuantityPurchased += $product_quantities[$product_id];
                                            }
                                        }
                                    }
                                }
                                $totalPurchasedAmount += $total;
                                $totalOrder++;
                            }
                        }

                        $totalAmount = $totalPurchasedAmount;
                        if($index == 'customer_based_on_purchase_history_order_count' || $index == 'customer_based_on_purchase_history_product_order_count'){
                            $totalAmount = $totalOrder;
                        }
                        if($index == 'customer_based_on_purchase_history_product_quantity_count'){
                            $totalAmount = $totalQuantityPurchased;
                        }
                        $purchased_history_type = isset($rule['purchased_history_type'])? $rule['purchased_history_type']: 'atleast';
                        if($purchased_history_type == 'less_than_or_equal'){
                            if($totalAmount <= $rule['purchased_history_amount']){
                                $allowed = 1;
                            }
                        } else {
                            if($totalAmount >= $rule['purchased_history_amount']){
                                $allowed = 1;
                            }
                        }
                    }
                }
            }

            return $allowed;
        }

        /**
         * get product from rule
         * */
        public function getProductsFromRule($product){
            $productInArray = array();
            if(empty($product)) return $productInArray;
            if(is_array($product)) $productInArray = $product;
            else if(is_string($product)){
                $productInArray = json_decode($product);
                $productInArray = FlycartWoocommerceVersion::backwardCompatibilityStringToArray($productInArray);
            }
            if(!is_array($productInArray)){
                $productInArray = array();
            }

            $variants = null;
            $productInArray = apply_filters('woo_discount_rule_products_to_include', $productInArray, array(), $variants);

            return $productInArray;
        }

        /**
         * Check product category matches
         *
         * @param $product object
         * @param $categories array
         * @return boolean
         * */
        protected function checkCategoryMatches($product, $categories){
            $result = false;
            $product_categories = FlycartWoocommerceProduct::get_category_ids($product);
            if(!empty($product_categories)){
                foreach ($product_categories as $cat_id){
                    if(in_array($cat_id, $categories)){
                        $result = true;
                        break;
                    }
                }
            }

            return $result;
        }

        /**
         * Check category found in cart
         *
         * @param $product object
         * @param $categories array
         * @param $rules array
         * @param $rule_sets array
         * @return boolean
         * */
        protected function checkAllCategoryFoundInCart($product, $categories, $rules, $rule_sets, $rules_with_all_data){
            global $woocommerce;
            $result = false;
            $sub_total = $item_count = $quantity = array();
            foreach ($woocommerce->cart->cart_contents as $key => $cartItem) {
                $product_categories = FlycartWoocommerceProduct::get_category_ids($cartItem['data']);
                $allow_discount = $this->checkForProductConditionsMatchesForAnProduct($cartItem['data'], $rules, $rules_with_all_data);
                if($allow_discount){
                    if(!empty($product_categories)){
                        if(is_array($product_categories)){
                            foreach ($categories as $c_key => $cat_id){
                                if(in_array($cat_id, $product_categories)){
                                    $_quantity = (isset($cartItem['quantity']) && $cartItem['quantity']) ? $cartItem['quantity'] : 1;
                                    $_sub_total = self::getSubTotalOfCartItem($cartItem);
                                    $_item_count = 1;

                                    if(isset($sub_total[$cat_id])){
                                        $sub_total[$cat_id] += $_sub_total;
                                    } else {
                                        $sub_total[$cat_id] = $_sub_total;
                                    }

                                    if(isset($item_count[$cat_id])){
                                        $item_count[$cat_id] += $_item_count;
                                    } else {
                                        $item_count[$cat_id] = $_item_count;
                                    }

                                    if(isset($quantity[$cat_id])){
                                        $quantity[$cat_id] += $_quantity;
                                    } else {
                                        $quantity[$cat_id] = $_quantity;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            foreach ($categories as $c_key => $cat_id){
                $specific_cat = true;
                foreach ($rules as $rule_type => $rule_values) {
                    if(!isset($sub_total[$cat_id])) $sub_total[$cat_id] = 0;
                    if(!isset($item_count[$cat_id])) $item_count[$cat_id] = 0;
                    if(!isset($quantity[$cat_id])) $quantity[$cat_id] = 0;
                    $ruleSuccess = $this->checkQtySubTotalConditionsMatches($rule_type, $rule_values, $rule_sets, $sub_total[$cat_id], $item_count[$cat_id], $quantity[$cat_id]);
                    if (!$ruleSuccess) {
                        $specific_cat = $result = false;
                        break;
                    } else {
                        $result = true;
                    }
                }
                if($specific_cat === false){
                    $result = false;
                    break;
                }
            }

            return $result;
        }

        /**
         * Check product matches
         *
         * @param $product object
         * @param $products array
         * @return boolean
         * */
        protected function checkProductMatches($product, $products){
            $result = false;
            $product_id = FlycartWoocommerceProduct::get_id($product);
            if(in_array($product_id, $products)){
                $result = true;
            }

            return $result;
        }

        /**
         * Get variants of selected products
         *
         * @param $discount_type string
         * @param $rules_with_all_data array
         * @return array
         * */
        protected function getVariantsOfSelectedProduct($discount_type, $rules_with_all_data){
            if(!empty($rules_with_all_data)){
                foreach ($rules_with_all_data as $rule){
                    if(isset($rule[$discount_type]) && isset($rule['product_variants'])){
                        if(!empty($rule['product_variants'])){
                            return $rule['product_variants'];
                        }
                    }
                }
            }

            return array();
        }

        /**
         * Check Product matches for a product
         *
         * @param $product object
         * @param $rules array
         * @param $rules_with_all_data array
         * @return boolean
         * */
        protected function checkForProductConditionsMatchesForAnProduct($product, $rules, $rules_with_all_data){
            $allow_discount = true;
            $config = FlycartWooDiscountBase::get_instance();
            $include_variants_on_select_parent_product = $config->getConfigData('include_variants_on_select_parent_product', 0);
            if($allow_discount){
                //check for product in list
                if(isset($rules['products_in_list'])){
                    if(!empty($rules['products_in_list']) && is_array($rules['products_in_list'])){
                        $product_ids = $rules['products_in_list'];
                        if($include_variants_on_select_parent_product){
                            $variant_ids = $this->getVariantsOfSelectedProduct('products_in_list', $rules_with_all_data);
                            if(!empty($variant_ids)){
                                $product_ids = array_merge($product_ids, $variant_ids);
                                $product_ids = array_unique($product_ids);
                            }
                        }
                        if(!empty($product_ids)){
                            $matched = $this->checkProductMatches($product, $product_ids);
                            if(!$matched){
                                $allow_discount = false;
                            }
                        }
                    } else {
                        $allow_discount = false;
                    }
                }
            }

            if($allow_discount){
                //check for product not in list
                if(isset($rules['products_not_in_list'])){
                    if(!empty($rules['products_not_in_list']) && is_array($rules['products_not_in_list'])){
                        $product_ids = $rules['products_not_in_list'];
                        if($include_variants_on_select_parent_product){
                            $variant_ids = $this->getVariantsOfSelectedProduct('products_not_in_list', $rules_with_all_data);
                            if(!empty($variant_ids)){
                                $product_ids = array_merge($product_ids, $variant_ids);
                                $product_ids = array_unique($product_ids);
                            }
                        }
                        if(!empty($product_ids)){
                            $matched = $this->checkProductMatches($product, $product_ids);
                            if($matched){
                                $allow_discount = false;
                            }
                        }
                    }
                }
            }
            if($allow_discount){
                //check for exclude products which are in sale
                if(isset($rules['exclude_sale_products'])){
                    $product_id = FlycartWoocommerceProduct::get_id($product);
                    $original_product = FlycartWoocommerceProduct::wc_get_product($product_id);
                    $is_on_sale = FlycartWoocommerceProduct::is_product_is_on_sale($original_product);
                    if($is_on_sale){
                        $allow_discount = false;
                    }
                }
            }

            return $allow_discount;
        }

        /**
         * Check for Category and product conditions matches for the product
         *
         * @param $product object
         * @param $rules array
         * @param $rules_with_all_data array
         * @param $rule_sets array
         * @return boolean
         * */
        protected function checkForCategoryAndProductMatchesForAnProduct($product, $rules, $rules_with_all_data, $rule_sets){
            $allow_discount = true;
            if(isset($rules['categories_in'])){
                if(!empty($rules['categories_in']) && is_array($rules['categories_in'])){
                    $matched = $this->checkCategoryMatches($product, $rules['categories_in']);
                    if(!$matched){
                        $allow_discount = false;
                    }
                } else {
                    $allow_discount = false;
                }
            }
            if(isset($rules['exclude_categories'])){
                if(!empty($rules['exclude_categories']) && is_array($rules['exclude_categories'])){
                    $matched = $this->checkCategoryMatches($product, $rules['exclude_categories']);
                    if($matched){
                        $allow_discount = false;
                    }
                }
            }
            if($allow_discount){
                if(isset($rules['atleast_one_including_sub_categories'])){
                    if(!empty($rules['atleast_one_including_sub_categories']) && is_array($rules['atleast_one_including_sub_categories'])){
                        $categories = FlycartWooDiscountRulesGeneralHelper::getAllSubCategories($rules['atleast_one_including_sub_categories']);
                        $matched = $this->checkCategoryMatches($product, $categories);
                        if(!$matched){
                            $allow_discount = false;
                        }
                    } else {
                        $allow_discount = false;
                    }
                }
            }

            if($allow_discount){
                if(isset($rules['in_each_category'])){
                    if(!empty($rules['in_each_category']) && is_array($rules['in_each_category'])){
                        $has_found_each_category = $this->checkAllCategoryFoundInCart($product, $rules['in_each_category'], $rules, $rule_sets, $rules_with_all_data);
                        if($has_found_each_category){
                            $found_at_least_one = false;
                            foreach ($rules['in_each_category'] as $category){
                                $matched = $this->checkCategoryMatches($product, array($category));
                                if($matched){
                                    $found_at_least_one = true;
                                    break;
                                }
                            }
                            if($found_at_least_one){
                                $allow_discount = true;
                            } else {
                                $allow_discount = false;
                            }
                        } else {
                            $allow_discount = false;
                        }
                    } else {
                        $allow_discount = false;
                    }
                }
            }

            if($allow_discount){
                $allow_discount = $this->checkForProductConditionsMatchesForAnProduct($product, $rules, $rules_with_all_data);
            }

            return $allow_discount;
        }

        /**
         * verify the cart items are from selected category
         * */
        protected function validateCartItemsInSelectedProductsAndCategories($index, $rule, $rules, $rules_with_all_data, $rule_sets){
            if($this->checked_for_categories_and_product_match){
                return $this->categories_and_product_match_value;
            }
            $ruleSuccess = 0;
            global $woocommerce;
            $allow_discount = $sub_total = $quantity = $item_count = 0;
            if(count($woocommerce->cart->cart_contents)){
                foreach ($woocommerce->cart->cart_contents as $key => $cartItem) {
                    $matches = $this->checkForCategoryAndProductMatchesForAnProduct($cartItem['data'], $rules, $rules_with_all_data, $rule_sets);
                    if($matches){
                        $allow_discount = 1;
                        $cart_item_quantity = (isset($cartItem['quantity']) && $cartItem['quantity']) ? $cartItem['quantity'] : 1;
                        $sub_total += self::getSubTotalOfCartItem($cartItem);
                        $quantity += $cart_item_quantity;
                        $item_count++;
                    }
                }
            }
            if($allow_discount){
                $ruleSuccess = 1;
                $process_condition_check = true;
                if(isset($rules['in_each_category']) && !empty($rules['in_each_category'])){
                    $process_condition_check = false;
                }
                if($process_condition_check){
                    if(is_array($rules) && count($rules)){
                        foreach ($rules as $rule_type => $rule_values){
                            $ruleSuccessResult = $this->checkQtySubTotalConditionsMatches($rule_type, $rule_values, $rule_sets, $sub_total, $item_count, $quantity);
                            if(!$ruleSuccessResult){
                                $ruleSuccess = 0;
                                break;
                            }
                        }
                    }
                }
            }
            $this->categories_and_product_match_value = $ruleSuccess;
            return $ruleSuccess;
        }

        /**
         * Check Quantity, Sub-Total, Item Count matches
         *
         * @param $rule_type string
         * @param $rule_values mixed
         * @param $rule_sets array
         * @param $sub_total float
         * @param $item_count int
         * @param $quantity int
         *
         * @return int
         * */
        protected function checkQtySubTotalConditionsMatches($rule_type, $rule_values, $rule_sets, $sub_total, $item_count, $quantity){
            $ruleSuccess = 1;
            $checkRuleTypes = array('quantity_least', 'quantity_less', 'subtotal_least', 'subtotal_less', 'item_count_least', 'item_count_less');
            if(in_array($rule_type, $checkRuleTypes)){
                if($rule_type == 'subtotal_least'){
                    if ($sub_total < $rule_values) {
                        $this->processPromotionMessage($rule_sets, $rule_values, $rule_type, array('subtotal' => $sub_total));
                        $ruleSuccess = 0;
                    }
                } elseif ($rule_type == 'subtotal_less'){
                    if ($sub_total >= $rule_values) {
                        $ruleSuccess = 0;
                    }
                } elseif ($rule_type == 'item_count_least'){
                    if ($item_count < $rule_values) {
                        $ruleSuccess = 0;
                    }
                } elseif ($rule_type == 'item_count_less'){
                    if ($item_count >= $rule_values) {
                        $ruleSuccess = 0;
                    }
                } elseif ($rule_type == 'quantity_least'){
                    if ($quantity < $rule_values) {
                        $ruleSuccess = 0;
                    }
                } elseif ($rule_type == 'quantity_less'){
                    if ($quantity >= $rule_values) {
                        $ruleSuccess = 0;
                    }
                }
            }

            return $ruleSuccess;
        }

        /**
         * Get tld from email
         * */
        protected function getTLDFromEmail($email){
            $emailArray = explode('@', $email);
            if(isset($emailArray[1])){
                $emailDomainArray = explode('.', $emailArray[1]);
                if(count($emailDomainArray)>1){
                    unset($emailDomainArray[0]);
                }
                if(count($emailDomainArray) > 1){
                    return array(end($emailDomainArray), implode('.', $emailDomainArray));
                } else {
                    return implode('.', $emailDomainArray);
                }
            }
            
            return $emailArray[0];
        }

        /**
         * validate tld from email
         * */
        protected function validateTLD($tlds, $rule){
            if(is_array($tlds)){
                foreach($tlds as $tld){
                    if(in_array($tld, $rule)){
                        return true;
                    }
                }
            } else {
                if(in_array($tlds, $rule)){
                    return true;
                }
            }

            return false;
        }

        /**
         * Get tld from email
         * */
        protected function getDomainFromEmail($email){
            $emailArray = explode('@', $email);
            if(isset($emailArray[1])){
                return $emailArray[1];
            }
            return $emailArray[0];
        }

        /**
         * Get cart total amount
         *
         * @access public
         * @return float
         */
        public function calculateCartSubtotal()
        {
            if(!empty($this->cart_items)){
                $run_before_calculate_event = apply_filters('woo_discount_rules_run_before_calculate_event_on_before_calculate_subtotal_in_cart_rules', true);
                if(!did_action('woocommerce_before_calculate_totals') && $run_before_calculate_event == true){
                    do_action('woocommerce_before_calculate_totals', FlycartWoocommerceCart::get_cart_object());
                }
            }

            $cart_subtotal = 0;
            // Iterate over all cart items and
            if(is_array($this->cart_items) && count($this->cart_items)){
                foreach ($this->cart_items as $cart_item_key => $cart_item) {
                    $cart_subtotal += self::getSubTotalOfCartItem($cart_item);
                }
            }

            $this->sub_total = (float)$cart_subtotal;

        }

        /**
         * Get subtotal of cart item
         *
         * @param $cart_item array
         * @return integer/float
         * */
        public static function getSubTotalOfCartItem($cart_item){
            $subtotal = 0;
            if(get_option('woocommerce_tax_display_cart', 'incl') == 'incl'){
                if(isset($cart_item['line_subtotal'])){
                    $subtotal = $cart_item['line_subtotal'];
                }
                if(isset($cart_item['line_subtotal_tax'])){
                    $subtotal += $cart_item['line_subtotal_tax'];
                }
            } else {
                $quantity = (isset($cart_item['quantity']) && $cart_item['quantity']) ? $cart_item['quantity'] : 1;
                $subtotal = ((float)FlycartWoocommerceProduct::get_price($cart_item['data'], true)) * $quantity;
            }

            return $subtotal;
        }

        /**
         * Get price of cart item
         *
         * @param $cart_item array
         * @return integer/float
         * */
        public function getPriceOfCartItem($cart_item){
            if(get_option('woocommerce_tax_display_cart', 'incl') == 'incl'){
                $price = FlycartWoocommerceProduct::get_price_including_tax($cart_item['data']);
            } else {
                $price = ((float)FlycartWoocommerceProduct::get_price($cart_item['data'], true));
            }

            return $price;
        }

        public function calculate_conditional_subtotal($conditions, $rule) {
            $cart_subtotal = 0;
            $rules = $rules_with_all_data = array();
            if(isset($rule['discount_rule'])){
                $rules = (is_string($rule['discount_rule']) ? json_decode($rule['discount_rule'], true) : array());
                $rules_with_all_data = $rules;
                // Simple array helper to re-arrange the structure.
                FlycartWooDiscountRulesGeneralHelper::reArrangeArray($rules);
            }
            // Iterate over all cart items and
            if(is_array($this->cart_items) && count($this->cart_items)){
                foreach ($this->cart_items as $cart_item_key => $cart_item) {
                    $apply_discount = false;

                    if($this->has_category_in_rule || $this->has_product_specific_rule){
                        $matches = $this->checkForCategoryAndProductMatchesForAnProduct($cart_item['data'], $rules, $rules_with_all_data, $rule);
                        if($matches){
                            $apply_discount = true;
                        }
                    }

                    if($apply_discount) {
                        //total should be specific to the products from certan categories
                        $cart_subtotal += self::getSubTotalOfCartItem($cart_item);
                    }

                }
            }

            return (float)$cart_subtotal;

        }

        public function does_item_belong_to_category($categories, $product) {
            $cat_id = FlycartWoocommerceProduct::get_category_ids($product);
            $result = array_intersect($categories, $cat_id);
            if(is_array($result) && count($result) > 0) {
                return true;
            }
            return false;
        }

        /**
         * To Sum the Cart Item's Qty.
         *
         * @return int Total Qty of Cart.
         */
        public function cartItemQtyTotal()
        {
            global $woocommerce;
            $cart_items = $woocommerce->cart->cart_contents;
            $total_quantity = 0;
            if(is_array($cart_items) && count($cart_items)){
                foreach ($cart_items as $cart_item) {
                    $current_quantity = (isset($cart_item['quantity']) && $cart_item['quantity']) ? $cart_item['quantity'] : 1;
                    $total_quantity += $current_quantity;
                }
            }
            return $total_quantity;
        }

        /**
         * Overall Discount Calculation based on Percentage or Flat.
         *
         * @param integer $sub_total Subtotal of the Cart.
         * @param integer $adjustment percentage or discount of adjustment.
         * @return integer Final Discount Amount.
         */
        public function calculateDiscount($sub_total, $adjustment)
        {
            $sub_total = ($sub_total < 0) ? 0 : $sub_total;

            $discount = 0;

            if ($adjustment['type'] == 'percentage') {
                if(((float)$adjustment['value']) > 0){
                    $discount = $sub_total * ($adjustment['value'] / 100);
                }
            } else if ($adjustment['type'] == 'price') {
                if(((float)$adjustment['value']) > 0){
                    $discount = $adjustment['value'];
                }
            }

            return ($discount <= 0) ? 0 : $discount;
        }

        /**
         * @param array $product_ids - list of discount products from admin settings
         * @param int $discount_quantity - quantity of products to be discount
         * @param string $rule_text - Text to be shown for coupon code
         * @return int
         */
        public function calculateProductDiscount(array $product_ids = array(), $discount_quantity = 1,$rule_text ="")
        {
            $have_to_do = apply_filters('woo_discount_rules_process_cart_bogo_auto_add', true);

            if(!$have_to_do){
                return true;
            }

            if (empty($product_ids))
                return true;
            if(empty($rule_text))
                $rule_text = apply_filters('woo_discount_rules_cart_bogo_coupon_code', '{{product_name}} X {{quantity}}');
            $carts = FlycartWoocommerceCart::get_cart();
            if(empty($carts))
                return true;
            $added_products = array();
            foreach ($carts as $cart_item_key => $cart_item) {
                if (empty($cart_item['data'])) {
                    continue;
                }
                $product_id = FlycartWoocommerceProduct::get_id($cart_item['data']);
                if (isset($added_products[$product_id]) && !empty($added_products[$product_id])) {
                    $added_products[$product_id]['item_quantity'] = $added_products[$product_id]['item_quantity'] + $cart_item['quantity'];
                } else {
                    $added_products[$product_id] = array('product' => $cart_item['data'], 'item_name' => FlycartWoocommerceProduct::get_name($cart_item['data']), 'item_quantity' => $cart_item['quantity'], 'item' => $cart_item_key, 'item_price' => FlycartWoocommerceProduct::get_price($cart_item['data'], true));
                }
            }
            if(is_array($product_ids) && count($product_ids)){
                foreach ($product_ids as $discounted_product_id) {
                    $discounted_price=0;
                    //Check the discounted product already found in cart
                    if (array_key_exists($discounted_product_id, $added_products)) {
                        $old_quantity = isset($added_products[$discounted_product_id]['item_quantity']) ? $added_products[$discounted_product_id]['item_quantity'] : 0;
                        if ($old_quantity < $discount_quantity) {
                            if (isset($added_products[$discounted_product_id]['item']) && !empty($added_products[$discounted_product_id]['item'])) {
                                FlycartWoocommerceCart::set_quantity($added_products[$discounted_product_id]['item'], $discount_quantity);
                            }
                        }
                        $discounted_price = ($discount_quantity * $added_products[$discounted_product_id]['item_price']);
                        $coupon_msg = self::formatBOGOCouponCode($added_products[$discounted_product_id]['item_name'], $discount_quantity, $added_products[$discounted_product_id]['product'], $rule_text);
                        if(isset($added_products[$discounted_product_id]) && isset($added_products[$discounted_product_id]['item_quantity'])){
                            if(!empty($added_products[$discounted_product_id]['item_quantity']) && ((int)$added_products[$discounted_product_id]['item_quantity']) > 0){
                                $discounted_price = $discounted_price/$added_products[$discounted_product_id]['item_quantity'];
                            }
                        }
                        $this->bogo_coupon_codes[$coupon_msg] = array('product_id' => $discounted_product_id, 'amount' => $discounted_price);
                    } else {
                        //If product not in cart,then add to cart
                        $product = FlycartWoocommerceProduct::wc_get_product($discounted_product_id);
                        if($product) {
                            if(!(!empty(self::$added_bogo_product_ids) && in_array($discounted_product_id, self::$added_bogo_product_ids))){
                                $cart_item_key = FlycartWoocommerceCart::add_to_cart($discounted_product_id, $discount_quantity);
                                self::$added_bogo_product_ids[] = $discounted_product_id;
                                global $flycart_woo_discount_rules;
                                add_filter('woo_discount_rules_apply_rules_repeatedly', '__return_true');//Fix: In few cases the strikeout doesn't applies
                                $flycart_woo_discount_rules->discountBase->handlePriceDiscount();
                                if(!empty($cart_item_key)){
                                    $cart_item = FlycartWoocommerceCart::get_cart_item($cart_item_key);
                                    if(!empty($cart_item['data'])){
                                        $product = $cart_item['data'];
                                    }
                                }
                                do_action('woo_discount_rules_cart_rules_after_adding_free_product_to_cart');
                                $discounted_price = ($discount_quantity * FlycartWoocommerceProduct::get_price($product, true));
                                $coupon_msg = self::formatBOGOCouponCode(FlycartWoocommerceProduct::get_name($product), $discount_quantity, $product, $rule_text);
                                if(isset($added_products[$discounted_product_id]) && isset($added_products[$discounted_product_id]['item_quantity'])){
                                    if(!empty($added_products[$discounted_product_id]['item_quantity']) && ((int)$added_products[$discounted_product_id]['item_quantity']) > 0){
                                        $discounted_price = $discounted_price/$added_products[$discounted_product_id]['item_quantity'];
                                    }
                                }
                                $this->bogo_coupon_codes[$coupon_msg] = array('product_id' => $discounted_product_id, 'amount' => $discounted_price);
                            }
                        }
                    }
                    $this->product_discount_total += $discounted_price;
                }
            }

            return true;
        }

        /**
         * Validate for product discount rules
         * @param $conditions
         * @param $rule_set
         * @return bool
         */
        function validateBOGOCart($conditions, $rule_set, $rule_sets){
            $this->checked_for_categories_and_product_match = false;
            $this->calculateCartSubtotal();
            $rules = (is_string($conditions) ? json_decode($conditions, true) : array());
            // Simple array helper to re-arrange the structure.
            $rules_with_all_data = $rules;
            FlycartWooDiscountRulesGeneralHelper::reArrangeArray($rules);
            if(is_array($rules) && count($rules)){
                foreach ($rules as $index => $rule) {
                    // Validating the Rules one by one.
                    if ($this->applyCartBOGORule($index, $rule, $rules, $rule_set, $rules_with_all_data, $rule_sets) == false) {
                        return false;
                    }
                }
            }
            return true;
        }

        /**
         * Rules for only BOGO products
         * @param $index
         * @param $rule
         * @param $rules
         * @param $rule_set
         * @return bool
         */
        function applyCartBOGORule($index, $rule, $rules, $rule_set, $rules_with_all_data, $rule_sets){
            //Calculating subtotal, quantity for BOGO Products
            $cart = array();
            $free_line_item = 0;
            $free_quantity = 0;
            $free_item_price = 0;
            if(is_array($this->cart_items) && count($this->cart_items)){
                foreach ($this->cart_items as $cart_items){
                    $product_id = FlycartWoocommerceProduct::get_id($cart_items['data']);
                    $cart[$product_id]['quantity'] = $cart_items['quantity'];
                    $cart[$product_id]['price'] = $this->getPriceOfCartItem($cart_items);
                    $cart[$product_id]['subtotal'] = self::getSubTotalOfCartItem($cart_items);
                }
            }
            $discounted_products = (isset($rule_set->cart_discounted_products)) ? $rule_set->cart_discounted_products : '[]';
            $products = json_decode($discounted_products);
            $rule_discount_quantity = (isset($rule_set->product_discount_quantity)) ? $rule_set->product_discount_quantity : 0;
            if(FlycartWooDiscountRulesGeneralHelper::is_countable($products)){
                foreach ($products as $discounted_product_id) {
                    if(array_key_exists($discounted_product_id,$cart))
                    {
                        if(isset($cart[$discounted_product_id]['price'])){
                            $free_line_item += 1;
                            $free_quantity += $rule_discount_quantity;
                            $free_item_price += $cart[$discounted_product_id]['price'];
                        }
                    }
                }
            }
            $cart_quantity_except_free = array_sum(array_column($cart,'quantity')) - $free_quantity;
            $cart_subtotal_except_free = array_sum(array_column($cart,'subtotal')) - $free_item_price;
            $cart_line_item_except_free = (count($cart)-$free_line_item);

            $skipRuleType = array('categories_in', 'exclude_categories', 'in_each_category', 'atleast_one_including_sub_categories', 'products_in_list', 'products_not_in_list', 'exclude_sale_products');
            $availableRuleToSkip = array_intersect($skipRuleType, array_keys($rules));
            switch ($index) {
                // Cart Subtotal.
                case 'subtotal_least':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($cart_subtotal_except_free < $rule) {
                        $this->processPromotionMessage($rule_sets, $rule, $index);
                        return false;
                    }
                    return true;
                    break;
                case 'subtotal_less':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($cart_subtotal_except_free >= $rule) {
                        return false;
                    }
                    return true;
                    break;

                // Cart Item Count.
                case 'item_count_least':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($cart_line_item_except_free < $rule) {
                        return false;
                    }
                    return true;
                    break;
                case 'item_count_less':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($cart_line_item_except_free >= $rule) {
                        return false;
                    }
                    return true;
                    break;

                // Quantity Count.
                case 'quantity_least':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($cart_quantity_except_free < $rule) {
                        return false;
                    }
                    return true;
                    break;
                case 'quantity_less':
                    if(!empty($availableRuleToSkip)){
                    } elseif ($cart_quantity_except_free >= $rule) {
                        return false;
                    }
                    return true;
                    break;

                // Logged In Users.
                case 'users_in':
                    $rule = FlycartWoocommerceVersion::backwardCompatibilityStringToArray($rule);
                    if (get_current_user_id() == 0 || !in_array(get_current_user_id(), $rule)) {
                        return false;
                    }
                    return true;
                    break;
                case 'shipping_countries_in':
                    $postCalcShippingCountry = $this->postData->get('calc_shipping_country', '');
                    if(!empty($postCalcShippingCountry)){
                        $shippingCountry = $postCalcShippingCountry;
                    }
                    if(empty($shippingCountry)){
                        $shippingCountry = WC()->customer->get_shipping_country();
                    }
                    if (empty($shippingCountry) || !in_array($shippingCountry, $rule)) {
                        return false;
                    }
                    return true;
                    break;
                case 'roles_in':
                    if (count(array_intersect(FlycartWooDiscountRulesGeneralHelper::getCurrentUserRoles(), $rule)) == 0) {
                        return false;
                    }
                    return true;
                    break;
                case ($index == 'customer_email_tld' || $index == 'customer_email_domain'):
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = trim($r);
                            $rule[$key] = trim($rule[$key], '.');
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    $postBillingEmail = $this->postData->get('billing_email', '', 'raw');
                    if($postBillingEmail != ''){
                        $postDataArray['billing_email'] = $postBillingEmail;
                    }
                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['billing_email'] = FlycartWoocommerceOrder::get_billing_email($order);
                        }
                    }
                    if(isset($postDataArray['billing_email']) && $postDataArray['billing_email'] != ''){
                        $user_email = $postDataArray['billing_email'];
                        if(get_current_user_id()){
                            update_user_meta(get_current_user_id(), 'billing_email', $user_email);
                        }
                        if($index == 'customer_email_tld')
                            $tld = $this->getTLDFromEmail($user_email);
                        else
                            $tld = $this->getDomainFromEmail($user_email);
                        if($this->validateTLD($tld, $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $user_email = get_user_meta( get_current_user_id(), 'billing_email', true );
                        if($user_email != '' && !empty($user_email)){
                            if($index == 'customer_email_tld')
                                $tld = $this->getTLDFromEmail($user_email);
                            else
                                $tld = $this->getDomainFromEmail($user_email);
                            if($this->validateTLD($tld, $rule)){
                                return true;
                            }
                        } else {
                            $user_details = get_userdata( get_current_user_id() );
                            if(isset($user_details->data->user_email) && $user_details->data->user_email != ''){
                                $user_email = $user_details->data->user_email;
                                if($index == 'customer_email_tld')
                                    $tld = $this->getTLDFromEmail($user_email);
                                else
                                    $tld = $this->getDomainFromEmail($user_email);
                                if($this->validateTLD($tld, $rule)){
                                    return true;
                                }
                            }
                        }
                    }
                    return false;
                    break;

                case 'customer_billing_city':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    $postBillingEmail = $this->postData->get('billing_city', '', 'raw');
                    if($postBillingEmail != ''){
                        $postDataArray['billing_city'] = $postBillingEmail;
                    }
                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['billing_city'] = FlycartWoocommerceOrder::get_billing_city($order);
                        }
                    }
                    if(isset($postDataArray['billing_city']) && $postDataArray['billing_city'] != ''){
                        $billingCity = $postDataArray['billing_city'];
                        if(in_array(strtolower($billingCity), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $billingCity = get_user_meta( get_current_user_id(), 'billing_city', true );
                        if($billingCity != '' && !empty($billingCity)){
                            if(in_array(strtolower($billingCity), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'customer_shipping_state':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    if(isset($postDataArray['ship_to_different_address']) && $postDataArray['ship_to_different_address']){
                        $shippingFieldName = 'shipping_state';
                    } else {
                        $shippingFieldName = 'billing_state';
                    }
                    $postShippingState = $this->postData->get($shippingFieldName, '', 'raw');
                    if($postShippingState != ''){
                        $postDataArray[$shippingFieldName] = $postShippingState;
                    }

                    $postCalcShippingState = $this->postData->get('calc_shipping_state', '', 'raw');
                    if(!empty($postCalcShippingState)){
                        $postDataArray[$shippingFieldName] = $postCalcShippingState;
                    }

                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['shipping_state'] = FlycartWoocommerceOrder::get_shipping_state($order);
                        }
                    }

                    $customer_from_session = FlycartWoocommerceSession::getSession('customer');
                    if(!empty($customer_from_session)){
                        if(isset($customer_from_session[$shippingFieldName])){
                            $postDataArray[$shippingFieldName] = $customer_from_session[$shippingFieldName];
                        } else if(isset($customer_from_session['shipping_state'])){
                            $postDataArray['shipping_state'] = $customer_from_session['shipping_state'];
                            if(empty($postDataArray[$shippingFieldName])){
                                $postDataArray[$shippingFieldName] = $postDataArray['shipping_state'];
                            }
                        }
                    }

                    if(isset($postDataArray[$shippingFieldName]) && $postDataArray[$shippingFieldName] != ''){
                        $shippingState = $postDataArray[$shippingFieldName];
                        if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $shippingState = get_user_meta( get_current_user_id(), 'shipping_state', true );
                        if($shippingState != '' && !empty($shippingState)){
                            if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'customer_shipping_city':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    if(isset($postDataArray['ship_to_different_address']) && $postDataArray['ship_to_different_address']){
                        $shippingFieldName = 'shipping_city';
                    } else {
                        $shippingFieldName = 'billing_city';
                    }
                    $postShippingState = $this->postData->get($shippingFieldName, '', 'raw');
                    if($postShippingState != ''){
                        $postDataArray[$shippingFieldName] = $postShippingState;
                    }

                    $postCalcShippingCity = $this->postData->get('calc_shipping_city', '', 'raw');
                    if(!empty($postCalcShippingCity)){
                        $postDataArray[$shippingFieldName] = $postCalcShippingCity;
                    }

                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['shipping_city'] = FlycartWoocommerceOrder::get_shipping_city($order);
                        }
                    }

                    $customer_from_session = FlycartWoocommerceSession::getSession('customer');
                    if(!empty($customer_from_session)){
                        if(isset($customer_from_session[$shippingFieldName])){
                            $postDataArray[$shippingFieldName] = $customer_from_session[$shippingFieldName];
                        } else if(isset($customer_from_session['shipping_city'])){
                            $postDataArray['shipping_city'] = $customer_from_session['shipping_city'];
                            if(empty($postDataArray[$shippingFieldName])){
                                $postDataArray[$shippingFieldName] = $postDataArray['shipping_city'];
                            }
                        }
                    }

                    if(isset($postDataArray[$shippingFieldName]) && $postDataArray[$shippingFieldName] != ''){
                        $shippingState = $postDataArray[$shippingFieldName];
                        if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $shippingState = get_user_meta( get_current_user_id(), 'shipping_city', true );
                        if($shippingState != '' && !empty($shippingState)){
                            if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'customer_shipping_zip_code':
                    $rule = explode(',', $rule);
                    if(is_array($rule) && count($rule)){
                        foreach($rule as $key => $r){
                            $rule[$key] = strtolower(trim($r));
                        }
                    }
                    $postData = $this->postData->get('post_data', '', 'raw');
                    $postDataArray = array();
                    if($postData != ''){
                        parse_str($postData, $postDataArray);
                    }
                    if(isset($postDataArray['ship_to_different_address']) && $postDataArray['ship_to_different_address']){
                        $shippingFieldName = 'shipping_postcode';
                    } else {
                        $shippingFieldName = 'billing_postcode';
                    }
                    $postShippingState = $this->postData->get($shippingFieldName, '', 'raw');
                    if($postShippingState != ''){
                        $postDataArray[$shippingFieldName] = $postShippingState;
                    }
                    $post_calc_shipping_postcode = $this->postData->get('calc_shipping_postcode', '', 'raw');
                    if(!empty($post_calc_shipping_postcode)){
                        $postDataArray[$shippingFieldName] = $post_calc_shipping_postcode;
                    }
                    if(!get_current_user_id()){
                        $order_id = $this->postData->get('order-received', 0);
                        if($order_id){
                            $order = FlycartWoocommerceOrder::wc_get_order($order_id);
                            $postDataArray['shipping_postcode'] = FlycartWoocommerceOrder::get_shipping_city($order);
                        }
                    }

                    $customer_from_session = FlycartWoocommerceSession::getSession('customer');
                    if(!empty($customer_from_session)){
                        if(isset($customer_from_session[$shippingFieldName])){
                            $postDataArray[$shippingFieldName] = $customer_from_session[$shippingFieldName];
                        } else if(isset($customer_from_session['shipping_postcode'])){
                            $postDataArray['shipping_postcode'] = $customer_from_session['shipping_postcode'];
                            if(empty($postDataArray[$shippingFieldName])){
                                $postDataArray[$shippingFieldName] = $postDataArray['shipping_postcode'];
                            }
                        }
                    }

                    if(isset($postDataArray[$shippingFieldName]) && $postDataArray[$shippingFieldName] != ''){
                        $shippingState = $postDataArray[$shippingFieldName];
                        if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                            return true;
                        }
                    } else if(get_current_user_id()){
                        $shippingState = get_user_meta( get_current_user_id(), 'shipping_postcode', true );
                        if($shippingState != '' && !empty($shippingState)){
                            if(in_array(strtolower($shippingState), $rule) || in_array(strtoupper($shippingState), $rule)){
                                return true;
                            }
                        }
                    }
                    return false;
                    break;
                case 'products_in_list':
                case 'products_not_in_list':
                case 'exclude_sale_products':
                case 'categories_in':
                case 'exclude_categories':
                case 'atleast_one_including_sub_categories':
                case 'in_each_category':
                    $ruleSuccess = $this->validateCartItemsInSelectedProductsAndCategories($index, $rule, $rules, $rules_with_all_data, $rule_sets);
                    if($ruleSuccess){
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'customer_based_on_first_order':
                case 'customer_based_on_purchase_history':
                case 'customer_based_on_purchase_history_order_count':
                case 'customer_based_on_purchase_history_product_order_count':
                case 'customer_based_on_purchase_history_product_quantity_count':
                    if($index == 'customer_based_on_first_order'){
                        $rule = array();
                    }
                    if($index == 'customer_based_on_first_order' || count($rule)){
                        $ruleSuccess = $this->validateCartItemsBasedOnPurchaseHistory($index, $rule, $rules);
                        if($ruleSuccess){
                            return true;
                        }
                    }
                    return false;
                    break;
                case 'create_dynamic_coupon':
                case 'coupon_applied_any_one':
                    if(!empty($rule)){
                        $ruleSuccess = $this->validateCartCouponAppliedAnyOne($index, $rule, $rules);
                        if($ruleSuccess){
                            if(is_string($rule)){
                                $coupons = explode(',', $rule);
                            } elseif (is_array($rule)){
                                $coupons = $rule;
                            } else {
                                return false;
                            }

                            FlycartWooDiscountRulesGeneralHelper::removeCouponPriceInCart($coupons);
                            return true;
                        }
                    }
                    return false;
                    break;
                case 'coupon_applied_all_selected':
                    if(!empty($rule)){
                        $ruleSuccess = $this->validateCartCouponAppliedAllSelected($index, $rule, $rules);
                        if($ruleSuccess){
                            if(is_string($rule)){
                                $coupons = explode(',', $rule);
                            } elseif (is_array($rule)){
                                $coupons = $rule;
                            } else {
                                return false;
                            }
                            FlycartWooDiscountRulesGeneralHelper::removeCouponPriceInCart($coupons);
                            return true;
                        }
                    }
                    return false;
                    break;
            }
        }

        /**
         * Set coupon applied
         * */
        protected static function setAppliedCoupon($coupon){
            if(!in_array($coupon, self::$applied_coupon)){
                self::$applied_coupon[] = $coupon;
            }
        }

        /**
         * get applied coupon
         * */
        public static function getAppliedCoupons(){
            return self::$applied_coupon;
        }

        /**
         * Format BOGO discount code
         *
         * @param $product_name string
         * @param $quantity integer
         * @param $product mixed
         * @param $coupon_code string
         * @return string
         * */
        public static function formatBOGOCouponCode($product_name, $quantity, $product, $coupon_code){
            $code = str_replace(array('{{product_name}}', '{{quantity}}'), array($product_name, $quantity), $coupon_code);
            $code = self::filterCouponCode($code);
            $code = wc_strtolower($code);
            $code = apply_filters('woo_discount_rules_cart_bogo_coupon_code_based_on_product', $code, $product, $quantity);

            return $code;
        }

        /**
         * Filter discount code
         *
         * @param $code string
         * @return string
         * */
        public static function filterCouponCode($code){
            $code = str_replace(array("'", '"'), array('', ''), $code);
            $code = wc_format_coupon_code($code);
            return $code;
        }
    }
}