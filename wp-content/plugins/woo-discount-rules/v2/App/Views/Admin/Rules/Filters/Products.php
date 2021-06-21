<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="products wdr-condition-type-options">
    <div class="products_group wdr-products_group">
        <div class="wdr-product_filter_method">
            <select name="filters[{i}][method]">
                <option value="in_list" selected><?php _e('In List', 'woo-discount-rules'); ?></option>
                <option value="not_in_list"><?php _e('Not In List', 'woo-discount-rules'); ?></option>
                <!--<option value="exclude"><?php /*_e('Exclude Product', 'woo-discount-rules'); */?></option>-->
            </select>
        </div>
        <div class="awdr-product-selector">
            <select multiple="" name="filters[{i}][value][]"
                    class="awdr_validation"
                    data-placeholder="<?php _e('Select Products', 'woo-discount-rules');?>"
                    data-list="products"
                    data-field="autocomplete"
                    style="width: 100%; max-width: 400px;  min-width: 180px;">
            </select>
        </div>
    </div>
</div>