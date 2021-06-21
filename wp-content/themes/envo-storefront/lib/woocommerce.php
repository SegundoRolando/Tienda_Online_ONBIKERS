<?php

if (!function_exists('envo_storefront_cart_link')) {

    function envo_storefront_cart_link() {
        ?>	
        <a class="cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'envo-storefront'); ?>">
            <i class="fa fa-shopping-bag"><span class="count"><?php echo wp_kses_data(WC()->cart->get_cart_contents_count()); ?></span></i>
            <div class="amount-cart"><?php echo wp_kses_data(WC()->cart->get_cart_subtotal()); ?></div> 
        </a>
        <?php
    }

}

if (!function_exists('envo_storefront_header_cart')) {

    function envo_storefront_header_cart() {
        if (get_theme_mod('woo_header_cart', 1) == 1) {
            ?>
            <div class="header-cart">
                <div class="header-cart-block">
                    <div class="header-cart-inner">
                        <?php envo_storefront_cart_link(); ?>
                        <ul class="site-header-cart menu list-unstyled text-center">
                            <li>
                                <?php the_widget('WC_Widget_Cart', 'title='); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php
        }
    }

}

if (!function_exists('envo_storefront_header_add_to_cart_fragment')) {
    add_filter('woocommerce_add_to_cart_fragments', 'envo_storefront_header_add_to_cart_fragment');

    function envo_storefront_header_add_to_cart_fragment($fragments) {
        ob_start();

        envo_storefront_cart_link();

        $fragments['a.cart-contents'] = ob_get_clean();

        return $fragments;
    }

}

if (!function_exists('envo_storefront_my_account')) {

    function envo_storefront_my_account() {
        if (get_theme_mod('woo_account', 1) == 1) {
            ?>
            <div class="header-my-account">
                <div class="header-login"> 
                    <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" title="<?php esc_attr_e('My Account', 'envo-storefront'); ?>">
                        <i class="fa fa-user-circle-o"></i>
                    </a>
                </div>
            </div>
            <?php
        }
    }

}
add_action('woocommerce_before_add_to_cart_quantity', 'envo_storefront_display_quantity_minus');

function envo_storefront_display_quantity_minus() {
    global $product;
    if ( ($product->get_stock_quantity() > 1 && !$product->managing_stock() ) || !$product->is_sold_individually( ) ) {
        echo '<button type="button" class="minus" >-</button>';
    }
}

add_action('woocommerce_after_add_to_cart_quantity', 'envo_storefront_display_quantity_plus');

function envo_storefront_display_quantity_plus() {
    global $product;
    if ( ($product->get_stock_quantity() > 1 && !$product->managing_stock() ) || !$product->is_sold_individually( ) ) {
        echo '<button type="button" class="plus" >+</button>';
    }
}
