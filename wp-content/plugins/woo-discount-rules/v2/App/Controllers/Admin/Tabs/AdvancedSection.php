<?php
namespace Wdr\App\Controllers\Admin\Tabs;

use http\Params;
use Wdr\App\Controllers\Configuration;
use Wdr\App\Controllers\DiscountCalculator;
use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Migration;
use Wdr\App\Helpers\Rule;

if (!defined('ABSPATH')) exit;

class AdvancedSection extends Base
{
    public $priority = 50;
    protected $tab = 'Advanced Options';

    /**
     * Help constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Advanced Options', 'woo-discount-rules');
    }

    /**
     * Render Read documents page
     * @param null $page
     * @return mixed|void
     */
    public function render($page = NULL)
    {
        $advanced_config = new Configuration();
        $params=array(
            'configuration' => $advanced_config,
        );
        self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Views/Admin/Tabs/AdvancedOptions.php')->setData($params)->display();
    }

    /**
     * run advanced config option
     * @param $advanced_config
     */
    public function runAdvancedOption($advanced_config){
        /**
         * wdr_recalculate_total_before_cart
         */
        if($advanced_config->getConfig('disable_recalculate_total', 0)){
            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Snippets/disableRecalculateTotal.php')->render();
        }

        /**
         * wdr_recalculate_total_when_coupon_apply
         */
        if( $advanced_config->getConfig('disable_recalculate_total_when_coupon_apply', 0)) {
            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Snippets/recalculateTotalBeforeApplyCoupon.php')->render();
        }

        /**
         * Apply discount for the products which already have custom price or discount
         */
        if( $advanced_config->getConfig('wdr_override_custom_price', 0)){ //
            self::$template_helper->setPath(WDR_PLUGIN_PATH . 'App/Snippets/overrideCustomPrice.php')->render();
        }
    }
}