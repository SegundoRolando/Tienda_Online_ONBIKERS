<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$active = 'pricing-rules';
include_once(WOO_DISCOUNT_DIR . '/view/includes/header.php');
include_once(WOO_DISCOUNT_DIR . '/view/includes/menu.php');

$config = (isset($config)) ? $config : '{}';

$data = array();
$rule_list = $config;
$flycartWooDiscountRulesPurchase = new FlycartWooDiscountRulesPurchase();
$isPro = $flycartWooDiscountRulesPurchase->isPro();

$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
$current_url = remove_query_arg( 'paged', $current_url );
if ( isset( $_GET['order'] ) && 'asc' === $_GET['order'] ) {
    $current_order = 'asc';
} else {
    $current_order = 'desc';
}
if ( isset( $_GET['orderby'] ) ) {
    $current_orderby = $_GET['orderby'];
} else {
    $current_orderby = '';
}
$orderby = 'ordering';
$desc_first = 0 ;
if ( $current_orderby === $orderby ) {
    $order = 'desc' === $current_order ? 'asc' : 'desc';
    $class[] = 'sorted';
    $class[] = $current_order;
} else {
    $order = $desc_first ? 'desc' : 'asc';
    $class[] = 'sortable';
    $class[] = $desc_first ? 'asc' : 'desc';
}
$current_language = FlycartWooDiscountRulesGeneralHelper::getWPMLLanguage();
?>

    <style>
        @media screen and (max-width: 600px) {
            table {
                width: 100%;
            }

            thead {
                display: none;
            }

            tr:nth-of-type(2n) {
                background-color: inherit;
            }

            tr td:first-child {
                background: #f0f0f0;
                font-weight: bold;
                font-size: 1.3em;
            }

            tbody td {
                display: block;
                text-align: left;
            }

            tbody td:before {
                content: attr(data-th);
                display: block;
                text-align: left;
            }
        }
    </style>

    <div class="container-fluid woo_discount_loader_outer" id="pricing_rule">
        <div class="row-fluid">
            <div class="<?php echo $isPro? 'col-md-12': 'col-md-8'; ?>">
                <div class="row">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><?php esc_html_e('Price Rules', 'woo-discount-rules'); ?></h4>
                        </div>
                        <?php if(0) { /* Removed from v2.3.6 */ ?>
                            <div class="col-md-4 text-right">
                                <br/>
                                <a href="https://www.flycart.org/woocommerce-discount-rules-examples?utm_source=woo-discount-rules&utm_campaign=doc&utm_medium=text-click&utm_content=example_price_rules#pricediscountexample" target="_blank" class="btn btn-info"><?php esc_html_e('View Examples', 'woo-discount-rules'); ?></a>
                                <a href="http://docs.flycart.org/woocommerce-discount-rules/price-discount-rules?utm_source=woo-discount-rules&utm_campaign=doc&utm_medium=text-click&utm_content=documentation" target="_blank" class="btn btn-info"><?php esc_html_e('Documentation', 'woo-discount-rules'); ?></a>
                            </div>
                        <?php } ?>
                        <hr>
                    </div>
                    <div class="">
                        <form id="woo_discount_list_form" method="post" action="?page=woo_discount_rules">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php if (isset($rule_list)) {
                                        if (count($rule_list) >= 6 && !$pro) { ?>
                                            <a href=javascript:void(0) class="btn btn-primary"><?php esc_html_e('You Reach Max. Rule Limit', 'woo-discount-rules'); ?></a>
                                        <?php } else {
                                            ?>
                                            <a href="?page=woo_discount_rules&type=new" id="add_new_rule"
                                               class="btn btn-primary"><?php esc_html_e('Add New Rule', 'woo-discount-rules'); ?></a>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="col-md-12">
                                    <div class="woo_discount_rules_bulk_action_con">
                                        <div class="alignleft actions bulkactions">
                                            <select name="bulk_action" id="bulk-action-selector-top">
                                                <option value=""><?php esc_html_e('Bulk Actions', 'woo-discount-rules'); ?></option>
                                                <option value="publish"><?php esc_html_e('Enable rules', 'woo-discount-rules'); ?></option>
                                                <option value="unpublish"><?php esc_html_e('Disable rules', 'woo-discount-rules'); ?></option>
                                                <option value="delete"><?php esc_html_e('Delete rules', 'woo-discount-rules'); ?></option>
                                            </select>
                                            <input id="wdr_do_bulk_action" class="button action" value="<?php esc_html_e('Apply', 'woo-discount-rules'); ?>" type="button">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row wdr_price_rule_listing_table_con">
                                <div class="">
                                    <table class="wp-list-table widefat fixed striped posts">
                                        <thead>
                                        <tr>
                                            <td id="cb" class="manage-column column-cb check-column">
                                                <input id="cb-select-all-1" type="checkbox" />
                                            </td>
                                            <th><?php esc_html_e('Name', 'woo-discount-rules'); ?></th>
                                            <th><?php esc_html_e('Start Date', 'woo-discount-rules'); ?></th>
                                            <th><?php esc_html_e('Expired On', 'woo-discount-rules'); ?></th>
                                            <th><?php esc_html_e('Status', 'woo-discount-rules'); ?></th>
                                            <?php if(!empty($current_language)){
                                                ?>
                                                <th><?php esc_html_e('Language', 'woo-discount-rules'); ?></th>
                                                <?php
                                            } ?>
                                            <th class="manage-column column-title column-primary sorted <?php echo $current_order; ?>" scope="col">
                                                <?php
                                                $column_display_name = esc_html__('Order', 'woo-discount-rules');
                                                $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
                                                echo $column_display_name;
                                                ?>
                                            </th>
                                            <th><?php esc_html_e('Action', 'woo-discount-rules'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody id="pricing_rule">
                                        <?php
                                        $i = 1;
                                        if (is_array($rule_list)) {
                                            if (count($rule_list) > 0) {
                                                foreach ($rule_list as $index => $rule) {
                                                    if (!$pro && $i > 6) continue;
                                                    $meta = $rule->meta;
                                                    $status = isset($meta['status'][0]) ? $meta['status'][0] : 'disable';
                                                    $class = 'btn btn-success';

                                                    if ($status == 'publish') {
                                                        $class = 'btn btn-success';
                                                        $value = esc_html__('Disable', 'woo-discount-rules');
                                                    } else {
                                                        $class = 'btn btn-warning';
                                                        $value = esc_html__('Enable', 'woo-discount-rules');;
                                                    }
                                                    ?>

                                                    <tr>
                                                        <th class="check-column">
                                                            <input id="cb-select-<?php echo $i; ?>" name="post[]" value="<?php echo $rule->ID; ?>" type="checkbox"/>
                                                        </th>
                                                        <td>
                                                            <div class="wdr_rule_title">
                                                                <a href="?page=woo_discount_rules&view=<?php echo $rule->ID ?>">
                                                                    <?php echo (isset($rule->rule_name) ? $rule->rule_name : '-'); ?>
                                                                </a>
                                                            </div>
                                                            <?php
                                                            if(isset($rule->rule_descr) && !empty($rule->rule_descr)){
                                                                ?>
                                                                <div class="wdr_desc_text">
                                                                    <?php echo $rule->rule_descr; ?>
                                                                </div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo(isset($rule->date_from) ? $rule->date_from : '-') ?></td>
                                                        <td><?php echo(isset($rule->date_to) ? $rule->date_to : '-') ?></td>
                                                        <td class="status_in_text" id="status_in_text_<?php echo $rule->ID ?>"><?php
                                                            if(!isset($rule->status)) $rule->status = 'disable';
                                                            if($rule->status == 'publish'){
                                                                echo "<span class='wdr_status_active_text alert alert-success'>".esc_html__('Active', 'woo-discount-rules')."</span>";
                                                            } else {
                                                                echo "<span class='wdr_status_disabled_text alert alert-danger'>".esc_html__('Disabled', 'woo-discount-rules')."</span>";
                                                            }
                                                            if($rule->status == 'publish'){
                                                                $date_from = (isset($rule->date_from) ? $rule->date_from : false);
                                                                $date_to = (isset($rule->date_to) ? $rule->date_to : false);
                                                                $validate_date_string = FlycartWooDiscountRulesGeneralHelper::validateDateAndTimeWarningTextForListingHTML($date_from, $date_to);
                                                                echo $validate_date_string;
                                                            }
                                                            ?></td>
                                                        <?php if(!empty($current_language)){
                                                            ?>
                                                            <td><?php echo((isset($rule->wpml_language) && ($rule->wpml_language != '')) ? $rule->wpml_language : ' - ') ?></td>
                                                            <?php
                                                        } ?>
                                                        <td><?php echo((isset($rule->rule_order) && ($rule->rule_order != '')) ? $rule->rule_order : ' - ') ?></td>
                                                        <td>
                                                            <a class="btn btn-primary" href="?page=woo_discount_rules&view=<?php echo $rule->ID ?>">
                                                                <?php esc_html_e('Edit', 'woo-discount-rules'); ?>
                                                            </a>
                                                            <?php if($pro){ ?>
                                                                <button class="btn btn-primary duplicate_price_rule_btn" data-id="<?php echo $rule->ID; ?>" type="button">
                                                                    <?php esc_html_e('Duplicate', 'woo-discount-rules'); ?>
                                                                </button>
                                                            <?php } ?>
                                                            <a class="<?php echo $class; ?> manage_status"
                                                               id="state_<?php echo $rule->ID ?>"><?php echo $value; ?>
                                                            </a>
                                                            <a class="btn btn-danger delete_rule" id="delete_<?php echo $rule->ID ?>">
                                                                <?php esc_html_e('Delete', 'woo-discount-rules'); ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $i++;
                                                }
                                            }
                                        }
                                        ?>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td id="cb" class="manage-column column-cb check-column">
                                                <input id="cb-select-all-1" type="checkbox" />
                                            </td>
                                            <th><?php esc_html_e('Name', 'woo-discount-rules'); ?></th>
                                            <th><?php esc_html_e('Start Date', 'woo-discount-rules'); ?></th>
                                            <th><?php esc_html_e('Expired On', 'woo-discount-rules'); ?></th>
                                            <th><?php esc_html_e('Status', 'woo-discount-rules'); ?></th>
                                            <?php if(!empty($current_language)){
                                                ?>
                                                <th><?php esc_html_e('Language', 'woo-discount-rules'); ?></th>
                                                <?php
                                            } ?>
                                            <th class="manage-column column-title column-primary sorted <?php echo $current_order; ?>" scope="col">
                                                <?php
                                                $column_display_name = esc_html__('Order', 'woo-discount-rules');
                                                $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
                                                echo $column_display_name;
                                                ?>
                                            </th>
                                            <th><?php esc_html_e('Action', 'woo-discount-rules'); ?></th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <hr>

                            <input type="hidden" name="form" value="pricing_rules">
                            <input type="hidden" id="ajax_path" value="<?php echo admin_url('admin-ajax.php') ?>">
                            <input type="hidden" name="wdr_nonce" value="<?php echo  FlycartWooDiscountRulesGeneralHelper::createNonce('wdr_rule_listing'); ?>">
                        </form>
                    </div>
                </div>
            </div>
            <?php if(!$isPro){ ?>
                <div class="col-md-1"></div>
                <!-- Sidebar -->
                <?php include_once(__DIR__ . '/template/sidebar.php'); ?>
                <!-- Sidebar END -->
            <?php } ?>
        </div>
        <div class="woo_discount_loader">
            <div class="lds-ripple"><div></div><div></div></div>
        </div>
    </div>
    <div class="clear"></div>
<?php include_once(WOO_DISCOUNT_DIR . '/view/includes/footer.php'); ?>