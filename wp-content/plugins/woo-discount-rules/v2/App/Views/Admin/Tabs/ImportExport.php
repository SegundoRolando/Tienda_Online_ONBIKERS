<?php
if (!defined('ABSPATH')) exit;

use Wdr\App\Models\DBTable;
use Wdr\App\Helpers\Helper;

$is_pro_activated = isset($is_pro_activated) ? $is_pro_activated : false;
?>
<br>
<div class="wdr_settings ui-page-theme-a awdr-container">
    <div class="wdr_settings_container" style="border-bottom: 1px solid black; padding-bottom: 10px;">
        <div>
            <h3><?php _e('Export tool', 'woo-discount-rules'); ?></h3>
            <div>
                <p>
                <form method="post">
                    <input type="hidden" name="security" value="<?php echo wp_create_nonce('awdr_export_rules') ?>">
                    <button type="submit" id="wdr-export" name="wdr-export" class="button button-primary">
                        <?php _e('Export', 'woo-discount-rules'); ?>
                    </button>
                </form>
                </p>
            </div>
        </div>
    </div>
    <?php if ($is_pro_activated) { ?>
        <div class="wdr_settings_container">
        <div>
            <h3><?php _e('Import Tool', 'woo-discount-rules'); ?></h3>
            <div><?php
                $message = '';
                if (isset($_POST['wdr-import']) && isset($_FILES["awdr_import_rule"]) && isset($_POST['security'])) {
                    //check for nonce, before
                    if (wp_verify_nonce($_POST['security'], 'awdr_import_rules_csv')) {
                        $originalFileName = $_FILES['awdr_import_rule']['name'];
                        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                        //check for valid file extension
                        if (strtolower($fileExtension) == "csv") {
                            $fileName = $_FILES["awdr_import_rule"]["tmp_name"];
                            $originalFileType = $_FILES["awdr_import_rule"]["type"];
                            $valid_csv_mime_types = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
                            //Check for valid mime type
                            if ($_FILES["awdr_import_rule"]["size"] > 0 && in_array($originalFileType, $valid_csv_mime_types)) {
                                $file = fopen($fileName, "r");
                                $current_date_time = '';
                                if (function_exists('current_time')) {
                                    $current_time = current_time('timestamp');
                                    $current_date_time = date('Y-m-d H:i:s', $current_time);
                                }
                                $current_user = get_current_user_id();
                                $i = 1;
                                $csv_separator = apply_filters('advanced_woo_discount_rules_csv_import_export_separator', ',');
                                $csv_length = apply_filters('advanced_woo_discount_rules_csv_length_for_import', 100000);
                                while (($column = fgetcsv($file, $csv_length, $csv_separator)) !== FALSE) {
                                    if ($i == 1) {
                                        $i++;
                                        continue;
                                    }
                                    $rule_id = intval(isset($column[0]) ? $column[0] : NULL);
                                    $enabled = intval(isset($column[1]) ? $column[1] : 0);
                                    $deleted = intval(isset($column[2]) ? $column[2] : 0);
                                    $exclusive = intval(isset($column[3]) ? $column[3] : 0);
                                    $title = sanitize_text_field(isset($column[4]) ? $column[4] : "Untitled Rule");
                                    $priority = intval(isset($column[5]) ? $column[5] : $rule_id);
                                    $apply_to = isset($column[6]) ? $column[6] : NULL;
                                    $filters = isset($column[7]) ? $column[7] : array();
                                    $filters = wp_json_encode(Helper::sanitizeJson($filters));
                                    $conditions = isset($column[8]) ? $column[8] : array();
                                    $conditions = wp_json_encode(Helper::sanitizeJson($conditions));
                                    $product_adjustments = isset($column[9]) ? $column[9] : array();
                                    $product_adjustments = wp_json_encode(Helper::sanitizeJson($product_adjustments));
                                    $cart_adjustment = isset($column[10]) ? $column[10] : array();
                                    $cart_adjustment = wp_json_encode(Helper::sanitizeJson($cart_adjustment));
                                    $buy_x_get_x = isset($column[11]) ? $column[11] : array();
                                    $buy_x_get_x = wp_json_encode(Helper::sanitizeJson($buy_x_get_x));
                                    $buy_x_get_y = isset($column[12]) ? $column[12] : array();
                                    $buy_x_get_y = wp_json_encode(Helper::sanitizeJson($buy_x_get_y));
                                    $bulk_adjustment = isset($column[13]) ? $column[13] : array();
                                    $bulk_adjustment = wp_json_encode(Helper::sanitizeJson($bulk_adjustment));
                                    $set_adjustment = isset($column[14]) ? $column[14] : array();
                                    $set_adjustment = wp_json_encode(Helper::sanitizeJson($set_adjustment));
                                    $other_discount = isset($column[15]) ? $column[15] : NULL;
                                    $date_from = isset($column[16]) && !empty($column[16]) ? intval($column[16]) : NULL;
                                    $date_to = isset($column[17]) && !empty($column[16]) ? intval($column[17]) : NULL;
                                    $usage_limits = intval(isset($column[18]) ? $column[18] : 0);
                                    $rule_language = isset($column[19]) ? $column[19] : array();
                                    $rule_language = wp_json_encode(Helper::sanitizeJson($rule_language));
                                    $used_limits = intval(isset($column[20]) ? $column[20] : 0);
                                    $additional = isset($column[21]) ? $column[21] : array('condition_relationship' => 'and');
                                    $additional = wp_json_encode(Helper::sanitizeJson($additional));
                                    $max_discount_sum = intval(isset($column[22]) ? $column[22] : NULL);
                                    $advanced_discount_message = isset($column[23]) ? $column[23] : array('display' => 0, 'badge_color_picker' => '#ffffff', 'badge_text_color_picker' => '#000000', 'badge_text' => '');
                                    $advanced_discount_message = wp_json_encode(Helper::sanitizeJson($advanced_discount_message));
                                    $discount_type = sanitize_key(isset($column[24]) ? $column[24] : "wdr_simple_discount");
                                    $used_coupons = isset($column[25]) ? $column[25] : array();
                                    $used_coupons = wp_json_encode(Helper::sanitizeJson($used_coupons));
                                    $arg = array(
                                        'enabled' => $enabled,
                                        'deleted' => $deleted,
                                        'exclusive' => $exclusive,
                                        'title' => (empty($title)) ? esc_html__('Untitled Rule', 'woo-discount-rules') : $title,
                                        'priority' => $priority,
                                        'apply_to' => $apply_to,
                                        'filters' => $filters,
                                        'conditions' => $conditions,
                                        'product_adjustments' => $product_adjustments,
                                        'cart_adjustments' => $cart_adjustment,
                                        'buy_x_get_x_adjustments' => $buy_x_get_x,
                                        'buy_x_get_y_adjustments' => $buy_x_get_y,
                                        'bulk_adjustments' => $bulk_adjustment,
                                        'set_adjustments' => $set_adjustment,
                                        'other_discounts' => $other_discount,
                                        'date_from' => $date_from,
                                        'date_to' => $date_to,
                                        'usage_limits' => $usage_limits,
                                        'rule_language' => $rule_language,
                                        'used_limits' => $used_limits,
                                        'additional' => $additional,
                                        'max_discount_sum' => $max_discount_sum,
                                        'advanced_discount_message' => $advanced_discount_message,
                                        'discount_type' => $discount_type,
                                        'used_coupons' => $used_coupons,
                                        'created_by' => $current_user,
                                        'created_on' => $current_date_time,
                                        'modified_by' => $current_user,
                                        'modified_on' => $current_date_time,
                                    );
                                    $column_format = array('%d', '%d', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s');
                                    $rule_id = DBTable::saveRule($column_format, $arg);
                                    if (!empty($rule_id)) {
                                        $type = "success";
                                        $message = __('<b style="color: green;">Rules Imported successfully</b>', 'woo-discount-rules');
                                    } else {
                                        $type = "error";
                                        $message = __('<b style="color: red;">Problem in Importing CSV Data</b>', 'woo-discount-rules');
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } ?>
                <form method="post" name="awdr-import-csv" id="awdr-import-csv" enctype="multipart/form-data">
                    <input type="hidden" name="security" value="<?php echo wp_create_nonce('awdr_import_rules_csv') ?>">
                    <input type="file" name="awdr_import_rule" id="awdr-file-uploader" accept=".csv"><br>
                    <span id="awdr-upload-response"><?php echo $message; ?></span></br>
                    <button type="submit" id="wdr-import" name="wdr-import" class="button button-primary">
                        <?php _e('Import', 'woo-discount-rules'); ?>
                    </button>
                </form>
            </div>
        </div>
        </div><?php
    } else { ?>
        <div class="wdr_settings_container">
        <div>
            <h3><?php _e('Import Tool', 'woo-discount-rules'); ?></h3>
            <p><?php _e('Unlock this feature by <a href="https://www.flycart.org/products/wordpress/woocommerce-discount-rules" target="_blank">Upgrading to Pro</a>', 'woo-discount-rules'); ?> </p>
        </div>
        </div><?php
    }
    ?>
</div>