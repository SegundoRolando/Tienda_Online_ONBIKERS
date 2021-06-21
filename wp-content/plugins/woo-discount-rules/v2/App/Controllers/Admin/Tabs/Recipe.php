<?php

namespace Wdr\App\Controllers\Admin\Tabs;

use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\DiscountCalculator;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Rule;
use Wdr\App\Helpers\Validation;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Recipe extends Base
{
    public $priority = 70;
    protected $tab = 'recipe';

    /**
     * GeneralSettings constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Recipe', 'woo-discount-rules');
    }

    /**
     * Render settings page
     * @param null $page
     * @return mixed|void
     */
    public function render($page = NULL)
    {
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
        self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/Recipe.php')->setData($params)->display();
    }
}