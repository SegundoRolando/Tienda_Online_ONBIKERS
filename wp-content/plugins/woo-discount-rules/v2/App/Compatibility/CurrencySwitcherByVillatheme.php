<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class CurrencySwitcherByVillatheme extends Base
{
    protected $key = 'compatible_cs_villatheme';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_filter('advanced_woo_discount_rules_discounted_price_of_cart_item', function($price, $cart_item, $cart_object, $discount_prices){
                $process_conversion = true;
                if($process_conversion){
                    $class_exists = false;
                    if(class_exists('\WOOMULTI_CURRENCY_F_Data')){
                        $setting         = new \WOOMULTI_CURRENCY_F_Data();
                        $class_exists = true;
                    } elseif(class_exists('\WOOMULTI_CURRENCY_Data')){
                        $setting         = new \WOOMULTI_CURRENCY_Data();
                        $class_exists = true;
                    }
                    if($class_exists === true){
                        $selected_currencies = $setting->get_list_currencies();
                        $current_currency    = $setting->get_current_currency();
                        if ( ! $current_currency ) {
                            return $price;
                        }
                        if ( $price ) {
                            $price = $price / $selected_currencies[ $current_currency ]['rate'];
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
        if ( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) || ($value == 1)) {
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