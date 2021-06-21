/**
 * Contains the wdr_rulebuilding logic, clone wdr-rules.
 *
 * @summary Contains rule building logic
 *
 * @requires jQuery
 */

/**
 * @namespace wdr_buildrule
 *
 * @type {Object}
 */
var wdr_buildrule;

(function ($) {

    wdr_buildrule = {

        /**
         * @summary Handles a click on product filter button.
         *
         * Adds the rule row while click the element.
         *
         * @returns {void}
         */
        wdr_clone_field: function (selector) {

            var settings = $.extend({
                addFilterType: null,
                addFilterMethod: null,
                ruleAppendTo: null,
                addConditionType: null,
                addRemoveIcon: null,
                addDiscountElement: null,
                addDiscountType: null,
                newIndex: null
            }, selector);

            var remove_icon = $(settings.addRemoveIcon).html();
            var filter_method = $(settings.addFilterMethod).html();
            if (settings.addDiscountElement === null && settings.addFilterType !== null && settings.addFilterMethod !== null && settings.ruleAppendTo !== null) {
                var filter_type = $(settings.addFilterType).html();
                filter_type = filter_type.replace(new RegExp('{i}', 'g'), settings.newIndex);
                filter_method = filter_method.replace(new RegExp('{i}', 'g'), settings.newIndex);
                $(settings.ruleAppendTo).append('<div class="wdr-grid wdr-filter-group" data-index="' + settings.newIndex + '">' + filter_type + filter_method + remove_icon + '</div>');
            } else if (settings.addDiscountType === null && settings.addDiscountElement === null && settings.addFilterType === null && settings.addFilterMethod !== null && settings.ruleAppendTo !== null && settings.addConditionType === null) {
                filter_method = filter_method.replace(new RegExp('{i}', 'g'), settings.newIndex);
                $(settings.ruleAppendTo).append(filter_method + remove_icon);
            } else if (settings.addDiscountElement === null && settings.addConditionType !== null && settings.addFilterMethod !== null && settings.ruleAppendTo !== null) {
                var condition_type = $(settings.addConditionType).html();
                let  style_inline = "";
                let  class_inline = "";
                var condition_method = $(settings.addFilterMethod).html();
                condition_method = condition_method.replace(new RegExp('{i}', 'g'), settings.newIndex);
                if(!condition_type || condition_type == 'undefined'){
                    condition_type = '';
                    style_inline = "display:none";
                    class_inline = "promo_show_hide_"+settings.newIndex;
                    remove_icon = '';
                    $(settings.ruleAppendTo +" [data-index='"+  settings.newIndex +"']").after('<div class="'+class_inline+' wdr-conditions-container wdr-condition-group" style="'+style_inline+'" data-index="' + settings.newIndex + '">' + condition_type + condition_method + remove_icon + '</div>');
                }else{
                    condition_type = condition_type.replace(new RegExp('{i}', 'g'), settings.newIndex);
                    $(settings.ruleAppendTo).append('<div class="'+class_inline+' wdr-conditions-container wdr-condition-group" style="'+style_inline+'" data-index="' + settings.newIndex + '">' + condition_type + condition_method + remove_icon + '</div>');
                }

            } else if (settings.addDiscountType == null && settings.addDiscountElement !== null && settings.addFilterMethod !== null && settings.ruleAppendTo !== null) {
                filter_method = filter_method.replace(new RegExp('{i}', 'g'), settings.newIndex);
                $(settings.ruleAppendTo).append(filter_method);
            } else if (settings.addDiscountType !== null && settings.ruleAppendTo !== null) {
                $(settings.ruleAppendTo).html(filter_method);
            }
        },

        /**
         * @summary Handles a click on product rule row remove button.
         *
         * Remove the rule row while click the element.
         *
         * @returns {void}
         */
        remove_wdr_field_group: function (selector) {
            var settings = $.extend({
                parentRow: null,
                parentsRow: null,
                siblingElements: null,
                thisObject: null,
            }, selector);
            if (settings.parentsRow !== null && settings.thisObject !== null) {
                $(settings.thisObject).parents(settings.parentsRow).remove();
            }
            if (settings.parentRow !== null && settings.siblingElements === null) {
                $(settings.parentRow).siblings().remove();
            }
        },

        /**
         * @summary Handles show & hide rule groups.
         *
         * show tab group if click button.
         *
         * @returns {void}
         */
        show_hide_rule_block: function (selector) {

            var settings = $.extend({
                showBlockId: null,
                hideBlockId: null,
                thisObject: null,
                discountTab: null,
                showTabId: null
            }, selector);

            //Show Only
            if (settings.showBlockId !== null && settings.hideBlockId === null) {
                $(settings.showBlockId).show();
            }

            //Show And Hide
            if (settings.showBlockId !== null && settings.hideBlockId !== null) {
                $(settings.showBlockId).show();
                $(settings.hideBlockId).hide();
                $(settings.thisObject).siblings('.wdr-active').removeClass('wdr-active wdr-inactive').prop('disabled', false).addClass('wdr-inactive');
                $(settings.thisObject).removeClass('wdr-inactive').addClass('wdr-active');
            }

            //Show And Hide Discount Tab
            if (settings.showBlockId !== null && settings.hideBlockId !== null) {
                $(settings.showBlockId).show();
                $(settings.hideBlockId).hide();
                $(settings.thisObject).siblings('.wdr-active').removeClass('wdr-active').prop('disabled', false);
                $(settings.thisObject).addClass('wdr-active');
            }
        }
    };
}(jQuery));