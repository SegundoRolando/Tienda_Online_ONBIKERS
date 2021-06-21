<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class CurrencySwitcherByWPWham extends Base
{
    protected $key = 'compatible_wcs_wpwham';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_filter('advanced_woo_discount_rules_discounted_price_of_cart_item', function($price, $cart_item, $cart_object, $discount_prices){
                if(!empty($discount_prices) && isset($discount_prices['discounted_price'])){
                    //Alg_WC_Currency_Switcher compatible
                    if (class_exists( 'Alg_WC_Currency_Switcher' ) ) {
                        if(function_exists('alg_wc_cs_get_currency_exchange_rate') && function_exists('alg_get_current_currency_code')){
                            $alg_wc_cs = alg_wc_cs_get_currency_exchange_rate(alg_get_current_currency_code());
                            $price = $price / $alg_wc_cs;
                        }
                    }
                }

                return $price;
            }, 10, 4);
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$available){
        $value = $this->config->getConfigData($this->key, 0);
        if ( is_plugin_active( 'currency-switcher-woocommerce/currency-switcher-woocommerce.php' ) || ($value == 1)) {
            $available = true;
            ?>
            <div class="awdr-compatible-field">
                <label>
                    <input type="checkbox" name="wdrc[<?php echo $this->key; ?>]" id="<?php echo $this->key; ?>" value="1" <?php if ($value == 1) { ?> checked <?php } ?>>
                    <?php esc_html_e('Add compatible for WooCommerce Currency Switcher', 'woo-discount-rules'); ?>
                </label>
            </div>
            <?php
        }
    }
}