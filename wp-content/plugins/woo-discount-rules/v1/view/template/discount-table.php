<?php
/**
 * List matched Rules in Table format
 *
 * This template can be overridden by copying it to yourtheme/plugin-folder-name/discount-table.php
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!isset($table_data) || empty($table_data)) return false;
$base_config = (is_string($data)) ? json_decode($data, true) : (is_array($data) ? $data : array());
$show_discount_table_header = isset($base_config['show_discount_table_header'])? $base_config['show_discount_table_header']: 'show';
$show_discount_title_table = isset($base_config['show_discount_title_table'])? $base_config['show_discount_title_table']: 'show';
$show_column_range_table = isset($base_config['show_column_range_table'])? $base_config['show_column_range_table']: 'show';
$show_column_discount_table = isset($base_config['show_column_discount_table'])? $base_config['show_column_discount_table']: 'show';
$is_super_admin = is_super_admin();
?>
<div class="<?php echo ($is_super_admin)? 'wdr_table_container wdr_table_container_for_admin': 'wdr_table_container' ?>">
    <?php
    if($is_super_admin){
        ?>
        <div class="wdr_table_off_settings_link_con">
            <a href="<?php echo admin_url("admin.php?page=woo_discount_rules&tab=settings&target=table"); ?>" target="_blank" class="wdr_table_off_settings_link">
                <?php esc_html_e('Hide table', 'woo-discount-rules'); ?>
            </a>
            <span class="wdr_table_off_settings_hint"><?php esc_html_e('This effect displays only for you (Admin)', 'woo-discount-rules'); ?></span>
        </div>
    <?php
    }
    ?>

    <table class="woo_discount_rules_table">
        <?php if($show_discount_table_header == 'show'){ ?>
            <thead>
            <tr class="wdr_tr_head">
                <?php if ($show_discount_title_table == 'show') { ?>
                    <td class="wdr_td_head_title"><?php esc_html_e('Name', 'woo-discount-rules'); ?></td>
                <?php } ?>
                <?php if ($show_column_range_table == 'show') { ?>
                    <td class="wdr_td_head_range"><?php esc_html_e('Range', 'woo-discount-rules'); ?></td>
                <?php } ?>
                <?php if ($show_column_discount_table == 'show') { ?>
                    <td class="wdr_td_head_discount"><?php esc_html_e('Discount', 'woo-discount-rules'); ?></td>
                <?php } ?>
            </tr>
            </thead>
        <?php } ?>
        <tbody>
        <?php
        $have_discount = false;
        $table = $table_data;
        foreach ($table as $index => $item) {
            if ($item) {
                foreach ($item as $id => $value) {
                    ?>
                    <tr class="wdr_tr_body">
                        <?php if ($show_discount_title_table == 'show') { ?>
                            <td class="wdr_td_body_title"><?php echo $table_data_content[$index.$id]['title']; ?></td>
                        <?php } ?>
                        <?php if ($show_column_range_table == 'show') { ?>
                            <td class="wdr_td_body_range"><?php echo $table_data_content[$index.$id]['condition']; ?></td>
                        <?php } ?>
                        <?php if ($show_column_discount_table == 'show') { ?>
                            <td class="wdr_td_body_discount"><?php echo $table_data_content[$index.$id]['discount']; ?></td>
                        <?php } ?>
                    </tr>
                <?php }
                $have_discount = true;
            }
        }
        if (!$have_discount) {
            ?>
            <tr class="wdr_tr_body_no_discount">
                <td colspan="2">
                    <?php esc_html_e('No Active Discounts.', 'woo-discount-rules'); ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>