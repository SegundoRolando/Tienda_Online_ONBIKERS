//jQuery.noConflict();
function validateFields(){
    var returnValue = false;
    (function ($) {
        var rule_order = $('#rule_order');
        if(rule_order.val() != ''){
            rule_order.removeClass('invalid-field');
            rule_order.next('.error').remove();
            returnValue = true;
        } else {
            $('a.general_tab').trigger('click');
            rule_order.addClass('invalid-field');
            rule_order.next('.error').remove();
            rule_order.after('<span class="error">'+woo_discount_localization.please_fill_this_field+'</span>');
            returnValue = false;
        }

    })(jQuery);
    return returnValue;
}

function validateWDRBOGOFields(){
    var returnValue = true;
    var discount_rule_range = jQuery('#discount_rule_list .discount_rule_list');
    var wdr_invalid_tags = discount_rule_range.find('.wdr_invalid');
    jQuery.each( wdr_invalid_tags, function( key, container ) {
        jQuery(container).removeClass('wdr_invalid');
    });

    var price_rule_method = jQuery('select#price_rule_method').val();
    if(price_rule_method == 'qty_based'){
        jQuery.each( discount_rule_range, function( key, container ) {
            var price_discount_type = jQuery(container).find('select.price_discount_type');
            if(jQuery.inArray(price_discount_type.val(), ['product_discount', 'buy_x_get_x', 'buy_x_get_y', 'more_than_one_cheapest', 'more_than_one_cheapest_from_cat', 'more_than_one_cheapest_from_all']) !== -1){
                var discount_product_option = jQuery(container).find('select.discount_product_option');
                if(jQuery.inArray(discount_product_option.val(), ['all', 'same_product']) !== -1){
                    var discount_bogo_qty = jQuery(container).find('input.discount_bogo_qty');
                    if(discount_bogo_qty.val() == '' || discount_bogo_qty.val() == null){
                        discount_bogo_qty.addClass('wdr_invalid');
                        returnValue = false;
                    } else {
                        discount_bogo_qty.removeClass('wdr_invalid');
                    }
                }
                if(jQuery.inArray(discount_product_option.val(), ['more_than_one_cheapest_from_all', 'more_than_one_cheapest', 'more_than_one_cheapest_from_cat']) !== -1){
                    var discount_product_qty = jQuery(container).find('input.discount_product_qty');
                    if(discount_product_qty.val() == '' || discount_product_qty.val() == null){
                        discount_product_qty.addClass('wdr_invalid');
                        returnValue = false;
                    } else {
                        discount_product_qty.removeClass('wdr_invalid');
                    }
                    var discount_product_item_count_type = jQuery(container).find('select.discount_product_item_count_type');
                    if(discount_product_item_count_type.val() == 'static'){
                        var discount_product_items_count_field = jQuery(container).find('input.discount_product_items_count_field');
                        if(discount_product_items_count_field.val() == '' || discount_product_items_count_field.val() == null){
                            discount_product_items_count_field.addClass('wdr_invalid');
                            returnValue = false;
                        } else {
                            discount_product_items_count_field.removeClass('wdr_invalid');
                        }
                    }
                }
                if(jQuery.inArray(discount_product_option.val(), ['more_than_one_cheapest_from_cat']) !== -1){
                    var category_list = jQuery(container).find('select.category_list');
                    if(category_list.val() == '' || category_list.val() == null){
                        jQuery(container).find('.bootstrap-select.category_list > button').addClass('wdr_invalid');
                        returnValue = false;
                    } else {
                        jQuery(container).find('.bootstrap-select.category_list > button').removeClass('wdr_invalid');
                    }
                }
                //discount_category_option_list_con
                if(parseInt(jQuery('#flycart_wdr_woocommerce_version').val()) > 2){
                    if(jQuery.inArray(discount_product_option.val(), ['all', 'any_cheapest', 'more_than_one_cheapest']) !== -1){
                        var product_field = jQuery(container).find('select.wc-product-search');
                        if(product_field.val() == '' || product_field.val() == null){
                            jQuery(container).find('.select2-selection').addClass('wdr_invalid');
                            returnValue = false;
                        } else {
                            jQuery(container).find('.select2-selection').removeClass('wdr_invalid');
                        }
                    }
                }

                var discount_product_discount_type = jQuery(container).find('select.discount_product_discount_type');
                if(discount_product_discount_type.val() == 'limited_percent'){
                    var discount_product_percent_field = jQuery(container).find('input.discount_product_percent_field');
                    if(discount_product_percent_field.val() == '' || discount_product_percent_field.val() == null){
                        discount_product_percent_field.addClass('wdr_invalid');
                        returnValue = false;
                    } else {
                        discount_product_percent_field.removeClass('wdr_invalid');
                    }
                }
            } else if(jQuery.inArray(price_discount_type.val(), ['percentage_discount', 'price_discount', 'fixed_price', 'set_discount']) !== -1){
                var price_discount_amount = jQuery(container).find('input.price_discount_amount');
                if(price_discount_amount.val() == '' || price_discount_amount.val() == null){
                    price_discount_amount.addClass('wdr_invalid');
                    returnValue = false;
                } else {
                    price_discount_amount.removeClass('wdr_invalid');
                }
            }
            var discount_range_min_qty = jQuery(container).find('input.discount_range_min_qty');
            if(discount_range_min_qty.val() == '' || discount_range_min_qty.val() == null){
                discount_range_min_qty.addClass('wdr_invalid');
                returnValue = false;
            } else {
                discount_range_min_qty.removeClass('wdr_invalid');
            }
            if(price_discount_type.val() != 'set_discount'){
                var discount_range_max_qty = jQuery(container).find('input.discount_range_max_qty');
                if(discount_range_max_qty.val() == '' || discount_range_max_qty.val() == null){
                    discount_range_max_qty.addClass('wdr_invalid');
                    returnValue = false;
                } else {
                    discount_range_max_qty.removeClass('wdr_invalid');
                }
            }
        });
    }
    if(returnValue == false){
        discount_rule_range.find('.wdr_invalid').first().focus();
        jQuery('a.discount_tab').trigger('click');
    }

    return returnValue;
}

function trigger_woocommerce_tooltip(){
    jQuery( '.tips, .help_tip, .woocommerce-help-tip' ).tipTip( {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    } );
}

function wooo_discount_range_altered() {
    var result = { 'has_set_discount': false, 'has_range_discount': false, 'has_fixed_price': false };
    var discount_types = jQuery('#discount_rule_list').find("select.price_discount_type");
    jQuery.each(discount_types, function( key, current_val ) {
        var adjustment_type = jQuery(current_val).val();
        if(adjustment_type == 'set_discount'){
            result.has_set_discount = true;
        } else if(adjustment_type == 'fixed_price'){
            result.has_fixed_price = true;
            result.has_range_discount = true;
        } else {
            result.has_range_discount = true;
        }
    });
    return result;
}
(function ($) {
    jQuery(document).ready(function () {
        // Tooltips
        trigger_woocommerce_tooltip();

        var ajax_url = $('#ajax_path').val();
        var admin_url = $('#admin_path').val();
        var pro_suffix = $('#pro_suffix').val();
        var is_pro = $('#is_pro').val();
        // $(".datepicker").datepicker();
        $(".wdr_datepicker").datetimepicker({
            //format: "dd MM yyyy - hh:ii",
            format: "mm/dd/yyyy hh:ii",
            autoclose: true,
            todayBtn: true,
            pickerPosition: "top-right"
        });

        //--------------------------------------------------------------------------------------------------------------
        //--------------------------------------------PRICING RULES-----------------------------------------------------
        //--------------------------------------------------------------------------------------------------------------

        // Manage Customer Selection ON-LOAD
        var user_selection = $('#apply_customer').val();
        if (user_selection == 'only_given') {
            $('#user_list').css('display', 'block');
        } else {
            $('#user_list').css('display', 'none');
        }

        // Saving Rule.
        $('#savePriceRule').on('click', function (event) {
            $(".form-control").removeClass('wdr_invalid');
            var validate = validateFields();
            $(".wdr_validation_notice").hide();
            if ($('#rule_name').val() == '') {
                jQuery('a.general_tab').trigger('click');
                alert(woo_discount_localization.please_enter_the_rule_name);
                return false;
            }

            var validate_bogo = validateWDRBOGOFields();

            if(validate == false || validate_bogo == false){
                return false;
            }
            var form = $('#form_price_rule').serialize();
            var current = $(this);
            var rule_id = $('#rule_id').val();
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            event.preventDefault();
            if ($('#rule_name').val() == '') {
                jQuery('a.general_tab').trigger('click');
                alert(woo_discount_localization.please_enter_the_rule_name);
            } else {
                current.val(woo_discount_localization.saving);
                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {action: 'savePriceRule', data: form},
                    beforeSend: function() {
                        loader.show();
                    },
                    complete: function() {
                        loader.hide();
                    },
                    success: function (result) {
                        var result = jQuery.parseJSON( result );
                        // After Status Changed.
                        resizeChart = setTimeout(function () {
                            current.val(woo_discount_localization.save_rule);
                        }, 300);
                        if(result.status == 1){
                            // Reset, if its New Form.
                            if (rule_id == 0) {
                                $('#form_price_rule')[0].reset();
                                window.location.replace(admin_url);
                            }
                            adminNotice();
                        } else {
                            //woo_discount_rule_error_notice(result.message);
                            if(result.invalid_field != undefined){
                                if(result.invalid_field == "dynamic_coupons_to_apply"){
                                    $("input[name='dynamic_coupons_to_apply']").addClass('wdr_invalid');
                                    $("a.restriction_tab").trigger('click');
                                    $("div.dynamic_coupons_to_apply_validation").html("<p>"+result.error_message+"</p>").show();
                                }
                            }
                        }
                        if(result.dynamic_coupons_to_apply != undefined){
                            $("input[name='dynamic_coupons_to_apply']").val(result.dynamic_coupons_to_apply);
                        }
                    }

                });
            }
        });

        // License key check
        $('#woo-disc-license-check').on('click', function (event) {
            var license_key = $('#woo-disc-license-key');
            var resp_msg = $('#woo-disc-license-check-msg');
            if(license_key.val() == ''){
                license_key.addClass('invalid-field');
                resp_msg.html('<div class="notice-message error inline notice-error notice-alt">'+woo_discount_localization.please_enter_a_key+'</div>');
                return false;
            }else{
                license_key.removeClass('invalid-field');
                resp_msg.html('');
            }

            var form = $('#discount_config').serialize();
            var current = $(this);

            event.preventDefault();

            current.removeClass('button-primary');
            current.addClass('button-secondary');
            current.val(woo_discount_localization.saving);
            $('.license-success, .license-failed').hide();
            var license_chk_req = $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'forceValidateLicenseKey', data: form},
                success: function () {
                    resizeChart = setTimeout(function () {
                        current.addClass('button-primary');
                        current.removeClass('button-secondary');
                        current.val('Validate');
                    }, 300);

                    //adminNotice();
                    // display a success message
                }
            });
            license_chk_req.done(function( resp ) {

                response = JSON.parse(resp);
                if (response['error']) {
                    resp_msg.html('<div class="notice-message error inline notice-error notice-alt">'+response['error']+'</div>');
                } else if( response['success']){
                    resp_msg.html('<div class="notice-message success inline notice-success notice-alt">'+response['success']+'</div>');
                }

            });
        });

        //To add an duplicate range fields
        $(document).on('click', '.create_duplicate_discount_range', function () {
            //$(".create_duplicate_discount_range").on('click', function () {
            var data_id = $(this).attr('data-id');
            $('#addNewDiscountRange').trigger('click');
            var new_inputs = $("#discount_rule_list .discount_rule_list:last-child").find('input,select');
            $.each( new_inputs, function( key, new_input ) {
                var field_name = $(new_input).attr('name');
                if(field_name != undefined){
                    var field_name_second_part = field_name.replace("discount_range[", "");
                    var field_name_second_part_split = field_name_second_part.split(']');
                    var field_name_id = field_name_second_part_split['0'];
                    var field_name_to_duplicate = field_name.replace("discount_range["+field_name_id+"]", "discount_range["+data_id+"]");
                    $("input[name='"+field_name+"']").val($("input[name='"+field_name_to_duplicate+"']").val());
                    if($("select[name='"+field_name+"']").hasClass('wc-product-search')){
                        $("select[name='"+field_name+"']").html($("select[name='"+field_name_to_duplicate+"']").html())
                    }
                    $("select[name='"+field_name+"']").val($("select[name='"+field_name_to_duplicate+"']").val());
                }
            });
            $("#discount_rule_list .discount_rule_list:last-child select").trigger('change');
            $('.product_list,.selectpicker').selectpicker('refresh');
            $('.wc-product-search').trigger( 'wc-enhanced-select-init' );
            $('select.discount_product_discount_type').trigger('change');
            // Tooltips
            trigger_woocommerce_tooltip();
        });

        // Adding New Discount Range.
        $('#addNewDiscountRange').on('click', function () {
            var count = $('.discount_rule_list').length + 1;
            if (is_pro) {
                var discount_types = wooo_discount_range_altered();
                var discount_type_set_discount_selected = ''
                if(discount_types.has_set_discount == true){
                    discount_type_set_discount_selected = ' selected="selected" '
                }
                var form = '<div class="discount_rule_list"> <div class="form-group"><label><span class="discount_for_min_quantity_text">'+woo_discount_localization.min_quantity+'</span> <input type="text" name="discount_range[' + count + '][min_qty]" class="form-control discount_range_min_qty" value="" placeholder="'+woo_discount_localization.place_holder_ex_1+'"></label>' +
                    '<label class="discount_for_max_quantity_label">'+woo_discount_localization.max_quantity+' <input type="text" name="discount_range[' + count + '][max_qty]" class="form-control discount_range_max_qty" value="" placeholder="'+woo_discount_localization.place_holder_ex_50+'"> </label> <label>'+woo_discount_localization.adjustment_type+'<select class="form-control price_discount_type" name="discount_range[' + count + '][discount_type]"> ' +
                    '<option value="percentage_discount"> '+woo_discount_localization.percentage_discount_in_adjustment_type+' </option> <option value="price_discount">'+woo_discount_localization.price_discount+' </option> <option value="fixed_price">'+woo_discount_localization.fixed_price+' </option> <option value="set_discount" '+discount_type_set_discount_selected+'>'+woo_discount_localization.set_discount+' </option> ' +
                    '<option value="buy_x_get_x">'+woo_discount_localization.buy_x_get_x+' </option> ' +
                    '<option value="buy_x_get_y">'+woo_discount_localization.buy_x_get_y+' </option> ' +
                    '<option value="more_than_one_cheapest">'+woo_discount_localization.more_than_one_cheapest_from_selected+' </option> ' +
                    '<option value="more_than_one_cheapest_from_cat">'+woo_discount_localization.more_than_one_cheapest_from_selected_category+' </option> ' +
                    '<option value="more_than_one_cheapest_from_all">'+woo_discount_localization.more_than_one_cheapest_from_all+' </option> ' +
                    '</select></label> ' +
                    '<label><span class="price_discount_amount price_discount_amount_tool_tip_con">\n' +
                    '</span></label>' +
                    '<label><span class="hide-for-product-discount">'+woo_discount_localization.value_text+'</span>' +
                    '<input type="text" name="discount_range[' + count + '][to_discount]" class="form-control price_discount_amount" value="" placeholder="'+woo_discount_localization.place_holder_ex_50+'"> ';
                form += '<div class="price_discount_product_list_con hide">' +
                    '<span class="bogo_receive_discount_for_text"> '+woo_discount_localization.apply_for+' </span><select class="discount_product_option" name="discount_range['+count+'][discount_product_option]"><option value="all">'+woo_discount_localization.all_selected+'</option><option value="same_product">'+woo_discount_localization.same_product+'</option>' +
                    '<option value="more_than_one_cheapest_from_cat">'+woo_discount_localization.more_than_one_cheapest_from_selected_category+'</option><option value="more_than_one_cheapest">'+woo_discount_localization.more_than_one_cheapest_from_selected+'</option><option value="more_than_one_cheapest_from_all">'+woo_discount_localization.more_than_one_cheapest_from_all+'</option>' +
                    '</select>';
                form += '&nbsp;<span class="woocommerce-help-tip discount_product_option_hint" data-tip="'+woo_discount_localization.discount_product_option_tooltip+'"></span>';
                form += '<div class="discount_product_option_bogo_con">';
                form += '&nbsp;<label> '+woo_discount_localization.free_quantity+' <span class="woocommerce-help-tip" data-tip="'+woo_discount_localization.number_of_quantities_in_each_products+'"></span> <input type="text" name="discount_range['+count+'][discount_bogo_qty]" class="form-control discount_bogo_qty" value="" placeholder="'+woo_discount_localization.place_holder_ex_1+'" /></label>';
                form += '</div>';
                form += '<div class="discount_product_option_more_cheapest_con hide">';
                var discount_product_item_count_type_class = ' selectpicker';
                var discount_product_item_count_type_hint_class = '';
                if(woo_discount_localization.enable_fixed_item_count_in_bogo == false){
                    discount_product_item_count_type_hint_class = discount_product_item_count_type_class = ' hide_discount_product_item_count_type';
                }
                form += '&nbsp;<select class="discount_product_item_count_type'+discount_product_item_count_type_class+'" name="discount_range['+count+'][discount_product_item_type]">';
                form += '<option value="dynamic">'+woo_discount_localization.dynamic_item_count+'</option>';
                form += '<option value="static">'+woo_discount_localization.fixed_item_count+'</option>';
                form += '</select>';
                form += '&nbsp;<span class="woocommerce-help-tip'+discount_product_item_count_type_hint_class+'" data-tip="'+woo_discount_localization.fixed_item_count_tooltip+'"></span>';
                form += '&nbsp;<label class="discount_product_items_count_field hide"> '+woo_discount_localization.item_count+'&nbsp;<span class="woocommerce-help-tip" data-tip="'+woo_discount_localization.discount_number_of_item_tooltip+'"></span><input type="text" name="discount_range['+count+'][discount_product_items]" class="form-control discount_product_items_count_field hide" value="" placeholder="'+woo_discount_localization.place_holder_ex_1+'" /></label>';
                form += '&nbsp;<label> '+woo_discount_localization.item_quantity+'&nbsp;<span class="woocommerce-help-tip" data-tip="'+woo_discount_localization.discount_number_of_each_item_tooltip+'"></span><input type="text" name="discount_range['+count+'][discount_product_qty]" class="form-control discount_product_qty" value="" placeholder="'+woo_discount_localization.place_holder_ex_1+'" /></label>';
                form += '</div>';
                form += '<div class="discount_product_option_list_con">';
                form += '<label>&nbsp;<span class="wdr_block_span">'+woo_discount_localization.choose_products+'</span>';
                if($('#flycart_wdr_woocommerce_version').val() == 2){
                    form += '&nbsp;<input type="hidden" class="wc-product-search" style="min-width: 250px" data-multiple="true" name="discount_range[' + count + '][discount_product][]" data-placeholder="'+woo_discount_localization.place_holder_search_for_products+'" data-action="woocommerce_json_search_products_and_variations" data-selected=""/>';
                } else {
                    form += '&nbsp;<select class="wc-product-search" multiple="multiple" style="min-width: 250px" name="discount_range[' + count + '][discount_product][]" data-placeholder="'+woo_discount_localization.place_holder_search_for_products+'" data-action="woocommerce_json_search_products_and_variations"></select>'
                }
                form += '</label>';
                form += '</div>';
                form += '<div class="discount_category_option_list_con hide">';
                form += '<label>&nbsp;<span class="wdr_block_span">'+woo_discount_localization.choose_categories+'</span>';
                form += '&nbsp;<select class="category_list selectpicker" multiple title="'+woo_discount_localization.none_selected+'" name="discount_range[' + count + '][discount_category][]">';
                $("#category_list select.category_list option").each(function()
                {
                    form += '<option value="'+$(this).val()+'">'+$(this).html()+'</option>';
                });
                form += '</select>';
                form += '</label>';
                form += '</div>';
                form += '<div class="discount_product_percent_con">';
                form += '&nbsp;<label>&nbsp;<span class="wdr_block_span">'+woo_discount_localization.percentage_discount+'</span>&nbsp;<select class="selectpicker discount_product_discount_type" name="discount_range['+ count +'][discount_product_discount_type]"><option value="">'+woo_discount_localization.percent_100+'</option><option value="limited_percent">'+woo_discount_localization.limited_percent+'</option></select></label>';
                form += '<span class="discount_product_percent_field">&nbsp;<input type="text" name="discount_range['+count+'][discount_product_percent]" class="discount_product_percent_field" value="" placeholder="'+woo_discount_localization.place_holder_ex_10+'" />&nbsp;<span class="woocommerce-help-tip" data-tip="'+woo_discount_localization.percentage_tooltip+'"></span></span> ';
                form += '</div>';
                form += '</div>';
                form += '</label>&nbsp;';
                form += '<label><a href="javascript:void(0)" data-id="'+count+'" class="btn btn-primary form-control create_duplicate_discount_range">'+woo_discount_localization.duplicate_text+'</a></label> ' +
                    '<label><a href=javascript:void(0) class="btn btn-danger form-control remove_discount_range">'+woo_discount_localization.remove_text+'</a></label> </div> </div>';
            } else {
                var form = '<div class="discount_rule_list"> <div class="form-group"><label><span class="discount_for_min_quantity_text">'+woo_discount_localization.min_quantity+'</span> <input type="text" name="discount_range[' + count + '][min_qty]" class="form-control discount_range_min_qty" value="" placeholder="'+woo_discount_localization.place_holder_ex_1+'"></label>' +
                    '<label class="discount_for_max_quantity_label">'+woo_discount_localization.max_quantity+' <input type="text" name="discount_range[' + count + '][max_qty]" class="form-control discount_range_max_qty" value="" placeholder="'+woo_discount_localization.place_holder_ex_50+'"> </label> <label>'+woo_discount_localization.adjustment_type+'<select class="form-control price_discount_type" name="discount_range[' + count + '][discount_type]"> ' +
                    '<option value="percentage_discount"> '+woo_discount_localization.percentage_discount_in_adjustment_type+' </option> <option disabled>'+woo_discount_localization.price_discount+' <b>' + pro_suffix + '</b> </option> <option disabled>'+woo_discount_localization.fixed_price+' <b>' + pro_suffix + '</b> </option> <option disabled>'+woo_discount_localization.set_discount+' <b>' + pro_suffix + '</b> </option> ' +
                    '<option disabled>'+woo_discount_localization.buy_x_get_x+' <b>' + pro_suffix + '</b> </option> ' +
                    '<option disabled>'+woo_discount_localization.buy_x_get_y+' <b>' + pro_suffix + '</b> </option> ' +
                    '<option disabled>'+woo_discount_localization.more_than_one_cheapest_from_selected+' <b>' + pro_suffix + '</b> </option> ' +
                    '<option disabled>'+woo_discount_localization.more_than_one_cheapest_from_selected_category+' <b>' + pro_suffix + '</b> </option> ' +
                    '<option disabled>'+woo_discount_localization.more_than_one_cheapest_from_all+' <b>' + pro_suffix + '</b> </option> ' +
                    '</select></label>' +
                    '<label><span class="price_discount_amount price_discount_amount_tool_tip_con">\n' +
                    '</span></label>' +
                    '<label>'+woo_discount_localization.value_text+' ' +
                    '<input type="text" name="discount_range[' + count + '][to_discount]" class="form-control price_discount_amount" value="" placeholder="'+woo_discount_localization.place_holder_ex_50+'"> ';
                form += '<div class="price_discount_product_list_con hide"><select class="product_list selectpicker price_discount_product_list" multiple title="'+woo_discount_localization.none_selected+'" name="discount_range[' + count + '][discount_product][]">';
                form += '<option>'+woo_discount_localization.none_text+'</option>';
                form += '</select></div>';
                form += '</label>&nbsp; ';
                form += ' <label><a href=javascript:void(0) data-id="'+count+'" class="btn btn-primary form-control create_duplicate_discount_range">'+woo_discount_localization.duplicate_text+'</a> </label> ' +
                    '<label><a href=javascript:void(0) class="btn btn-danger form-control remove_discount_range">'+woo_discount_localization.remove_text+'</a> </label></div> </div>';
            }
            $('#discount_rule_list').append(form);
            $('.product_list,.selectpicker').selectpicker('refresh');
            $('.wc-product-search').trigger( 'wc-enhanced-select-init' );
            $('select.discount_product_discount_type').trigger('change');
            $('select.price_discount_type').trigger('change');
            // Tooltips
            trigger_woocommerce_tooltip();
        });

        // Removing Discount Rule.
        $(document).on('click', '.remove_discount_range', function () {
            var confirm_delete = confirm(woo_discount_localization.are_you_sure_to_remove_this);
            if (confirm_delete) {
                $(this).closest('.discount_rule_list').remove();
            }
        });

        // Enabling and Disabling the Status of the Rule.
        $('.manage_status').on('click', function (event) {
            event.preventDefault();
            var current = $(this);
            var id = $(this).attr('id');
            var wdr_nonce = $('input[name="wdr_nonce"]').val();
            id = id.replace('state_', '');
            $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: "json",
                data: {action: 'UpdateStatus', id: id, from: 'pricing-rules', wdr_nonce: wdr_nonce},
                success: function (response) {
                    // After Status Changed.
                    if (response.status == 'Disable') {
                        current.removeClass('btn-success');
                        current.addClass('btn-warning');
                        current.html(woo_discount_localization.enable_text);
                    } else if (response.status == 'Publish') {
                        current.addClass('btn-success');
                        current.removeClass('btn-warning');
                        current.html(woo_discount_localization.disable_text);
                    }
                    if (response.status_html != '') {
                        $("#status_in_text_"+id).html(response.status_html);
                    }
                }

            });
        });

        // Remove Rule.
        $('.delete_rule').on('click', function (event) {
            event.preventDefault();
            var current = $(this);
            var id = $(this).attr('id');
            var wdr_nonce = $('input[name="wdr_nonce"]').val();
            id = id.replace('delete_', '');
            var confirm_delete = confirm(woo_discount_localization.are_you_sure_to_remove);
            if (confirm_delete) {
                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {action: 'RemoveRule', id: id, from: 'pricing-rules', wdr_nonce: wdr_nonce},
                    success: function () {
                        // After Removed.
                        current.closest('tr').remove();
                        location.reload(true);
                    }
                });
            }
        });

        $('#restriction_block').hide();
        $('#discount_block').hide();

        $('.general_tab').on('click', function () {
            $('#general_block').show();
            $('#restriction_block').hide();
            $('#discount_block').hide();
            makeActiveForSelectedTab($("a.general_tab"));
        });
        $('.restriction_tab').on('click', function () {
            if(validateFields() == true){
                $('#general_block').hide();
                $('#restriction_block').show();
                $('#discount_block').hide();
                makeActiveForSelectedTab($(".restriction_tab"));
            }
        });
        $('.discount_tab').on('click', function () {
            $('#general_block').hide();
            $('#restriction_block').hide();
            $('#discount_block').show();
            makeActiveForSelectedTab($(".discount_tab"));
        });



        // Manage the Type of Apply.
        $('#apply_to').on('change', function () {
            var option = $(this).val();
            var hint_text = '';
            $('#cumulative_for_products_cont').hide();
            if (option == 'specific_products') {
                hint_text = woo_discount_localization.apply_to_hint_specific_products;
                $('#product_list').css('display', 'block');
                $('#category_list').css('display', 'none');
                $('#product_attributes_list').css('display', 'none');
                $('#product_exclude_list').hide();
                $('#cumulative_for_products_cont').show();
            } else if (option == 'specific_category') {
                hint_text = woo_discount_localization.apply_to_hint_specific_category;
                $('#product_list').css('display', 'none');
                $('#product_attributes_list').css('display', 'none');
                $('#category_list').css('display', 'block');
                $('#product_exclude_list').show();
            } else if (option == 'specific_attribute') {
                hint_text = woo_discount_localization.apply_to_hint_specific_attribute;
                $('#product_list').css('display', 'none');
                $('#category_list').css('display', 'none');
                $('#product_attributes_list').css('display', 'block');
                $('#product_exclude_list').show();
            } else {
                hint_text = woo_discount_localization.apply_to_hint_all_products;
                $('#product_list').css('display', 'none');
                $('#category_list').css('display', 'none');
                $('#product_attributes_list').css('display', 'none');
                $('#product_exclude_list').show();
                $('#cumulative_for_products_cont').show();
            }
            $('.apply_to_hint').html(hint_text);
        });
        $('#apply_to').trigger('change');

        // Manage the Customer.
        $('#apply_customer').on('change', function () {
            var option = $(this).val();
            if (option == 'only_given') {
                $('#user_list').show();
            } else {
                $('#user_list').hide();
            }
        });
        $('#coupon_option_price_rule').on('change', function () {
            var option = $(this).val();
            if (option == 'none') {
                $('.coupons_to_apply_price_rule_con').hide();
                $('.dynamic_coupons_to_apply_price_rule_con').hide();
            } else if (option == 'create_dynamic_coupon') {
                $('.coupons_to_apply_price_rule_con').hide();
                $('.dynamic_coupons_to_apply_price_rule_con').show();
            } else {
                $('.dynamic_coupons_to_apply_price_rule_con').hide();
                $('.coupons_to_apply_price_rule_con').show();
            }
        });
        $('#coupon_option_price_rule').trigger('change');

        $('#subtotal_option_price_rule').on('change', function () {
            var option = $(this).val();
            if (option == 'none') {
                $('.subtotal_to_apply_price_rule_con').hide();
            } else {
                $('.subtotal_to_apply_price_rule_con').show();
            }
        });
        $('#subtotal_option_price_rule').trigger('change');

        $('#show_discount_table').on('change', function () {
            var option = $(this).val();
            if (option == 'show') {
                $('.discount_table_options').show();
            } else if (option == 'advance') {
                $('.discount_table_options').hide();
                $('.discount_table_option_advance').show();
            } else {
                $('.discount_table_options').hide();
            }
        });
        $('#show_discount_table').trigger('change');

        $('#message_on_apply_cart_discount').on('change', function () {
            var option = $(this).val();
            if (option == 'yes') {
                $('.message_on_apply_cart_discount_options').show();
            } else {
                $('.message_on_apply_cart_discount_options').hide();
            }
        });
        $('#message_on_apply_cart_discount').trigger('change');

        $('#message_on_apply_price_discount').on('change', function () {
            var option = $(this).val();
            if (option == 'yes') {
                $('.message_on_apply_price_discount_options').show();
            } else {
                $('.message_on_apply_price_discount_options').hide();
            }
        });
        $('#message_on_apply_price_discount').trigger('change');

        $('select[name="show_sale_tag_on_product_page"]').on('change', function () {
            var option = $(this).val();
            if (option == 'show' || option == 'show_on_any_rules_matches') {
                $('#customize_sale_tag').prop('checked', true).trigger('change');
            } else {
                $('#customize_sale_tag').prop('checked', false).trigger('change');
            }
        });

        $('#display_you_saved_text').on('change', function () {
            var option = $(this).val();
            if (option == 'no') {
                $('.display_you_saved_text_options').hide();
            } else {
                $('.display_you_saved_text_options').show();
            }
        });
        $('#display_you_saved_text').trigger('change');

        $('#customize_sale_tag').on('change', function () {
            if ($("#customize_sale_tag").is(":checked")) {
                $('.customize_sale_tag_option').show();
            } else {
                $('.customize_sale_tag_option').hide();
            }
        });
        $('#customize_sale_tag').trigger('change');

        $(document).on('keyup', '.rule_descr', function () {
            var value = $(this).val();
            value = '| ' + value;
            var id = $(this).attr('id');
            id = id.replace('rule_descr_', '');
            $('#rule_label_' + id).html(value);
        });

        //--------------------------------------------------------------------------------------------------------------
        //-----------------------------------------------CART RULES-----------------------------------------------------
        //--------------------------------------------------------------------------------------------------------------

        $(document).on('click', '#add_cart_rule', function () {
            var count = $('.cart_rules_list').length;
            var product_list = '';
            var products_list = '';
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            var wdr_nonce = $('input[name="wdr_search_nonce"]').val();
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'loadProductSelectBox', name: 'discount_rule['+count+'][purchase_history_products]', wdr_nonce: wdr_nonce},
                beforeSend: function() {
                    loader.show();
                },
                complete: function() {
                    loader.hide();
                },
                success: function (response) {
                    product_list = response;
                    $('#purchase_history_products_list_'+count).html(product_list);
                    $('.wc-product-search').trigger( 'wc-enhanced-select-init' );
                }
            });
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'loadProductSelectBox', name: 'discount_rule['+count+'][products]', wdr_nonce: wdr_nonce},
                beforeSend: function() {
                    loader.show();
                },
                complete: function() {
                    loader.hide();
                },
                success: function (response) {
                    products_list = response;
                    $('#products_list_'+count).html(products_list);
                    $('.wc-product-search').trigger( 'wc-enhanced-select-init' );
                }
            });

            // Cloning the List.
            var user_list = $('#cart_user_list_0 > option').clone();
            var category_list = $('#cart_category_list_0 > option').clone();
            var coupon_list = $('#cart_coupon_list_0 > option').clone();
            var roles_list = $('#cart_roles_list_0 > option').clone();
            var country_list = $('#cart_countries_list_0 > option').clone();
            var order_status_list = $('#order_status_list_0 > option').clone();
            if (is_pro) {
                var form = '<div class="cart_rules_list row"> <div class="col-md-3 form-group"> <label>'+woo_discount_localization.type_text+' <select class="form-control cart_rule_type" id="cart_condition_type_' + count + '" name="discount_rule[' + count + '][type]"> <optgroup label="'+woo_discount_localization.cart_subtotal+'"><option value="subtotal_least" selected="selected">'+woo_discount_localization.subtotal_at_least+'</option><option value="subtotal_less">'+woo_discount_localization.subtotal_less_than+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.cart_item_count+'"><option value="item_count_least">'+woo_discount_localization.number_of_line_items_in_cart_at_least+'</option><option value="item_count_less">'+woo_discount_localization.number_of_line_items_in_cart_less_than+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.quantity_sum+'"><option value="quantity_least">'+woo_discount_localization.total_number_of_quantities_in_cart_at_least+'</option><option value="quantity_less">'+woo_discount_localization.total_number_of_quantities_in_cart_less_than+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.products_in_cart+'">' +
                    '<option value="products_in_list">'+woo_discount_localization.products_in_list+'</option>' +
                    '<option value="products_not_in_list">'+woo_discount_localization.products_not_in_list+'</option>' +
                    '<option value="exclude_sale_products">'+woo_discount_localization.exclude_sale_products+'</option>' +
                    '</optgroup>' +
                    '<optgroup label="'+woo_discount_localization.categories_in_cart+'">' +
                    '<option value="categories_in">'+woo_discount_localization.categories_in_cart+'</option>' +
                    '<option value="atleast_one_including_sub_categories">'+woo_discount_localization.atleast_one_including_sub_categories+'</option>' +
                    '<option value="in_each_category">'+woo_discount_localization.in_each_category_cart+'</option>' +
                    '<option value="exclude_categories">'+woo_discount_localization.exclude_categories_in_cart+'</option>' +
                    '</optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_details_must_be_logged_in+'"><option value="users_in">'+woo_discount_localization.user_in_list+'</option><option value="roles_in">'+woo_discount_localization.user_role_in_list+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_email+'"><option value="customer_email_tld">'+woo_discount_localization.customer_email_tld+'</option><option value="customer_email_domain">'+woo_discount_localization.customer_email_domain+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_billing_details+'"><option value="customer_billing_city">'+woo_discount_localization.customer_billing_city+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_shipping_details+'">' +
                    '<option value="customer_shipping_city">'+woo_discount_localization.customer_shipping_city+'</option>' +
                    '<option value="customer_shipping_state">'+woo_discount_localization.customer_shipping_state+'</option>' +
                    '<option value="shipping_countries_in">'+woo_discount_localization.shipping_country_list+'</option>' +
                    '<option value="customer_shipping_zip_code">'+woo_discount_localization.customer_shipping_zip_code+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.purchase_history+'">' +
                    '<option value="customer_based_on_first_order">'+woo_discount_localization.first_order_discount+'</option>'+
                    '<option value="customer_based_on_purchase_history">'+woo_discount_localization.purchased_amount+'</option>'+
                    '<option value="customer_based_on_purchase_history_order_count">'+woo_discount_localization.number_of_order_purchased+'</option>'+
                    '<option value="customer_based_on_purchase_history_product_order_count">'+woo_discount_localization.number_of_order_purchased_in_product+'</option>'+
                    '<option value="customer_based_on_purchase_history_product_quantity_count">'+woo_discount_localization.number_of_order_quantity_purchased_in_product+'</option>'+
                    '</optgroup>' +
                    '<optgroup label="'+woo_discount_localization.coupon_applied+'"><option value="create_dynamic_coupon">'+woo_discount_localization.create_a_coupon+'</option><option value="coupon_applied_any_one">'+woo_discount_localization.atleast_any_one+'</option><option value="coupon_applied_all_selected">'+woo_discount_localization.all_selected_coupon+'</option></optgroup>' +
                    '</select></label><div class="wdr_cart_rule_doc_con"></div></div>' +
                    '<div class="col-md-3 form-group"><label> <span class="value_text_' +count+ '">'+woo_discount_localization.value_text+'</span><div id="general_' + count + '"><input type="text" class="form-control" name="discount_rule[' + count + '][option_value]"></div>' +
                    '<div id="user_div_' + count + '">';
                if($('#flycart_wdr_woocommerce_version').val() == 2){
                    form += '<input class="wc-customer-search" style="width: 250px" name="discount_rule[' + count + '][users_to_apply][]" data-placeholder="'+woo_discount_localization.place_holder_search_for_a_user+'"/>';
                } else {
                    form += '<select class="wc-customer-search" style="width: 250px" multiple="multiple" name="discount_rule[' + count + '][users_to_apply][]" data-placeholder="'+woo_discount_localization.place_holder_search_for_a_user+'"></select>';
                }
                form += '</div>' +
                    '<div id="product_div_' + count + '"><select id="cart_product_list_' + count + '" class="product_list selectpicker"  title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][product_to_apply][]"></select></div>' +
                    '<div id="products_div_' + count + '"><div class="form-group" id="products_list_'+ count +'"></div></div>' +
                    '<div id="exclude_on_sale_products_div_' + count + '">'+woo_discount_localization.exclude_sale_products_desc+'</div>' +
                    '<div id="category_div_' + count + '"><select id="cart_category_list_' + count + '" class="category_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][category_to_apply][]"></select></div>';
                if($('#has_large_number_of_coupon').val() == 1){
                    form += '<div id="coupon_div_' + count + '"><select id="cart_coupon_list_' + count + '" class="coupons_selectbox_multi_select_wdr" multiple name="discount_rule[' + count + '][coupon_to_apply][]"></select></div>';
                } else {
                    form += '<div id="coupon_div_' + count + '"><select id="cart_coupon_list_' + count + '" class="coupon_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][coupon_to_apply][]"></select></div>';
                }
                form += '<div id="roles_div_' + count + '"><select id="cart_roles_list_' + count + '" class="roles_list selectpicker"  title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][user_roles_to_apply][]"></select></div>' +
                    '<div id="countries_div_' + count + '"><select id="cart_countries_list_' + count + '" class="country_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][countries_to_apply][]"></select></div>' +
                    '<div id="purchase_history_div_' + count + '">' +
                    '<div class="form-group wdr_hide" id="purchase_history_products_list_'+ count +'">'+
                    '</div>'+
                    '<select class="selectpicker purchased_history_type" data-live-search="true" name="discount_rule['+count+'][purchased_history_type]">' +
                    '<option value="atleast">'+woo_discount_localization.greater_than_or_equal_to+'</option>' +
                    '<option value="less_than_or_equal">'+woo_discount_localization.less_than_or_equal_to+'</option>' +
                    '</select>' +
                    ' <input name="discount_rule[' + count + '][purchased_history_amount]" value="" type="text"/> '+woo_discount_localization.in_order_status+' <select id="order_status_list_' + count + '" class="order_status_list selectpicker"  data-live-search="true" multiple name="discount_rule[' + count + '][purchase_history_order_status][]"></select>' +
                    ' <select class="selectpicker purchased_history_duration" data-index="'+count+'" name="discount_rule['+count+'][purchased_history_duration]">' +
                    '<option value="all_time">'+woo_discount_localization.from_all_previous_orders+'</option>' +
                    '<option value="7_days">'+woo_discount_localization.last_7_days+'</option>' +
                    '<option value="14_days">'+woo_discount_localization.last_14_days+'</option>' +
                    '<option value="30_days">'+woo_discount_localization.last_30_days+'</option>' +
                    '<option value="60_days">'+woo_discount_localization.last_60_days+'</option>' +
                    '<option value="90_days">'+woo_discount_localization.last_90_days+'</option>' +
                    '<option value="180_days">'+woo_discount_localization.last_180_days+'</option>' +
                    '<option value="1_year">'+woo_discount_localization.last_1_year+'</option>' +
                    '<option value="custom_days">'+woo_discount_localization.custom_days+'</option>' +
                    '</select>' +
                    '<span class="purchased_history_duration_days_con" id="purchased_history_duration_days_con_'+count+'">' +
                    '<input name="discount_rule['+count+'][purchased_history_duration_days]" value="" placeholder="30" type="text"/> '+woo_discount_localization.in_days+
                    '</span>' +
                    '</div><div class="notice inline notice-alt notice-warning cart_rule_validation_error wdr_validation_notice"></div>' +
                    '</div><div class="col-md-1"> <label> '+woo_discount_localization.action_text+'</label> <br> <a href=javascript:void(0) class="btn btn-danger remove_cart_rule">'+woo_discount_localization.remove_text+'</a>  </div>' +
                    '</label></div>';
            } else {
                var form = '<div class="cart_rules_list row"> <div class="col-md-3 form-group"> <label>'+woo_discount_localization.type_text+' <select class="form-control cart_rule_type" id="cart_condition_type_' + count + '" name="discount_rule[' + count + '][type]"> <optgroup label="'+woo_discount_localization.cart_subtotal+'"><option value="subtotal_least" selected="selected">'+woo_discount_localization.subtotal_at_least+'</option><option value="subtotal_less">'+woo_discount_localization.subtotal_less_than+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.cart_item_count+'"><option value="item_count_least">'+woo_discount_localization.number_of_line_items_in_cart_at_least+'</option><option value="item_count_less">'+woo_discount_localization.number_of_line_items_in_cart_less_than+'</option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.quantity_sum+'"><option disabled>'+woo_discount_localization.total_number_of_quantities_in_cart_at_least+' <b>' + pro_suffix + '</b></option><option disabled>'+woo_discount_localization.total_number_of_quantities_in_cart_less_than+' <b>' + pro_suffix + '</b></option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.products_in_cart+'">' +
                    '<option disabled>'+woo_discount_localization.products_in_list+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.products_not_in_list+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.exclude_sale_products+' <b>' + pro_suffix + '</b></option>' +
                    '</optgroup>' +
                    '<optgroup label="'+woo_discount_localization.categories_in_cart+'">' +
                    '<option disabled>'+woo_discount_localization.categories_in_cart+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.atleast_one_including_sub_categories+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.in_each_category_cart+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.exclude_categories_in_cart+' <b>' + pro_suffix + '</b></option>' +
                    '</optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_details_must_be_logged_in+'"><option disabled>'+woo_discount_localization.user_in_list+' <b>' + pro_suffix + '</b></option><option disabled>'+woo_discount_localization.user_role_in_list+' <b>' + pro_suffix + '</b></option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_email+'"><option disabled>'+woo_discount_localization.customer_email_tld+' <b>' + pro_suffix + '</b></option><option disabled>'+woo_discount_localization.customer_email_domain+'<b>' + pro_suffix + '</b></option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_billing_details+'"><option disabled>'+woo_discount_localization.customer_billing_city+' <b>' + pro_suffix + '</b></option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.customer_shipping_details+'">' +
                    '<option disabled>'+woo_discount_localization.customer_shipping_city+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.customer_shipping_state+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.shipping_country_list+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.customer_shipping_zip_code+' <b>' + pro_suffix + '</b></option></optgroup>' +
                    '<optgroup label="'+woo_discount_localization.purchase_history+'"><option disabled>'+woo_discount_localization.purchased_amount+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.number_of_order_purchased+' <b>' + pro_suffix + '</b></option>' +
                    '<option disabled>'+woo_discount_localization.number_of_order_purchased_in_product+' <b>' + pro_suffix + '</b></option>' +
                    '</optgroup>' +
                    '<optgroup label="'+woo_discount_localization.coupon_applied+'"><option disabled>'+woo_discount_localization.atleast_any_one+' <b>' + pro_suffix + '</b></option><option disabled>'+woo_discount_localization.all_selected+' <b>' + pro_suffix + '</b></option></optgroup>' +
                    '</select></label><div class="wdr_cart_rule_doc_con"></div></div>' +
                    '<div class="col-md-3 form-group"><label> <span class="value_text_'+count+'">'+woo_discount_localization.value_text+'</span><div id="general_' + count + '"><input type="text" class="form-control" name="discount_rule[' + count + '][option_value]"></div>' +
                    '<div id="user_div_' + count + '"><select id="cart_user_list_' + count + '" class="user_list selectpicker"  title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][users_to_apply][]"></select></div>' +
                    '<div id="product_div_' + count + '"><select id="cart_product_list_' + count + '" class="product_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][product_to_apply][]"></select></div>' +
                    '<div id="category_div_' + count + '"><select id="cart_category_list_' + count + '" class="category_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][category_to_apply][]"></select></div>' +
                    '<div id="roles_div_' + count + '"><select id="cart_roles_list_' + count + '" class="roles_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][user_roles_to_apply][]"></select></div>' +
                    '<div id="countries_div_' + count + '"><select id="cart_countries_list_' + count + '" class="country_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][countries_to_apply][]"></select></div>' +
                    '<div id="purchase_history_div_' + count + '"><select id="order_status_list_' + count + '" class="order_status_list selectpicker" title="'+woo_discount_localization.none_selected+'" data-live-search="true" multiple name="discount_rule[' + count + '][purchase_history_order_status][]"></select></div>' +
                    '</div><div class="col-md-1"> <label> '+woo_discount_localization.action_text+' </label><br><a href=javascript:void(0) class="btn btn-danger remove_cart_rule">'+woo_discount_localization.remove_text+'</a>  </div>' +
                    '</label></div>';
            }

            // Append to Cart rules list.
            $('#cart_rules_list').append(form);
            if(product_list != ''){
                $('#purchase_history_products_list_'+count).html(product_list);
                $('.wc-product-search').trigger( 'wc-enhanced-select-init' );
            }
            if(products_list != ''){
                $('#products_list_'+count).html(products_list);
                $('.wc-product-search').trigger( 'wc-enhanced-select-init' );
            }

            $('.wc-customer-search').trigger( 'wc-enhanced-select-init' );
            $('.coupons_selectbox_multi_select_wdr').trigger('trigger_ajax_select_wdr');

            // Append the List of Values.
            $('#cart_user_list_' + count).append(user_list);
            $('#cart_product_list_' + count).append(product_list);
            $('#products_list_' + count).append(products_list);
            $('#cart_category_list_' + count).append(category_list);
            $('#cart_coupon_list_' + count).append(coupon_list);
            $('#cart_roles_list_' + count).append(roles_list);
            $('#cart_countries_list_' + count).append(country_list);
            $('#order_status_list_' + count).append(order_status_list);

            $('select.purchased_history_duration').trigger('change');
            // Refresh the SelectPicker.
            $('.product_list,.category_list,.coupon_list,.roles_list,.country_list,.order_status_list,.purchased_history_type,.purchased_history_duration').selectpicker('refresh');

            // Default Hide List.
            $('#user_div_' + count).css('display', 'none');
            $('#product_div_' + count).css('display', 'none');
            $('#products_div_' + count).css('display', 'none');
            $('#exclude_on_sale_products_div_' + count).css('display', 'none');
            $('#category_div_' + count).css('display', 'none');
            $('#coupon_div_' + count).css('display', 'none');
            $('#roles_div_' + count).css('display', 'none');
            $('#countries_div_' + count).css('display', 'none');
            $('#purchase_history_div_' + count).css('display', 'none');
            $("#cart_rules_list").trigger('cart_rule_condition_updated');
        });

        $(document).on('change', '.cart_rule_type', function () {
            var id = $(this).attr('id');
            id = id.replace('cart_condition_type_', '');
            var active = $(this).val();
            showOnly(active, id);
            $("#cart_rules_list").trigger('cart_rule_condition_updated');
        });

        $('#cart_rule_discount_type').on('change', function () {
            var option = $(this).val();
            switch (option) {
                case 'shipping_price':
                    $('#cart_rule_discount_value_con').addClass('wdr_hide_important');
                    $('#cart_rule_product_discount_field').addClass('wdr_hide_important');
                    break;
                case 'product_discount':
                    $('#cart_rule_discount_value_con').addClass('wdr_hide_important');
                    $('#cart_rule_product_discount_field').removeClass('wdr_hide_important');
                    break;
                default:
                    $('#cart_rule_product_discount_field').addClass('wdr_hide_important');
                    $('#cart_rule_discount_value_con').removeClass('wdr_hide_important');
                    break;
            }
        });
        $('#cart_rule_discount_type').trigger('change');

        //on change discount type in price discount
        $(document).on('change', '.price_discount_type', function () {
            var discount_amount = $(this).closest('.discount_rule_list').find('.price_discount_amount');
            var price_discount_amount = $(this).closest('.discount_rule_list').find('.price_discount_product_list_con');
            var discount_product_percent_con = $(this).closest('.discount_rule_list').find('.discount_product_percent_con');
            var discount_for_min_quantity_text = $(this).closest('.discount_rule_list').find('.discount_for_min_quantity_text');
            var discount_for_max_quantity_label = $(this).closest('.discount_rule_list').find('.discount_for_max_quantity_label');
            discount_for_max_quantity_label.show();
            discount_for_min_quantity_text.html(woo_discount_localization.min_quantity);
            if(jQuery.inArray($(this).val(), ['product_discount', 'buy_x_get_x', 'buy_x_get_y', 'more_than_one_cheapest', 'more_than_one_cheapest_from_cat', 'more_than_one_cheapest_from_all']) !== -1){
                if(jQuery.inArray($(this).val(), ['buy_x_get_x', 'buy_x_get_y', 'more_than_one_cheapest', 'more_than_one_cheapest_from_cat', 'more_than_one_cheapest_from_all']) !== -1){
                    var discount_product_option_field = $(this).closest('.discount_rule_list').find('.discount_product_option');
                    if($(this).val() == 'buy_x_get_x'){
                        $(discount_product_option_field).val('same_product');
                    } else if($(this).val() == 'buy_x_get_y'){
                        $(discount_product_option_field).val('all');
                    } else {
                        $(discount_product_option_field).val($(this).val());
                        if(woo_discount_localization.enable_fixed_item_count_in_bogo == false){
                            var discount_product_item_count_type_field = $(this).closest('.discount_rule_list').find('.discount_product_item_count_type');
                            $(discount_product_item_count_type_field).val('dynamic');
                            $(discount_product_item_count_type_field).trigger('change');
                        }
                    }
                    $(discount_product_option_field).trigger('change');
                    discount_amount.hide();
                    var discount_type_tool_tip_con = $(this).closest('.discount_rule_list').find('.price_discount_amount_tool_tip_con');
                    var adjustment_tool_tip_text = '';
                    if($(this).val() == 'buy_x_get_x'){
                        adjustment_tool_tip_text = woo_discount_localization.buy_x_get_x_tool_tip_text;
                    } else if($(this).val() == 'buy_x_get_y'){
                        adjustment_tool_tip_text = woo_discount_localization.buy_x_get_y_tool_tip_text;
                    } else if($(this).val() == 'more_than_one_cheapest'){
                        adjustment_tool_tip_text = woo_discount_localization.more_than_one_cheapest_tool_tip_text;
                    } else if($(this).val() == 'more_than_one_cheapest_from_cat'){
                        adjustment_tool_tip_text = woo_discount_localization.more_than_one_cheapest_from_cat_tool_tip_text;
                    } else if($(this).val() == 'more_than_one_cheapest_from_all'){
                        adjustment_tool_tip_text = woo_discount_localization.more_than_one_cheapest_from_all_tool_tip_text;
                    }
                    discount_type_tool_tip_con.html('<span class="woocommerce-help-tip percentage_discount_amount_tool_tip" data-tip="'+adjustment_tool_tip_text+'"></span>').show();
                    trigger_woocommerce_tooltip();
                }
                price_discount_amount.removeClass('hide').show();
                discount_product_percent_con.removeClass('hide').show();
                $(this).closest('.discount_rule_list').find('.hide-for-product-discount').hide();
            } else {
                discount_amount.show();
                price_discount_amount.hide();
                discount_product_percent_con.hide();
                $(this).closest('.discount_rule_list').find('.hide-for-product-discount').show();
                var discount_type_tool_tip_con = $(this).closest('.discount_rule_list').find('.price_discount_amount_tool_tip_con');
                if($(this).val() == 'percentage_discount'){
                    discount_type_tool_tip_con.html('<span class="woocommerce-help-tip percentage_discount_amount_tool_tip" data-tip="'+woo_discount_localization.percentage_discount_amount_tool_tip_text+'"></span>');
                } else if($(this).val() == 'fixed_price'){
                    discount_type_tool_tip_con.html('<span class="woocommerce-help-tip percentage_discount_amount_tool_tip" data-tip="'+woo_discount_localization.fixed_price_discount_amount_tool_tip_text+'"></span>');
                } else if($(this).val() == 'set_discount'){
                    discount_type_tool_tip_con.html('<span class="woocommerce-help-tip percentage_discount_amount_tool_tip" data-tip="'+woo_discount_localization.set_discount_amount_tool_tip_text+'"></span>');
                } else {
                    discount_type_tool_tip_con.html('<span class="woocommerce-help-tip percentage_discount_amount_tool_tip" data-tip="'+woo_discount_localization.price_discount_amount_tool_tip_text+'"></span>');
                }
                trigger_woocommerce_tooltip();

            }
            if($(this).val() == 'set_discount'){
                discount_for_max_quantity_label.hide();
                discount_for_min_quantity_text.html(woo_discount_localization.set_quantity);
            }
            var discount_types = wooo_discount_range_altered();
            $('.set_discount_info_text, .fixed_price_discount_info_text').hide();
            if(discount_types.has_set_discount == true){
                $('.set_discount_info_text').show();
            }
            if(discount_types.has_fixed_price == true){
                $('.fixed_price_discount_info_text').show();
            }
            if(discount_types.has_set_discount == true && discount_types.has_range_discount == true ){
                $('.set_discount_with_range_warning').show();
            } else {
                $('.set_discount_with_range_warning').hide();
            }
        });
        $('.price_discount_type').trigger('change');

        //on change discount_product_option in product discount
        $(document).on('change', 'select.discount_product_option', function () {
            var discount_product = $(this).closest('.price_discount_product_list_con').find('.discount_product_option_list_con');
            var discount_category = $(this).closest('.price_discount_product_list_con').find('.discount_category_option_list_con');
            var discount_product_more_cheapest = $(this).closest('.price_discount_product_list_con').find('.discount_product_option_more_cheapest_con');
            var discount_product_option_bogo_con = $(this).closest('.price_discount_product_list_con').find('.discount_product_option_bogo_con');
            discount_category.addClass('hide');
            discount_product_option_bogo_con.addClass('hide');
            if($(this).val() == 'all' || $(this).val() == 'same_product'){
                discount_product_option_bogo_con.removeClass('hide');
            }
            if($(this).val() == 'any_cheapest_from_all' || $(this).val() == 'more_than_one_cheapest_from_all'){
                discount_product.addClass('hide');
            } else {
                discount_product.removeClass('hide');
            }
            if($(this).val() == 'more_than_one_cheapest' || $(this).val() == 'more_than_one_cheapest_from_all' || $(this).val() == 'more_than_one_cheapest_from_cat'){
                discount_product_more_cheapest.removeClass('hide');
            } else {
                discount_product_more_cheapest.addClass('hide');
            }
            if($(this).val() == 'more_than_one_cheapest_from_cat'){
                discount_product.addClass('hide');
                discount_category.removeClass('hide');
            }
            if($(this).val() == 'same_product'){
                discount_product.addClass('hide');
            }

        });
        $('select.discount_product_option').trigger('change');

        $(document).on('change', 'select.discount_product_item_count_type', function () {
            var optionVal = $(this).val();
            var target = $(this).closest('.discount_product_option_more_cheapest_con').find('.discount_product_items_count_field');
            if (optionVal == 'static') {
                target.removeClass('hide');
            } else {
                target.addClass('hide');
            }
        });
        $('select.discount_product_item_count_type').trigger('change');

        //on change discount_product_discount_type in product discount
        $(document).on('change', 'select.discount_product_discount_type', function () {
            var discount_product_percent_field = $(this).closest('.discount_product_percent_con').find('.discount_product_percent_field');
            if($(this).val() == 'limited_percent'){
                discount_product_percent_field.removeClass('hide');
            } else {
                discount_product_percent_field.addClass('hide');
            }
        });
        $('select.discount_product_discount_type').trigger('change');


        // Saving Cart Rule.
        $('#saveCartRule').on('click', function (event) {
            $(".form-control").removeClass('wdr_invalid');
            $("div.cart_rule_validation_error").hide();
            var form = $('#form_cart_rule').serialize();
            var current = $(this);
            var rule_id = $('#rule_id').val();
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            event.preventDefault();
            if ($('#rule_name').val() == '') {
                alert(woo_discount_localization.please_enter_the_rule_name);
            } else {
                current.val(woo_discount_localization.saving);
                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {action: 'saveCartRule', data: form},
                    beforeSend: function() {
                        loader.show();
                    },
                    complete: function() {
                        loader.hide();
                    },
                    success: function (result) {
                        var result = jQuery.parseJSON( result );
                        // After Status Changed.
                        resizeChart = setTimeout(function () {
                            current.val(woo_discount_localization.save_rule);
                        }, 300);
                        if(result.create_dynamic_coupon != undefined){
                            var error_row_con = $('select.cart_rule_type option[value="create_dynamic_coupon"]:selected');
                            error_row_con.closest('.cart_rules_list').find('input.form-control').val(result.create_dynamic_coupon);
                            $("input[name='dynamic_coupons_to_apply']").val(result.create_dynamic_coupon);
                        }
                        if(result.status == 1){
                            // Reset, if its New Form.
                            if (rule_id == 0) {
                                window.location.replace(admin_url + '&tab=cart-rules');
                            }
                            adminNotice();
                        } else {
                            //woo_discount_rule_error_notice(result.message);
                            if(result.invalid_field != undefined){
                                if(result.invalid_field == "create_dynamic_coupon"){
                                    error_row_con.closest('.cart_rules_list').find('input.form-control').addClass('wdr_invalid');
                                    $("a.restriction_tab").trigger('click');
                                    error_row_con.closest('.cart_rules_list').find("div.cart_rule_validation_error").html("<p>"+result.error_message+"</p>").show();
                                }
                            }
                        }
                    }
                });
            }
        });

        // Change the List to Show, on change of Rule Type.
        $('.cart_rule_type').on('change', function () {
            var id = $(this).attr('id');
            id = id.replace('cart_condition_type_', '');

            $('#cart_user_list_' + id).selectpicker('val', []);
            $('#cart_product_list_' + id).selectpicker('val', []);
            $('#cart_category_list_' + id).selectpicker('val', []);
            $('#cart_coupon_list_' + id).selectpicker('val', []);
            $('#cart_roles_list_' + id).selectpicker('val', []);
            $('#cart_countries_list_' + id).selectpicker('val', []);
            $('#order_status_list_' + id).selectpicker('val', []);

        });

        // Enabling and Disabling the Status of the Rule.
        $('.cart_manage_status').on('click', function (event) {
            event.preventDefault();
            var current = $(this);
            var id = $(this).attr('id');
            var wdr_nonce = $('input[name="wdr_nonce"]').val();
            id = id.replace('state_', '');
            $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: "json",
                data: {action: 'UpdateStatus', id: id, from: 'cart-rules', wdr_nonce: wdr_nonce},
                success: function (response) {
                    // After Status Changed.
                    if (response.status == 'Disable') {
                        current.addClass('btn-warning');
                        current.removeClass('btn-success');
                        current.html(woo_discount_localization.enable_text);
                    } else if (response.status == 'Publish') {
                        current.removeClass('btn-warning');
                        current.addClass('btn-success');
                        current.html(woo_discount_localization.disable_text);
                    }
                    if (response.status_html != '') {
                        $("#status_in_text_"+id).html(response.status_html);
                    }
                }

            });
        });

        // Removing Cart Rule.
        $('.cart_delete_rule').on('click', function (event) {
            event.preventDefault();
            var current = $(this);
            var id = $(this).attr('id');
            var wdr_nonce = $('input[name="wdr_nonce"]').val();
            id = id.replace('delete_', '');
            var confirm_delete = confirm(woo_discount_localization.are_you_sure_to_remove);
            if (confirm_delete) {
                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {action: 'RemoveRule', id: id, from: 'cart-rules', wdr_nonce: wdr_nonce},
                    success: function () {
                        // After Removed.
                        current.closest('tr').remove();
                        location.reload(true);
                    }
                });
            }
        });


        $('.awdr-switch-version-button').on('click', function (event) {
            event.preventDefault();
            var version = $(this).attr('data-version');
            var page = $(this).attr('data-page');
            var nonce = $(this).attr('data-nonce');
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'awdr_switch_version', version: version, page: page, wdr_nonce: nonce},
                success: function (data) {
                    if(data.data.status == true){
                        window.location.replace(data.data.url);
                    }
                    if(data.data.type !== undefined){
                        if(data.data.type == 'auto_install'){
                            $("#wdr_switch_popup").modal("show");
                            $(".wdr_pro_install_message").html(data.data.message);
                        } else {
                            $(".wdr_switch_message").html(data.data.message);
                        }
                    } else {
                        $(".wdr_switch_message").html(data.data.message);
                    }
                }
            });
        });
        $(document).on('click', '#wdr_switch_popup .wdr-close-modal-box, #wdr_switch_popup .modal-sandbox', function (event) {
            $('#wdr_switch_popup').modal('hide');
        });
        $(document).on('click', '.awdr_auto_install_pro_plugin', function (event) {
            event.preventDefault();
            $(".awdr_auto_install_pro_plugin").html("Proccessing please wait..")
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'awdr_auto_install_pro_plugin'},
                success: function (data) {
                    $(".wdr_switch_message").html('');
                    $(".wdr_pro_install_message").html(data);
                    if($("#wdr_pro_install_status").val() == "1"){
                        $('.awdr-switch-version-button').trigger('click');
                    }
                }
            });
        });

        // Removing Cart Condition.
        $(document).on('click', '.remove_cart_rule', function () {
            var confirm_remove = confirm(woo_discount_localization.are_you_sure_to_remove);
            if (confirm_remove) {
                $(this).closest('.cart_rules_list').remove();
                $("#cart_rules_list").trigger('cart_rule_condition_updated');
            }
        });

        $('#based_on_purchase_history').on('change', function () {
            var checked = $( this ).val();
            if(checked == "0" || checked == "" || checked == "first_order"){
                $('#based_on_purchase_history_fields').hide();
            } else {
                $('#based_on_purchase_history_fields').show();
            }
            if(checked == "3" || checked == "4"){
                $("#purchase_history_products").show();
            } else {
                $("#purchase_history_products").hide();
            }
        });
        $('#based_on_purchase_history').trigger('change');

        $('#purchased_history_duration').on('change', function () {
            var duration_value = $( this ).val();
            if(duration_value == "custom_days"){
                $('#purchased_history_duration_days_con').show();
            } else {
                $('#purchased_history_duration_days_con').hide();
            }
        });
        $('#purchased_history_duration').trigger('change');

        $('#price_rule_method').on('change', function () {
            var rule_method = $(this).val();
            $('.price_discounts_con, .price_discount_condition_con').hide();
            $('.'+rule_method+'_discount_cont, .'+rule_method+'_condition_cont').show();
        });
        $('#price_rule_method').trigger('change');

        $('#product_based_condition_quantity_rule').on('change', function () {
            var quantity_values = $(this).val();
            if(quantity_values == 'from'){
                $('.product_based_condition_to').css({"display": "inline-block"})
            } else {
                $('.product_based_condition_to').css({"display": "none"})
            }
        });
        $('#product_based_condition_quantity_rule').trigger('change');

        $('#product_based_condition_get_discount_type').on('change', function () {
            var discount_type_value = $(this).val();
            $('.get_discount_type_product_tag, .get_discount_type_category_tag').hide();
            if(discount_type_value == 'product'){
                $('.get_discount_type_product_tag').show();
            } else {
                $('.get_discount_type_category_tag').show();
            }
        });
        $('#product_based_condition_get_discount_type').trigger('change');

        // product_based_condition_product_to_apply_count_option
        $('#product_based_condition_product_to_apply_count_option').on('change', function () {
            var value = $(this).val();
            if(value == 'all'){
                $('#product_based_condition_product_to_apply_count').css({"display": "none"})
            } else {
                $('#product_based_condition_product_to_apply_count').css({"display": "inline-block"})
            }
        });
        $('#product_based_condition_product_to_apply_count_option').trigger('change');

        $('#wdr_do_bulk_action').on('click', function (event) {
            event.preventDefault();
            var formData = $('#woo_discount_list_form').serializeArray();
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            if($('#bulk-action-selector-top').val() != ''){
                if($('#bulk-action-selector-top').val() == 'delete'){
                    if(!confirm(woo_discount_localization.are_you_sure_to_delete)){
                        return false;
                    }
                }

                if ($("#woo_discount_list_form input:checkbox:checked").length > 0) {
                    formData.push({'name': 'action', 'value': 'doBulkAction'});
                    $.ajax({
                        url: ajax_url,
                        type: 'POST',
                        data: formData,
                        beforeSend: function() {
                            loader.show();
                        },
                        complete: function() {
                            loader.hide();
                        },
                        success: function (response) {
                            jQuery('#woo-admin-message').html(' <div class="notice notice-success is-dismissable"><p>'+response+'</p></div>');
                            location.reload();
                        }
                    });
                } else {
                    alert(woo_discount_localization.please_select_at_least_one_checkbox);
                    return false;
                }
            } else {
                alert(woo_discount_localization.please_select_bulk_action);
                return false;
            }
        });

        function createDuplicateRule(id, type) {
            if(id != undefined && type != undefined){
                var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
                var wdr_nonce = $('input[name="wdr_nonce"]').val();
                $.ajax({
                    url: ajax_url,
                    type: 'POST',
                    data: {'action': 'createDuplicateRule', 'id': id, 'type': type, 'wdr_nonce': wdr_nonce},
                    beforeSend: function() {
                        loader.show();
                    },
                    complete: function() {
                        loader.hide();
                    },
                    success: function (response) {
                        jQuery('#woo-admin-message').html(' <div class="notice notice-success is-dismissable"><p>'+response+'</p></div>');
                        location.reload();
                    }
                });
            }
        }

        $('.duplicate_price_rule_btn').on('click', function (event) {
            event.preventDefault();
            ;            createDuplicateRule($(this).attr('data-id'), 'price_rule');
        });
        $('.duplicate_cart_rule_btn').on('click', function (event) {
            event.preventDefault();
            createDuplicateRule($(this).attr('data-id'), 'cart_rule');
        });
        //--------------------------------------------------------------------------------------------------------------
        //-----------------------------------------------SETTINGS-------------------------------------------------------
        //--------------------------------------------------------------------------------------------------------------

        function woo_email_customizer_save_settings(type, current){
            var form = $('#discount_config').serialize();
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            current.val(woo_discount_localization.saving);
            if(type == 'settings'){
                var enable_bootstrap_pre_val = $('#enable_bootstrap_id').attr('data-val');
                var enable_bootstrap_current_val = $('input[name="enable_bootstrap"]:checked').val();
            }

            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'saveConfig', from: type, data: form},
                beforeSend: function() {
                    loader.show();
                },
                complete: function() {
                    loader.hide();
                },
                success: function () {
                    // After Removed.
                    resizeChart = setTimeout(function () {
                        current.val(woo_discount_localization.save_text);
                        if(type == 'settings'){
                            if(enable_bootstrap_pre_val != enable_bootstrap_current_val){
                                location.reload();
                            }
                        }
                    }, 300);
                    adminNotice();
                }
            });
        }

        $('#saveConfig').on('click', function (event) {
            event.preventDefault();
            woo_email_customizer_save_settings('settings', $(this))
            /*var form = $('#discount_config').serialize();
            var current = $(this);
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            current.val(woo_discount_localization.saving);
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'saveConfig', from: 'settings', data: form},
                beforeSend: function() {
                    loader.show();
                },
                complete: function() {
                    loader.hide();
                },
                success: function () {
                    // After Removed.
                    resizeChart = setTimeout(function () {
                        current.val(woo_discount_localization.save_text);
                    }, 300);
                    adminNotice();
                }
            });*/
        });
        $('#save_taxonomy_config').on('click', function (event) {
            event.preventDefault();
            woo_email_customizer_save_settings('taxonomy', $(this))
        });

        $('#refresh_wdr_cache').on('click', function (event) {
            event.preventDefault();
            var loader = $('.woo_discount_loader_outer > .woo_discount_loader');
            var wdr_nonce = $('input[name="wdr_nonce"]').val();
            $.ajax({
                url: ajax_url,
                type: 'POST',
                data: {action: 'resetWDRCache', wdr_nonce: wdr_nonce},
                beforeSend: function() {
                    loader.show();
                },
                complete: function() {
                    loader.hide();
                },
                success: function (response) {
                    //adminNotice();
                    jQuery('#woo-admin-message').html(' <div class="notice notice-success is-dismissable"><p>'+response+'</p></div>');
                }
            });
        });

        $('input[type=radio][name=enable_variable_product_cache]').change(function() {
            if (this.value == '1') {
                $('.enable_variable_product_cache_con').show();
            } else {
                $('.enable_variable_product_cache_con').hide();
            }
        });
        $('input[type=radio][name=enable_variable_product_cache]:checked').trigger('change');

        $('select#enable_free_shipping').on('change', function () {
            var option = $(this).val();
            if (option == 'woodiscountfree') {
                $('#woodiscount_settings_free_shipping_con').show();
            } else {
                $('#woodiscount_settings_free_shipping_con').hide();
            }
        });
        $('select#enable_free_shipping').trigger('change');

        $(document).on('change', 'select.purchased_history_duration', function () {
            //$('select.purchased_history_duration').on('change', function () {
            var option_val = $(this).val();
            var option_index = $(this).attr('data-index');
            if(option_index != undefined && option_index != null && option_index != ''){
                if (option_val == 'custom_days') {
                    $('#purchased_history_duration_days_con_'+option_index).show();
                } else {
                    $('#purchased_history_duration_days_con_'+option_index).hide();
                }
            }
        });
        $('select.purchased_history_duration').trigger('change');

        function woo_discount_disable_cart_condition_options(field, disable_values) {
            var selected_value = $(field).val();
            $(field).find("option[wdr_altered='1']").removeAttr("disabled");
            if(disable_values.length){
                $.each( disable_values, function( key, value ) {
                    $(field).find("option[value='"+value+"']").attr("disabled", "disabled").attr("wdr_altered", "1");
                });
                if(jQuery.inArray(selected_value, disable_values) !== -1){
                    $(field).find('option:enabled').eq(0).prop('selected',true).trigger("change");
                }
            }
        }

        $("#cart_rules_list").on('cart_rule_condition_updated', function () {
            var cart_condition_type = $(this).find('select.cart_rule_type');
            var allow_promotion_message, has_subtotal_least, has_quantity_least;
            allow_promotion_message = has_subtotal_least = has_quantity_least = false;
            $(".promotion_subtotal_from_con").hide();
            var used_conditions = [];
            $.each( cart_condition_type, function( key, container ) {
                woo_discount_disable_cart_condition_options(container, used_conditions);
                var option_value = $(container).val();
                used_conditions.push(option_value);
                if(option_value == 'subtotal_least'){
                    allow_promotion_message = true;
                    if(option_value == 'subtotal_least'){
                        has_subtotal_least = true;
                        $(".promotion_subtotal_from_con").show();
                    }
                }
            });
            if(allow_promotion_message === true){
                $(".show_promotion_message_cart_block").show();
            } else {
                $(".show_promotion_message_cart_block").hide();
            }
        });
        $("#cart_rules_list").trigger('cart_rule_condition_updated');

        //--------------------------------------------------------------------------------------------------------------
        //-----------------------------------------------SIDE PANEL-----------------------------------------------------
        //--------------------------------------------------------------------------------------------------------------

        $('.woo-side-button').on('click', function () {
            //$('#woo-side-panel').toggle();
            if ($('#sidebar_text').html() == woo_discount_localization.show_text) {
                $('#sidebar_text').html(woo_discount_localization.hide_text);
                $('.woo-side-panel').show();
                $('#sidebar_icon').addClass('dashicons-arrow-left');
                $('#sidebar_icon').removeClass('dashicons-arrow-down');
            } else {
                $('#sidebar_text').html(woo_discount_localization.show_text);
                $('.woo-side-panel').hide();
                $('#sidebar_icon').removeClass('dashicons-arrow-left');
                $('#sidebar_icon').addClass('dashicons-arrow-down');
            }
        });

        $('.coupons_selectbox_multi_select_wdr').on('trigger_ajax_select_wdr', function (event) {
            var wdr_nonce = $('input[name="wdr_search_nonce"]').val();
            $('.coupons_selectbox_multi_select_wdr').selectWoo({
                ajax: {
                    url: ajax_url,
                    type: 'GET',
                    // url: "https://api.github.com/search/repositories",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            wdr_nonce: wdr_nonce,
                            action: 'loadCoupons'
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;
                        if(data.length >= 1){
                            $.each(data, function( index, value ) {
                                value.id = value.post_name;
                            });
                        }
                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 30) < data.length
                            }
                        };
                    },
                    cache: true
                },
                placeholder: woo_discount_localization.coupon_select_box_placeholder,
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 3,
                templateResult: formatCouponSelectBoxWDR,
                templateSelection: formatCouponSelectionWDR
            });
        });
        $('.coupons_selectbox_multi_select_wdr').trigger('trigger_ajax_select_wdr');


        function formatCouponSelectBoxWDR (repo) {
            if (repo.loading) {
                return repo.text;
            }
            var markup = "<div class='select2-result-repository clearfix'>" +
                "<div class='select2-result-repository__meta'>" +
                "<div class='select2-result-repository__title'>" + repo.post_title + "</div>";

            markup += "</div></div>";

            return markup;
        }

        function formatCouponSelectionWDR (repo) {
            return repo.post_title || repo.post_name || repo.text;
        }

    });

    //------------------------------------------------------------------------------------------------------------------
    function processShowOnlyTags(id_prefix, id){
        var availableTags = ["user_div_", "product_div_", "category_div_", "coupon_div_", "general_", "roles_div_", "countries_div_", "purchase_history_div_", "products_div_", "exclude_on_sale_products_div_"];
        $.each(availableTags, function( index, value ) {
            if(value == id_prefix)
                $('#'+value+id).css('display', 'block');
            else
                $('#'+value+id).css('display', 'none');
        });
    }
    function showOnly(option, id) {
        $('.value_text_'+id).css('display', 'block');
        if (option == 'products_atleast_one' || option == 'products_not_in') {
            processShowOnlyTags('product_div_', id);
        } else if (option == 'categories_atleast_one' || option == 'categories_in' || option == 'in_each_category' || option == 'exclude_categories' || option == 'atleast_one_including_sub_categories') {
            processShowOnlyTags('category_div_', id);
        } else if (option == 'coupon_applied_any_one' || option == 'coupon_applied_all_selected') {
            processShowOnlyTags('coupon_div_', id);
        } else if (option == 'users_in') {
            processShowOnlyTags('user_div_', id);
        } else if (option == 'roles_in') {
            processShowOnlyTags('roles_div_', id);
        } else if (option == 'shipping_countries_in') {
            processShowOnlyTags('countries_div_', id);
        } else if (option == 'products_in_list' || option == 'products_not_in_list') {
            processShowOnlyTags('products_div_', id);
        } else if (option == 'exclude_sale_products') {
            $('.value_text_'+id).css('display', 'none');
            processShowOnlyTags('exclude_on_sale_products_div_', id);
        } else if (option == 'customer_based_on_first_order') {
            processShowOnlyTags('general_', id);
            $('.value_text_'+id).css('display', 'none');
            $('#general_'+id).css('display', 'none');
        } else if (option == 'customer_based_on_purchase_history' || option == 'customer_based_on_purchase_history_product_order_count' || option == 'customer_based_on_purchase_history_order_count' || option == 'customer_based_on_purchase_history_product_quantity_count') {
            processShowOnlyTags('purchase_history_div_', id);
            if(option == 'customer_based_on_purchase_history_product_order_count' || option == 'customer_based_on_purchase_history_product_quantity_count'){
                $('#purchase_history_div_'+id+' #purchase_history_products_list_'+id).show();
            } else {
                $('#purchase_history_div_'+id+' #purchase_history_products_list_'+id).hide();
            }
        } else {
            processShowOnlyTags('general_', id);
        }

    }

    function adminNotice() {
        jQuery('#woo-admin-message').html(' <div class="notice notice-success is-dismissable"><p>'+woo_discount_localization.saved_successfully+'</p></div>');

        setTimeout(function () {
            jQuery('#woo-admin-message').html('');
        }, 2000);
    }

    function woo_discount_rule_error_notice(message) {
        jQuery('#woo-admin-message').html(' <div class="notice notice-error is-dismissable"><p>'+message+'</p></div>');

        setTimeout(function () {
            jQuery('#woo-admin-message').html('');
        }, 2000);
    }

    function makeActiveForSelectedTab(selected){
        var container = selected.closest('.nav-tab-wrapper');
        container.find('.nav-tab').removeClass('nav-tab-active');
        selected.addClass('nav-tab-active');
    }

})(jQuery);