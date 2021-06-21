<?php

namespace Wdr\App\Compatibility;

if (!defined('ABSPATH')) exit;

class ProductAddOnsByWooCommerce extends Base
{
    protected $key = 'compatible_woocommerce_product_addon';

    /**
     * Do compatibility script
     * */
    public function run(){
        $value = $this->config->getConfigData($this->key, 0);
        if($value){
            add_action( 'wp_head', function () {
                $currency_code = '$';
                if(function_exists('get_woocommerce_currency_symbol')){
                    $currency_code = html_entity_decode(get_woocommerce_currency_symbol());
                }
                ?>
                <script type="application/javascript">
                    (function ($) {
                        $(document).ready(function ($) {
                            var currency_string = '<?php echo $currency_code; ?>';
                            var $form = jQuery('form.cart').first();
                            /**
                             * Strikeout for option title
                             * */
                            $('.product-addon .amount').each(function(){
                                var $targets = $(this);
                                $lock = $targets.attr('data-lock');
                                if($lock === undefined || $lock === null){
                                    $lock = false;
                                }
                                if($lock == false){
                                    var price = newText = $(this).text().replace(currency_string, '');
                                    if(price != '' && price != "-"){
                                        var option = {
                                            custom_price: price,
                                            original_price: price
                                        };
                                        $targets.attr('data-lock', true);
                                        $.AdvanceWooDiscountRules.getDynamicDiscountPriceFromCartForm($form, $targets, option);
                                    }
                                }
                            });

                            $(document.body).on( "advanced_woo_discount_rules_on_get_response_for_dynamic_discount", function ( e, response, target, options ) {
                                if(response.success == true){
                                    var price_html = ''
                                    if(response.data !== undefined){
                                        if(response.data.initial_price_html !== undefined && response.data.discounted_price_html !== undefined){
                                            price_html += '<del>'+response.data.initial_price_html+'</del>';
                                            price_html += ' <ins>'+response.data.discounted_price_html+'</ins>';
                                            target.html(price_html);
                                        }
                                    }
                                }
                                target.attr('data-lock', false);
                            });

                            /**
                             * Strikeout for option values and subtotal
                             * */
                            $form.on('updated_addons', function () {
                                setTimeout(function () {
                                    $('.product-addon-totals .amount').each(function(){
                                        var $targets = $(this);
                                        $lock = $targets.attr('data-lock');
                                        if($lock === undefined || $lock === null){
                                            $lock = false;
                                        }
                                        if($lock == false){
                                            var price = newText = $(this).text().replace(currency_string, '');
                                            if(price != '' && price != "-"){
                                                var option = {
                                                    custom_price: price,
                                                    original_price: price
                                                };
                                                $targets.attr('data-lock', true);
                                                $.AdvanceWooDiscountRules.getDynamicDiscountPriceFromCartForm($form, $targets, option);
                                            }
                                        }
                                    });
                                }, 0);
                            });
                        });

                    })(jQuery);
                </script>
            <?php } );
        }
    }

    /**
     * load fields
     * */
    public function loadFields(&$available){
        $value = $this->config->getConfigData($this->key, 0);
        if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) || ($value == 1)) {
            $available = true;
            ?>
            <div class="awdr-compatible-field">
                <label>
                    <input type="checkbox" name="wdrc[<?php echo $this->key; ?>]" id="<?php echo $this->key; ?>" value="1" <?php if ($value == 1) { ?> checked <?php } ?>>
                    <?php esc_html_e('Show discount price in product pages for WooCommerce Product Add Ons.', 'woo-discount-rules'); ?>
                </label>
            </div>
            <?php
        }
    }
}