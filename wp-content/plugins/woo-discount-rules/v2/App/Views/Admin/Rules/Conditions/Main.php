<?php

use Wdr\App\Helpers\Helper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$is_pro = \Wdr\App\Helpers\Helper::hasPro();
?>
<div class="wdr-rule-menu">
    <h2><?php _e('Rules (Optional)', 'woo-discount-rules'); ?> - <span><a href="https://docs.flycart.org/en/articles/3834240-conditions-rules?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=rule_condition" target="_blank" style="font-size: 12px;"><?php _e('Read Docs', 'woo-discount-rules'); ?></a></span></h2>
    <div class="awdr-rules-content">
        <?php echo Helper::ruleConditionDescription();?>
    </div>
</div>
<div class="wdr-rule-options-con"><?php
    if ($conditions = $rule->getConditions()) {
        $condition_relationship = $rule->getRelationship('condition', 'and');
        $wdr_product_conditions = $base->getProductConditionsTypes();
        $awdr_discount_type = $rule->getRuleDiscountType();?>
        <!--Product Condition Start  promo_show_hide_-->
        <div class="wdr-condition-template">
        <div class="wdr-block">
            <div class="wdr-conditions-relationship">
                <label><b><?php _e('Conditions Relationship ', 'woo-discount-rules'); ?></b></label>&nbsp;&nbsp;&nbsp;&nbsp;
                <label><input type="radio" name="additional[condition_relationship]"
                              value="and" <?php echo ($condition_relationship == 'and') ? 'checked' : '' ?>
                    ><?php _e('Match All', 'woo-discount-rules'); ?></label>
                <label><input type="radio" name="additional[condition_relationship]"
                              value="or" <?php echo ($condition_relationship == 'or') ? 'checked' : '' ?>><?php _e('Match Any', 'woo-discount-rules'); ?>
                </label>
            </div>
            <div class="wdr-condition-group-items">
                <div class="wdr-conditions-container wdr-condition-group" data-index="1"></div><?php
                $i = 2;
                $render_saved_condition = false;
                foreach ($conditions as $condition) {
                    $type = isset($condition->type) ? $condition->type : NULL;
                    $custom_taxonomy_type_on_edit = $type;
                    if($awdr_discount_type != 'wdr_free_shipping' && $type == 'cart_item_product_onsale'){
                        continue;
                    }
                    if (!empty($type) && isset($rule->available_conditions[$type]['object'])) {
                        $template = $rule->available_conditions[$type]['template'];
                        $extra_params = isset($rule->available_conditions[$type]['extra_params']) ? $rule->available_conditions[$type]['extra_params'] : array();
                        if (file_exists($template)) {
                            $options = isset($condition->options) ? $condition->options : array(); ?>
                            <div class="wdr-grid wdr-conditions-container wdr-condition-group" data-index="<?php echo $i; ?>">
                                <div class="wdr-condition-type">
                                    <select name="conditions[<?php echo $i; ?>][type]"
                                            class="wdr-product-condition-type awdr-left-align"
                                            style="width: 100%"><?php
                                        if (isset($wdr_product_conditions) && !empty($wdr_product_conditions)) {
                                            foreach ($wdr_product_conditions as $wdr_condition_key => $wdr_condition_value) {
                                                ?>
                                                <optgroup
                                                label="<?php _e($wdr_condition_key, 'woo-discount-rules'); ?>"><?php
                                                foreach ($wdr_condition_value as $key => $value) {?>
                                                    <option class="<?php echo ($awdr_discount_type != 'wdr_free_shipping' && $key == 'cart_item_product_onsale') ? 'wdr-hide awdr-free-shipping-special-condition' : 'awdr-free-shipping-special-condition'; ?>"
                                                    <?php
                                                    if(isset($value['enable']) && $value['enable'] === false){
                                                        ?>
                                                        disabled="disabled"
                                                        <?php
                                                    } else {
                                                        ?>
                                                        value="<?php echo $key; ?>"
                                                        <?php
                                                    }
                                                    ?>
                                                    <?php if ($key == $type) {
                                                        echo 'selected';
                                                    } ?>><?php _e($value['label'], 'woo-discount-rules'); ?></option><?php
                                                } ?>
                                                </optgroup><?php
                                            }
                                        } ?>
                                    </select>
                                    <span class="wdr_desc_text awdr-clear-both"><?php _e('Condition Type', 'woo-discount-rules'); ?></span>
                                </div><?php
                                extract($extra_params);
                                $render_saved_condition = true;
                                include $template;
                                $custom_taxonomy_type_on_edit = null;

                                ?>
                                <div class="wdr-btn-remove" style="float: left">
                                    <span class="dashicons dashicons-no-alt remove-current-row"></span>
                                </div>
                            </div><?php
                            $config = new \Wdr\App\Controllers\Configuration();
                            $subtotal_promo = $config->getConfig("show_subtotal_promotion", '');
                            $cart_quantity_promo = $config->getConfig("show_cart_quantity_promotion", '');
                            $type_promotion = isset($condition->type) ? $condition->type : NULL;
                            if($type_promotion == 'cart_subtotal' && $subtotal_promo == 1){
                                $operator = isset($options->operator) ? $options->operator : 'greater_than_or_equal';?>
                                <div class="wdr-grid wdr-conditions-container wdr-condition-group <?php echo 'promo_show_hide_'.$i; ?>" data-index="<?php echo $i; ?>" style="<?php echo ($operator == 'greater_than_or_equal' || $operator == 'greater_than') ? '': 'display: none'; ?>">
                                    <?php include(WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Others/SubtotalPromotion.php'); ?>
                                </div>
                               <?php
                            }else if($type_promotion == 'cart_items_quantity' && $cart_quantity_promo == 1 && $is_pro){
                                $operator = isset($options->operator) ? $options->operator : 'greater_than_or_equal';?>
                                <div class="wdr-grid wdr-conditions-container wdr-condition-group <?php echo 'promo_show_hide_'.$i; ?>" data-index="<?php echo $i; ?>" style="<?php echo ($operator == 'greater_than_or_equal' || $operator == 'greater_than') ? '': 'display: none'; ?>">
                                    <?php include(WDR_PLUGIN_PATH . 'App/Views/Admin/Rules/Others/QuantityPromotion.php'); ?>
                                </div>
                                <?php
                            }
                            $i++;
                        }
                    }
                    $custom_taxonomy_type_on_edit = null;
                } ?>
            </div>
            <div class="add-condition add-condition-and-filters">
                <button type="button"
                        class="button add-product-condition"><?php _e('Add condition', 'woo-discount-rules'); ?></button>
            </div>
        </div>
        </div><?php
    } else {?>
        <div class="wdr-condition-template">
            <div class="wdr-block">
                <div class="wdr-conditions-relationship">
                    <label><b><?php _e('Conditions Relationship', 'woo-discount-rules'); ?></b></label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <label><input type="radio" name="additional[condition_relationship]"
                                  value="and" checked><?php _e('Match All', 'woo-discount-rules'); ?></label>
                    <label><input type="radio" name="additional[condition_relationship]"
                                  value="or"><?php _e('Match Any', 'woo-discount-rules'); ?>
                    </label>
                </div>
                <div class="wdr-condition-group-items">
                    <div class="wdr-conditions-container wdr-condition-group" data-index="1"></div>
                </div>
                <div class="wdp-block add-condition">
                    <button type="button"
                            class="button add-product-condition"><?php _e('Add condition', 'woo-discount-rules'); ?></button>
                </div>
            </div>
        </div>
    <?php } ?>
    <!--Product Condition End-->
    <!--Rule Limit Start-->
    <div class="wdr-condition-template">
        <div class="wdr-block">
            <div class="wdr-conditions-relationship"><?php
                $usage_limits = $rule->getUsageLimits();
                $used_limits = $rule->getUsedLimits(); ?>
                <label><b><?php _e('Rule Limits', 'woo-discount-rules'); ?></b>
                    <span class="awdr-rule-limit-timestamp"><?php
                        if(!empty($current_time)) echo sprintf(esc_html__('Current date and time: %s', 'woo-discount-rules'), '<b>' . date('Y-m-d H:i', $current_time) . '</b>'); ?>
                    </span>
                    <span class="awdr-rule-limit-timestamp "> <?php
                        _e('Rule Used: ', 'woo-discount-rules');
                        echo "<b class='awdr-used-limit-total'>".$used_limits."</b>"; ?>
                    </span>
                </label>

            </div>
            <div class="awdr-general-settings-section">
                <div class="wdr-rule-setting">
                    <div class="wdr-apply-to" style="float:left;">

                        <input type="number" name="usage_limits" value="<?php echo (!empty($usage_limits)) ? $usage_limits : '';?>" min="1" class="wdr-title number_only_field" id="select_usage_limits" placeholder="Unlimited">

                        <span class="wdr_desc_text"><?php _e('Maximum usage limit', 'woo-discount-rules'); ?></span>
                    </div>
                    <div class="wdr-rule-date-valid">
                        <div class="wdr-dateandtime-value">
                            <input type="text"
                                   name="date_from"
                                   class="wdr-condition-date wdr-title"
                                   data-class="start_datetimeonly"
                                   placeholder="<?php _e('Rule Vaild From', 'woo-discount-rules'); ?>"
                                   data-field="date"
                                   autocomplete="off"
                                   id="rule_datetime_from"
                                   value="<?php echo $rule->getStartDate(false, 'Y-m-d H:i'); ?>">
                            <span class="wdr_desc_text"><?php _e('Vaild from', 'woo-discount-rules'); ?></span>
                        </div>
                        <div class="wdr-dateandtime-value">
                            <input type="text"
                                   name="date_to"
                                   class="wdr-condition-date wdr-title"
                                   data-class="end_datetimeonly"
                                   placeholder="<?php _e('Rule Valid To', 'woo-discount-rules'); ?>"
                                   data-field="date" autocomplete="off"
                                   id="rule_datetime_to"
                                   value="<?php echo $rule->getEndDate(false, 'Y-m-d H:i'); ?>">
                            <span class="wdr_desc_text"><?php _e('Vaild to', 'woo-discount-rules'); ?></span>
                        </div>
                    </div>
                    <?php
                    if (!empty($site_languages) && is_array($site_languages) && count($site_languages) > 1) {
                        ?>
                        <div class="wdr-language-value">
                            <select multiple
                                    class="edit-preloaded-values"
                                    data-list="site_languages"
                                    data-field="preloaded"
                                    data-placeholder="<?php _e('Select values', 'woo-discount-rules') ?>"
                                    name="rule_language[]"><?php
                                $chosen_languages = $rule->getLanguages();
                                foreach ($site_languages as $language_key => $name) {
                                    if (in_array($language_key, $chosen_languages)) {
                                        ?>
                                        <option value="<?php echo $language_key; ?>"
                                                selected><?php echo $name; ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                            <span class="wdr_desc_text"><?php _e('Language', 'woo-discount-rules'); ?></span>
                        </div>
                        <?php
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <!--Rule Limit End-->
</div>