
jQuery( document ).ready( function() {
    jQuery(document).on('change', 'input[name="quantity"]', function () {
        if(woo_discount_rules.show_product_strikeout == undefined){
            return false;
        }
        if(woo_discount_rules.show_product_strikeout !== 'show_on_qty_update'){
            return false;
        }
        var form = jQuery(this).closest('form');
        if(form.length){
            var btn = form.find("button[name='add-to-cart']");
            var product_id = null;
            var current_object = jQuery(this);
            var quantity = jQuery(this).val();
            var target_class = '.summary';
            if(woo_discount_rules.product_price_container_class !== undefined && woo_discount_rules.product_price_container_class !== ''){
                target_class = woo_discount_rules.product_price_container_class;
            }
            var target = jQuery(this).closest(target_class).find('.price');
            if(btn.length && parseInt(btn.val()) > 0){
                product_id = btn.val();
                if(!target.length){
                    target = null;
                }
            } else {
                var input_product_id = input_variation_id = null;
                if(jQuery('input[name="product_id"]').length){
                    var input_product_id = form.find('input[name="product_id"]');
                }
                if(jQuery('input[name="variation_id"]').length){
                    var input_variation_id = form.find('input[name="variation_id"]');
                }

                if(input_product_id !== null){
                    product_id = input_product_id.val();
                }
                if(input_variation_id !== null){
                    product_id = input_variation_id.val();
                    target = form.find(".woocommerce-variation-price");
                }
            }
            if(parseInt(product_id) > 0){
                jQuery.ajax({
                    url: woo_discount_rules.ajax_url,
                    dataType: "json",
                    type: "POST",
                    data: {action: "loadWooDiscountStrikeoutPriceOfProduct", id: product_id, qty: quantity, price_html: ''},
                    beforeSend: function() {
                    },
                    complete: function() {
                    },
                    success: function (response) {
                        if(response.status == 1){
                            if(target !== null){
                                target.html(response.price_html);
                                if(response.product_type == 'variation'){
                                    if(response.has_single_price != undefined && response.parent_id != undefined){
                                        if(response.has_single_price == 1){
                                            var variation_price_target = jQuery('#product-'+response.parent_id+' .summary > p.price');
                                            variation_price_target.html(response.price_html);
                                        }
                                    }
                                }
                            }
                        }
                        current_object.trigger("woo_discount_rules_after_display_strikeout_price_on_quantity_update", [ product_id, response ]);
                    }
                });
            }
        }
    });
    jQuery( ".single_variation_wrap" ).on( "show_variation, woo_discount_rules_after_variant_strikeout", function ( event, variation ) {
        jQuery(this).closest('form').find('input[name="quantity"]').trigger('change');
    });
    jQuery(document).on('mouseenter', '.wdr_table_container', function () {
        jQuery('.wdr_table_container .wdr_table_off_settings_link_con').show();
        jQuery('.wdr_table_container_for_admin').addClass('wdr_table_cbr');
    });
    jQuery(document).on('mouseleave', '.wdr_table_container', function () {
        jQuery('.wdr_table_container .wdr_table_off_settings_link_con').hide();
        jQuery('.wdr_table_container_for_admin').removeClass('wdr_table_cbr');
    });
});