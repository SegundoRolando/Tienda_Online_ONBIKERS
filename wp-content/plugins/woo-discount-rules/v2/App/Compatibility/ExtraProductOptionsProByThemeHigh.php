<?php

namespace Wdr\App\Compatibility;

use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit;

class ExtraProductOptionsProByThemeHigh extends Base
{
    protected $key = 'compatible_epop_themehigh';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_filter('thwepo_product_price_html', function($price_html, $product_id){
                if(Woocommerce::is_ajax()){
                    $price = trim(strip_tags($price_html));
                    $replace_strings = array('&#36;', '&nbsp;');
                    if(function_exists('wc_get_product')){
                        $product = Woocommerce::getProduct($product_id);
                        $original_price = $product->get_price();
                        $prices = explode('&nbsp;', $price);
                        $price = str_replace($replace_strings, '', $prices[0]);
                        $price = (float)$price;
                        if($original_price != $price){
                            $result = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', $price, $product, 1, $price, 'discounted_price', true, true);
                            if($result !== false){
                                $price_html = "<del>".$price_html."</del><ins>".wc_price($result)."</ins>";
                            }
                        }
                    }
                }

                return $price_html;
            }, 10, 2);
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$available){
        $value = $this->config->getConfigData($this->key, 0);
        if ( is_plugin_active( 'woocommerce-extra-product-options-pro/woocommerce-extra-product-options-pro.php' ) || ($value == 1)) {
            $available = true;
            ?>
            <div class="awdr-compatible-field">
                <label>
                    <input type="checkbox" name="wdrc[<?php echo $this->key; ?>]" id="<?php echo $this->key; ?>" value="1" <?php if ($value == 1) { ?> checked <?php } ?>>
                    <?php esc_html_e('Add compatible for WooCommerce Extra Product Options', 'woo-discount-rules'); ?>
                </label>
                <br>
                <span class="wdr_desc_text awdr-clear-both"><?php esc_html_e('Limitation: Strikeout for add-on price and subtotal in product page.', 'woo-discount-rules'); ?></span>
            </div>
            <?php
        }
    }
}