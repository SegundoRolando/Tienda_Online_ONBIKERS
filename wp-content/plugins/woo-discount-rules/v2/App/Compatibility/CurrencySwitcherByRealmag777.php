<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class CurrencySwitcherByRealmag777 extends Base
{
    protected $key = 'compatible_wcs_realmag777';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_filter('advanced_woo_discount_rules_discounted_price_of_cart_item', function($price, $cart_item, $cart_object, $discount_prices){
                if(!empty($discount_prices) && isset($discount_prices['discounted_price'])){
                    global $WOOCS;
                    if(isset($WOOCS)){
                        if (method_exists($WOOCS, 'get_currencies')){
                            $currencies = $WOOCS->get_currencies();
                            $convert_to_current_currency = false;
                            if(isset($WOOCS->is_geoip_manipulation) && $WOOCS->is_geoip_manipulation){
                                $convert_to_current_currency = true;
                            }
                            if(isset($WOOCS->is_multiple_allowed) && $WOOCS->is_multiple_allowed){
                                $convert_to_current_currency = true;
                            }
                            if(isset($WOOCS->woocs_is_fixed_enabled) && $WOOCS->woocs_is_fixed_enabled){
                                $convert_to_current_currency = true;
                            }
                            if($convert_to_current_currency === true){
                                if(isset($currencies[$WOOCS->current_currency]) && isset($currencies[$WOOCS->current_currency]['rate'])){
                                    if($currencies[$WOOCS->current_currency]['rate'] != 0){
                                        $price = $price / $currencies[$WOOCS->current_currency]['rate'];
                                    }
                                }
                            }
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
        if ( is_plugin_active( 'woocommerce-currency-switcher/index.php' ) || ($value == 1)) {
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