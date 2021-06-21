<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$active = 'settings';
include_once(WOO_DISCOUNT_DIR . '/view/includes/header.php');
include_once(WOO_DISCOUNT_DIR . '/view/includes/menu.php');

$data = $config;

if (is_string($data)) $data = json_decode($data, true);
$flycartWooDiscountRulesPurchase = new FlycartWooDiscountRulesPurchase();
$isPro = $flycartWooDiscountRulesPurchase->isPro();
$target = isset($_REQUEST['target'])? sanitize_text_field($_REQUEST['target']): '';
if($target === 'table'){
    ?>
    <script>
        jQuery( document ).ready( function() {
            jQuery('#discount_config a[href="#wdr_s_price_rules"]').trigger('click');
            jQuery('#discount_config a[href="#wdr_price_rule_offer_table"]').trigger('click');
            jQuery('#discount_config #show_discount_table').focus();
        });
    </script>
<?php
}
?>

<div class="container-fluid woo_discount_loader_outer">
    <div class="row-fluid">
        <div class="<?php echo $isPro? 'col-md-12': 'col-md-8'; ?>">
            <form method="post" id="discount_config">
                <div class="col-md-12" align="right">
                    <br/>
                    <input type="submit" id="saveConfig" value="<?php esc_html_e('Save', 'woo-discount-rules'); ?>" class="btn btn-success"/>
                    <?php /* Removed from v2.3.6 echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('introduction/discount-price-rules-settings', 'settings', 'btn btn-info'); */ ?>
                </div>
                <div class="row">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#wdr_s_general"><?php esc_html_e('General', 'woo-discount-rules'); ?></a></li>
                        <li><a data-toggle="tab" href="#wdr_s_price_rules"><?php esc_html_e('Price rules', 'woo-discount-rules'); ?></a></li>
                        <li><a data-toggle="tab" href="#wdr_s_cart_rules"><?php esc_html_e('Cart rules', 'woo-discount-rules'); ?></a></li>
                        <li><a data-toggle="tab" href="#wdr_s_performance"><?php esc_html_e('Performance', 'woo-discount-rules'); ?></a></li>
                        <li><a data-toggle="tab" href="#wdr_s_promotion"><?php esc_html_e('Promotion', 'woo-discount-rules'); ?></a></li>
                    </ul>

                    <div class="tab-content">
                        <div id="wdr_s_general" class="tab-pane fade in active">
                            <?php include_once(WOO_DISCOUNT_DIR . '/view/settings_general.php'); ?>
                        </div>
                        <div id="wdr_s_price_rules" class="tab-pane fade">
                            <?php include_once(WOO_DISCOUNT_DIR . '/view/settings_price_rules.php'); ?>
                        </div>
                        <div id="wdr_s_cart_rules" class="tab-pane fade">
                            <?php include_once(WOO_DISCOUNT_DIR . '/view/settings_cart_rules.php'); ?>
                        </div>
                        <div id="wdr_s_performance" class="tab-pane fade">
                            <?php include_once(WOO_DISCOUNT_DIR . '/view/settings_performance.php'); ?>
                        </div>
                        <div id="wdr_s_promotion" class="tab-pane fade">
                            <?php include_once(WOO_DISCOUNT_DIR . '/view/settings_promotion.php'); ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="ajax_path" value="<?php echo admin_url('admin-ajax.php') ?>">
                <input type="hidden" name="wdr_nonce" value="<?php echo  FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_save_rule_config'); ?>">
            </form>
        </div>
        <?php if(!$isPro){ ?>
            <div class="col-md-1"></div>
            <!-- Sidebar -->
            <?php include_once(__DIR__ . '/template/sidebar.php'); ?>
            <!-- Sidebar END -->
        <?php } ?>
    </div>
    <div class="woo_discount_loader">
        <div class="lds-ripple"><div></div><div></div></div>
    </div>
</div>