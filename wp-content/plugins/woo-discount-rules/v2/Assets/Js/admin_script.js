/* global jQuery, ajaxurl, wdr_data */
jQuery(document).ready(function ($) {
    const alert_counter = {counts: 1};

    /**
     * Filter Block
     */
    $('.wdr-btn-add-product-filter').click(function () {
        wdr_buildrule.show_hide_rule_block({
            showBlockId: ".wdr-filter-block",
            hideBlockId: '.wdr-discount-template, .wdr-advanced-layout-block',
            thisObject: this,
        });
    });

    /*Add filter section*/
    $('.add-product-filter').click(function () {
        var last_index = $('.wdr-filter-group-items').children().last().attr('data-index');
        last_index = CalculateDataIndex(last_index);

        wdr_buildrule.wdr_clone_field({
            addFilterType: '.wdr-build-filter-type',
            addFilterMethod: '.products',
            addRemoveIcon: '.wdr-icon-remove',
            ruleAppendTo: ".wdr-filter-group-items",
            newIndex: last_index
        });
        make_wdr_select2_search($('.wdr-filter-group[data-index="' + last_index + '"]').find('[data-field="autocomplete"]'));
        $('.wdr-filter-group[data-index=' + last_index + ']').append("<div class='wdr_filter_desc_text'>" + wdr_data.localization_data.filter_products + "</div>");
    });

    /*Remove filter section*/
    $(document).on('click', '.remove-current-row', function () {

        if ($('.wdr-filter-group-items > div').length >= 2) {
            wdr_buildrule.remove_wdr_field_group({
                parentsRow: ".wdr-filter-group",
                thisObject: this,
            });
        }
    });

    /*Add filter section while change select option*/
    $(document).on('change', '.wdr-product-filter-type', function () {
        let last_index = $(this).parents('.wdr-filter-group').data('index');
        let current_block = $(this).val();
        wdr_buildrule.remove_wdr_field_group({
            parentRow: $(this).parent(),
        });
        wdr_buildrule.wdr_clone_field({
            addFilterMethod: '.' + current_block,
            addRemoveIcon: '.wdr-icon-remove',
            ruleAppendTo: $(this).parents('.wdr-filter-group'),
            newIndex: last_index
        });
        //
        switch (current_block) {
            case "products":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_products + '</div>');
                break;
            case "product_category":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_Category + '</div>');
                break;
            case "product_attributes":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_Attributes + '</div>');
                break;
            case "product_tags":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_Tags + '</div>');
                break;
            case "product_sku":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_SKUs + '</div>');
                break;
            case "product_on_sale":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_On_sale_products + '</div>');
                break;
            case "all_products":
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_all_products + '</div>');
                break;
            default:
                $('.wdr-filter-group[data-index="' + last_index + '"]').append('<div class="wdr_filter_desc_text">' + wdr_data.localization_data.filter_custom_taxonomies + '</div>');
                break;

        }
        make_wdr_select2_search($(this).parents('.wdr-filter-group').find('[data-field="autocomplete"]'));
    });


    /**
     * Condition Block
     */
    $('.wdr-btn-add-condition').click(function () {
        wdr_buildrule.show_hide_rule_block({
            showBlockId: ".wdr-condition-template",
            hideBlockId: ".wdr-filter-block, .wdr-discount-template, .wdr-advanced-layout-block",
            thisObject: this,
        });

    });

    /*Add Discount section*/
    $('.add-product-condition').click(function () {
        var last_index = $('.wdr-condition-group-items').children().last().attr('data-index');
        last_index = CalculateDataIndex(last_index);
        wdr_buildrule.wdr_clone_field({
            addConditionType: '.wdr-build-condition-type',
            addFilterMethod: '.cart_subtotal',
            addRemoveIcon: '.wdr-icon-remove',
            ruleAppendTo: ".wdr-condition-group-items",
            newIndex: last_index
        });

        if (wdr_data.enable_subtotal_promo_text == '1') {
            wdr_buildrule.wdr_clone_field({
                addConditionType: 'empty-promo',
                addFilterMethod: '.wdr-subtotal-promo-messeage-main',
                addRemoveIcon: '.wdr-icon-remove',
                ruleAppendTo: ".wdr-condition-group-items",
                newIndex: last_index
            });
        }
        $('.subtotal_operator').trigger('change');
    });

    function wdrShowHidePromotionSection(tis){
        let promotion_operator = tis.val();
        let current_promo_index = tis.parents('.wdr-conditions-container').attr("data-index");
        if (promotion_operator == 'greater_than_or_equal' || promotion_operator == 'greater_than') {
            $('.promo_show_hide_' + current_promo_index).show();
        } else {
            $('.promo_show_hide_' + current_promo_index).hide();
        }
    }

    $(document).on('change', '.subtotal_operator', function () {
        if (wdr_data.enable_subtotal_promo_text == '1' ) {
            wdrShowHidePromotionSection($(this));
        }
    });
    $(document).on('change', '.wdr_quantity_operator', function () {
        if ( wdr_data.enable_cart_quantity_promo_text == '1') {
            wdrShowHidePromotionSection($(this));
        }
    });

    /*Remove section*/
    $(document).on('click', '.remove-current-row', function () {
        if ($('.wdr-condition-group-items > div').length >= 2) {
            wdr_buildrule.remove_wdr_field_group({
                parentsRow: ".wdr-conditions-container",
                thisObject: this,
            });
            if (wdr_data.enable_subtotal_promo_text == '1' || wdr_data.enable_cart_quantity_promo_text == '1') {
                let condition_type = $(this).parent('.wdr-btn-remove').siblings('.wdr-condition-type').find('.wdr-product-condition-type').val();
                if (condition_type == 'cart_subtotal') {
                    let promo_index = $(this).parents('.wdr-conditions-container').attr("data-index");
                    $('.promo_show_hide_' + promo_index).remove();
                }
                if (condition_type == 'cart_items_quantity') {
                    let promo_index = $(this).parents('.wdr-conditions-container').attr("data-index");
                    $('.promo_show_hide_' + promo_index).remove();
                }
            }
        }
    });

    /*Add condition section on select option*/
    $(document).on('change', '.wdr-product-condition-type', function () {
        var last_index = $(this).parents('.wdr-condition-group').data('index');
        var current_block = $(this).val();

        wdr_buildrule.remove_wdr_field_group({
            parentRow: $(this).parent()
        });
        wdr_buildrule.wdr_clone_field({
            addFilterMethod: '.' + current_block,
            addRemoveIcon: '.wdr-icon-remove',
            ruleAppendTo: $(this).parents('.wdr-conditions-container'),
            newIndex: last_index
        });

        var promo_index = $(this).parents('.wdr-conditions-container').attr("data-index");
        //if Class Exists then checking the first object that is returned from JQuery
        if($('.promo_show_hide_' + promo_index)[0] != 'undefined'){
            $('.promo_show_hide_' + promo_index).remove();
        }

        if (current_block == 'order_time') {
            $('.wdr_time_picker').datetimepicker({
                datepicker: false,
                format: 'H:i'
            });
        } else if (current_block == 'cart_subtotal') {
            if (wdr_data.enable_subtotal_promo_text == '1') {
                wdr_buildrule.wdr_clone_field({
                    addConditionType: 'empty-promo',
                    addFilterMethod: '.wdr-subtotal-promo-messeage-main',
                    addRemoveIcon: '.wdr-icon-remove',
                    ruleAppendTo: ".wdr-condition-group-items",
                    newIndex: last_index
                });
            }
            $('.subtotal_operator').trigger('change');
        }else if (current_block == 'cart_items_quantity') {
            if (wdr_data.enable_cart_quantity_promo_text == '1') {
                wdr_buildrule.wdr_clone_field({
                    addConditionType: 'empty-promo',
                    addFilterMethod: '.wdr-cart-quantity-promo-messeage-main',
                    addRemoveIcon: '.wdr-icon-remove',
                    ruleAppendTo: ".wdr-condition-group-items",
                    newIndex: last_index
                });
            }
            $('.wdr_quantity_operator').trigger('change');
        }

        //$('.wdr-condition-date').datetimepicker();
        make_wdr_select2_search($(this).parents('.wdr-conditions-container').find('[data-field="autocomplete"]'));
        make_select2_preloaded($(this).parents('.wdr-conditions-container').find('[data-field="preloaded"]'));
        make_select2_all_loaded($(this).parents('.wdr-conditions-container').find('[data-field="autoloaded"]'));
        wdr_initialize_datetime($(this).parents('.wdr-conditions-container').find('[data-field="date"]'));
    });

    /*Initialise and show & hide coupon search section*/
    $(document).on('change', '.wdr_copon_type', function () {
        var coupon_type = $(this).val();

        if (coupon_type === "at_least_one_any") {
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-search').css("display", "none");
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-value').css("display", "none");
            //$(this).parents('.wdr_cart_coupon_group').find('#rm-coupon option:selected').remove();
        } else if (coupon_type === "none_at_all") {
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-search').css("display", "none");
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-value').css("display", "none");
            //$(this).parents('.wdr_cart_coupon_group').find('#rm-coupon option:selected').remove();
        } else if (coupon_type === "custom_coupon") {
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-search').css("display", "none");
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-value').css("display", "block");
            //$(this).parents('.wdr_cart_coupon_group').find('#rm-coupon option:selected').remove();
        } else {
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-value').css("display", "none");
            $(this).parents('.wdr_cart_coupon_group').find('.wdr-cart-coupon-search').css("display", "block");
            make_wdr_select2_search($(this).parents('.wdr-conditions-container').find('[data-field="autocomplete"]'));
        }
    });

    /**
     * Discount Block
     */
    $('.wdr-btn-add-discount').click(function () {
        wdr_buildrule.show_hide_rule_block({
            showBlockId: ".wdr-discount-template",
            hideBlockId: '.wdr-filter-block, .wdr-advanced-layout-block',
            thisObject: this,
        });
    });

    /*Add section*/
    $(document).on('click', '.add_discount_elements', function () {
        //alert(1);
        var data_append = $(this).data('append');
        var discount_ele = $(this).data('discount-method');
        var next_starting_value = $(this).data('next-starting-value');
        var last_index = $('.' + data_append).children().last().attr('data-index');
        last_index = CalculateDataIndex(last_index);
        wdr_buildrule.wdr_clone_field({
            addFilterMethod: '.' + discount_ele,
            ruleAppendTo: '.' + data_append,
            addDiscountElement: 'enable',
            newIndex: last_index
        });

        if (next_starting_value != '' && next_starting_value != 'undefined') {
            $('.' + data_append + ' ' + next_starting_value + ':last-child').find('.awdr_value_selector').val('');
            let bogo_auto_add_value = $('.' + data_append + ' ' + next_starting_value + ':nth-last-child(2)').find('.awdr_auto_add_value').val();
            bogo_auto_add_value = parseInt(bogo_auto_add_value) + 1;
            if (bogo_auto_add_value != '' && !isNaN(bogo_auto_add_value)) {
                $('.' + data_append + ' ' + next_starting_value + ':nth-last-child(1)').find('.awdr_next_value').val(bogo_auto_add_value);
            }
        }
        if (discount_ele == 'add_buyx_gety_range') {
            $('.buyx_gety_individual_range[data-index=' + last_index + ']').find('.awdr-buyx-gety-max').show();
            $('.buyx_gety_individual_range[data-index=' + last_index + ']').find('.awdr-bogo-recurcive').prop("checked", false);
            $('.wdr-buyx-gety-discount-inner').css("border-bottom", "1px solid #ddd");
        } else if (discount_ele == 'add_buyx_getx_range') {
            $('.buyx_getx_individual_range[data-index=' + last_index + ']').find('.awdr-buyx-getx-max').show();
            $('.buyx_getx_individual_range[data-index=' + last_index + ']').find('.awdr-bogo-recurcive').prop("checked", false);
            $('.buyx_getx_individual_range').css("border-bottom", "1px solid #ddd");
        }
        make_wdr_select2_search($('.' + data_append + ' div:last-child').find('[data-field="autocomplete"]'));
        make_wdr_select2_search($('.' + data_append + ' div:last-child').find('[data-list="product_category"]'));
    });

    /*Remove Section*/
    $(document).on('click', '.wdr_discount_remove', function () {
        wdr_buildrule.remove_wdr_field_group({
            parentsRow: '.wdr-discount-group',
            thisObject: this,
        });
    });

    /*Discounts Tabs navigation*/
    $(document).on('click', '[data-click="wdr-bottombar"]', function () {
        var show_discount_content = $(this).data('dtype');
        $(this).hide();
        $('.' + show_discount_content).show();
    });

    function awdr_process_v1_to_v2_migration(awdr_nonce) {
        $.ajax({
            data: {method: 'do_v1_v2_migration', action: 'wdr_ajax', awdr_nonce: awdr_nonce},
            type: 'post',
            url: ajaxurl,
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (data) {
                if (data.data.status != undefined) {
                    if (data.data.status == "completed") {
                        notify(wdr_data.localization_data.processing_migration_success_message, 'success', alert_counter);
                        location.reload();
                    } else {
                        awdr_process_v1_to_v2_migration(awdr_nonce);
                    }
                    $(".wdr_migration_process_status").html(data.data.display_text);
                } else {
                    location.reload();
                }
            }
        });
    }

    /**
     * Process on sale list
     */
    $(document).on('click', '#awdr_rebuild_on_sale_list', function () {
        var rules = $("#awdr_rebuild_on_sale_rules").val();
        $(".awdr_rebuild_on_sale_list_notice").html("");
        if (rules != null) {
            $("#awdr_rebuild_on_sale_list").attr('disabled', "disabled");
            $("#awdr_rebuild_on_sale_list").html(wdr_data.localization_data.rebuild_on_sale_list_processing_text);
            awdr_process_on_sale_list(rules, $(this));
        } else {
            $(".awdr_rebuild_on_sale_list_notice").html(wdr_data.localization_data.rebuild_on_sale_list_error_please_select_rule);
        }
    });

    /**
     * Process on sale list
     */
    $(document).on('click', '#awdr_rebuild_on_sale_list_on_rule_page', function () {
        $(this).attr('disabled', "disabled");
        $(this).html(wdr_data.localization_data.rebuild_on_sale_list_processing_text);
        awdr_process_on_sale_list(null, $(this));
        //$(".awdr_rebuild_on_sale_rule_page_con").removeClass("need_attention");
    });

    function awdr_process_on_sale_list(rules, current_obj) {
        $.ajax({
            data: {
                method: 'rebuild_onsale_list',
                action: 'wdr_ajax',
                rules: rules,
                awdr_nonce: current_obj.attr('data-awdr_nonce')
            },
            type: 'post',
            url: ajaxurl,
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
                current_obj.removeAttr('disabled');
            },
            success: function (data) {
                current_obj.html(wdr_data.localization_data.rebuild_on_sale_list_processed_text);
                current_obj.removeAttr('disabled');
            }
        });
    }

    /**
     * Process migration
     */
    $(document).on('click', '#awdr_do_v1_v2_migration', function () {
        $(".wdr_migration_process").append(wdr_data.localization_data.processing_migration_text);
        awdr_process_v1_to_v2_migration($(this).attr('data-awdr_nonce'));
    });

    /**
     * Skip migration
     */
    $(document).on('click', '#awdr_skip_v1_v2_migration', function () {
        $(".wdr_migration_process").append(wdr_data.localization_data.skip_migration_text);
        $.ajax({
            data: {method: 'skip_v1_v2_migration', action: 'wdr_ajax'},
            type: 'post',
            url: ajaxurl,
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (data) {
                if (data === 'failed') {
                    notify(wdr_data.localization_data.error, 'error', alert_counter);
                } else if (data.data === true) {
                    $(".wdr_migration_process").append(wdr_data.localization_data.skip_migration_success_message);
                    notify(wdr_data.localization_data.skip_migration_success_message, 'success', alert_counter);
                }
                location.reload();
            }
        });
    });

    $('.awdr-switch-version-button').on('click', function (event) {
        event.preventDefault();
        var version = $(this).attr('data-version');
        var page = $(this).attr('data-page');
        var nonce = $(this).attr('data-nonce');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {action: 'awdr_switch_version', version: version, page: page, wdr_nonce: nonce},
            success: function (data) {
                if (data.data.status == true) {
                    window.location.replace(data.data.url);
                }
                $(".wdr_switch_message").html(data.data.message);
            }
        });
    });

    /**
     * Duplicate Rule
     */
    $(document).on('click', '.wdr_duplicate_rule', function () {
        let loader = $('.woo_discount_loader');
        $.ajax({
            data: {
                rowid: $(this).data('duplicate-rule'),
                awdr_nonce: $(this).data('awdr_nonce'),
                method: 'duplicate_rule',
                action: 'wdr_ajax'
            },
            type: 'post',
            url: ajaxurl,
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (data) {
                if (data === 'failed') {
                    notify(wdr_data.localization_data.error, 'error', alert_counter);
                } else {
                    notify(wdr_data.localization_data.duplicate_rule, 'success', alert_counter);
                }
                location.reload();
            }
        });
    });

    /**
     * Delete Rule
     */
    $(document).on('click', '.wdr_delete_rule', function () {
        var wdr_delete_rule_row = $(this).closest('tr');
        if (confirm(wdr_data.localization_data.delete_confirm)) {
            let loader = $('.woo_discount_loader');
            $.ajax({
                data: {
                    rowid: $(this).data('delete-rule'),
                    awdr_nonce: $(this).data('awdr_nonce'),
                    method: 'delete_rule',
                    action: 'wdr_ajax'
                },
                type: 'post',
                url: ajaxurl,
                beforeSend: function () {
                    loader.show();
                },
                complete: function () {
                    loader.hide();
                },
                error: function (request, error) {
                    notify(wdr_data.localization_data.error, 'error', alert_counter);
                },
                success: function (data) {
                    if (data === 'failed') {
                        notify(wdr_data.localization_data.error, 'error', alert_counter);
                    } else {
                        notify(wdr_data.localization_data.deleted_rule, 'success', alert_counter);
                        wdr_delete_rule_row.hide(500, function () {
                            wdr_delete_rule_row.remove();
                        });
                    }
                }
            });
        }
    });

    /**
     * Manage Rule Status
     */
    $(document).on('change', '.wdr_manage_status', function () {
        let change_status = '';
        let parent_tr = $(this).closest('tr');
        if ($(this).prop("checked") == true) {
            change_status = 1;
        } else {
            change_status = 0;
        }
        let loader = $('.woo_discount_loader');
        $.ajax({
            data: {
                rowid: $(this).data('manage-status'),
                awdr_nonce: $(this).data('awdr_nonce'),
                method: 'manage_status',
                action: 'wdr_ajax',
                changeto: change_status
            },
            type: 'post',
            url: ajaxurl,
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (data) {
                if (data === 'failed') {
                    notify(wdr_data.localization_data.error, 'error', alert_counter);
                } else {
                    if (change_status == 1) {
                        $(parent_tr).find('.awdr-enabled-status').show();
                        notify(wdr_data.localization_data.enabled_rule, 'success', alert_counter);
                    } else {
                        $(parent_tr).find('.awdr-enabled-status').hide();
                        notify(wdr_data.localization_data.disabled_rule, 'success', alert_counter);
                    }
                }
            }
        });
    });

    /**
     * ajax search function
     * @param $el
     */
    function make_wdr_select2_search($el) {
        $el.selectWoo({
            width: '100%',
            minimumInputLength: 1,
            placeholder: $el.data('placeholder'),
            escapeMarkup: function (text) {
                return text;
            },
            language: {
                noResults: function () {
                    return wdr_data.labels.select2_no_results;
                },
                errorLoading: function () {
                    /* Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.*/
                    return wdr_data.labels.searching_text;
                }
            },
            ajax: {
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        query: params.term,
                        action: 'wdr_ajax',
                        method: $el.data('list') || 'products',
                        awdr_nonce: $('input[name=wdr_ajax_select2]').val() || '',
                        taxonomy: $el.data('taxonomy') || '',
                        selected: $el.val()
                    };
                },
                processResults: function (response) {
                    return {results: response.data || []};
                }
            }
        });
        $el.parent().find('.select2-search__field').css('width', '100%');
    }

    /**
     * ajax edit search function on document ready
     */
    $('.edit-filters').selectWoo({
        width: '100%',
        minimumInputLength: 1,
        placeholder: wdr_data.labels.placeholders,
        language: {
            noResults: function () {
                return wdr_data.labels.select2_no_results;
            }
        },
        ajax: {
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    query: params.term,
                    action: 'wdr_ajax',
                    method: $(this).data('list') || 'products',
                    awdr_nonce: $('input[name=wdr_ajax_select2]').val() || '',
                    taxonomy: $(this).data('taxonomy') || '',
                    selected: $(this).val()
                };
            },
            processResults: function (response) {
                return {results: response.data || []};
            }
        }
    });
    $('.edit-filters').parent().find('.select2-search__field').css('width', '100%');


    /**
     * Preloaded values search function
     * @param $el
     */
    function make_select2_preloaded($els) {
        $els.each(function (index, el) {
            var $el = $(el);
            var data = wdr_data.lists[$el.data('list')];
            $el.selectWoo({
                width: '100%',
                escapeMarkup: function (text) {
                    return text;
                },
                minimumInputLength: 1,
                placeholder: $el.data('placeholder'),
                language: {
                    noResults: function () {
                        return wdr_data.labels.select2_no_results;
                    }
                },
                data: data
            });

            $el.parent().find('.select2-search__field').css('width', '100%');
        });
    }

    /**
     * Preloaded values search function
     * @param $el
     */
    function make_select2_all_loaded($els) {
        $els.each(function (index, el) {
            var $el = $(el);
            var data = wdr_data.lists[$el.data('list')];
            $el.selectWoo({
                width: '100%',
                escapeMarkup: function (text) {
                    return text;
                },
                placeholder: $el.data('placeholder'),
                language: {
                    noResults: function () {
                        return wdr_data.labels.select2_no_results;
                    }
                },
                data: data
            });

            $el.parent().find('.select2-search__field').css('width', '100%');
        });
    }

    /**
     * ajax edit pre_loaded search function in document ready
     */

    $('.edit-preloaded-values').each(function (index, el) {
        var $el = $(el);
        var data = wdr_data.lists[$el.data('list')];

        $el.selectWoo({
            width: '100%',
            escapeMarkup: function (text) {
                return text;
            },
            minimumInputLength: 1,
            placeholder: $el.data('placeholder'),
            language: {
                noResults: function () {
                    return wdr_data.labels.select2_no_results;
                }
            },
            data: data
        });

        $el.parent().find('.select2-search__field').css('width', '100%');
    });

    function run_preload_values(){
        $('.append-preloaded-values').selectWoo();
       // $('.append-preloaded-values').parent().find('.select2-search__field').css('width', '100%');
    }
    /**
     * ajax edit pre_loaded search function in document ready
     */
    $('.edit-all-loaded-values').each(function (index, el) {
        var $el = $(el);
        var data = wdr_data.lists[$el.data('list')];

        $el.selectWoo({
            width: '100%',
            escapeMarkup: function (text) {
                return text;
            },
            placeholder: $el.data('placeholder'),
            language: {
                noResults: function () {
                    return wdr_data.labels.select2_no_results;
                }
            },
            data: data
        });

        $el.parent().find('.select2-search__field').css('width', '100%');
    });

    /**
     * Date Time picker Initialize for on change
     * @param $els
     */
    function wdr_initialize_datetime($els) {
        $els.each(function (index, el) {
            var $el = $(el);
            var datepicker_type = $el.data('class');

            if (datepicker_type == 'start_dateonly') {
                $('[data-class="' + datepicker_type + '"]').datetimepicker({
                    format: 'Y-m-d',
                    onShow: function (ct) {
                        this.setOptions({
                            maxDate: $('[data-class="end_dateonly"]').val() ? $('[data-class="end_dateonly"]').val() : false
                        })
                    },
                    timepicker: false,
                });
            } else if (datepicker_type == 'end_dateonly') {
                $('[data-class="' + datepicker_type + '"]').datetimepicker({
                    format: 'Y-m-d',
                    onShow: function (ct) {
                        this.setOptions({
                            minDate: $('[data-class="start_dateonly"]').val() ? $('[data-class="start_dateonly"]').val() : false
                        })
                    },
                    timepicker: false,
                });
            } else if (datepicker_type == 'start_datetimeonly') {
                $('[data-class="' + datepicker_type + '"]').datetimepicker({
                    timepicker: true,
                    format: 'Y-m-d H:i',
                    onShow: function (ct) {
                        this.setOptions({
                            maxDate: $('[data-class="end_datetimeonly"]').val() ? $('[data-class="end_datetimeonly"]').val() : false
                        })
                    },
                });
            } else if (datepicker_type == 'end_datetimeonly') {
                $('[data-class="' + datepicker_type + '"]').datetimepicker({
                    timepicker: true,
                    format: 'Y-m-d H:i',
                    onShow: function (ct) {
                        this.setOptions({
                            minDate: $('[data-class="start_datetimeonly"]').val() ? $('[data-class="start_datetimeonly"]').val() : false
                        })
                    },
                });
            }

        });
    }

    /**
     * Calculate Data Index Value
     * @param last_index
     * @returns {number}
     * @constructor
     */
    function CalculateDataIndex(last_index) {
        if (last_index === 0) {
            return 0;
        } else {
            return parseInt(last_index) + 1;
        }
    }

    /**
     * Save Rule using ajax
     */
    $('#wdr-save-rule').submit(function (e) {
        e.preventDefault();
        let validation = woo_discount_rule_validation($(this));
        if (!validation) {
            return false;
        }
        let loader = $('.woo_discount_loader');

        $.ajax({
            data: $(this).serialize(),
            type: 'post',
            url: ajaxurl,
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (response) {
                var data = response.data;
                if (response.success) {
                    if (data.build_index != undefined) {
                        if (data.build_index.required_rebuild != undefined) {
                            if (data.build_index.required_rebuild == true) {
                                $(".awdr_rebuild_on_sale_rule_page_con").addClass("need_attention");
                            }
                        }
                    }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        notify(wdr_data.localization_data.save_rule, 'success', alert_counter);
                    } else {
                        $('.wdr_desc_text.coupon_error_msg').hide();
                        $(".coupon_name_msg").css("border", "");
                        notify(wdr_data.localization_data.save_rule, 'success', alert_counter);
                    }
                } else {
                    if (data.coupon_message) {
                        $(".coupon_name_msg").css("border", "1px solid #FF0000").focus();
                        notify(wdr_data.localization_data.coupon_exists, 'error', alert_counter);
                    }else{
                        for (const [key, value] of Object.entries(data)) {
                            if (data.hasOwnProperty(key)) {
                                value.forEach(function(message){
                                    notify(message, 'error',alert_counter);
                                });
                            }
                        }
                    }
                }
            }
        });
    });

    /**
     * Save and Close Button
     */
    $(document).on('click', '.wdr_save_close', function () {
        $('input[name=wdr_save_close]').val('1');
        $(".wdr_save_stay").click();
    });

    /**
     * Rule validation starts here
     * @param form
     * @returns {boolean}
     */
    function woo_discount_rule_validation(form) {
        let discount_type = $('.awdr-product-discount-type').val();
        let wdr_filter_validations = [];
        let wdr_discount_validations = [];
        let wdr_condition_validations = [];
        switch (discount_type) {
            case 'wdr_simple_discount':
                wdr_filter_validations = wdr_filter_validation();
                wdr_discount_validations = wdr_discount_validation(discount_type);
                wdr_condition_validations = wdr_condition_validation();
                break;
            case 'wdr_cart_discount':
                wdr_filter_validations = wdr_filter_validation();
                wdr_discount_validations = wdr_discount_validation(discount_type);
                wdr_condition_validations = wdr_condition_validation();
                break;
            case 'wdr_free_shipping':
                wdr_condition_validations = wdr_condition_validation();
                break;
            case 'wdr_bulk_discount':
                wdr_filter_validations = wdr_filter_validation();
                wdr_discount_validations = wdr_discount_validation(discount_type);
                wdr_condition_validations = wdr_condition_validation();
                break;
            case 'wdr_set_discount':
                wdr_filter_validations = wdr_filter_validation();
                wdr_discount_validations = wdr_discount_validation(discount_type);
                wdr_condition_validations = wdr_condition_validation();
                break;
            case 'wdr_buy_x_get_x_discount':
                wdr_filter_validations = wdr_filter_validation();
                wdr_discount_validations = wdr_discount_validation(discount_type);
                wdr_condition_validations = wdr_condition_validation();
                break;
            case 'wdr_buy_x_get_y_discount':
                wdr_filter_validations = wdr_filter_validation();
                wdr_discount_validations = wdr_discount_validation(discount_type);
                wdr_condition_validations = wdr_condition_validation();
                break
            default:
            case 'not_selected':
                break;
        }
        if (wdr_filter_validations.indexOf("fails") !== -1) {
            return false;
        }
        if (wdr_discount_validations.indexOf("fails") !== -1) {
            return false;
        }
        if (wdr_condition_validations.indexOf("fails") !== -1) {
            return false;
        }
        return true;
    }

    /**
     * Validate Filter section
     * @returns {[]}
     */
    function wdr_filter_validation() {
        let filter_array = [];
        $('.wdr-filter-group').each(function (index, element) {
            let product_filter = $(element).find('.wdr-product-filter-type').val();
            let product_filter_val = $(element).find('.awdr_validation').val();
            switch (product_filter) {
                case 'all_products':
                    break;
                case 'products':
                    if (!product_filter_val) {
                        filter_array.push("fails");
                        $(element).find('.select2-selection').css("border", "1px solid red");
                        $(element).find('.select2-selection').focus();
                    } else {
                        $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                    }
                    break;
                case 'product_category':
                    if (!product_filter_val) {
                        filter_array.push("fails");
                        $(element).find('.select2-selection').css("border", "1px solid red");
                        $(element).find('.select2-selection').focus();
                    } else {
                        $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                    }
                    break;
                case 'product_attributes':
                    if (!product_filter_val) {
                        filter_array.push("fails");
                        $(element).find('.select2-selection').css("border", "1px solid red");
                        $(element).find('.select2-selection').focus();
                    } else {
                        $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                    }
                    break;
                case 'product_tags':
                    if (!product_filter_val) {
                        filter_array.push("fails");
                        $(element).find('.select2-selection').css("border", "1px solid red");
                        $(element).find('.select2-selection').focus();
                    } else {
                        $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                    }
                    break;
                case 'product_sku':
                    if (!product_filter_val) {
                        filter_array.push("fails");
                        $(element).find('.select2-selection').css("border", "1px solid red");
                        $(element).find('.select2-selection').focus();
                    } else {
                        $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                    }
                    break;
                case 'product_on_sale':
                    break;
                default:
                    if (!product_filter_val) {
                        filter_array.push("fails");
                        $(element).find('.select2-selection').css("border", "1px solid red");
                        $(element).find('.select2-selection').focus();
                    } else {
                        $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                    }
                    break;
            }
        });
        return filter_array;
    }

    /**
     * validate Discount section
     * @param discount_type
     * @returns {[]}
     */
    function wdr_discount_validation(discount_type) {
        let discount_array = [];
        switch (discount_type) {
            case 'wdr_simple_discount':
                let product_discount = $('.product_discount_value').val();
                if (product_discount == '' || !product_discount) {
                    discount_array.push("fails");
                    $('.product_discount_value').css("border", "1px solid red");
                    $('.product_discount_value').focus();
                } else {
                    $('.product_discount_value').css("border", "1px solid #7e8993");
                }
                break;
            case 'wdr_cart_discount':
                let cart_discount = $('.awdr_cart_discount_value').val();
                if (cart_discount == '' || !cart_discount) {
                    discount_array.push("fails");
                    $('.awdr_cart_discount_value').css("border", "1px solid red");
                    $('.awdr_cart_discount_value').focus();
                } else {
                    $('.awdr_cart_discount_value').css("border", "1px solid #7e8993");
                }
                break;
            case 'wdr_free_shipping':
                break;
            case 'wdr_bulk_discount':
                $('.bulk_range_setter').find('.awdr-bulk-group').each(function (index, element) {
                    let min_qty = $(element).find('.bulk_discount_min').val();
                    let max_qty = $(element).find('.bulk_discount_max').val();
                    let max_val = $(element).find('.bulk_discount_value').val();
                    if (min_qty == '' && max_qty == '') {
                        discount_array.push("fails");
                        $(element).find('.bulk_discount_min').css("border", "1px solid red");
                        $(element).find('.bulk_discount_min').focus();
                        $(element).find('.bulk_discount_max').css("border", "1px solid red");
                        $(element).find('.bulk_discount_max').focus();
                    } else {
                        $(element).find('.bulk_discount_min').css("border", "1px solid #7e8993");
                        $(element).find('.bulk_discount_max').css("border", "1px solid #7e8993");
                    }
                    let max_val_int = parseInt(max_val);
                    if (max_val == '' || max_val_int < 0 ) {
                        discount_array.push("fails");
                        $(element).find('.bulk_discount_value').css("border", "1px solid red");
                        $(element).find('.bulk_discount_value').focus();
                    } else {
                        $(element).find('.bulk_discount_value').css("border", "1px solid #7e8993");
                    }
                });
                break;
            case 'wdr_set_discount':
                $('.set_range_setter').find('.awdr-set-group').each(function (index, element) {
                    let min_qty = $(element).find('.set_discount_min').val();
                    let max_val = $(element).find('.set_discount_value').val();
                    if (min_qty == '') {
                        discount_array.push("fails");
                        $(element).find('.set_discount_min').css("border", "1px solid red");
                        $(element).find('.set_discount_min').focus();
                    } else {
                        $(element).find('.set_discount_min').css("border", "1px solid #7e8993");
                    }
                    if (max_val == '') {
                        discount_array.push("fails");
                        $(element).find('.set_discount_value').css("border", "1px solid red");
                        $(element).find('.set_discount_value').focus();
                    } else {
                        $(element).find('.set_discount_value').css("border", "1px solid #7e8993");
                    }
                });
                break;
            case 'wdr_buy_x_get_x_discount':
                $('.buyx_getx_range_setter').find('.buyx_getx_individual_range').each(function (index, element) {
                    let min_qty = $(element).find('.bxgx-min').val();
                    let max_qty = $(element).find('.bxgx-max').val();
                    let free_qty = $(element).find('.bxgx-qty').val();
                    let select_type = $(element).find('.buyx_getx_discount_select').val();
                    let max_val = $(element).find('.bxgx-value').val();
                    if ($(element).find('.awdr-bogo-recurcive').prop("checked") == true) {
                        if (min_qty == '') {
                            discount_array.push("fails");
                            $(element).find('.bxgx-min').css("border", "1px solid red");
                            $(element).find('.bxgx-min').focus();
                        } else {
                            $(element).find('.bxgx-min').css("border", "1px solid #7e8993");
                        }
                    }
                    if (min_qty == '' && max_qty == '') {
                        discount_array.push("fails");
                        $(element).find('.bxgx-min').css("border", "1px solid red");
                        $(element).find('.bxgx-min').focus();
                        $(element).find('.bxgx-max').css("border", "1px solid red");
                        $(element).find('.bxgx-max').focus();
                    } else {
                        $(element).find('.bxgx-min').css("border", "1px solid #7e8993");
                        $(element).find('.bxgx-max').css("border", "1px solid #7e8993");
                    }
                    if (free_qty == '') {
                        discount_array.push("fails");
                        $(element).find('.bxgx-qty').css("border", "1px solid red");
                        $(element).find('.bxgx-qty').focus();
                    } else {
                        $(element).find('.bxgx-qty').css("border", "1px solid #7e8993");
                    }

                    if (select_type != 'free_product' && max_val == '') {
                        discount_array.push("fails");
                        $(element).find('.bxgx-value').css("border", "1px solid red");
                        $(element).find('.bxgx-value').focus();
                    } else {
                        $(element).find('.bxgx-value').css("border", "1px solid #7e8993");
                    }
                });
                break;
            case 'wdr_buy_x_get_y_discount':
                let bxgy_type = $('.select_bxgy_type').val();
                $('.awdr_buyx_gety_range_setter').find('.buyx_gety_individual_range').each(function (index, element) {
                    let min_qty = $(element).find('.bxgy-min').val();
                    let max_qty = $(element).find('.bxgy-max').val();
                    let product_qty = $(element).find('.bxgy-product-selector').val();
                    let category_qty = $(element).find('.bxgy-category-selector').val();
                    let free_qty = $(element).find('.bxgy-qty').val();
                    let select_type = $(element).find('.buyx_gety_discount_select').val();
                    let max_val = $(element).find('.bxgy-val').val();

                    if (bxgy_type == 'bxgy_product') {
                        if (!product_qty) {
                            discount_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                    } else if (bxgy_type == 'bxgy_category') {
                        if (!category_qty) {
                            discount_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                    } else if (bxgy_type == '0' || !bxgy_type) {
                        discount_array.push("fails");
                        $('.select_bxgy_type').css("border", "1px solid red");
                        $('.select_bxgy_type').focus();
                    } else {
                        $('.select_bxgy_type').css("border", "1px solid #7e8993");
                    }

                    if ($(element).find('.awdr-bogo-recurcive').prop("checked") == true) {
                        if (min_qty == '') {
                            discount_array.push("fails");
                            $(element).find('.bxgy-min').css("border", "1px solid red");
                            $(element).find('.bxgy-min').focus();
                        } else {
                            $(element).find('.bxgy-min').css("border", "1px solid #7e8993");
                        }
                    }
                    if (min_qty == '' && max_qty == '') {
                        discount_array.push("fails");
                        $(element).find('.bxgy-min').css("border", "1px solid red");
                        $(element).find('.bxgy-min').focus();
                        $(element).find('.bxgy-max').css("border", "1px solid red");
                        $(element).find('.bxgy-max').focus();
                    } else {
                        $(element).find('.bxgy-min').css("border", "1px solid #7e8993");
                        $(element).find('.bxgy-max').css("border", "1px solid #7e8993");
                    }
                    if (free_qty == '') {
                        discount_array.push("fails");
                        $(element).find('.bxgy-qty').css("border", "1px solid red");
                        $(element).find('.bxgy-qty').focus();
                    } else {
                        $(element).find('.bxgy-qty').css("border", "1px solid #7e8993");
                    }

                    if (select_type != 'free_product' && max_val == '') {
                        discount_array.push("fails");
                        $(element).find('.bxgy-val').css("border", "1px solid red");
                        $(element).find('.bxgy-val').focus();
                    } else {
                        $(element).find('.bxgy-val').css("border", "1px solid #7e8993");
                    }
                });
                break
            default:
            case 'not_selected':
                discount_array.push("fails");
                break;
        }
        return discount_array;
    }

    function wdr_condition_validation() {
        let condition_array = [];
        $('.wdr-condition-group').each(function (index, element) {
            let condition_type = $(element).find('.wdr-product-condition-type').val();
            if (typeof condition_type !== 'undefined') {
                switch (condition_type) {
                    case 'cart_subtotal':
                        let sub_total = $(element).find('.float_only_field').val();
                        if (sub_total == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_items_quantity':
                        let item_qty = $(element).find('.float_only_field').val();
                        if (item_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_coupon':
                        let coupon_type = $(element).find('.wdr_copon_type').val();
                        let custom_coupon = $(element).find('.coupon_name_msg').val();
                        let wc_coupon_val = $(element).find('#rm-coupon').val();
                        if (coupon_type == 'custom_coupon') {
                            if (custom_coupon == '') {
                                condition_array.push("fails");
                                $(element).find('.coupon_name_msg').css("border", "1px solid red");
                                $(element).find('.coupon_name_msg').focus();
                            } else {
                                $(element).find('.coupon_name_msg').css("border", "1px solid #7e8993");
                            }
                        } else {
                            if (!wc_coupon_val) {
                                condition_array.push("fails");
                                $(element).find('.select2-selection').css("border", "1px solid red");
                                $(element).find('.select2-selection').focus();
                            } else {
                                $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                            }
                        }
                        break;
                    case 'cart_items_weight':
                        let item_weight = $(element).find('.float_only_field').val();
                        if (item_weight == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_payment_method':
                        let wc_payment_gateway = $(element).find('.select2-hidden-accessible').val();
                        if (!wc_payment_gateway) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_line_items_count':
                        let line_item_count = $(element).find('.float_only_field').val();
                        if (line_item_count == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_item_product_attributes':
                        let wc_attributes = $(element).find('.awdr-attribute-validation').val();
                        let attribute_qty = $(element).find('.awdr-num-validation').val();
                        if (!wc_attributes) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (attribute_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-num-validation').css("border", "1px solid red");
                            $(element).find('.awdr-num-validation').focus();
                        } else {
                            $(element).find('.awdr-num-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_item_product_category':
                        let wc_category = $(element).find('.awdr-category-validation').val();
                        let category_qty = $(element).find('.awdr-num-validation').val();
                        if (!wc_category) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (category_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-num-validation').css("border", "1px solid red");
                            $(element).find('.awdr-num-validation').focus();
                        } else {
                            $(element).find('.awdr-num-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_item_product_combination':
                        let wc_product = $(element).find('.awdr-product-validation').val();
                        let min_qty = $(element).find('.product_from_qty').val();
                        let max_qty = $(element).find('.product_to_qty').val();
                        let combination_operator = $(element).find('.combination_operator').val();
                        if (!wc_product) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (combination_operator == 'in_range') {
                            if (min_qty == '' && max_qty == '') {
                                condition_array.push("fails");
                                $(element).find('.product_from_qty').css("border", "1px solid red");
                                $(element).find('.product_from_qty').focus();
                                $(element).find('.product_to_qty').css("border", "1px solid red");
                                $(element).find('.product_to_qty').focus();
                            } else {
                                $(element).find('.product_from_qty').css("border", "1px solid #7e8993");
                                $(element).find('.product_to_qty').css("border", "1px solid #7e8993");
                            }
                        } else {
                            if (min_qty == '') {
                                condition_array.push("fails");
                                $(element).find('.product_from_qty').css("border", "1px solid red");
                                $(element).find('.product_from_qty').focus();
                            } else {
                                $(element).find('.product_from_qty').css("border", "1px solid #7e8993");
                            }
                        }
                        break;
                    case 'cart_item_product_sku':
                        let wc_sku = $(element).find('.awdr-sku-validation').val();
                        let sku_qty = $(element).find('.awdr-num-validation').val();
                        if (!wc_sku) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (sku_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-num-validation').css("border", "1px solid red");
                            $(element).find('.awdr-num-validation').focus();
                        } else {
                            $(element).find('.awdr-num-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_item_product_tags':
                        let wc_tags = $(element).find('.awdr-tag-validation').val();
                        let tag_qty = $(element).find('.awdr-num-validation').val();
                        if (!wc_tags) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (tag_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-num-validation').css("border", "1px solid red");
                            $(element).find('.awdr-num-validation').focus();
                        } else {
                            $(element).find('.awdr-num-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_item_products':
                        let wc_products = $(element).find('.awdr-product-validation').val();
                        let product_qty = $(element).find('.awdr-num-validation').val();
                        if (!wc_products) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (product_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-num-validation').css("border", "1px solid red");
                            $(element).find('.awdr-num-validation').focus();
                        } else {
                            $(element).find('.awdr-num-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'cart_item_category_combination':
                        let wc_cat = $(element).find('.awdr-cat-validation').val();
                        let from_qty = $(element).find('.cat_from_qty').val();
                        let to_qty = $(element).find('.cat_to_qty').val();
                        let cat_combination = $(element).find('.cat_combination_operator').val();
                        if (!wc_cat) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (cat_combination == 'in_range') {
                            if (from_qty == '' && to_qty == '') {
                                condition_array.push("fails");
                                $(element).find('.cat_from_qty').css("border", "1px solid red");
                                $(element).find('.cat_from_qty').focus();
                                $(element).find('.cat_to_qty').css("border", "1px solid red");
                                $(element).find('.cat_to_qty').focus();
                            } else {
                                $(element).find('.cat_from_qty').css("border", "1px solid #7e8993");
                                $(element).find('.cat_to_qty').css("border", "1px solid #7e8993");
                            }
                        } else {
                            if (from_qty == '') {
                                condition_array.push("fails");
                                $(element).find('.cat_from_qty').css("border", "1px solid red");
                                $(element).find('.cat_from_qty').focus();
                            } else {
                                $(element).find('.cat_from_qty').css("border", "1px solid #7e8993");
                            }
                        }
                        break;
                    case 'order_date':
                        let from_date = $(element).find('.awdr-from-date').val();
                        let end_date = $(element).find('.awdr-end-date').val();
                        if (from_date == '' && end_date == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-from-date').css("border", "1px solid red");
                            $(element).find('.awdr-from-date').focus();
                            $(element).find('.awdr-end-date').css("border", "1px solid red");
                            $(element).find('.awdr-end-date').focus();
                        } else {
                            $(element).find('.awdr-from-date').css("border", "1px solid #7e8993");
                            $(element).find('.awdr-end-date').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'order_date_and_time':
                        let from_date_time = $(element).find('.awdr-from-date').val();
                        let end_date_time = $(element).find('.awdr-end-date').val();
                        if (from_date_time == '' && end_date_time == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-from-date').css("border", "1px solid red");
                            $(element).find('.awdr-from-date').focus();
                            $(element).find('.awdr-end-date').css("border", "1px solid red");
                            $(element).find('.awdr-end-date').focus();
                        } else {
                            $(element).find('.awdr-from-date').css("border", "1px solid #7e8993");
                            $(element).find('.awdr-end-date').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'order_days':
                        let order_days = $(element).find('.order_days').val();
                        if (!order_days) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'order_time':
                        let from_time = $(element).find('.wdr-from-time').val();
                        let end_time = $(element).find('.wdr-to-time').val();
                        if (from_time == '' && end_time == '') {
                            condition_array.push("fails");
                            $(element).find('.wdr-from-time').css("border", "1px solid red");
                            $(element).find('.wdr-from-time').focus();
                            $(element).find('.wdr-to-time').css("border", "1px solid red");
                            $(element).find('.wdr-to-time').focus();
                        } else {
                            $(element).find('.wdr-from-time').css("border", "1px solid #7e8993");
                            $(element).find('.wdr-to-time').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'purchase_last_order':
                        let last_order = $(element).find('.wdr-wc-order-status').val();
                        if (!last_order) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'purchase_last_order_amount':
                        let last_order_amount_status = $(element).find('.wdr-wc-order-status').val();
                        let last_order_amount = $(element).find('.float_only_field').val();
                        if (!last_order_amount_status) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (last_order_amount == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'purchase_previous_orders':
                        let made_order_status = $(element).find('.wdr-wc-order-status').val();
                        let made_order_amount = $(element).find('.float_only_field').val();
                        if (!made_order_status) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (made_order_amount == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'purchase_previous_orders_for_specific_product':
                        let made_order_status_for_product = $(element).find('.wdr-wc-order-status').val();
                        let made_order_amount_for_product = $(element).find('.float_only_field').val();
                        let ordered_product = $(element).find('.specific_product').val();
                        if (!ordered_product) {
                            condition_array.push("fails");
                            $(element).find('.wdr-previous-order-product-selector .select2-selection').css("border", "1px solid red");
                            $(element).find('.wdr-previous-order-product-selector .select2-selection').focus();
                        } else {
                            $(element).find('.wdr-previous-order-product-selector .select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (!made_order_status_for_product) {
                            condition_array.push("fails");
                            $(element).find('.wdr-previous-order-product-status .select2-selection').css("border", "1px solid red");
                            $(element).find('.wdr-previous-order-product-status .select2-selection').focus();
                        } else {
                            $(element).find('.wdr-previous-order-product-status .select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (made_order_amount_for_product == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'purchase_quantities_for_specific_product':
                        let status_for_product_qty = $(element).find('.wdr-wc-order-status').val();
                        let amount_for_product_qty = $(element).find('.float_only_field').val();
                        let product_ordered = $(element).find('.specific_product').val();
                        if (!product_ordered) {
                            condition_array.push("fails");
                            $(element).find('.wdr-previous-order-product-selector .select2-selection').css("border", "1px solid red");
                            $(element).find('.wdr-previous-order-product-selector .select2-selection').focus();
                        } else {
                            $(element).find('.wdr-previous-order-product-selector .select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (!status_for_product_qty) {
                            condition_array.push("fails");
                            $(element).find('.wdr-previous-order-product-status .select2-selection').css("border", "1px solid red");
                            $(element).find('.wdr-previous-order-product-status .select2-selection').focus();
                        } else {
                            $(element).find('.wdr-previous-order-product-status .select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (amount_for_product_qty == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }

                        break;
                    case 'purchase_spent':
                        let spent_status = $(element).find('.wdr-wc-order-status').val();
                        let spent_amount = $(element).find('.float_only_field').val();
                        if (!spent_status) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        if (spent_amount == '') {
                            condition_array.push("fails");
                            $(element).find('.float_only_field').css("border", "1px solid red");
                            $(element).find('.float_only_field').focus();
                        } else {
                            $(element).find('.float_only_field').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'shipping_city':
                        let shipping_city = $(element).find('.awdr-validation').val();
                        if (shipping_city == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-validation').css("border", "1px solid red");
                            $(element).find('.awdr-validation').focus();
                        } else {
                            $(element).find('.awdr-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'shipping_country':
                        let shipping_country = $(element).find('.get_awdr_shipping_country').val();
                        if (!shipping_country) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'shipping_state':
                        let shipping_state = $(element).find('.get_awdr_shipping_state').val();
                        let shipping_state_based_country = $(element).find('.get_awdr_state_based_country').val();
                        if (!shipping_state) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }

                        if (!shipping_state_based_country) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'shipping_zipcode':
                        let shipping_zipcode = $(element).find('.awdr-validation').val();
                        if (shipping_zipcode == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-validation').css("border", "1px solid red");
                            $(element).find('.awdr-validation').focus();
                        } else {
                            $(element).find('.awdr-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'Billing_city':
                        let Billing_city = $(element).find('.awdr-validation').val();
                        if (Billing_city == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-validation').css("border", "1px solid red");
                            $(element).find('.awdr-validation').focus();
                        } else {
                            $(element).find('.awdr-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'user_email':
                        let user_email = $(element).find('.awdr-validation').val();
                        if (user_email == '') {
                            condition_array.push("fails");
                            $(element).find('.awdr-validation').css("border", "1px solid red");
                            $(element).find('.awdr-validation').focus();
                        } else {
                            $(element).find('.awdr-validation').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'user_list':
                        let user_list = $(element).find('.wdr_user_list').val();
                        if (!user_list) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                    case 'user_role':
                        let user_role = $(element).find('.wdr_user_role').val();
                        if (!user_role) {
                            condition_array.push("fails");
                            $(element).find('.select2-selection').css("border", "1px solid red");
                            $(element).find('.select2-selection').focus();
                        } else {
                            $(element).find('.select2-selection').css("border", "1px solid #7e8993");
                        }
                        break;
                }
            }
        });
        return condition_array;
    }

    /**
     * save default configuration settings
     */
    $('#configuration-form').submit(function (e) {
        e.preventDefault();
       /* $("#awdr_banner_editor-html").click();
        $("#awdr_banner_editor-tmce").click();
        let awdr_banner_editer = $('#awdr_banner_editor').val();*/
        /*$('.customize_banner_content').val(awdr_banner_editer);*/
        let values = $(this).serialize();
        let loader = $('.woo_discount_loader');
       /* values += "&banner_content=" + awdr_banner_editer;*/
        $.ajax({
            data: values,
            type: 'post',
            url: ajaxurl,
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (response) {
                switch (response.data.save_popup ){
                    case 'alert_in_popup':
                        if (response.data.save_popup == "alert_in_popup" && response.data.security_pass == "passed") {
                            $('.awdr-save-green').show();
                            setTimeout(
                                function () {
                                    $('.awdr-save-green').fadeOut(500);
                                    window.location.replace(wdr_data.admin_url+'&tab=settings');
                                }, 2000
                            );
                        }else if(response.data.save_popup == "alert_in_popup" && response.data.security_pass == "fails"){
                            $('.awdr-error-red').show();
                            setTimeout(
                                function () {
                                    $('.awdr-error-red').fadeOut(500);
                                    window.location.replace(wdr_data.admin_url+'&tab=settings');
                                }, 2000
                            );

                        }
                        break;
                    case 'alert_in_normal':
                        if (response.data.save_popup == "alert_in_normal" && response.data.security_pass == "passed") {
                            notify(wdr_data.localization_data.save_settings, 'success', alert_counter);
                            setTimeout(
                                function () {
                                    window.location.replace(wdr_data.admin_url+'&tab=settings');
                                }, 1000
                            );

                        }else if(response.data.save_popup == "alert_in_normal" && response.data.security_pass == "fails"){
                            notify(wdr_data.localization_data.error, 'error', alert_counter);
                            setTimeout(
                                function () {
                                    window.location.replace(wdr_data.admin_url+'&tab=settings');
                                }, 1000
                            );
                        }
                        break;
                    default:
                        $('.awdr-error-red').show();
                        notify(wdr_data.localization_data.error, 'error', alert_counter);
                        setTimeout(
                            function () {
                                $('.awdr-error-red').fadeOut(500);
                                window.location.replace(wdr_data.admin_url+'&tab=settings');
                            }, 1000
                        );
                        break;
                }
            }
        });
    });

    /**
     * save default configuration settings
     */
    $('#awdr_advanced_configuration_form').submit(function (e) {
        e.preventDefault();
        let values = $(this).serialize();
        let loader = $('.woo_discount_loader');
        $.ajax({
            data: values,
            type: 'post',
            url: ajaxurl,
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (response) {

                if (response.data.security_pass == "passed") {
                    $('.awdr-save-green').show();
                    setTimeout(
                        function () {
                            $('.awdr-save-green').fadeOut(500);
                            window.location.replace(wdr_data.admin_url+'&tab=advanced_section');
                        }, 2000
                    );
                }else if(response.data.security_pass == "fails"){
                    $('.awdr-error-red').show();
                    setTimeout(
                        function () {
                            $('.awdr-error-red').fadeOut(500);
                            window.location.replace(wdr_data.admin_url+'&tab=advanced_section');
                        }, 2000
                    );

                }
            }
        });
    });

    /**
     * Save and Close settings
     */
    $(document).on('click', '.bulk-table-customized-setting', function () {
        $('input[name=customizer_save_alert]').val('1');
        $(".save-configuration-submit").click();
    });

    $('#sort_customizable_table').dragtable({
        persistState: function (table) {
            table.el.find('th').each(function (i) {
                if (this.id != '') {
                    table.sortOrder[this.id] = i;
                    if (this.id == 'customize-bulk-table-title') {
                        $('.customize_bulk_table_title').val(i);
                    } else if (this.id == 'customize-bulk-table-discount') {
                        $('.customize_bulk_table_discount').val(i);
                    } else if (this.id == 'customize-bulk-table-range') {
                        $('.customize_bulk_table_range').val(i);
                    }
                }
            });
        }
    });


    /**
     * Display page bulk action on top
     */
    $('#wdr-bulk-action-top').submit(function (e) {
        e.preventDefault();
        if ($('input[name="saved_rules[]"]:checked').length > 0) {
            let action = $('#bulk-action-selector-top').val();
            let result;
            if (action == 'enable') {
                result = confirm("Are you sure to enable the selected rules?");
            } else if (action == 'disable') {
                result = confirm("Are you sure to disable the selected rules?");
            } else if (action == 'delete') {
                result = confirm("Are you sure to delete the selected rules?");
            } else {
                return false;
            }
            if (result == false) {
                return false;
            }
        } else {
            return false;
        }
        let loader = $('.woo_discount_loader');
        $.ajax({
            data: $(this).serialize(),
            type: 'post',
            url: ajaxurl,
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (data) {
                if (data.disable == 'disabled') {
                    notify(wdr_data.localization_data.disabled_rule, 'success', alert_counter);
                } else if (data.enable == 'enabled') {
                    notify(wdr_data.localization_data.enabled_rule, 'success', alert_counter);
                } else if (data.delete == 'deleted') {
                    notify(wdr_data.localization_data.deleted_rule, 'success', alert_counter);
                } else {
                    notify(wdr_data.localization_data.error, 'error', alert_counter);
                }
                window.location.replace(wdr_data.admin_url);
            }
        });
    });

    /**
     * Display page search action on top
     */
    $('#wdr-search-top').submit(function (e) {
        e.preventDefault();
        var search_value = $(this).serializeArray();
        var adminUrl = search_value[0].value;
        var searchQry = search_value[1].value;
        var redirectUrl = adminUrl + '&name=' + searchQry;
        window.location.replace(redirectUrl);
    });

    /**
     * Number Validation
     * float only
     */
    $(document).on("keypress keyup blur", ".float_only_field", function (event) {
        $(this).val($(this).val().replace(/[^0-9\.]/g, ''));
        if ((event.which !== 46 || $(this).val().indexOf('.') !== -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    /**
     * Number only
     */
    $(document).on("keypress keyup blur", ".number_only_field", function (event) {
        if ((event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    /**
     * Initialize date pickers in document ready
     */
    $('[data-class="start_dateonly"]').datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        onShow: function (ct) {
            this.setOptions({
                maxDate: $('[data-class="end_dateonly"]').val() ? $('[data-class="end_dateonly"]').val() : false
            })
        },
    });
    $('[data-class="end_dateonly"]').datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        onShow: function (ct) {
            this.setOptions({
                minDate: $('[data-class="start_dateonly"]').val() ? $('[data-class="start_dateonly"]').val() : false
            })
        },
    });

    $('[data-class="start_datetimeonly"]').datetimepicker({
        timepicker: true,
        format: 'Y-m-d H:i',
        onShow: function (ct) {
            this.setOptions({
                maxDate: $('[data-class="end_datetimeonly"]').val() ? $('[data-class="end_datetimeonly"]').val() : false
            })
        },
    });
    $('[data-class="end_datetimeonly"]').datetimepicker({
        timepicker: true,
        format: 'Y-m-d H:i',
        onShow: function (ct) {
            this.setOptions({
                minDate: $('[data-class="start_datetimeonly"]').val() ? $('[data-class="start_datetimeonly"]').val() : false
            })
        },
    });

    $('.wdr_time_picker').datetimepicker({
        datepicker: false,
        format: 'H:i'
    });

    /**
     * bulk discount
     * hide and show the selected category
     */
    $(document).on('change', '.wdr-bulk-type', function () {

        var bulk_type = $(this).val();
        if (bulk_type == 'product_selected_categories') {
            $('.wdr-bulk-cat-selector').show();
        } else {
            // $('.wdr-bulk-cat-selector').hide().find('#rm-category option:selected').remove();
            $('.wdr-bulk-cat-selector').hide();
        }
    });

    /**
     * alert notification
     * @param message
     * @param type
     * @param alert_counter
     */
    function notify(message, type = "success", alert_counter = null) {

        switch (type) {
            case "error":
                var class_name = "wdr-alert-error";
                break;
            case "warning":
                var class_name = "wdr-alert-warning";
                break;
            default:
            case "success":
                var class_name = "wdr-alert-success";
                break;
        }

        let div_id = 'wdr-notify-msg-' + alert_counter.counts;
        let html = '<div style="display: none;" class="wdr-alert ' + class_name + '" id="' + div_id + '">' + message + '</div>';
        let notify_holder = $("#notify-msg-holder");
        notify_holder.append(html);
        let message_div = $("#" + div_id);
        var notify_count = alert_counter.counts;
        alert_counter.counts = parseInt(notify_count) + parseInt(1);
        message_div.fadeIn(500);
        setTimeout(
            function () {
                message_div.fadeOut(500);
                message_div.remove();
            }, 5000
        );
    }

    /**
     * settings
     * settings show hide option
     */
    $('.settings_option_show_hide').click(function () {
        var show_hide_class = $(this).data("name");
        if ($(this).val() == '1') {
            $('.' + show_hide_class).show();
            if (show_hide_class == 'hide_table_position') {
                $('.wdr-popup-link').show();
            }
        } else {
            $('.' + show_hide_class).hide();
            if (show_hide_class == 'hide_table_position') {
                $('.wdr-popup-link').hide();
            }
        }
    });

    /**
     * show hide bulk table options
     */
    $('.bulk_table_customizer_preview').click(function () {
        var col_name = $(this).data("colname");
        var show_hide = $(this).data("showhide");
        if (show_hide == 'show') {
            $('.' + col_name).show();
        } else {
            $('.' + col_name).hide();
        }
    });

    /**
     * settings
     * You saved text show hide option
     */
    $('.settings_option_show_hide_on_change').change(function () {

        if ($(this).val() == 'disabled') {
            $('.display_you_saved_text').hide();
        } else {
            $('.display_you_saved_text').show();
        }
    });

    /**
     * rules listing page
     * select and unselect the checkbox for bulk action
     */
    $(document).on('change', '.wdr-rules-select', function () {
        var bulk_selector = $(".wdr-rules-select");
        var selectable = $(this).val();
        if (selectable == 'off') {
            bulk_selector.val('on');
            $(".wdr-rules-selector").prop('checked', true);
        } else if (selectable == 'on') {
            bulk_selector.val('off');
            $(".wdr-rules-selector").prop('checked', false);

        }
    });
    /**
     * check all selected checkbox and check bulk action checkbox
     */
    $(document).on('change', '.wdr-rules-selector', function () {

        var bulk_selector = $(".wdr-rules-select");
        var totalCheckboxes = $('.wdr-ruleboard ' + 'input:checkbox').length;
        var numberOfChecked = $('.wdr-ruleboard ' + 'input:checkbox:checked').length;

        if (totalCheckboxes == numberOfChecked) {
            bulk_selector.val('on');
            bulk_selector.prop('checked', true);
        } else if (totalCheckboxes != numberOfChecked) {
            bulk_selector.val('off');
            bulk_selector.prop('checked', false);
        }
    });

    /**
     * Sticky Header
     */
    /* window.onscroll = function () {
         stickyHeader();
     };*/

    var header = document.getElementById("ruleHeader");

    function stickyHeader() {

        if (header != null) {
            var sticky = header.offsetTop;
            if (window.pageYOffset > sticky) {
                header.classList.add("wdr-sticky");
            } else {
                header.classList.remove("wdr-sticky");
            }
        }
    }

    /**
     * sorting rule priority
     */
    $("#sortable").sortable({
        delay: 150,
        stop: function (event, ui) {
            var selectedData = new Array();
            $('#sortable>tr').each(function () {
                selectedData.push($(this).attr("id"));
            });
            updatePriorityOrder(selectedData);
        }
    });

    function updatePriorityOrder(data) {
        $.ajax({
            data: {
                position: data,
                method: 'update_priority_order',
                action: 'wdr_ajax',
                awdr_nonce: $("input[name=awdr_rule_list_nonce]").val()
            },
            type: 'post',
            url: ajaxurl,
            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (data) {
                if (data === false) {
                    notify(wdr_data.localization_data.error, 'error', alert_counter);
                } else {
                    notify(wdr_data.localization_data.save_priority, 'success', alert_counter);
                }
            }
        });
    }

    $("#sortable").disableSelection();

    /**
     * read doc popup
     */
    $(document).on('click', '.help-popup', function () {
        var popup_id = '#' + $(this).attr('data-id');
        $(popup_id).dialog({
            modal: true,
            closeText: ''
        });
    });

    /**
     * Discount Buttons Show Hide
     */
    $(document).on('click', '.remove-clicked-discount-block', function () {
        var show_discount_button = $(this).data('showblock');
        var remove_value = $(this).data('removeval');
        var remove_option = $(this).data('removeopt');
        $('.' + show_discount_button).hide();
        $('.' + remove_value).removeAttr('value');
        $('.' + remove_option).prop('selectedIndex', 0);
        $('[data-dtype=' + show_discount_button + ']').show();

        if (show_discount_button == "wdr-bulk-discount") {
            $('.bulk_product_category_selector option:selected').remove();
            $('.wdr-bulk-cat-selector').hide();
        }
    });

    /**
     * Advanced layout tab
     */
    $('.wdr-btn-add-message').click(function () {
        wdr_buildrule.show_hide_rule_block({
            showBlockId: ".wdr-advanced-layout-block",
            hideBlockId: '.wdr-discount-template, .wdr-filter-block',
            thisObject: this,
        });
    });


    var bulk_min_range_length = $('.bulk-min').length;
    if (bulk_min_range_length >= 2) {
        var min_val = $('.bulk_discount_min').val();
        var max_val = $('.bulk_discount_max').val();
        var discount_val = $('.bulk_discount_value').val();
        if (min_val || max_val || discount_val) {
            $('.adv-msg-min-qty, .adv-msg-max-qty').show();
        }
    }

    var bulk_min_range_length = $('.set-min').length;
    if (bulk_min_range_length >= 2) {
        var min_val = $('.set_discount_min').val();
        var discount_val = $('.set_discount_value').val();
        if (min_val || discount_val) {
            $('.adv-msg-min-qty').show();
            $('.adv-msg-max-qty').hide();
        }
    }

    /**
     * settings
     * table discount column show hide option
     */
    $('.popup_table_discount_column_value').click(function () {

        if ($(this).val() == 1) {
            $('.wdr_table_discounted_value').show();
            $('.wdr_table_discounted_price').hide();
        } else {
            $('.wdr_table_discounted_value').hide();
            $('.wdr_table_discounted_price').show();
        }
    });
    /**
     * Model popup
     */
    $(".modal-trigger").click(function (e) {
        e.preventDefault();
        dataModal = $(this).attr("data-modal");
        $("#" + dataModal).css({"display": "block"});
        // $("body").css({"overflow-y": "hidden"}); //Prevent double scrollbar.
    });

    $(".close-modal, .modal-sandbox").click(function () {
        $(".modal").css({"display": "none"});
        // $("body").css({"overflow-y": "auto"}); //Prevent double scrollbar.
    });

    $('#badge_colorpicker').on('change', function () {
        $('#badge_hexcolor').val(this.value);
    });
    $('#badge_hexcolor').on('change', function () {
        $('#badge_colorpicker').val(this.value);
    });

    $('#text_colorpicker').on('change', function () {
        $('#text_hexcolor').val(this.value);
    });
    $('#text_hexcolor').on('change', function () {
        $('#text_colorpicker').val(this.value);
    });

    /**
     * shortcode copy function
     * @param element
     */
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
    }

    /**
     * Shortcode copy event
     */
    $('#awdr_shortcode_copy_btn').on('click', function () {
        copyToClipboard("#awdr_shortcode_text");
        var btn = $(this);
        btn.html(wdr_data.localization_data.copied);
        setTimeout(
            function () {
                btn.html(wdr_data.localization_data.copy_shortcode);
            }, 2000
        );
    });

    /**
     * Woocommerce Tooltip event
     */
    function awdr_trigger_woocommerce_tooltip() {
        $('.tips, .help_tip, .woocommerce-help-tip').tipTip({
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
        });
    }

    awdr_trigger_woocommerce_tooltip();

    /**
     * Change Title text dynamically in cuztomizer table
     */
    $('.awdr_popup_col_name_text_box').keyup(function () {
        var column_text = $(this).val();
        var column_class = $(this).data("keyup");
        $('.' + column_class).html(column_text);
    });
    $('.awdr_popup_col_name_text_box').blur(function () {
        var column_text = $(this).val();
        var column_class = $(this).data("keyup");
        $('.' + column_class).html(column_text);
    });

    /**
     * Show/Hide apply discount subsequently row in settings tab
     */
    $('.apply_product_and_cart_discount_to').change(function () {
        let subsequent_class = $(this).data("subsequent");
        if ($(this).val() == 'all') {
            $('.' + subsequent_class).show();
        } else {
            $('.' + subsequent_class).hide();
        }
    });

    /**
     * Bogo Discount Type
     */
    $(document).on('change', '.awdr-bogo-discount-type', function () {
        let get_bogo_free_type = $(this).val();
        let get_bogo_free_type_parent = $(this).attr('data-parent');
        let get_bogo_free_type_siblings = $(this).attr('data-siblings');

        switch (get_bogo_free_type) {
            case 'flat':
                $(this).parent('.' + get_bogo_free_type_parent).siblings('.' + get_bogo_free_type_siblings).show();
                $(this).parent('.' + get_bogo_free_type_parent).siblings('.' + get_bogo_free_type_siblings).find('.wdr_desc_text').text(wdr_data.localization_data.buyx_getx_value);
                break;
            case 'percentage':
                $(this).parent('.' + get_bogo_free_type_parent).siblings('.' + get_bogo_free_type_siblings).show();
                $(this).parent('.' + get_bogo_free_type_parent).siblings('.' + get_bogo_free_type_siblings).find('.wdr_desc_text').text(wdr_data.localization_data.buyx_getx_percentage);
                break;
            case 'free_product':
            default:
                $(this).parent('.' + get_bogo_free_type_parent).siblings('.' + get_bogo_free_type_siblings).hide();
        }
    });

    $(document).on('change', '.awdr-bogo-recurcive', function () {
        let get_bogo_range_parent = $(this).attr('data-recursive-row');
        let get_recursive_parent = $(this).attr('data-recursive-parent');
        let hide_if_recursive = $(this).attr('data-hide-add-range');
        let bogo_max_range = $(this).attr('data-bogo-max-range');
        let bogo_min_range = $(this).attr('data-bogo-min-range');
        let bogo_border_bottom = $(this).attr('data-bogo-border');
        let recursive_length = $('.' + get_bogo_range_parent).length;
        let parant_ranges_row_button = $(this).attr('data-ranges-row-parent');
        if ($(this).prop("checked") == false) {
            $(this).parents('.' + parant_ranges_row_button).siblings('.' + hide_if_recursive).show();
            if(bogo_max_range != ''){
                $(this).parents('.' + get_recursive_parent).siblings('.' + bogo_max_range).show();
                $(this).parents('.' + get_recursive_parent).siblings().find('.' + bogo_max_range).show();
            }
            //  $('.'+bogo_max_range).show();
            $(this).parents('.' + get_recursive_parent).siblings('.' + bogo_min_range).find('.wdr_desc_text').text(wdr_data.localization_data.recursive_min_qty);
            $('.' + bogo_border_bottom).css("border-bottom", "1px solid #ddd");
            return;
        }
        if (recursive_length > 3) {
            let isRecursive = confirm(wdr_data.localization_data.recursive_warning);
            if (isRecursive) {
                $(this).parents('.' + get_bogo_range_parent).siblings().remove();
            } else {
                $(this).prop("checked", false);
            }
        }
        if ($(this).prop("checked") == true) {
            $(this).parents('.' + parant_ranges_row_button).siblings('.' + hide_if_recursive).hide();
            if(bogo_max_range != ''){
                $(this).parents('.' + get_recursive_parent).siblings('.' + bogo_max_range).hide();
                $(this).parents('.' + get_recursive_parent).siblings().find('.' + bogo_max_range).hide();
            }
            //$('.'+bogo_max_range).hide();
            $(this).parents('.' + get_recursive_parent).siblings('.' + bogo_min_range).find('.wdr_desc_text').text(wdr_data.localization_data.recursive_qty);
            $('.' + bogo_border_bottom).css("border-bottom", "unset");
        }
    });

    $(document).on('change', '.awdr_mode_of_operator', function () {
        let mode_of_operator = $(this).val();
        let discount_type = $('.awdr-product-discount-type').val();
        if (discount_type == 'wdr_buy_x_get_y_discount') {
            if (mode_of_operator == "variation") {
                $('.awdr-bxgy-dynamic-tip').attr("title", wdr_data.localization_data.bulk_variants_discount_description_tool_tip);
                $('.awdr-example').html(wdr_data.localization_data.mode_variation_cumulative_example);
            } else if (mode_of_operator == "product") {
                $('.awdr-bxgy-dynamic-tip').attr("title", wdr_data.localization_data.bulk_individual_product_discount_description_tool_tip);
                $('.awdr-example').html('');
            } else {
                $('.awdr-bxgy-dynamic-tip').attr("title", wdr_data.localization_data.bulk_filter_together_discount_description_tool_tip);
                $('.awdr-example').html('');
            }
            return;
        }
        if (mode_of_operator == "variation") {
            $('.awdr-discount-content').html(wdr_data.localization_data.bulk_variants_discount_description);
            $('.awdr-example').html(wdr_data.localization_data.mode_variation_cumulative_example);
        } else if (mode_of_operator == "product") {
            $('.awdr-discount-content').html(wdr_data.localization_data.bulk_individual_product_discount_description);
            $('.awdr-example').html('');
        } else {
            $('.awdr-discount-content').html(wdr_data.localization_data.bulk_filter_together_discount_description);
            $('.awdr-example').html('');
        }
    });

    $(document).on('change', '.awdr-product-discount-type', function () {
        $('.awdr-example').html('');
        var data_placement = $(this).data('placement');
        let discount_type = $(this).val();
        let read_doc_prefix = 'awdr_doc_';
        switch (discount_type) {
            case 'wdr_simple_discount':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_x_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
            case 'wdr_cart_discount':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_x_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
            case 'wdr_free_shipping':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_x_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
            case 'wdr_bulk_discount':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_x_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
            case 'wdr_set_discount':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_buy_x_get_x_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
            case 'wdr_buy_x_get_x_discount':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
            case 'wdr_buy_x_get_y_discount':
                $('.' + read_doc_prefix + discount_type).show();
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_x_discount').hide();
                break
            default:
            case 'not_selected':
                $('.awdr_doc_wdr_simple_discount,.awdr_doc_wdr_cart_discount,.awdr_doc_wdr_free_shipping,.awdr_doc_wdr_bulk_discount,.awdr_doc_wdr_set_discount,.awdr_doc_wdr_buy_x_get_x_discount,.awdr_doc_wdr_buy_x_get_y_discount').hide();
                break;
        }
        if ($(this).val() != "not_selected" && $(this).val() != 'wdr_free_shipping') {
            wdr_buildrule.wdr_clone_field({
                addFilterMethod: '.' + $(this).val(),
                ruleAppendTo: '.' + data_placement,
                addDiscountType: 'enable',
            });
            if (!$(".awdr-free-shipping-special-condition").hasClass("wdr-hide")) {
                $('.awdr-free-shipping-special-condition').addClass('wdr-hide');
            }
            awdrRemoveOnSaleCondition();
            $(".calculate_cart_from option[value='from_filter']").show();
            $('.adv-msg-discount, .adv-msg-discount-price').show();

            $('.awdr-hidden-new-rule').fadeIn(500);
            $('.awdr-filter-section').fadeIn(500);
            $("button.wdr_save_stay, button.wdr_save_close").attr("disabled", false).removeClass("wdr_save_btn_disabled");
            make_wdr_select2_search($('.' + data_placement).find('[data-field="autocomplete"]'));
            $('.awdr-discount-container').show();
            $('.' + data_placement).find('.bulk_range_setter_group').addClass('bulk_range_setter').attr('id', 'bulk_adjustment_sortable');
            $('.' + data_placement).find('.set_range_setter_group').addClass('set_range_setter').attr('id', 'bulk_adjustment_sortable');
            $('#bulk_adjustment_sortable').sortable();
            $("#bulk_adjustment_sortable").disableSelection();
            $('.awdr-filter-heading').html(wdr_data.localization_data.common_filter_heading);
            $('.awdr-filter-content').html(wdr_data.localization_data.common_filter_description);
            $('.awdr-discount-heading').html(wdr_data.localization_data.common_discount_heading);
            $('.awdr-discount-content').html(wdr_data.localization_data.common_discount_description);
            $('.awdr-rules-content').html(wdr_data.localization_data.common_rules_description);

            if ($(this).val() == 'wdr_buy_x_get_y_discount') {
                if (!$(".awdr-free-shipping-special-condition").hasClass("wdr-hide")) {
                    $('.awdr-free-shipping-special-condition').addClass('wdr-hide');
                }
                awdrRemoveOnSaleCondition();
                //$('.awdr_mode_of_operator').trigger('change');
                $('.awdr-discount-heading').html(wdr_data.localization_data.two_column_bxgy_discount_heading);
                make_wdr_select2_search($('.' + data_placement).find('[data-list="product_category"]'));
                $('.adv-msg-min-qty, .adv-msg-max-qty, .adv-msg-discount, .adv-msg-discount-price').hide();
            }
            if ($(this).val() == 'wdr_buy_x_get_x_discount') {
                if (!$(".awdr-free-shipping-special-condition").hasClass("wdr-hide")) {
                    $('.awdr-free-shipping-special-condition').addClass('wdr-hide');
                }
                awdrRemoveOnSaleCondition();
                $('.adv-msg-min-qty, .adv-msg-max-qty, .adv-msg-discount, .adv-msg-discount-price').hide();
            }
            if ($(this).val() == 'wdr_bulk_discount') {
                if (!$(".awdr-free-shipping-special-condition").hasClass("wdr-hide")) {
                    $('.awdr-free-shipping-special-condition').addClass('wdr-hide');
                }
                awdrRemoveOnSaleCondition();
                $('.awdr-discount-heading').html(wdr_data.localization_data.two_column_bulk_discount_heading);
                $('.awdr_mode_of_operator').trigger('change');
                $('.adv-msg-min-qty, .adv-msg-max-qty, .adv-msg-discount, .adv-msg-discount-price').show();
            } else if ($(this).val() == 'wdr_set_discount') {
                if (!$(".awdr-free-shipping-special-condition").hasClass("wdr-hide")) {
                    $('.awdr-free-shipping-special-condition').addClass('wdr-hide');
                }
                awdrRemoveOnSaleCondition();
                $('.awdr_mode_of_operator').trigger('change');
                $('.awdr-discount-heading').html(wdr_data.localization_data.two_column_set_discount_heading);
                $('.adv-msg-min-qty, .adv-msg-discount, .adv-msg-discount-price').show();
                $('.adv-msg-max-qty').hide();
            }
        } else if ($(this).val() == 'wdr_free_shipping') {
            $('.awdr-free-shipping-special-condition').removeClass('wdr-hide');
            $('.awdr-hidden-new-rule').fadeIn(500);
            $('.awdr-filter-section').fadeOut();
            $("button.wdr_save_stay, button.wdr_save_close").attr("disabled", false).removeClass("wdr_save_btn_disabled");
            $('.' + data_placement).html('');
            $('.awdr-discount-container').hide();
            $('.adv-msg-min-qty, .adv-msg-max-qty, .adv-msg-discount, .adv-msg-discount-price').hide();
            $(".calculate_cart_from option[value='from_filter']").hide();
        } else {
            if (!$(".awdr-free-shipping-special-condition").hasClass("wdr-hide")) {
                $('.awdr-free-shipping-special-condition').addClass('wdr-hide');
            }
            awdrRemoveOnSaleCondition();
            $("button.wdr_save_stay, button.wdr_save_close").attr("disabled", true).addClass("wdr_save_btn_disabled");
            $('.awdr-hidden-new-rule').fadeOut(500);
        }

        $(this).trigger("advanced_woo_discount_rules_on_change_adjustment_type", [$(this).val()]);
    });
    $(".awdr-product-discount-type").trigger('change');

    function awdrRemoveOnSaleCondition() {
        $('.wdr-condition-group').each(function (index, element) {
            let condition_type = $(element).find('.wdr-product-condition-type').val();
            if (typeof condition_type !== 'undefined') {
                switch (condition_type) {
                    case 'cart_item_product_onsale':
                        $(this).remove();
                        break;
                }
            }
        });
    }

    if (wdr_data.rule_id == "view") {
        $("button.wdr_save_stay, button.wdr_save_close").attr("disabled", false).removeClass("wdr_save_btn_disabled");
    } else {
        $("button.wdr_save_stay, button.wdr_save_close").attr("disabled", true).addClass("wdr_save_btn_disabled");
    }

    $(document).on('change', '.apply_fee_coupon_checkbox', function () {
        if ($(this).prop("checked") == true) {
            $(this).parents('.awdr_rtl_compatible').siblings('.apply_fee_coupon_label').show();
        } else {
            $(this).parents('.awdr_rtl_compatible').siblings('.apply_fee_coupon_label').hide();
        }
    });

    $(document).on('change', '.bulk_table_customizer_show_hide_column', function () {
        var col_name = $(this).data("colname");
        if ($(this).prop("checked") == true) {
            $('.' + col_name).show();
        } else {
            $('.' + col_name).hide();
        }
    });

    var acc = document.getElementsByClassName("awdr-accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function (e) {
            e.preventDefault();
            this.classList.toggle("awdr-accordion-active");
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                ///  panel.style.display = "none";
                $(panel).slideUp(1000);
            } else {
                $(panel).slideDown(1000);
                ///panel.style.display = "block";
            }
        });
    }
    $(document).on('change', '.on_sale_badge_condition', function () {
        if ($(this).val() === 'disabled') {
            $('.sale_badge_toggle').hide();
            $('.sale_badge_customizer').hide();
        } else {
            $('.sale_badge_toggle').show();
            if ($('#customize_on_sale_badge').prop("checked") == true) {
                $('.sale_badge_customizer').show();
            } else {
                $('.sale_badge_customizer').hide();
            }
        }
    });
    $(document).on('change', '#customize_on_sale_badge', function () {
        if ($(this).prop("checked") == true) {
            $('.sale_badge_customizer').show();
        } else {
            $('.sale_badge_customizer').hide();
        }
    });
    $(document).on('change', '#badge_colorpicker', function () {
        let background_color = $(this).val();
        $('.awdr_admin_discount_bar').css('background-color', background_color);
    });
    $(document).on('change', '#badge_hexcolor', function () {
        let background_color = $(this).val();
        $('.awdr_admin_discount_bar').css('background-color', background_color);
    });
    $(document).on('change', '#text_colorpicker', function () {
        let text_color = $(this).val();
        $('.awdr_admin_discount_bar').css('color', text_color);
    });
    $(document).on('change', '#text_hexcolor', function () {
        let text_color = $(this).val();
        $('.awdr_admin_discount_bar').css('color', text_color);
    });
    $(document).on('change', '#awdr_discount_bar_content', function () {
        let discount_bar_content = $(this).val();
        $('.awdr_admin_discount_bar').html(discount_bar_content);
    });

    $(document).on('click', '.awdr-hidden-search', function () {
        let search_string = $('.awdr-hidden-name').val();
        $('.wdr-rule-search-key').val(search_string);
        $('#wdr-search-top').submit();
    });

    /**
     * Rule limit dynamic message
     */
    $(document).on('change', '#select_usage_limits', function () {
        let selected_limit = $("#select_usage_limits").val();
        if (selected_limit == "") {
            //$('.usage-limits-display').hide();
            $('.awdr-rule-limit-disabled-outer .rule_limit_msg_outer').hide();
            $('.awdr-rule-limit-disabled').hide();
        } else {
            //$('.usage-limits-display').show();
            var rule_applied_total = $('.awdr-used-limit-total').html();
            if (selected_limit <= parseInt(rule_applied_total)) {
                $('.awdr-rule-limit-disabled').hide();
                $('.awdr-rule-limit-disabled-outer').show();
                $('.awdr-rule-limit-disabled-outer .rule_limit_msg_outer').html(wdr_data.localization_data.invalid_rule_limit);
            } else {
                $('.awdr-rule-limit-disabled').hide();
                $('.awdr-rule-limit-disabled-outer').hide();
            }
        }
        // awdrRuleDateValidationMessage();
    });

    /**
     * Import File validation
     */
    $("#awdr-import-csv").on("submit", function () {
        var fileType = ".csv";
        var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(" + fileType + ")$");
        if (!regex.test($("#awdr-file-uploader").val().toLowerCase())) {
            $("#awdr-upload-response").html(wdr_data.localization_data.invalid_file_type);
            return false;
        }
        return true;
    });

   $(document).on('change', '.get_awdr_state_based_country', function (){
        let selected_country = $(this).val();
        if(!selected_country){
           return false;
        }
        let selected_index = $(this).parents('.wdr-conditions-container').attr('data-index');
        let append_data = $(this).parents('.wdr_shipping_state_group');
        let remove_data = $(this).parent().siblings('.wdr-shipping-state-value');
        let loader = $('.woo_discount_loader');
        let selected_state = $(this).parent().siblings('.wdr-shipping-state-value').find('.get_awdr_shipping_state').val();
        var data = {
            action: 'wdr_ajax',
            method: 'get_state_details',
            selected_country: selected_country,
            selected_state: selected_state,
            selected_index: selected_index,
            awdr_nonce: $('input[name=wdr_ajax_select2]').val() || '',
        };
        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'POST',
            beforeSend: function () {
                loader.show();
            },
            complete: function () {
                loader.hide();
            },
            success: function (response) {
                remove_data.remove();
                append_data.append("<div class='wdr-shipping-state-value wdr-select-filed-hight wdr-search-box' style='width: min-content;'>"+
                   response.data +"<span class='wdr_select2_desc_text'>"+wdr_data.localization_data.select_state+"</span> </div>");
                run_preload_values();

            },
            error: function (response) {
                console.log('error');
            }
        });
    });
    $('.get_awdr_state_based_country').trigger('change');

});
