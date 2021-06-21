<?php
namespace Wdr\App\Controllers\Admin\Tabs;

use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\OnSaleShortCode;
use Wdr\App\Helpers\Migration;
use Wdr\App\Helpers\Rule;

if (!defined('ABSPATH')) exit;

class DiscountRules extends Base
{
    public $priority = 10;
    protected $tab = 'rules';

    /**
     * DiscountRules constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->title = __('Discount Rules', 'woo-discount-rules');
    }

    /**
     * Render rules listing page
     * @param null $page
     * @return mixed|void
     */
    function render($page = NULL)
    {
        $rule_helper = new Rule();
        $available_conditions = $this->getAvailableConditions();
        $params = array();
        //$params['configuration'] = new Configuration();
        $params['base'] = $this;
        $params['site_languages'] = $this->getAvailableLanguages();
        if (isset($page) && !empty($page)) {
            $id = $this->input->get('id', 0);
            $id = intval($id);
            if(is_int($id) && $id >= 0 ){} else {
                $id = 0;
            }
            $params['rule'] = $rule_helper->getRule($id, $available_conditions);
            $params['page'] = $page;
            $params['product_filters'] = $this->getProductFilterTypes();
            $params['on_sale_page_rebuild'] = OnSaleShortCode::getOnPageReBuildOption($id);
            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Manage.php' )->setData($params)->display();
        } else {
            $params['has_migration'] = $this->isMigrationAvailable();
            if($params['has_migration']){
                $params['migration_rule_count'] =$this->getV1RuleCount();
            }

            $name = $this->input->get('name', '');
            if (empty($name)) {
                $params['rules'] = $rule_helper->getAllRules($available_conditions);
            } else {
                $params['rules'] = $rule_helper->searchRuleByName($name, $available_conditions);
            }
            $params['input'] = $this->input;

            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/DiscountRule.php')->setData($params)->display();
        }
    }

    /**
     * Load welcome content
     * */
    protected function getV1RuleCount(){
        $migration = new Migration();
        $data['price_rules'] = $data['cart_rules'] = 0;
        $price_rules = $migration->getV1Rules('woo_discount', 1);
        $cart_rules = $migration->getV1Rules('woo_discount_cart', 1);
        if(!empty($price_rules)){
            $data['price_rules'] = count($price_rules);
        }
        if(!empty($cart_rules)){
            $data['cart_rules'] = count($cart_rules);
        }

        return $data;
    }

    /**
     * Load welcome content
     * */
    protected function isMigrationAvailable(){
        $migration = new Migration();
        $has_migration = $migration->getMigrationInfoOf('has_migration', null);
        if($has_migration){
            $skipped_migration = $migration->getMigrationInfoOf('skipped_migration', 0);
            $migration_completed = $migration->getMigrationInfoOf('migration_completed', 0);
            if($skipped_migration || $migration_completed){
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Get all available languages
     * @return mixed|void
     */
    function getAvailableLanguages()
    {
        $language_helper_object = self::$language_helper;
        $available_languages = $language_helper_object::getAvailableLanguages();
        $processed_languages = array();
        if (!empty($available_languages)) {
            foreach ($available_languages as $key => $lang) {
                $native_name = isset($lang['native_name']) ? $lang['native_name'] : NULL;
                $processed_languages[$key] = $native_name;
            }
        } else {
            $default_language = self::$language_helper->getDefaultLanguage();
            $processed_languages[$default_language] = self::$language_helper->getLanguageLabel($default_language);
        }
        return $processed_languages;
    }
}
