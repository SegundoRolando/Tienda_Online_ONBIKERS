<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$active = 'settings';
include_once(WOO_DISCOUNT_DIR . '/view/includes/header.php');
include_once(WOO_DISCOUNT_DIR . '/view/includes/menu.php');

$data = $config;

if (is_string($data)) $data = json_decode($data, true);
$flycartWooDiscountRulesPurchase = new FlycartWooDiscountRulesPurchase();
$isPro = $flycartWooDiscountRulesPurchase->isPro();
?>

<div class="container-fluid woo_discount_loader_outer">
    <div class="row-fluid">
        <div class="<?php echo $isPro? 'col-md-12': 'col-md-8'; ?>">
            <div class="row form-group">
                <div class="col-md-12">
                    <br/>
                    <h4><?php esc_html_e('Documentation', 'woo-discount-rules'); ?></h4>
                    <hr>
                </div>
            </div>
            <div class="row form-group enable_variable_product_cache_con">
                <div class="col-md-12">
                    <div class="col-md-4">
                        <h4><?php esc_html_e('Installation and Intro:', 'woo-discount-rules'); ?></h4>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('introduction/getting-started', 'getting_started', esc_html__('Getting started', 'woo-discount-rules'), esc_html__('Welcome onboard', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('introduction/license-key-activation', 'license_key_activation', esc_html__('License Key activation', 'woo-discount-rules'), esc_html__('Learn how to obtain the license key and activate it', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('introduction/version-updates', 'version_updates', esc_html__('Version Updates!', 'woo-discount-rules'), esc_html__('Learn how to update to latest versions', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('introduction/popular-discount-scenarios', 'popular_discount_rules', esc_html__('Popular Discount Rules', 'woo-discount-rules'), esc_html__('What type of discount scenarios are most commonly used', 'woo-discount-rules')); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4><?php esc_html_e('Price rules/BOGO discounts:', 'woo-discount-rules'); ?></h4>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('simple-discount-rules/bulk-discounts-tiered-pricing-discounts', 'bulk_pricing_discount', esc_html__('Bulk/Tiered pricing discounts', 'woo-discount-rules'), esc_html__('Learn how to create bulk/tiered quantity discounts in WooCommerce', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('buy-one-get-one-deals/how-to-create-a-perfect-bogo-discount-rule-in-woocommerce', 'perfect_bogo', esc_html__('How to create a perfect BOGO discount rule in WooCommerce', 'woo-discount-rules'), esc_html__('Buy One Get One deals can be simple to complex. Learn how to get them working correct in your online store', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('role-based-discounts/user-role-based-discount-rules', 'role_based', esc_html__('User Role based discount rules', 'woo-discount-rules'), esc_html__('Learn how to create user role based / customer group based discount in WooCommerce', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('purchase-history-based-discounts/purchase-history-based-discount', 'purchase_history', esc_html__('Purchase History Based Discount', 'woo-discount-rules'), esc_html__('Price Rule and Cart Rule which gives discount based on the purchase history', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('coupon-based-discounts/activate-discount-rule-using-a-coupon-code-in-woocommerce', 'coupon_based', esc_html__('Coupon code based discounts', 'woo-discount-rules'), esc_html__('Apply the dynamic discount rules after the customer enters a valid coupon code', 'woo-discount-rules')); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4><?php esc_html_e('Cart Based Rules:', 'woo-discount-rules'); ?></h4>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('shipping-based-discounts/free-shipping-cart-based-rule', 'free_shipping', esc_html__('Free Shipping', 'woo-discount-rules'), esc_html__('Learn how to create a free shipping cart based rule', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('subtotal-based-discounts/subtotal-based-tiered-discounts', 'subtotal_based', esc_html__('Subtotal based - Tiered discounts', 'woo-discount-rules'), esc_html__('Learn how to create tiered discount based on the subtotal value', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('cart-based-discount-examples/free-product-cart-based-rules', 'free_product', esc_html__('Free product discount', 'woo-discount-rules'), esc_html__('How to provide a automatic adding free product in cart under certain conditions', 'woo-discount-rules')); ?>
                        </div>
                        <div class="col-md-12">
                            <?php echo FlycartWooDiscountRulesGeneralHelper::docsURLHTMLForDocumentation('category-specific-discount/category-combination-get-discount-only-when-category-a-b-c-are-in-the-cart', 'category_combination', esc_html__('Category Combination', 'woo-discount-rules'), esc_html__('Category Combination (get discount only when Category A+ B + C are in the cart)', 'woo-discount-rules')); ?>
                        </div>
                    </div>
                </div>
            </div>
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