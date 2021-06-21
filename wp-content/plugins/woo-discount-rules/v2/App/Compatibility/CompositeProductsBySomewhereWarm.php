<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class CompositeProductsBySomewhereWarm extends Base
{
    protected $key = 'compatible_cp_somewherewarm';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_filter('advanced_woo_discount_rules_calculate_discount_for_cart_item', function($calculate_discount, $cart_item){
                if(isset($cart_item['composite_item']) && !empty($cart_item['composite_item'])){
                    $calculate_discount = false;
                }
                return $calculate_discount;
            }, 10, 2);

            add_filter('advanced_woo_discount_rules_include_cart_item_to_count_quantity', function($take_count, $cart_item){
                if(isset($cart_item['composite_item']) && !empty($cart_item['composite_item'])){
                    $take_count = false;
                }
                return $take_count;
            }, 10, 2);

            add_filter('advanced_woo_discount_rules_process_cart_item_for_cheapest_rule', function($take_count, $cart_item){
                if(isset($cart_item['composite_item']) && !empty($cart_item['composite_item'])){
                    $take_count = false;
                }
                return $take_count;
            }, 10, 2);
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$available){
        $value = $this->config->getConfigData($this->key, 0);
        if ( is_plugin_active( 'woocommerce-composite-products/woocommerce-composite-products.php' ) || ($value == 1)) {
            $available = true;
            ?>
            <div class="awdr-compatible-field">
                <label>
                    <input type="checkbox" name="wdrc[<?php echo $this->key; ?>]" id="<?php echo $this->key; ?>" value="1" <?php if ($value == 1) { ?> checked <?php } ?>>
                    <?php esc_html_e('Add compatible for WooCommerce Composite Products', 'woo-discount-rules'); ?>
                </label>
                <br>
                <span class="wdr_desc_text awdr-clear-both"><?php esc_html_e('Limitation: Product page strikeout. We suggest to disable strikeout on product page.', 'woo-discount-rules'); ?></span>
            </div>
            <?php
        }
    }
}