<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="">
    <div class="row form-group">
        <div class="col-md-12">
            <br/>
            <h4><?php esc_html_e('Performance settings', 'woo-discount-rules'); ?></h4>
            <hr>
        </div>
    </div>
    <?php $data['enable_variable_product_cache'] = (isset($data['enable_variable_product_cache']) ? $data['enable_variable_product_cache'] : 0); ?>
    <div class="row form-group">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Enable cache for variable products table content', 'woo-discount-rules'); ?>
            </label>
        </div>

        <div class="col-md-6">
            <label><input type="radio" name="enable_variable_product_cache" value="1" <?php echo ($data['enable_variable_product_cache'] == 1)? 'checked': '' ?>/> <?php esc_html_e('Yes', 'woo-discount-rules'); ?></label>
            <label><input type="radio" name="enable_variable_product_cache" value="0" <?php echo ($data['enable_variable_product_cache'] == 0)? 'checked': '' ?> /> <?php esc_html_e('No', 'woo-discount-rules'); ?></label>
        </div>
    </div>
    <div class="row form-group enable_variable_product_cache_con">
        <div class="col-md-2">
            <label>
                <?php esc_html_e('Clear cache', 'woo-discount-rules'); ?>
            </label>
        </div>
        <div class="col-md-6">
            <input type="button" id="refresh_wdr_cache" value="<?php esc_attr_e('Clear cache', 'woo-discount-rules'); ?>" class="btn btn-warning">
        </div>
    </div>
</div>