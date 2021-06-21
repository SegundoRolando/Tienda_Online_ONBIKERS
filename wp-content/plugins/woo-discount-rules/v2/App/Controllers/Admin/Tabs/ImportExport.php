<?php
namespace Wdr\App\Controllers\Admin\Tabs;

use Wdr\App\Helpers\Rule;

if (!defined('ABSPATH')) exit;

class ImportExport extends Base
{
    public $priority = 50;
    protected $tab = 'importexport';

    /**
     * GeneralSettings constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Import/Export', 'woo-discount-rules');
    }

    /**
     * Render Import Export page
     * @param null $page
     * @return mixed|void
     */
    public function render($page = NULL)
    {
        $rule_helper = new Rule();
        $is_pro_installed = \Wdr\App\Helpers\Helper::hasPro();
        $params = array(
            'rules' => $rule_helper->exportRuleByName('all'),
            'is_pro_activated' => $is_pro_installed,
        );
        self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/ImportExport.php')->setData($params)->display();
    }
}