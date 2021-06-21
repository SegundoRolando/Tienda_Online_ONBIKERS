<?php
/**
 * List matched Rules in Table format
 *
 * This template can be overridden by copying it to yourtheme/plugin-folder-name/discount-table-advance.php
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!isset($table_data) || empty($table_data)) return false;
$base_config = (is_string($data)) ? json_decode($data, true) : (is_array($data) ? $data : array());
$discount_rules = $table_data;
$discount_rules_displayed = array();
$do_discount_from_regular_price = isset($base_config['do_discount_from_regular_price'])? $base_config['do_discount_from_regular_price']: 'sale';
if(class_exists('FlycartWoocommerceProduct')){
    if($do_discount_from_regular_price == "regular"){
        if ($product->is_type(array('variable', 'variable-subscription'))) {
            if(method_exists($product, 'get_variation_regular_price')){
                $product_price = $product->get_variation_regular_price('min');
            }
        } else {
            $product_price = FlycartWoocommerceProduct::get_regular_price($product);
        }
    } else {
        $product_price = FlycartWoocommerceProduct::get_price($product);
    }
} else {
    $product_price = $product->get_price();
}

$searchForReplace = array('{{title}}', '{{description}}', '{{min_quantity}}', '{{max_quantity}}', '{{discount}}', '{{discounted_price}}', '{{condition}}');
$i = 1;
?>
<div class="wdr_table_content_advance_con">
    <?php foreach ($discount_rules as $index => $item) {
        if ($item) {
            foreach ($item as $id => $value) {
                if (!empty($value->advance_table_format)) {
                    $html_content = $value->advance_table_format;
                    $discounted_price_text = '';
                    $min_qty = empty($value->min_qty) ? '' : $value->min_qty;
                    $max_qty = empty($value->max_qty) ? '' : $value->max_qty;
                    $condition = $table_data_content[$index . $id]['condition'];
                    $discount_text = $table_data_content[$index . $id]['discount'];
                    $discounted_title_text = $table_data_content[$index . $id]['title'];
                    $discounted_description_text = $value->description;
                    if($value->discount_type == 'product_discount'){
                        if($value->discount_product_discount_type == 'limited_percent'){
                            if ($value->discount_product_percent > 0) {
                                $discount = ($product_price / 100) * $value->discount_product_percent;
                                $discounted_price = $product_price - $discount;
                                if($discounted_price < 0) $discounted_price = 0;
                                $discounted_price_text = wc_price($discounted_price);
                            }
                        } else {
                            $discounted_price_text = wc_price($product_price);
                        }
                    } else {
                        if ($value->discount_type == "percentage_discount") {
                            if ($value->to_discount > 0) {
                                $discount = ($product_price / 100) * $value->to_discount;
                                $discounted_price = $product_price - $discount;
                                if($discounted_price < 0) $discounted_price = 0;
                                $discounted_price_text = wc_price($discounted_price);
                            }
                        } else {
                            if ($value->to_discount > 0) {
                                $discounted_price = $product_price - $value->to_discount;
                                if($discounted_price < 0) $discounted_price = 0;
                                $discounted_price_text = wc_price($discounted_price);
                            }
                        }
                    }
                    $string_to_replace = array($discounted_title_text, $discounted_description_text, $min_qty, $max_qty, $discount_text, $discounted_price_text, $condition);
                    $html_content = str_replace($searchForReplace, $string_to_replace, $html_content);
                    if(!in_array($html_content, $discount_rules_displayed)){
                        $discount_rules_displayed[] = $html_content;
                        $i++;
                        $row = $i%2;
                        ?>
                        <div class="wdr_table_content_advance_item wdr_row<?php echo $row;?>">
                            <?php echo $html_content; ?>
                        </div>
                        <?php
                    }
                }
            }
        }
    }
    ?>
</div>