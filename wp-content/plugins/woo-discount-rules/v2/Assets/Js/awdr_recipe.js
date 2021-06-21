/* global jQuery, ajaxurl, wdr_data */
jQuery(document).ready(function ($) {

    $('.awdr_recipe_main_card').click(function () {
        let choose_recipe = $(this).attr("data-recipe-group");
        $('.'+choose_recipe).show();
        $('.awdr_recipe_main_card').each(function (index, element) {
            let hidden_recipe = $(element).attr("data-recipe-group");
            if(choose_recipe != hidden_recipe){
                $('.'+hidden_recipe).hide();
                $(element).css({"background-color": "#ffffff", "color": "#444444"})
            }else{
                $(element).css({"background-color": "#6495ed", "color": "#ffffff"})
            }
        });
    });


    /**
     * save rule
     */
    $(document).on('click', '.awdr_recipe_content', function () {
        var recipe_nonce = $(this).attr('data-recipe-nonce');
        var recipe_type = $(this).attr('data-select-recipe');
        var target_element = $(this).next();
        $.ajax({
            data: {
                method: 'create_rule_recipe',
                action: 'wdr_ajax',
                awdr_nonce: recipe_nonce,
                awdr_recipe_type: recipe_type,
            },
            type: 'post',
            url: ajaxurl,

            error: function (request, error) {
                notify(wdr_data.localization_data.error, 'error', alert_counter);
            },
            success: function (response) {
                var data = response.data;
                if (response.success) {
                    if (data.redirect && parseInt(data.rule_id) != 0) {
                        target_element.attr("href", data.redirect);
                        target_element.show();
                        target_element.css({"background-color": "#f3f5f6", "color": "#0071a1", "border-color": "#7e8993"})
                    } else {
                        $('.wdr_desc_text.coupon_error_msg').hide();
                        $(".coupon_name_msg").css("border", "");
                        notify(wdr_data.localization_data.save_rule, 'success', alert_counter);
                    }
                } else {
                    notify(wdr_data.localization_data.save_priority, 'success', alert_counter);
                }
            }
        });

    });

});
