<?php
/**
 * @since 2.3.6
 */

add_action('plugins_loaded', function (){
    add_filter('woocommerce_product_get_price', function ($price, $product){
        if(isset($product->is_awdr_free_product)){
            if($product->is_awdr_free_product){
                $price = 0;
            }
        }

        return $price;
    }, PHP_INT_MAX, 2);
    add_filter('woocommerce_product_variation_get_price', function ($price, $product){
        if(isset($product->is_awdr_free_product)){
            if($product->is_awdr_free_product){
                $price = 0;
            }
        }

        return $price;
    }, PHP_INT_MAX, 2);

    add_action('advanced_woo_discount_rules_after_apply_discount', function (){
        if (function_exists('WC')) {
            if (method_exists(WC()->cart, 'get_cart')) {
                $cart_items = WC()->cart->get_cart();
                if(!empty($cart_items)){
                    foreach ($cart_items as $key => $item){
                        if ( !empty( $item['wdr_free_product'] ) ){
                            if(!empty($item["data"])){
                                if($item['wdr_free_product'] == 'Free'){
                                    $item["data"]->is_awdr_free_product = 1;
                                }
                            }
                        }
                    }
                }
            }
        }
    }, 10);

    add_filter('advanced_woo_discount_rules_do_apply_price_discount', '__return_false');

    add_filter('woocommerce_product_get_price', function ($price, $product){
        if(isset($product->awdr_discount_price)){
            if($product->awdr_discount_price >= 0){
                $price = $product->awdr_discount_price;
            }
        }
        return $price;
    }, PHP_INT_MAX, 2);

    add_filter('woocommerce_product_variation_get_price', function ($price, $product){
        if(isset($product->awdr_discount_price)){
            if($product->awdr_discount_price >= 0){
                $price = $product->awdr_discount_price;
            }
        }
        return $price;
    }, PHP_INT_MAX, 2);

    add_action('advanced_woo_discount_rules_discounted_price_of_cart_item', function ($price, $cart_item, $cart_object, $calculated_cart_item_discount){
        if(!empty($cart_item["data"])){
            if(isset($calculated_cart_item_discount['initial_price'])){
                $cart_item["data"]->awdr_product_original_price = $calculated_cart_item_discount['initial_price'];
            }
            $cart_item["data"]->awdr_discount_price = $price;
        }

        return $price;
    }, 10, 4);
}, PHP_INT_MAX);