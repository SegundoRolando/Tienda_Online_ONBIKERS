<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="col-md-3">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-md-12">
                <br>
                <a href="https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=wpwoodiscountrules&utm_medium=plugin&utm_campaign=inline&utm_content=woo-discount-rules" target="_blank" class="btn btn-success"><?php esc_html_e('Looking for more features? Upgrade to PRO', 'woo-discount-rules'); ?></a>
            </div>
            <div class="col-md-12">
                <div id="" align="right">
                    <div class="woo-side-button" class="hide-on-click">
                        <span id="sidebar_text"><?php esc_html_e('Hide', 'woo-discount-rules'); ?></span>
                        <span id="sidebar_icon" class="dashicons dashicons-arrow-left"></span>
                    </div>
                </div>
                <div class="woo-side-panel">
                    <div class="panel">
                        <div class="panel-body">
                            <h3><?php esc_html_e('With PRO version, you can create:', 'woo-discount-rules')?></h3>
                            <p><?php esc_html_e('- Categories based discounts', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- User roles based discounts', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Buy One Get One Free deals', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Buy X Get Y deals', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Buy 2, get 1 at 50% discount', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Buy 3 for $10 (Package / Bundle [Set] Discount)', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Different discounts with one coupon code', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Purchase history based discounts', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Free product / gift', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Discount for variants', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Conditional discounts', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Fixed cost discounts', 'woo-discount-rules')?></p>
                            <p><?php esc_html_e('- Offer fixed price on certain conditions', 'woo-discount-rules')?></p>
                            <p><a href="https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=wpwoodiscountrules&amp;utm_medium=plugin&amp;utm_campaign=inline&amp;utm_content=woo-discount-rules" class="btn btn-success" target="_blank">Go PRO</a></p>
                        </div>
                    </div>
                </div>
                <div class="woo-side-panel">
                    <?php
                    echo FlycartWooDiscountRulesGeneralHelper::getSideBarContent();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>