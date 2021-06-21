<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class AeliaCurrencySwitcherByAelia extends Base
{
    protected $key = 'compatible_cs_aelia';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_filter('advanced_woo_discount_rules_converted_currency_value', function($price){
                if(is_numeric($price) && !empty($price)) {
                    // Get the source currency. We assume that it's always shop's base currency
                    $from_currency = get_option('woocommerce_currency');
                    // Pass the price to the currency conversion filter provided by the Currency Switcher. This
                    // will ensure that the discount is converted correctly
                    if(function_exists('get_woocommerce_currency')){
                        $price = apply_filters('wc_aelia_cs_convert', $price, $from_currency, get_woocommerce_currency(), 5);
                        if(function_exists('bcdiv')){
                            $price = bcdiv($price, 1, 2);
                        }
                    }
                }

                return $price;
            }, 10);

            add_filter('advanced_woo_discount_rules_calculate_cart_subtotal_manually', '__return_true');
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$available){
        $value = $this->config->getConfigData($this->key, 0);
        if ( is_plugin_active( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php' ) || ($value == 1)) {
            $available = true;
            ?>
            <div class="awdr-compatible-field">
                <label>
                    <input type="checkbox" name="wdrc[<?php echo $this->key; ?>]" id="<?php echo $this->key; ?>" value="1" <?php if ($value == 1) { ?> checked <?php } ?>>
                    <?php esc_html_e('Add compatible for Aelia Currency Switcher', 'woo-discount-rules'); ?>
                </label>
            </div>
            <?php
        }
    }
}