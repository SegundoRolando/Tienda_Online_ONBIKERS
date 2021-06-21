<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class WholesalePricesByRymeraWebCo extends Base
{
    protected $key = 'compatible_wsp_rymera';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            global $wdr_comp_disable_strikeout;
            add_filter('wwp_filter_wholesale_price_html', function($wholesale_price_html , $price , $product , $user_wholesale_role , $wholesale_price_title_text , $raw_wholesale_price , $source){
                global $wdr_comp_disable_strikeout;
                $wdr_comp_disable_strikeout[$product->get_id()] = true;
                $result = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', $raw_wholesale_price, $product, 1, $raw_wholesale_price, 'discounted_price', true);
                if($result !== false){
                    /*To remove the original price strikeout un-comment the next line */
                    /*$wholesale_price_html = preg_replace('/<del.*<\/del>/', '', $wholesale_price_html);*/
                    $wholesale_price_html = "<del>".$wholesale_price_html."</del><ins>".$wholesale_price_title_text.' '.wc_price($result)."</ins>";
                }
                return $wholesale_price_html;
            }, 10, 7);

            add_filter('advanced_woo_discount_rules_modify_price_html', function($enable, $price_html, $product, $quantity){
                global $wdr_comp_disable_strikeout;
                if(isset($wdr_comp_disable_strikeout[$product->get_id()]) && $wdr_comp_disable_strikeout[$product->get_id()] == true){
                    $enable = false;
                }
                return $enable;
            }, 10, 4);
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$available){
        $value = $this->config->getConfigData($this->key, 0);
        if ( is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) || is_plugin_active( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' ) || ($value == 1)) {
            $available = true;
            ?>
            <div class="awdr-compatible-field">
                <label>
                    <input type="checkbox" name="wdrc[<?php echo $this->key; ?>]" id="<?php echo $this->key; ?>" value="1" <?php if ($value == 1) { ?> checked <?php } ?>>
                    <?php esc_html_e('Add compatible for WooCommerce Wholesale Prices', 'woo-discount-rules'); ?>
                </label>
            </div>
            <?php
        }
    }
}