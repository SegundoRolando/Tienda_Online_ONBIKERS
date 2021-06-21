<?php
if (!defined('ABSPATH')) exit;

$rules_count = count($rules);
$is_pro = \Wdr\App\Helpers\Helper::hasPro();
?>
<br>
<div id="wpbody-content" class="awdr-container">
    <div class="" style ="<?php if(!$is_pro){ echo "width: 71%; float: left;"; }?>" >
        <div class="col-md-6 col-lg-6 text-left awdr-list-header-btn" <?php if(!$is_pro){ echo 'style="width:100%; float: left"'; }?>>
            <h1 class="wp-heading-inline"><?php _e('Discount Rules', 'woo-discount-rules'); ?></h1>
            <a href="<?php echo admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules', 'task' => 'create'))); ?>"
               class="btn btn-primary"><?php _e('Add New Rule', 'woo-discount-rules'); ?></a>
            <?php if($has_migration == true) {
                ?>
                <a class="wdr-popup-link btn btn-primary"><span class="modal-trigger" data-modal="wdr_migration_popup"><?php _e("Migrate rules from v1", 'woo-discount-rules'); ?></a>

                <div class="modal" id="wdr_migration_popup">
                    <div class="modal-sandbox"></div>
                    <div class="modal-box">
                        <div class="modal-header">
                            <div class="close-modal"><span class="wdr-close-modal-box">&#10006;</span></div>
                            <h1 class="wdr-modal-header-title"><?php _e("Migration", 'woo-discount-rules'); ?></h1>
                        </div>
                        <div class="modal-body">
                            <h2 class="wdr_tabs_container nav-tab-wrapper">
                                <?php esc_html_e('Migrate rules from v1 to v2', 'woo-discount-rules'); ?>
                            </h2>
                            <div class="wdr_migration_text_con">
                                <p>
                                    <b><?php esc_html_e('Available price rules', 'woo-discount-rules'); ?>:</b> <?php echo isset($migration_rule_count['price_rules'])? $migration_rule_count['price_rules']: 0;?>
                                </p>
                                <p>
                                    <b><?php esc_html_e('Available cart rules', 'woo-discount-rules'); ?>:</b> <?php echo isset($migration_rule_count['cart_rules'])? $migration_rule_count['cart_rules']: 0?>
                                </p>
                                <p>
                                    <?php _e('Once migration is completed, please open the rules and check their configuration once again to make sure it meets your discount scenario. If required, please adjust the rule configuration. If you need any help, just open a ticket at <a href="https://www.flycart.org/support" target="_blank">https://www.flycart.org/support</a>', 'woo-discount-rules'); ?>
                                </p>
                            </div>
                            <div class="wdr_settings">
                                <div class="wdr_migration_container">
                                    <button class="btn btn-primary" type="button" data-awdr_nonce="<?php echo \Wdr\App\Helpers\Helper::create_nonce('awdr_v2_migration'); ?>" id="awdr_do_v1_v2_migration"><?php esc_html_e('Migrate', 'woo-discount-rules'); ?></button>
                                    <span class="close-modal"><button class="btn btn-warning wdr-close-modal-box" type="button"><?php esc_html_e('Skip', 'woo-discount-rules'); ?></button></span>
                                    <div class="wdr_migration_process">
                                    </div>
                                    <div class="wdr_migration_process_status">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }?>
            <a href="https://www.flycart.org/woocommerce-discount-rules-examples?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=examples#commonly-asked-scenarios" target="_blank"
               class="btn btn-info text-right" style="float: right"><?php _e('View Examples', 'woo-discount-rules'); ?></a>
            <a href="https://docs.flycart.org/en/collections/2195266-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=documentation" target="_blank"
               class="btn btn-info text-right" style="float: right"><?php _e('Documentation', 'woo-discount-rules'); ?></a>
        </div>

        <br/>
        <form id="wdr-search-top" method="get" style="display: none">
            <input type="hidden" name="adminUrl"
                   value="<?php echo admin_url('admin.php?page=woo_discount_rules'); ?>">
                <input type="hidden" name="name" value="" class="wdr-rule-search-key">
                <input type="hidden" name="awdr_nonce" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('awdr_ajax_search_rule'); ?>">
                <input type="submit" class="button" class="wdr-trigger-search-key"
                       value="<?php _e('Search Rules', 'woo-discount-rules'); ?>">
        </form>
        <form id="wdr-bulk-action-top" method="post">
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top"
                           class="screen-reader-text"><?php _e('Search Rules', 'woo-discount-rules'); ?></label>
                    <select name="wdr_bulk_action" id="bulk-action-selector-top">
                        <option value="-1"><?php _e('Bulk Actions', 'woo-discount-rules'); ?></option>
                        <option value="enable"><?php _e('Enable', 'woo-discount-rules'); ?></option>
                        <option value="disable"><?php _e('Disable', 'woo-discount-rules'); ?></option>
                        <option value="delete"><?php _e('Delete', 'woo-discount-rules'); ?></option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="<?php _e('Apply', 'woo-discount-rules');?>">
                    <input type="search" name="awdr-hidden-name" class="awdr-hidden-name" value="<?php echo $input->get('name'); ?>">
                    <input type="hidden" name="awdr_nonce" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('awdr_ajax_rule_bulk_actions'); ?>">
                    <input type="button" class="button awdr-hidden-search"
                           value="<?php _e('Search Rules', 'woo-discount-rules'); ?>">
                </div>
                <div class="tablenav-pages one-page">
                <span class="displaying-num"><?php echo $rules_count . ' ';
                    ($rules_count == 0 || $rules_count == 1) ? _e('item', 'woo-discount-rules') : _e('items', 'woo-discount-rules'); ?></span>
                </div>
                <br class="clear">
            </div>

            <table class="wp-list-table widefat fixed posts">
                <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                    </td>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input name="bulk_check[]" class="wdr-rules-select" type="checkbox" value="off"/>
                    </td>

                    <th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
                        <a href="javascript:void(0);">
                            <span><?php _e('Title', 'woo-discount-rules'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th scope="col" id="author"
                        class="manage-column column-author"><?php _e('Discount Type', 'woo-discount-rules'); ?></th>
                    <th scope="col" id="author"
                        class="manage-column column-author"><?php _e('Start Date', 'woo-discount-rules'); ?></th>
                    <th scope="col" id="tags"
                        class="manage-column column-tags"><?php _e('Expired On', 'woo-discount-rules'); ?></th>
                    <?php
                    if (count($site_languages) > 1) {
                        ?>
                        <th scope="col" id="tags"
                            class="manage-column column-tags"><?php _e('Language(s)', 'woo-discount-rules'); ?></th>
                        <?php
                    }
                    ?>
                    <!--<th scope="col" id="tags" class="manage-column column-tags">
                        <?php /*_e('Priority','woo-discount-rules') */?>
                    </th>-->
                    <th scope="col" id="status"
                        class="manage-column column-tags"><?php _e('Status', 'woo-discount-rules'); ?></th>
                    <th scope="col" id="title"
                        class="manage-column column-title"><?php _e('Action', 'woo-discount-rules'); ?></th>
                </tr>
                </thead>
                <tbody class="wdr-ruleboard" id="sortable"><?php
                if ($rules) {
                    foreach ($rules as $rule_row) { ?>
                        <tr id="<?php echo $rule_row->getId(); ?>" class="awdr-listing-rule-tr">
                            <th scope="row" class="check-column awdr-listing-rule-check-box-align">
                                <span class="dashicons dashicons-menu" style="padding-left: 5px;"></span>
                            </th>
                            <th scope="row" class="check-column awdr-listing-rule-check-box-align">
                                <input id="cb-select-<?php echo $rule_row->getId(); ?>" class="wdr-rules-selector"
                                       type="checkbox" name="saved_rules[]"
                                       value="<?php echo $rule_row->getId(); ?>">
                            </th>
                            <td class="title column-title has-row-actions column-primary page-title"
                                data-colname="Title">
                                <strong>
                                    <a class="row-title"
                                       href="<?php echo admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules', 'task' => 'view', 'id' => $rule_row->getId()))); ?>"
                                       aria-label="“<?php echo $rule_row->getTitle(); ?>” (Edit)"><?php echo $rule_row->getTitle();
                                       if($rule_row->isExclusive()) {?>
                                               <span class="awdr-exclusive-disable-listing"><?php _e('Exclusive', 'woo-discount-rules'); ?></span> <?php
                                       }?></a>
                                </strong>
                                <div class="awdr_created_date_html">
                                <?php
                                $created_by = $rule_row->getRuleCreatedBy();
                                if($created_by) {
                                    if (function_exists('get_userdata')) {
                                        if ($user = get_userdata($created_by)) {
                                            if (isset($user->data->display_name)) {
                                                $created_by = $user->data->display_name;
                                            }
                                        }
                                    }
                                }
                                $created_on = $rule_row->getRuleCreatedOn();

                                $modified_by = $rule_row->getRuleModifiedBy();
                                if($modified_by) {
                                    if (function_exists('get_userdata')) {
                                        if ($user = get_userdata($modified_by)) {
                                            if (isset($user->data->display_name)) {
                                                $modified_by = $user->data->display_name;
                                            }
                                        }
                                    }
                                }
                                $modified_on = $rule_row->getRuleModifiedOn();
                                if($created_by && !empty($created_by) && !empty($created_on)){ ?>
                                    <span class="wdr_desc_text"><?php _e('Created by: ' .$created_by.'' , 'woo-discount-rules');?>,<?php  _e(' On: ' . $created_on , 'woo-discount-rules'); ?> &nbsp;</span><?php }
                                if($modified_by && !empty($modified_by) && !empty($modified_on)){?>
                                    <span class="wdr_desc_text"><?php _e('Modified by: ' .$modified_by.'' , 'woo-discount-rules');?>,<?php  _e(' On: ' . $modified_on , 'woo-discount-rules'); ?> </span><?php
                                }?>
                                </div>
                            </td>
                            <td class="author column-author" data-colname="Author"><?php
                                $get_discount_type = $rule_row->getRuleDiscountType();
                                $discount_type_name = '-';
                                switch ($get_discount_type){
                                    case'wdr_simple_discount':
                                        $discount_type_name = __('Product Adjustment', 'woo-discount-rules');
                                        break;
                                    case'wdr_cart_discount':
                                        $discount_type_name =  __('Cart Adjustment', 'woo-discount-rules');
                                        break;
                                    case'wdr_free_shipping':
                                        $discount_type_name = __('Free Shipping', 'woo-discount-rules');
                                        break;
                                    case'wdr_bulk_discount':
                                        $discount_type_name = __('Bulk Discount', 'woo-discount-rules');
                                        break;
                                    case'wdr_set_discount':
                                        $discount_type_name = __('Set Discount', 'woo-discount-rules');
                                        break;
                                    case'wdr_buy_x_get_x_discount':
                                        $discount_type_name = __('Buy X get X', 'woo-discount-rules');
                                        break;
                                    case'wdr_buy_x_get_y_discount':
                                        $discount_type_name = __('Buy X get Y', 'woo-discount-rules');
                                        break;
                                }
                                ?>
                                <abbr><?php echo $discount_type_name; ?></abbr>
                            </td>
                            <td class="author column-author" data-colname="Author"><?php
                                $get_start_date = $rule_row->getStartDate($timestamp = false, $format = "Y-m-d H:i");
                                ?>
                                <abbr><?php echo is_null($get_start_date) ? '-' : $get_start_date; ?></abbr>
                            </td>
                            <td class="date column-date" data-colname="Date"><?php
                                $get_end_date = $rule_row->getEndDate($timestamp = false, $format = "Y-m-d H:i");
                                ?>
                                <abbr><?php echo is_null($get_end_date) ? '-' : $get_end_date; ?></abbr>
                            </td>
                            <?php
                            if (count($site_languages) > 1) {
                                ?>
                                <td>
                                    <?php
                                    $chosen_languages = $rule_row->getLanguages();
                                    if (!empty($chosen_languages)) {
                                        $i = 1;
                                        foreach ($chosen_languages as $language) {
                                            echo isset($site_languages[$language]) ? $site_languages[$language] : '';
                                            if (count($chosen_languages) > $i) {
                                                echo ', ';
                                            }
                                            $i++;
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <?php
                            }
                            ?>
                           <!-- <td></td>-->
                            <td class="date column-tag" data-colname="wdr-rule-status">
                                <label class="switch switch-left-right">
                                    <input class="switch-input wdr_manage_status" name="toogle_action" type="checkbox" data-awdr_="<?php echo $rule_row->getId(); ?>" data-awdr_nonce="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_manage_status'.$rule_row->getId()); ?>" data-manage-status="<?php echo $rule_row->getId(); ?>" <?php echo ($rule_row->isEnabled()) ? 'checked' : '';?>/>
                                    <span class="switch-label" data-on="<?php _e('Enabled', 'woo-discount-rules'); ?>" data-off="<?php _e('Disabled', 'woo-discount-rules'); ?>"></span>
                                    <span class="switch-handle"></span>
                                </label>
                                    <span class="awdr-enabled-status" style="<?php echo (!$rule_row->isEnabled()) ? 'display:none' : '';?>">
                                    <?php
                                    $rule_status = $rule_row->getRuleVaildStatus();
                                    $check_rule_limit = $rule_row->checkRuleUsageLimits();
                                    $current_time_stamp = current_time('timestamp');
                                    $current_time = $rule_row->formatDate($current_time_stamp, $format = "Y-m-d H:i", false);
                                    if($rule_status == 'in_future'){
                                        if ($check_rule_limit == 'Disabled') { ?>
                                            <span class="awdr-listing-status-text"><?php _e(' - ( Not running )', 'woo-discount-rules');?></span><br>
                                                <span class="awdr-text-warning"><b><?php _e('Rule reached maximum usage limit', 'woo-discount-rules');?> </b>
                                                </span><?php

                                        } else{ ?>
                                            <span class="awdr-listing-status-text"><?php _e(' - ( Will run in future)', 'woo-discount-rules');?></span><br><?php
                                            if(isset($current_time) && !empty($current_time)) {
                                                ?>
                                                <span class="awdr-text-warning"><b><?php _e('Your server current date and time:', 'woo-discount-rules');?> </b><?php echo $current_time; ?>
                                                </span><?php
                                            }
                                        }
                                    }elseif ($rule_status == 'expired'){
                                        if ($check_rule_limit == 'Disabled') { ?>
                                            <span class="awdr-listing-status-text"><?php _e(' - ( Not running )', 'woo-discount-rules');?></span><br>
                                            <span class="awdr-text-warning"><b><?php _e('Rule reached maximum usage limit', 'woo-discount-rules');?> </b>
                                            </span><?php
                                        } else{ ?>
                                            <span class="awdr-listing-status-text"><?php _e(' - ( Not running - validity expired)', 'woo-discount-rules');?></span><br><?php
                                            if(isset($current_time) && !empty($current_time)) {
                                                ?>
                                                <span class="awdr-text-warning"><b><?php _e('Your server current date and time:', 'woo-discount-rules');?> </b><?php echo $current_time; ?>
                                                </span><?php
                                            }
                                        }
                                    }else{
                                        if ($check_rule_limit == 'Disabled') { ?>
                                            <span class="awdr-listing-status-text"><?php _e(' - ( Not running )', 'woo-discount-rules');?></span><br>
                                            <span class="awdr-text-warning"><b><?php _e('Rule reached maximum usage limit', 'woo-discount-rules');?> </b>
                                            </span><?php
                                        } else{ ?>
                                            <span class="awdr-listing-status-text"><?php _e(' - (Running)', 'woo-discount-rules');?></span><?php
                                        }
                                    }?>
                                    </span>
                            </td>
                            <td class="awdr-rule-buttons">
                                <a class="btn btn-primary"
                                   href="<?php echo admin_url("admin.php?" . http_build_query(array('page' => WDR_SLUG, 'tab' => 'rules', 'task' => 'view', 'id' => $rule_row->getId()))); ?>">
                                    <?php _e('Edit', 'woo-discount-rules'); ?></a>
                                <a class="btn btn-primary wdr_duplicate_rule"
                                   data-duplicate-rule="<?php echo $rule_row->getId(); ?>" data-awdr_nonce="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_duplicate_rule'.$rule_row->getId()); ?>"><?php _e('Duplicate', 'woo-discount-rules'); ?></a>
                                <a class="btn btn-danger wdr_delete_rule"
                                   data-delete-rule="<?php echo $rule_row->getId(); ?>" data-awdr_nonce="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_delete_rule'.$rule_row->getId()); ?>">
                                    <?php _e('Delete', 'woo-discount-rules'); ?></a>
                            </td>
                        </tr>

                        <?php
                    }
                } else {
                    ?>
                    <tr class="no-items">
                        <td></td>
                        <td></td>
                        <td class="colspanchange" colspan="2"><?php _e('No rules found.', 'woo-discount-rules');?></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                    </td>
                    <td class="manage-column column-cb check-column">
                        <input name="bulk_check[]" class="wdr-rules-select" type="checkbox" value="off"/>
                    </td>
                    <th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
                        <a href="javascript:void(0);">
                            <span><?php _e('Title', 'woo-discount-rules'); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th scope="col" id="author"
                        class="manage-column column-author"><?php _e('Discount Type', 'woo-discount-rules'); ?></th>
                    <th scope="col" id="author"
                        class="manage-column column-author"><?php _e('Start Date', 'woo-discount-rules'); ?></th>
                    <th scope="col" id="tags"
                        class="manage-column column-tags"><?php _e('Expired On', 'woo-discount-rules'); ?></th>
                    <?php
                    if (count($site_languages) > 1) {
                        ?>
                        <th scope="col" id="tags"
                            class="manage-column column-tags"><?php _e('Language(s)', 'woo-discount-rules'); ?></th>
                        <?php
                    }
                    ?>
                    <!--<th scope="col" id="tags" class="manage-column column-tags">
                        <?php /*_e('Priority','woo-discount-rules') */?>
                    </th>-->
                    <th scope="col" id="status"
                        class="manage-column column-tags"><?php _e('Status', 'woo-discount-rules'); ?></th>
                    <th scope="col" id="title"
                        class="manage-column column-title"><?php _e('Action', 'woo-discount-rules'); ?></th>
                </tr>
                </tfoot>
            </table>


            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <!-- <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk
                         action</label><select name="action2" id="bulk-action-selector-bottom">
                         <option value="-1">Bulk Actions</option>
                         <option value="edit" class="hide-if-no-js">Edit</option>
                         <option value="trash">Move to Trash</option>
                     </select>
                     <input type="submit" id="doaction2" class="button action" value="Apply">-->
                </div>
                <div class="alignleft actions">
                </div>
                <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo $rules_count . ' ';
                        ($rules_count == 0 || $rules_count == 1) ? _e('item', 'woo-discount-rules') : _e('items', 'woo-discount-rules'); ?></span></span>
                </div>
                <br class="clear">
            </div>
            <input type="hidden" name="awdr_rule_list_nonce" value="<?php echo \Wdr\App\Helpers\Helper::create_nonce('awdr_rule_list'); ?>">
            <input type="hidden" name="action" value="wdr_ajax">
            <input type="hidden" name="method" value="bulk_action">
            <input type="hidden" name="adminUrl" value="<?php echo admin_url('admin.php?page=woo_discount_rules') ?>">
        </form>
        <br class="clear">
    </div>
    <?php
    if(!$is_pro){ ?>
        <div class="col-md-6 col-lg-6 text-right" style="width: 27%; float: right;">
            <div class="col-md-12">
                <a href="https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=wpwoodiscountrules&utm_medium=plugin&utm_campaign=inline&utm_content=woo-discount-rules" target="_blank" class="btn btn-success"><?php esc_html_e('Looking for more features? Upgrade to PRO', 'woo-discount-rules'); ?></a>
            </div>
            <div class="woo-side-panel">
                <div class="panel">
                    <div class="panel-body">
                        <h3><?php esc_html_e('With PRO version, you can create:', 'woo-discount-rules')?></h3>
                        <p><?php esc_html_e('- Categories based discounts', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- User roles based discounts', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Buy One Get One Free deals', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Buy X Get Y deals', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Buy 2, get 1 at 50% discount', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Buy 3 for $10 (Package / Bundle [Set] Discount)', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Different discounts with one coupon code', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Purchase history based discounts', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Free product / gift', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Discount for variants', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Conditional discounts', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Fixed cost discounts', 'woo-discount-rules')?></p>
                        <p><?php esc_html_e('- Offer fixed price on certain conditions', 'woo-discount-rules')?></p>
                        <p><a href="https://www.flycart.org/products/wordpress/woocommerce-discount-rules?utm_source=wpwoodiscountrules&amp;utm_medium=plugin&amp;utm_campaign=inline&amp;utm_content=woo-discount-rules" class="btn btn-success" target="_blank"><?php esc_html_e('Go PRO', 'woo-discount-rules'); ?></a></p>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
