<?php

namespace Wdr\App\Controllers\Admin;

use Wdr\App\Controllers\Admin\Tabs\Compatible;
use Wdr\App\Controllers\Admin\Tabs\DiscountRules;
use Wdr\App\Controllers\Admin\Tabs\Help;
use Wdr\App\Controllers\Admin\Tabs\GeneralSettings;
use Wdr\App\Controllers\Admin\Tabs\ImportExport;
use Wdr\App\Controllers\Admin\Tabs\Recipe;
use Wdr\App\Controllers\Admin\Tabs\Statistics;
use Wdr\App\Controllers\Base;
use Wdr\App\Controllers\OnSaleShortCode;
use Wdr\App\Helpers\Language;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Migration;
use Wdr\App\Helpers\SurveyForm;
use Wdr\App\Controllers\Configuration;

if (!defined('ABSPATH')) exit;

class Settings extends Base
{
    public $tabs;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Initialize admin menu
     */
    function AddMenu()
    {
        if (!is_admin()) return;
        global $submenu;
        if (isset($submenu['woocommerce'])) {
            add_submenu_page(
                'woocommerce',
                __('Woo Discount Rules', 'woo-discount-rules'),
                __('Woo Discount Rules', 'woo-discount-rules'),
                'manage_woocommerce', WDR_SLUG,
                array($this, 'adminTabs')
            );
        }
    }

    /**
     * Show up the survey form
     */
    function setupSurveyForm()
    {
        $survey = new SurveyForm();
        $survey->init('woo-discount-rules', 'Discount Rules for WooCommerce', 'woo-discount-rules');
    }

    /**
     * Add settings link
     * @param $links
     * @return array
     */
    function wdr_action_link($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=woo_discount_rules&tab=settings') . '">' . __('Settings', 'woo-discount-rules') . '</a>',
        );
        return array_merge($action_links, $links);
    }

    /**
     * Create admin tabs and menus
     */
    function adminTabs()
    {
        $id = $this->input->get('id', 0);
        $current_tab = $this->getCurrentTab();
        $tabs = $this->getTabs();
        $page = $this->getPageTask();
        $handler = isset($tabs[$current_tab]) ? $tabs[$current_tab] : $tabs[$this->getDefaultTab()];
        $params = array(
            'tabs' => $tabs,
            'handler' => $handler,
            'page' => $page,
            'current_tab' => $current_tab);
        $params['on_sale_page_rebuild'] = OnSaleShortCode::getOnPageReBuildOption($id);
//        $load_welcome_content = $this->loadWelcomeContent();
        $path = WDR_PLUGIN_PATH . 'App/Views/Admin/Menu.php';
//        if($load_welcome_content === true){
//            $path = WDR_PLUGIN_PATH . 'App/Views/Admin/welcome-text.php';
//        }
        self::$template_helper->setPath($path)->setData($params)->display();

    }

    /**
     * Load welcome content
     * */
    protected function loadWelcomeContent(){
        return false;
    }

    /**
     * get current active tab
     * @return mixed|string
     */
    private function getCurrentTab()
    {
        $get_current_tab = $this->input->get('tab');
        return isset($get_current_tab) ? $get_current_tab : $this->getDefaultTab();
    }

    /**
     * Default tab for admin
     * @return string
     */
    private function getDefaultTab()
    {
        return 'rules';
    }

    /**
     * Get available tabs
     * @return mixed
     */
    private function getTabs()
    {
        // return $this->tabs;
        $tabs = array(
            'rules' => new DiscountRules(),
            'settings' => new GeneralSettings(),
            'statistics' => new Statistics(),
            'compatible' => new Compatible(),
            'importexport' => new ImportExport(),
            'help' => new Help(),
            'recipe' => new Recipe()
        );
        uasort($tabs, function ($tab1, $tab2) {
            $priority1 = (int)isset($tab1->priority) ? $tab1->priority : 1000;
            $priority2 = (int)isset($tab2->priority) ? $tab2->priority : 1000;
            if ($priority1 <= $priority2) {
                return -1;
            } else {
                return 1;
            }
        });
        return $this->tabs = $tabs;
    }

    /**
     * get page action
     * @return mixed|string
     */
    private function getPageTask()
    {
        return $this->input->get('task', '');
    }

    /**
     * display the admin notices if our old plugin found
     */
    function adminNotices()
    {
        if (defined('WOO_DISCOUNT_VERSION')) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . __("We found that your were using our old \"Woo discount rules\" plugin, Please disable it!", 'woo-discount-rules') . '</p></div>';
        }
    }

    /**
     * Add admin scripts
     * @param $hook
     */
    public function adminScripts()
    {
        if ( !isset($_GET['page']) || $_GET['page'] != WDR_SLUG) {
            return;
        }
        /**
         *Enqueue css
         */
        wp_enqueue_style(WDR_SLUG . '-datetimepickercss', WDR_PLUGIN_URL . 'Assets/Css/jquery.datetimepicker.min.css', array(), WDR_VERSION);
        wp_enqueue_style(WDR_SLUG . '-admin', WDR_PLUGIN_URL . 'Assets/Css/admin_style.css', array(), WDR_VERSION);
        wp_enqueue_style(WDR_SLUG . '-jquery-ui-css', WDR_PLUGIN_URL . 'Assets/Js/Jquery-ui/jquery-ui.min.css', array(), WDR_VERSION);
        wp_enqueue_style(WDR_SLUG . '-dragable-ui-css', WDR_PLUGIN_URL . 'Assets/Css/dragtable.css', array(), WDR_VERSION);
        /**
         * Enqueue js
         */
        if(apply_filters('advanced_woo_discount_rules_load_select_js', true)){
            wp_enqueue_script('wdr-select2-js', self::$woocommerce_helper->getWooPluginUrl() . '/assets/js/select2/select2.full.min.js', array('jquery'), WDR_VERSION);
            wp_enqueue_script(WDR_SLUG . '-rulebuilder', WDR_PLUGIN_URL . 'Assets/Js/rulebuilder.js', array('jquery', 'wdr-select2-js', WDR_SLUG . '-datetimepickerjs'), WDR_VERSION);
        } else {
            wp_enqueue_script(WDR_SLUG . '-rulebuilder', WDR_PLUGIN_URL . 'Assets/Js/rulebuilder.js', array('jquery', WDR_SLUG . '-datetimepickerjs'), WDR_VERSION);
        }
        if(version_compare(getAWDRWooVersion(), '3.2.0', '<')){
            wp_enqueue_script('selectWoo', WDR_PLUGIN_URL . 'Assets/Js/selectWoo.full.min.js', array('jquery'), WDR_VERSION);
        }
        wp_enqueue_style('wdr-select2-js', self::$woocommerce_helper->getWooPluginUrl() . '/assets/css/select2.css', array(), WDR_VERSION);
        wp_enqueue_script( 'woocommerce_admin' );
        wp_enqueue_script( 'wc-enhanced-select' );
        //To load woocommerce product select
        wp_enqueue_style( 'woocommerce_admin_styles' );

        wp_enqueue_script(WDR_SLUG . '-jquery-ui', WDR_PLUGIN_URL . 'Assets/Js/Jquery-ui/jquery-ui.min.js', array('jquery'), WDR_VERSION);
        wp_enqueue_script(WDR_SLUG . '-datetimepickerjs', WDR_PLUGIN_URL . 'Assets/Js/jquery.datetimepicker.full.min.js', array('jquery'), WDR_VERSION);
        wp_enqueue_script(WDR_SLUG . '-moment', WDR_PLUGIN_URL . 'Assets/Js/moment.min.js', array('jquery'), WDR_VERSION);
        wp_register_script(WDR_SLUG . '-admin', WDR_PLUGIN_URL . 'Assets/Js/admin_script.js', array(), WDR_VERSION);
        wp_register_script(WDR_SLUG . '-recipe', WDR_PLUGIN_URL . 'Assets/Js/awdr_recipe.js', array(), WDR_VERSION);
        wp_enqueue_script(WDR_SLUG . '-admin');
        wp_enqueue_script(WDR_SLUG . '-recipe');
        wp_enqueue_script(WDR_SLUG . '-dragndraop-js', WDR_PLUGIN_URL . 'Assets/Js/jquery.dragtable.js', array(), WDR_VERSION);

        if ( isset( $_REQUEST['tab'] ) AND $_REQUEST['tab'] == 'statistics' ) {
            wp_enqueue_script( 'google-charts-loader', 'https://www.gstatic.com/charts/loader.js', array(), WDR_VERSION );

            wp_enqueue_script( WDR_SLUG.'-statistics',
                WDR_PLUGIN_URL . 'Assets/Js/admin-statistics.js', array( 'jquery' ), WDR_VERSION );
        }
        $preloaded_lists = array(
            'payment_methods' => $this->getPaymentMethod(),
            'countries' => $this->getCountries(),
            'states' => $this->getStates(),
            'user_roles' => $this->getUserRoles(),
            'weekdays' => $this->getWeekDays(),
            'site_languages' => $this->getSiteLanguages(),
            'order_status' => $this->getWoocommerceOrderStatus(),
            'banner_position' => $this->getBannerPosition(),
        );
        $localization_data = $this->getLocalizationData();
       $conig =  new Configuration();
        $wdr_data = array(
            'labels' => array(
                'select2_no_results' => __('no results', 'woo-discount-rules'),
                'placeholders' => __('Select Values', 'woo-discount-rules'),
                'searching_text' => __('Searching…', 'woo-discount-rules'),
            ),
            'lists' => $preloaded_lists,
            'admin_url' => admin_url('admin.php?page=woo_discount_rules'),
            'localization_data' => $localization_data,
            'enable_subtotal_promo_text' => $conig->getConfig('show_subtotal_promotion', ''),
            'enable_cart_quantity_promo_text' => $conig->getConfig('show_cart_quantity_promotion', ''),
            'rule_id' =>  $this->input->get('task', 'create'),
        );
        wp_localize_script(WDR_SLUG . '-admin', 'wdr_data', $wdr_data);

        //Remove UI Date picker which making conflict in some websites
        wp_dequeue_script( 'jquery-ui-datepicker' );
        wp_deregister_script( 'jquery-ui-datepicker' );
        wp_dequeue_script( 'jquery-datetimepicker' );
        wp_deregister_script( 'jquery-datetimepicker' );
    }

    /**
     * Get Payment Gateway Methods from WC
     * @return array
     */
    public function getPaymentMethod()
    {
        $payment_gateways = self::$woocommerce_helper->getPaymentMethodList();
        $result = array();
        foreach ($payment_gateways as $payment_gateway) {
            $result[] = array(
                'id' => $payment_gateway->id,
                'text' => $payment_gateway->title,
            );
        }
        return array_values($result);
    }

    /**
     * get countries from WC
     * @return array
     */
    public function getCountries()
    {
        $countries = self::$woocommerce_helper->getCountriesList();
        $result = array_map(function ($id, $text) {
            return array(
                'id' => $id,
                'text' => $text,
            );
        }, array_keys($countries), $countries);
        return array_values($result);
    }

    /**
     * get States from WC
     * @return array
     */
    public function getStates()
    {
        $country_states = self::$woocommerce_helper->getStatesList();
        $result = array();
        foreach ($country_states as $states) {
            foreach ($states as $id => $text) {
                $result[] = array(
                    'id' => $id,
                    'text' => $text,
                );
            }
        }
        return $result;
    }

    /**
     * get user roles
     * @return array
     */
    public function getUserRoles()
    {
        $all_roles = self::$woocommerce_helper->getUserRolesList();
        $result = array_map(function ($id, $role) {
            return array(
                'id' => (string)$id,
                'text' => $role['name'],
            );
        }, array_keys($all_roles), $all_roles);
        $result[] = array(
            'id' => 'woo_discount_rules_guest',
            'text' => esc_html__('Guest', 'woo-discount-rules'),
        );
        return array_values($result);
    }

    /**
     * get users nickname
     * @param $ids
     * @return array
     */
    /* public function getUsers($ids)
     {
         $users = get_users(array(
             'fields' => array('ID', 'user_nicename'),
             'include' => $ids,
             'orderby' => 'user_nicename',
         ));
         return array_map(function ($user) {
             return array(
                 'id' => (string)$user->ID,
                 'text' => $user->user_nicename,
             );
         }, $users);
     }*/
    /**
     * Build week days
     * @return array
     */
    public function getWeekDays()
    {
        $result = self::$woocommerce_helper->getWeekDaysList();
        $days_array = array();
        foreach ($result as $day_key => $day) {
            $days_array[] = array(
                'id' => $day_key,
                'text' => $day,
            );
        }
        return $days_array;
    }

    /**
     * Build week days
     * @return array
     */
    public function getBannerPosition()
    {
        $result = self::$woocommerce_helper->getBannerPositionList();
        $banner_position_array = array();
        foreach ($result as $position_key => $position) {
            $banner_position_array[] = array(
                'id' => $position_key,
                'text' => $position,
            );
        }
        return $banner_position_array;
    }

    /**
     * Build week days
     * @return array
     */
    public function getSiteLanguages()
    {
        $language_helper = new Language();
        $available_languages = $language_helper::getAvailableLanguages();
        $processed_languages = array();
        if (!empty($available_languages)) {
            foreach ($available_languages as $key => $lang) {
                $native_name = isset($lang['native_name']) ? $lang['native_name'] : NULL;
                $processed_languages[] = array(
                    'id' => $key,
                    'text' => $native_name,
                );
            }
        } else {
            $default_language = $language_helper->getDefaultLanguage();
            $processed_languages[$default_language] = $language_helper->getLanguageLabel($default_language);
            $processed_languages[] = array(
                'id' => $default_language,
                'text' => $language_helper->getLanguageLabel($default_language),
            );
        }
        return $processed_languages;
    }

    /**
     * get woocommerce order status
     * @return array
     */
    public function getWoocommerceOrderStatus()
    {
        $order_status = self::$woocommerce_helper->getOrderStatusList();
        $result = array_map(function ($id, $status) {
            return array(
                'id' => $id,
                'text' => $status,
            );
        }, array_keys($order_status), $order_status);
        return array_values($result);
    }

    /**
     * Get localisation script
     */
    protected function getLocalizationData()
    {
        return array(
            'disable' => esc_html__('Disable', 'woo-discount-rules'),
            'active' => esc_html__('Active', 'woo-discount-rules'),
            'enable' => esc_html__('Enable', 'woo-discount-rules'),
            'running' => esc_html__(' - (Running)', 'woo-discount-rules'),
            'error' => esc_html__('Oops!! something went wrong!', 'woo-discount-rules'),
            'duplicate_rule' => esc_html__('Rule duplicated successfully!', 'woo-discount-rules'),
            'deleted_rule' => esc_html__('Rule deleted successfully!', 'woo-discount-rules'),
            'delete_confirm' => esc_html__('Are you sure want to delete this rule!', 'woo-discount-rules'),
            'disabled_rule' => esc_html__('Rule disabled successfully!', 'woo-discount-rules'),
            'enabled_rule' => esc_html__('Rule enabled successfully!', 'woo-discount-rules'),
            'save_rule' => esc_html__('Rule saved successfully!', 'woo-discount-rules'),
            'save_settings' => esc_html__('Settings saved successfully!', 'woo-discount-rules'),
            'save_priority' => esc_html__('Rule priority changed successfully!', 'woo-discount-rules'),
            'priority_not_saved' => esc_html__('Rule priority not changed !', 'woo-discount-rules'),
            'chart_data' => esc_html__('No data for this period', 'woo-discount-rules'),
            'coupon_exists' => esc_html__('Oops! Coupon already exists in Woocommerce', 'woo-discount-rules'),
            'copied' => esc_html__('Copied!', 'woo-discount-rules'),
            'copy_shortcode' => esc_html__('Copy ShortCode', 'woo-discount-rules'),
            'recursive_warning' => esc_html__('If you make this recursive other row(s) will be removed!', 'woo-discount-rules'),
            'recursive_qty' => esc_html__('Quantity', 'woo-discount-rules'),
            'recursive_min_qty' => esc_html__('Minimum Quantity', 'woo-discount-rules'),
            'buyx_getx_value' => esc_html__('Discount value', 'woo-discount-rules'),
            'buyx_getx_percentage' => esc_html__('Discount percentage', 'woo-discount-rules'),
            'bogo_buyx_gety_filter_heading' => esc_html__('Filter (Buy)', 'woo-discount-rules'),
            'common_filter_heading' => esc_html__('Filter', 'woo-discount-rules'),
            'common_filter_description' => __('<p>Choose <b>what gets discount</b> (products/categories/attributes/SKU and so on )</p>
<p>Note : You can also exclude products/categories.</p>', 'woo-discount-rules'),
            'common_discount_heading' => esc_html__('Discount', 'woo-discount-rules'),
            'two_column_bulk_discount_heading' => __('Discount - <a href="https://docs.flycart.org/en/articles/3914904-bulk-discounts-tiered-pricing-discounts-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bulk_documentation" style="font-size: 12px;" target="_blank">Read Docs</a>', 'woo-discount-rules'),
            'two_column_set_discount_heading' => __('Discount - <a href="https://docs.flycart.org/en/articles/3809899-bundle-set-discount-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bundle_set" style="font-size: 12px;" target="_blank">Read Docs</a>', 'woo-discount-rules'),
            'two_column_bxgy_discount_heading' => __('Discount - <a href="https://docs.flycart.org/en/articles/3810570-buy-x-get-y-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bxgy" style="font-size: 12px;" target="_blank">Read Docs</a>', 'woo-discount-rules'),
            'two_column_bxgx_discount_heading' => __('Discount - <a href="https://docs.flycart.org/en/articles/3810071-buy-one-get-one-free-buy-x-get-x-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=bxgx" style="font-size: 12px;" target="_blank">Read Docs</a>', 'woo-discount-rules'),
            'common_discount_description' => __('<p>Select discount type and its value (percentage/price/fixed price)</p>', 'woo-discount-rules'),
            'bulk_filter_together_discount_description' => __('<p>Select discount type and its value (percentage/price/fixed price)</p> <div class="awdr-count-by-description"><b>Filter set above :</b><p> This will count the quantities of products set in the “Filter” section.</p>
<p><b>Example:</b> If you selected a few categories there, it will count the quantities of products in those categories added in cart. If you selected a few products in the filters section, then it will count the quantities together.</p>
<p><b>Example:</b> Let’s say, you wanted to offer a Bulk Quantity discount for Category A and chosen Category A in the filters. So when a customer adds 1 quantity each of X, Y and Z from Category A, then the count here is 3.</p></div>', 'woo-discount-rules'),

            'bulk_filter_together_discount_description_tool_tip' => Helper::bogoToolTipDescriptionForFilterTogether(),

            'bulk_individual_product_discount_description' => __('<p>Select discount type and its value (percentage/price/fixed price)</p> <div class="awdr-count-by-description"><b>Individual Product :</b><p>This counts the total quantity of each product / line item separately.</p>
<p><b>Example:</b> If a customer wanted to buy 2 quantities of Product A,  3 quantities of Product B, then count will be maintained at the product level. </p>
<span>2 - count of Product A</span></br>
<span>3 - Count of Product B</span>
<p>In case of variable products, the count will be based on each variant because WooCommerce considers a variant as a product itself.  </p></div>', 'woo-discount-rules'),
            'bulk_individual_product_discount_description_tool_tip' => Helper::bogoToolTipDescriptionForIndividualProduct(),
            'bulk_variants_discount_description' => __('<p>Select discount type and its value (percentage/price/fixed price)</p><div class="awdr-count-by-description"><b>All variants in each product together :</b><p>Useful when applying discounts based on variable products and you want the quantity to be counted based on the parent product.</p>
<p><b>Example:</b>
Say, you have Product A - Small, Medium, Large.
If a customer buys  2 of Product A - Small,  4 of Product A - Medium,  6 of Product A - Large, then the count will be: 6+4+2 = 12
</p></div>', 'woo-discount-rules'),
            'bulk_variants_discount_description_tool_tip' => Helper::bogoToolTipDescriptionForvariants(),
            'common_rules_heading' => esc_html__('Rules (optional)', 'woo-discount-rules'),
            'common_rules_description' => Helper::ruleConditionDescription(),
            'bogo_buyx_gety_filter_description' => __('<p>Choose Buy Products. (products/categories/attributes/tags/sku) Example : For Buy X get Y scenarios, choose X here.</p>', 'woo-discount-rules'),
            'bogo_buyx_getx_filter_description' => __('<p>Choose on which products the discount should be applied (This can be products/categories/SKU)</p>', 'woo-discount-rules'),
            'bogo_buyx_getx_discount_heading' => esc_html__('Get Discount', 'woo-discount-rules'),
            'bogo_buyx_getx_discount_content' => __('<p>Enter the min/max ranges and choose free item quantity.</p><p>Note : Enable recursive checkbox if the discounts should be applied in sequential ranges. </p><p>Example : Buy 1 get 1, Buy 2 get 2, Buy 3 get 3 and so on..</p>', 'woo-discount-rules'),
            'bogo_buyx_gety_discount_heading' => esc_html__('Get Discount', 'woo-discount-rules'),
            'bogo_buyx_gety_discount_content' => __('<p>Choose the adjustment type to which the discount should be applied. You can choose from products/categories/all products.</p><p>Note : Enable recursive checkbox if the discounts should be applied in sequential ranges. </p>', 'woo-discount-rules'),
            'bogo_buyx_gety_discount_content_for_product' => __('<p>Discount will be applied <b>only the selected products (based on mode of apply)</b></p><p>Note : Enable recursive checkbox if the discounts should be applied in sequential ranges. </p>', 'woo-discount-rules'),
            'bogo_buyx_gety_discount_content_for_category' => __('<p>Discount will be applied <b>only the selected categories (based on mode of apply)</b></p><p>Note : Enable recursive checkbox if the discounts should be applied in sequential ranges. </p><p>Example ranges:</p><p>Buy 2, get 1 free (a.k.a: Buy 1 get 1 free)</p><table><tbody><tr><td>Min quantity</td><td>Max quantity</td><td>Free quantity</td></tr><tr><td>2</td><td>3</td><td>1</td></tr></tr><tr><td>4</td><td>5</td><td>2</td></tr></tbody></table>', 'woo-discount-rules'),
            'bogo_buyx_gety_discount_content_for_all' => __('<p>Discount applies on the cheapest/highest priced <b>product IN CART</b>.</p><p>Note : Enable recursive checkbox if the discounts should be applied in sequential ranges. </p><p>Example ranges:</p><p>Buy 2, get 1 free (a.k.a: Buy 1 get 1 free)</p><table><tbody><tr><td>Min quantity</td><td>Max quantity</td><td>Free quantity</td></tr><tr><td>2</td><td>3</td><td>1</td></tr></tr><tr><td>4</td><td>5</td><td>2</td></tr></tbody></table>', 'woo-discount-rules'),
            /*'bogo_buyx_getx_rules_description' => Helper::ruleConditionDescription(),
            'bogo_buyx_gety_rules_description' => Helper::ruleConditionDescription(),*/
            'processing_migration_text' => __('<p>Processing migration, please wait...</p>', 'woo-discount-rules'),
            'processing_migration_success_message' => __('<p>Migration completed.</p>', 'woo-discount-rules'),
            'skip_migration_success_message' => __('<p>Migration skipped.</p>', 'woo-discount-rules'),
            'skip_migration_text' => __('<p>Skipping migration, please wait...</p>', 'woo-discount-rules'),
            'mode_variation_cumulative_example' => __('<span><b>Example:</b> Product A - Small and Product A - Medium will be counted as 2 quantity</span>', 'woo-discount-rules'),
            'filter_all_products' => __('<span>Discount applies to all eligible products in the store</span>', 'woo-discount-rules'),
            'filter_products' => __('<span>Choose products that get the discount using "In List". If you want to exclude a few products, choose "Not In List" and select the products you wanted to exclude from discount. (You can add multiple filters)</span>', 'woo-discount-rules'),
            'filter_Category' => __('<span>Choose categories that get the discount using "In List". If you want to exclude a few categories, choose "Not In List" and select the categories you wanted to exclude from discount. (You can add multiple filters of same type)</span>', 'woo-discount-rules'),
            'filter_Attributes' => __('<span> Choose attributes that get the discount using "In List". If you want to exclude a few attributes, choose "Not In List" and select the attributes you wanted to exclude from discount. (You can add multiple filters of same type)</span>', 'woo-discount-rules'),
            'filter_Tags' => __('<span>Choose tags that get the discount using "In List". If you want to exclude a few tags, choose "Not In List" and select the tags you wanted to exclude from discount. (You can add multiple filters of same type)</span>', 'woo-discount-rules'),
            'filter_SKUs' => __('<span>Choose SKUs that get the discount using "In List". If you want to exclude a few SKUs, choose "Not In List" and select the SKUs you wanted to exclude from discount. (You can add multiple filters of same type)</span>', 'woo-discount-rules'),
            'filter_On_sale_products' => __('<span>Choose whether you want to include (or exclude) products on sale (those having a sale price) for the discount </span>', 'woo-discount-rules'),
            'filter_custom_taxonomies' => __('<span>Discount applies to custom taxonomy</span>', 'woo-discount-rules'),
            'rebuild_on_sale_list_processing_text' => __('Processing please wait..', 'woo-discount-rules'),
            'rebuild_on_sale_list_processed_text' => __('Rebuild index processed', 'woo-discount-rules'),
            'rebuild_on_sale_list_error_please_select_rule' => __('Please select the rules to build index', 'woo-discount-rules'),
            'invalid_file_type' => __("Invalid File. Upload : <b style='color:red;'>.csv</b> File. </br>", 'woo-discount-rules'),
            'invalid_rule_limit' => __("<b>This rule is not running currently:</b> Rule reached maximum usage limit", 'woo-discount-rules'),
            'invalid_rule_limit_with_date_future' => __("<b>This rule is not running currently:</b> Start date and time is set in the future date", 'woo-discount-rules'),
            'invalid_rule_limit_with_date_expire' => __("<b>This rule is not running currently:</b> Validity expired", 'woo-discount-rules'),
            'select_state' => __("Select State", 'woo-discount-rules'),
        );
    }
}