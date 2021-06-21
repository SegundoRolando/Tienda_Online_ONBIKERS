<?php
namespace Wdr\App\Controllers\Admin\Tabs;

if (!defined('ABSPATH')) exit;

class Help extends Base
{
    public $priority = 80;
    protected $tab = 'help';

    /**
     * Help constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Documentation', 'woo-discount-rules');
    }

    /**
     * Render Read documents page
     * @param null $page
     * @return mixed|void
     */
    public function render($page = NULL)
    {
        $is_pro_installed = \Wdr\App\Helpers\Helper::hasPro();
        $params = array(
            'is_pro' => $is_pro_installed,
        );
        self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/help.php')->setData($params)->display();
    }
}