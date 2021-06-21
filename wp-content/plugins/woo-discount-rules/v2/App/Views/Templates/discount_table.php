<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!empty($ranges) && !empty($woocommerce)) {
    if ($ranges['layout']['type'] == 'advanced') {
        $i=0;
        $existing_rule_id = 0;
        $tag_opened = false;
        foreach ($ranges as $key => $badge_settings){
            if($key !== 'layout'){
                $current_rule_id = isset($badge_settings['rule_id'])? $badge_settings['rule_id'] : '';
                $badge_bg_color = (!empty($badge_settings['badge_bg_color'])) ? $badge_settings['badge_bg_color'] : false;
                $badge_text_color = (!empty($badge_settings['badge_text_color'])) ? $badge_settings['badge_text_color'] : false;
                $badge_text = (!empty($badge_settings['badge_text'])) ? htmlspecialchars_decode($badge_settings['badge_text']) : false;
                if($current_rule_id !== $existing_rule_id){
                    $tag_opened = true;
                    if($existing_rule_id !== 0){
                        ?>
                        </div>
                        <?php
                    }
                    $existing_rule_id = $current_rule_id;
                    ?>
                    <div class="awdr_discount_bar awdr_row_<?php echo $i;?>" style="<?php if($badge_bg_color){
                        echo "background-color:".$badge_bg_color.';';
                    }if($badge_text_color) {
                        echo "color:".$badge_text_color.';';
                    }?>">
                    <?php
                }
                ?>
                <div class="awdr_discount_bar_content">
                    <?php echo $badge_text; ?>
                </div>
                <?php
                $i++;
            }
        }
        if($tag_opened){
            ?>
            </div>
            <?php
        }
    } elseif ($ranges['layout']['type'] == 'default') {
        if(isset($ranges['layout']['bulk_variant_table']) && $ranges['layout']['bulk_variant_table'] == "default_variant_empty"){?>
            <div class="awdr-bulk-customizable-table"> </div><?php
        }else{
            $tbl_title = $base::$config->getConfig('customize_bulk_table_title', 0);
            $tbl_discount = $base::$config->getConfig('customize_bulk_table_discount', 2);
            $tbl_range = $base::$config->getConfig('customize_bulk_table_range', 1);

            $tbl_title_text = $base::$config->getConfig('table_title_column_name', 'Title');
            $tbl_discount_text = $base::$config->getConfig('table_discount_column_name', 'Discount');
            $tbl_range_text = $base::$config->getConfig('table_range_column_name', 'Range');

            $table_sort_by_columns = array(
                'tbl_title' => $tbl_title,
                'tbl_discount' => $tbl_discount,
                'tbl_range' => $tbl_range,
            );
            asort($table_sort_by_columns); ?>
            <div class="awdr-bulk-customizable-table">
            <table id="sort_customizable_table" class="wdr_bulk_table_msg sar-table">
                <thead class="wdr_bulk_table_thead">
                <tr class="wdr_bulk_table_tr wdr_bulk_table_thead" style="<?php echo (!$base::$config->getConfig('table_column_header', 1) ? 'display:none' : '')?>">
                    <?php foreach ($table_sort_by_columns as $column => $order) {
                        if ($column == "tbl_title") {
                            ?>
                        <th id="customize-bulk-table-title" class="wdr_bulk_table_td awdr-dragable"
                            style="<?php if(!$base::$config->getConfig('table_column_header', 0)){
                                echo 'display:none';
                            }else{
                                echo((!$base::$config->getConfig('table_title_column', 0)) ? 'display:none' : '');
                            } ?>"><span><?php _e($tbl_title_text, 'woo-discount-rules') ?></span>
                            </th><?php
                        } elseif ($column == "tbl_discount") {
                            ?>
                        <th id="customize-bulk-table-discount" class="wdr_bulk_table_td awdr-dragable"
                            style="<?php if(!$base::$config->getConfig('table_column_header', 0)){
                                echo 'display:none';
                            }else{
                                echo((!$base::$config->getConfig('table_discount_column', 0)) ? 'display:none' : '');
                            } ?>"><span><?php _e($tbl_discount_text, 'woo-discount-rules') ?></span>
                            </th><?php
                        } else {
                            ?>
                        <th id="customize-bulk-table-range" class="wdr_bulk_table_td awdr-dragable"
                            style="<?php if(!$base::$config->getConfig('table_column_header', 0)){
                                echo 'display:none';
                            }else{
                                echo((!$base::$config->getConfig('table_range_column', 0)) ? 'display:none' : '');
                            }?>"><span><?php _e($tbl_range_text, 'woo-discount-rules') ?></span></th><?php
                        }
                    }?>
                </tr>
                </thead>
                <tbody><?php
                foreach ($ranges as $range) :
                    $cart_discount_text = '';
                    $discount_type_value = isset($range['discount_value']) ? $range['discount_value'] : 0;
                    //echo "<pre>"; print_r($discount_type_value); echo "</pre>";
                    if (!isset($range['discount_value'])){
                        continue;
                    }
                    ?>
                    <tr class="wdr_bulk_table_tr bulk_table_row">
                        <?php
                        /**
                         * Discount value
                         */

                        if (isset($range['discount_method']) && $range['discount_method'] == 'cart') {
                            $cart_discount_text = __(' (in cart)', 'woo-discount-rules');
                        }
                        $discount_type = isset($range['discount_type']) ? $range['discount_type'] : 'flat';
                        if ($discount_type == "flat") {
                            $discount_value = $woocommerce->formatPrice($discount_type_value);
                            $discount_value .= __(' flat', 'woo-discount-rules');
                            $discount_value .= !empty($cart_discount_text) ? $cart_discount_text : '';
                        } elseif ($discount_type == "percentage") {
                            $discount_value = isset($range['discount_value']) ? $range['discount_value'] : 0;
                            $discount_value .= '%';
                            $discount_value .= !empty($cart_discount_text) ? $cart_discount_text : '';
                        } else {
                            $discount_value = $woocommerce->formatPrice($discount_type_value);
                        }

                        if (isset($range['discount_method']) && $range['discount_method'] != 'cart') {
                            $discounted_price_for_customizer = $woocommerce->formatPrice(isset($range['discounted_price']) ? $range['discounted_price'] : 0);
                        }else{
                            $discounted_price_for_customizer = $discount_value;
                        }
                        /**
                         * Discount Range
                         */
                        if (isset($range['discount_method']) && $range['discount_method'] == 'set') {
                            $for_text = '';
                        } else {
                            $for_text = ' +';
                        }
                        if (isset($range['from']) && !empty($range['from']) && isset($range['to']) && !empty($range['to'])) {
                            $discount_range = $range['from'] . ' - ' . $range['to'];
                        } elseif (isset($range['from']) && !empty($range['from']) && isset($range['to']) && empty($range['to'])) {
                            $discount_range = $range['from']. $for_text;
                        } elseif (isset($range['from']) && empty($range['from']) && isset($range['to']) && !empty($range['to'])) {
                            $discount_range =  '0 - ' . $range['to'];
                        } elseif (isset($range['from']) && empty($range['from']) && isset($range['to']) && empty($range['to'])) {
                            $discount_range = '';
                        }?><?php
                        /**
                         * Table Data <td>'s
                         */
                        $j=1;
                        foreach ($table_sort_by_columns as $column => $order) {
                            if ($column == "tbl_title") {?>
                            <td class="wdr_bulk_table_td wdr_bulk_title  col_index_<?php echo $j;?>" data-colindex="<?php echo $j;?>"
                                style="<?php echo (!$base::$config->getConfig('table_title_column', 0)) ? 'display:none' : '';?>">
                                <?php echo isset($range['rule_title']) ? $range['rule_title'] : '-' ?>
                                </td><?php

                            } elseif ($column == "tbl_discount") {?>
                            <td class="wdr_bulk_table_td wdr_bulk_table_discount  col_index_<?php echo $j;?>" data-colindex="<?php echo $j;?>"
                                style="<?php echo (!$base::$config->getConfig('table_discount_column', 0)) ? 'display:none' : '';?>">
                                <span class="wdr_table_discounted_value" style="<?php echo ( !$base::$config->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php echo $discount_value; ?></span>
                                <span class="wdr_table_discounted_price" style="<?php echo ( $base::$config->getConfig('table_discount_column_value', 0)) ? 'display: none' : '';?>"><?php echo $discounted_price_for_customizer; ?></span>
                                </td><?php
                            } else {?>
                                <td class="wdr_bulk_table_td wdr_bulk_range  col_index_<?php echo $j;?>" data-colindex="<?php echo $j;?>"
                                    style="<?php echo (!$base::$config->getConfig('table_range_column', 0) || isset($range['discount_method']) && in_array($range['discount_method'], array('product', 'cart'))) ? 'display:none':'';?>"><?php echo $discount_range ?></td><?php
                            }
                            $j++;
                        }?>
                    </tr>
                <?php
                endforeach;
                ?>
                </tbody>
            </table>
            </div><?php
        }
    }
}