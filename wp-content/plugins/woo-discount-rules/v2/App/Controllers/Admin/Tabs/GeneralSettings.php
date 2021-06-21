<?php
namespace Wdr\App\Controllers\Admin\Tabs;
use http\Params;
use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\DiscountCalculator;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Migration;
use Wdr\App\Helpers\Rule;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GeneralSettings extends Base
{
    public $priority = 20;
    protected $tab = 'settings';

    /**
     * GeneralSettings constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Settings', 'woo-discount-rules');
    }

    /**
     * Render settings page
     * @param null $page
     * @return mixed|void
     */
    public function render($page = NULL)
    {
            $reset_migration = $this->input->get('reset_migration', '');
            if(!empty($reset_migration) && $reset_migration == 1){
                $this->resetMigration();
            }
            $rule_helper = new Rule();
            $available_rules_for_customizer = $rule_helper->getAvailableRules($this->getAvailableConditions());
            $params=array(
                'woocommerce' => self::$woocommerce_helper,
                'configuration' => new Configuration(),
                'is_pro' => Helper::hasPro(),
                'discount_calculator' => new DiscountCalculator($available_rules_for_customizer),
                'template_helper' => self::$template_helper,
                'base' => $this,
            );
            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/settings.php')->setData($params)->display();
    }

    /**
     * Reset migration
     * */
    protected function resetMigration(){
        $migration = new Migration();
        $data['has_migration'] = null;
        $data['migration_completed'] = 0;
        $data['v1_last_migrated_price_rule_id'] = 0;
        $data['v1_last_migrated_cart_rule_id'] = 0;
        $data['skipped_migration'] = 0;
        $migration->updateMigrationInfo($data);
        update_option('advanced_woo_discount_rules_current_version', null);
    }
}