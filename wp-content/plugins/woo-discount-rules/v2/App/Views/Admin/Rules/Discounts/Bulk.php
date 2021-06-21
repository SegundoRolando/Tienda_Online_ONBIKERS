<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$is_pro = \Wdr\App\Helpers\Helper::hasPro();
?>
<div class="wdr-discount-group awdr-bulk-group" data-index="<?php echo $bulk_index; ?>">
    <div class="range_setter_inner">
        <div class="bulk-row-main">
            <div class="bulk-row-start wdr-input-filed-hight bulk-row-inner">
                <div class="dashicons dashicons-menu awdr-sort-icon"></div>
                <div class="bulk-min">
                    <input type="number"
                           name="bulk_adjustments[ranges][<?php echo $bulk_index; ?>][from]"
                           class="bulk_discount_min awdr_value_selector awdr_next_value"
                           placeholder="<?php _e('min', 'woo-discount-rules'); ?>"
                           min="0"
                           step="any"
                           value="<?php if (isset($range_value->from) && !empty($range_value->from)) {
                               echo $range_value->from;
                           } ?>">
                    <span class="wdr_desc_text"><?php _e('Minimum Quantity ', 'woo-discount-rules'); ?></span>
                </div>
                <div class="bulk-max">
                    <input type="number"
                           name="bulk_adjustments[ranges][<?php echo $bulk_index; ?>][to]"
                           class="bulk_discount_max awdr_value_selector awdr_auto_add_value"
                           placeholder="<?php _e('max', 'woo-discount-rules'); ?>"
                           min="0"
                           step="any"
                           value="<?php if (isset($range_value->to) && !empty($range_value->to)) {
                               echo $range_value->to;
                           } ?>">
                    <span class="wdr_desc_text"><?php _e('Maximum Quantity ', 'woo-discount-rules'); ?></span>
                </div>
                <div class="bulk_gen_disc_type wdr-select-filed-hight">
                    <select name="bulk_adjustments[ranges][<?php echo $bulk_index; ?>][type]"
                            class="bulk-discount-type bulk_discount_select">
                        <option value="percentage" <?php if (isset($range_value->type) && $range_value->type == 'percentage') {
                            echo 'selected';
                        } ?>><?php _e('Percentage discount', 'woo-discount-rules') ?></option>
                        <option value="flat" <?php if (isset($range_value->type) && $range_value->type == 'flat') {
                            echo 'selected';
                        } ?>><?php _e('Fixed discount', 'woo-discount-rules') ?></option>
                        <?php if($is_pro){ ?>
                            <option value="fixed_price" <?php if (isset($range_value->type) && $range_value->type == 'fixed_price') {
                                echo 'selected';
                            } ?>><?php _e('Fixed price for item', 'woo-discount-rules') ?></option>
                        <?php } else { ?>
                            <option disabled><?php _e('Fixed price for item - PRO -', 'woo-discount-rules') ?></option>
                        <?php } ?>
                    </select>
                    <span class="wdr_desc_text"><?php _e('Discount Type', 'woo-discount-rules'); ?></span>
                </div>
                <div class="bulk_amount">
                    <input type="number"
                           name="bulk_adjustments[ranges][<?php echo $bulk_index; ?>][value]"
                           class="bulk_discount_value bulk_value_selector awdr_value_selector"
                           placeholder="<?php _e('Discount', 'woo-discount-rules'); ?>"
                           min="0"
                           step="any"
                           value="<?php echo (isset($range_value->value) && !empty($range_value->value)) ? $range_value->value : 0;?>">
                    <span class="wdr_desc_text"><?php _e('Discount Value', 'woo-discount-rules'); ?></span>
                </div>
                <div class="bulk_amount">
                    <input type="text" name="bulk_adjustments[ranges][<?php echo $bulk_index; ?>][label]"
                           class="bulk_value_selector awdr_value_selector"
                           placeholder="<?php _e('Label', 'woo-discount-rules'); ?>" min="0"
                           value="<?php if (isset($range_value->label) && !empty($range_value->label)) {
                               echo $range_value->label;
                           } ?>">
                    <span class="wdr_desc_text"><?php _e('Title column For Bulk Table', 'woo-discount-rules'); ?></span>
                </div>
                <div class="wdr-btn-remove">
                    <span class="dashicons dashicons-no-alt wdr_discount_remove" data-rmdiv="bulk_range_group"></span>
                </div>
            </div>
        </div>
    </div>
</div>