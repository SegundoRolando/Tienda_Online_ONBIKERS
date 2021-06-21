<?php
namespace Wdr\App\Controllers\Admin\Tabs;

use Wdr\App\Helpers\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Compatible extends Base
{
    public $priority = 30;
    protected $tab = 'compatible';
    protected $option_key = 'awdr_compatibility';
    protected $options = null;
    public static $instance;
    protected $available_classes = null;

    /**
     * GeneralSettings constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Compatibility', 'woo-discount-rules');
    }

    /**
     * To create instance
     * */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Render settings page
     * @param null $page
     * @return mixed|void
     */
    public function render($page = NULL)
    {
            $this->saveCompatibilitySettings();
            $params=array(
                'woocommerce' => self::$woocommerce_helper,
                'template_helper' => self::$template_helper,
                'base' => $this,
            );
            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/compatible.php')->setData($params)->display();
    }

    /**
     * Get Config
     * */
    public function getConfig($reload = 0){
        if($this->options === null || $reload == 1){
            $option = get_option($this->option_key, array());
            if (empty($option)) {
                $this->options = array();
            } else {
                $this->options = $option;
            }
        }

        return $this->options;
    }

    /**
     * Get config data
     * */
    public function getConfigData($key, $default = ''){
        $options = $this->getConfig(1);
        if(isset($options[$key])){
            return $options[$key];
        }
        return $default;
    }

    /**
     * Save compatibility settings
     * */
    protected function saveCompatibilitySettings(){
        if(Helper::hasAdminPrivilege()){
            if(isset($_POST['awdr_compatibility_submit'])){
                $awdr_compatibility_submit = intval($_POST['awdr_compatibility_submit']);
                if($awdr_compatibility_submit == 1){
                    $wdrc = isset($_POST['wdrc'])? $_POST['wdrc']: array();
                    if(!empty($wdrc)){
                        $wdrc = array_map('absint', $wdrc);
                    }
                    update_option($this->option_key, $wdrc);
                }
            }
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$has_compatibility_plugin){
        $available_classes = $this->getAvailableCompatibilityClasses();
        if(!empty($available_classes)){
            foreach ($available_classes as $available_class){
                if(method_exists($available_class, 'loadFields')){
                    $available_class->loadFields($has_compatibility_plugin);
                }
            }
        }
    }

    /**
     * Run compatibility scripts
     * */
    public function runCompatibilityScripts(){
        $available_classes = $this->getAvailableCompatibilityClasses();
        if(!empty($available_classes)){
            foreach ($available_classes as $available_class){
                if(method_exists($available_class, 'run')){
                    $available_class->run();
                }
            }
        }
    }

    /**
     * available compatibility classes
     * @return array
     */
    public function getAvailableCompatibilityClasses()
    {
        if($this->available_classes === null){
            $this->available_classes = array();
            //Read the compatibility directory
            if (file_exists(WDR_PLUGIN_PATH . 'App/Compatibility/')) {
                $compatibility_list = array_slice(scandir(WDR_PLUGIN_PATH . 'App/Compatibility/'), 2);
                if (!empty($compatibility_list)) {
                    foreach ($compatibility_list as $compatible_file_name) {
                        $class_name = basename($compatible_file_name, '.php');
                        if (!in_array($class_name, array('Base'))) {
                            $compatible_class_name = 'Wdr\App\Compatibility\\' . $class_name;
                            if (class_exists($compatible_class_name)) {
                                $compatible_object = new $compatible_class_name();
                                if ($compatible_object instanceof \Wdr\App\Compatibility\Base) {
                                    $this->available_classes[] = $compatible_object;
                                }
                            }
                        }
                    }
                }
            }
            $this->available_classes = apply_filters( 'advanced_woo_discount_rules_available_compatibility_classes', $this->available_classes);

        }
        return $this->available_classes;
    }
}