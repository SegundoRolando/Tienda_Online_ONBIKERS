<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('wp_ajax_awdr_auto_install_pro_plugin', function (){
    if (current_user_can( 'manage_woocommerce' )) {
        FlycartWooDiscountRulesExistingPROUpdater::installProPlugin();
        exit;
    } else {
        die(__('Authentication required', 'woo-discount-rules'));
    }
});
add_action('wp_ajax_awdr_switch_version', function (){
    $version = isset($_REQUEST['version'])? $_REQUEST['version']: '';
    $page = isset($_REQUEST['page'])? $_REQUEST['page']: '';
    $wdr_nonce = isset($_REQUEST['wdr_nonce'])? $_REQUEST['wdr_nonce']: '';
    $return['status'] = false;
    $return['message'] = esc_html__('Invalid request', 'woo-discount-rules');
    if($version == "v1"){
        \Wdr\App\Helpers\Helper::validateRequest('wdr_ajax_switch_version', $wdr_nonce);
    } else {
        FlycartWooDiscountRulesGeneralHelper::validateRequest('wdr_ajax_switch_version', $wdr_nonce);
    }
    if (current_user_can( 'manage_woocommerce' )) {
        if($version !== '' && $page !== ''){
            $url = admin_url('admin.php?page=' . $page . '&awdr_switch_plugin_to=' . $version);
            $do_switch = true;
            if (!isAWDREnvironmentCompatible()) {
                $return['message'] = __('Discount Rules 2.0 requires minimum PHP version of ', 'woo-discount-rules') . ' ' . WDR_REQUIRED_PHP_VERSION;
                wp_send_json_success($return);
            }
            if (!isAWDRWooCompatible()) {
                $return['message'] = __('Discount Rules 2.0 requires at least Woocommerce', 'woo-discount-rules') . ' ' . WDR_WC_REQUIRED_VERSION;
                wp_send_json_success($return);
            }
            if (defined('WDR_BACKWARD_COMPATIBLE')) {
                if(WDR_BACKWARD_COMPATIBLE == true){
                    if ($version == "v2") {
                        if (!defined('WDR_PRO')) {
                            $do_switch = false;
                        }
                    }
                }
            }
            if($do_switch){
                if(in_array($version, array('v1', 'v2'))){
                    update_option('advanced_woo_discount_rules_load_version', $version);
                }
                $return['status'] = true;
                $return['message'] = '';
                $return['url'] = $url;
            } else {
                $has_auto_update = false;
                if (!is_multisite()) {
                    if(class_exists('FlycartWooDiscountRulesExistingPROUpdater')){
                        if(FlycartWooDiscountRulesExistingPROUpdater::availableAutoInstall()){
                            $has_auto_update = true;
                        }
                    }
                }
                if($has_auto_update){
                    $return['type'] = 'auto_install';
                    $message = __('<p>Since 2.0, you need BOTH Core and Pro (2.0) packages installed and activated.</p>', 'woo-discount-rules');
                    $message .= __('<p><b>Why we made this change?</b></p>', 'woo-discount-rules');
                    $message .= __('<p>This arrangement is to avoid the confusion in the installation and upgrade process. Many users first install the core free version. Then purchase the PRO version and try to install it over the free version. Since both free and pro packages have same names, wordpress asks them to uninstall free and then install pro. As you can see, this is quite confusing for the end users.</p>', 'woo-discount-rules');
                    $message .= __('<p>As a result, starting from 2.0, we now have two packs: 1. Core 2. PRO.</p>', 'woo-discount-rules');
                    $message .= '<p><button type="button" class="awdr_auto_install_pro_plugin btn btn-info">'.__('Download and Install', 'woo-discount-rules').'</button></p>';
                    $return['message'] = $message;
                } else {
                    $return['message'] = __('Since 2.0, you need BOTH Core and Pro (2.0) packages installed and activated.  Please download the Pro 2.0 pack from My Downloads page in our site, install and activate it. <a href="https://docs.flycart.org/en/articles/4006520-switching-to-2-0-from-v1-x-versions?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=switch_to_v2" target="_blank">Here is a guide and video tutorial</a>', 'woo-discount-rules');
                    $return['type'] = 'manual_install';
                }
            }
        }
    }

    wp_send_json_success($return);
});


/**
 * Action sto show the toggle button
 */
add_action('advanced_woo_discount_rules_on_settings_head', function () {
    $has_switch = true;
    $page = NULL;
    if (isset($_GET['page'])) {
        $page = sanitize_text_field($_GET['page']);
    }
    global $awdr_load_version;
    $version = ($awdr_load_version == "v1") ? "v2" : "v1";
    $url = admin_url('admin.php?page=' . $page . '&awdr_switch_plugin_to=' . $version);
    $message = __('Switch to Discount Rules V2  which comes with a better UI and advanced rules. (You can switch back any time. Your settings and rules in V1 are  kept as is)', 'woo-discount-rules');
    $button_text = __("Switch to v2", 'woo-discount-rules');
    if($version == "v1"){
        $has_switch = \Wdr\App\Helpers\Migration::hasSwitchBackOption();
        $message = __('Would you like to switch to older Woo Discount Rules?', 'woo-discount-rules');
        $button_text = __("Click here to Switch back", 'woo-discount-rules');
    }
    if($has_switch){
        if($version == "v1"){
            $nounce = \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_switch_version');
        } else {
            $nounce = FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_ajax_switch_version');
        }
        echo '<div style="background: #fff;padding: 20px;font-size: 13px;font-weight: bold;">' . $message . ' <button class="btn btn-info awdr-switch-version-button" data-version="' . $version . '" data-page="'.$page.'" data-nonce="'.$nounce.'">' . $button_text . '</button></div>';
        echo "<div class='wdr_switch_message' style='color:#a00;font-weight: bold;'></div>";
        echo '<div class="modal" id="wdr_switch_popup">
                    <div class="modal-sandbox"></div>
                    <div class="modal-box">
                        <div class="modal-header">
                            <div class="close-modal"><span class="wdr-close-modal-box">&#10006;</span></div>
                            <h1 class="wdr-modal-header-title">'.__("Install 2.0 Pro package", 'woo-discount-rules').'</h1>
                        </div>
                        <div class="modal-body">
                            <div class=\'wdr_pro_install_message\'></div>
                        </div>
                    </div>
                </div>';
    }
});

add_action('advanced_woo_discount_rules_content_next_to_tabs', function () {
    $has_switch = true;
    $page = NULL;
    if (isset($_GET['page'])) {
        $page = sanitize_text_field($_GET['page']);
    }
    global $awdr_load_version;
    $version = ($awdr_load_version == "v1") ? "v2" : "v1";
    if($version == "v1"){
        $has_switch = \Wdr\App\Helpers\Migration::hasSwitchBackOption();
    }
    if($version == "v1"){
        $nounce = \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_switch_version');
    } else {
        $nounce = FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_ajax_switch_version');
    }
    if($has_switch){
        $button_text = __("Switch back to Discount Rules 1.x", 'woo-discount-rules');
        echo '<button class="btn btn-info awdr-switch-version-button awdr-switch-version-button-on-tab" data-version="' . $version . '" data-page="'.$page.'" data-nonce="'.$nounce.'">' . $button_text . '</button>';
    }
});

/**
 * Determines if the server environment is compatible with this plugin.
 *
 * @return bool
 * @since 1.0.0
 *
 */
if(!function_exists('isAWDREnvironmentCompatible')){
    function isAWDREnvironmentCompatible()
    {
        return version_compare(PHP_VERSION, WDR_REQUIRED_PHP_VERSION, '>=');
    }
}

/**
 * Check the woocommerce is active or not
 * @return bool
 */
if(!function_exists('isAWDRWooActive')){
    function isAWDRWooActive()
    {
        $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins, false) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
    }
}

/**
 * Check woocommerce version is compatibility
 * @return bool
 */
if(!function_exists('isAWDRWooCompatible')){
    function isAWDRWooCompatible()
    {
        $current_wc_version = getAWDRWooVersion();
        return version_compare($current_wc_version, WDR_WC_REQUIRED_VERSION, '>=');
    }
}

/**
 * get the version of woocommerce
 * @return mixed|null
 */
if(!function_exists('getAWDRWooVersion')){
    function getAWDRWooVersion()
    {
        if (defined('WC_VERSION')) {
            return WC_VERSION;
        }
        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_folder = get_plugins('/woocommerce');
        $plugin_file = 'woocommerce.php';
        $wc_installed_version = NULL;
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            $wc_installed_version = $plugin_folder[$plugin_file]['Version'];
        }
        return $wc_installed_version;
    }
}

/**
 * Determines if the WordPress compatible.
 *
 * @return bool
 * @since 1.0.0
 *
 */
if(!function_exists('isAWDRWpCompatible')){
    function isAWDRWpCompatible()
    {
        $required_wp_version = 4.9;
        return version_compare(get_bloginfo('version'), $required_wp_version, '>=');
    }
}

if(!function_exists('awdr_check_compatible')){
    function awdr_check_compatible(){
        if (!isAWDREnvironmentCompatible()) {
            exit(__('This plugin can not be activated because it requires minimum PHP version of ', 'woo-discount-rules') . ' ' . WDR_REQUIRED_PHP_VERSION);
        }
        if (!isAWDRWooActive()) {
            exit(__('Woocommerce must installed and activated in-order to use Advanced woo discount rules!', 'woo-discount-rules'));
        }
        if (!isAWDRWooCompatible()) {
            exit(__(' Advanced woo discount rules requires at least Woocommerce', 'woo-discount-rules') . ' ' . WDR_WC_REQUIRED_VERSION);
        }
    }
}

/**
 * For plugin translation
 * */
add_action( 'plugins_loaded', function (){
    if(function_exists('load_plugin_textdomain')){
        load_plugin_textdomain( 'woo-discount-rules', FALSE, basename( dirname( __FILE__ ) ) . '/i18n/languages/' );
    }
});
