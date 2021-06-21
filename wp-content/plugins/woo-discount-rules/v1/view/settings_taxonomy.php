<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$active = 'taxonomy';
include_once(WOO_DISCOUNT_DIR . '/view/includes/header.php');
include_once(WOO_DISCOUNT_DIR . '/view/includes/menu.php');

$data = $config;

if (is_string($data)) $data = json_decode($data, true);
$flycartWooDiscountRulesPurchase = new FlycartWooDiscountRulesPurchase();
$isPro = $flycartWooDiscountRulesPurchase->isPro();
?>

<div class="container-fluid woo_discount_loader_outer">
    <div class="row-fluid">
        <div class="<?php echo $isPro? 'col-md-12': 'col-md-8'; ?>">
            <form method="post" id="discount_config">
                <div class="col-md-12" align="right">
                    <br/>
                    <input type="submit" id="save_taxonomy_config" value="<?php esc_html_e('Save', 'woo-discount-rules'); ?>" class="btn btn-success"/>
                    <?php /* Removed from v2.3.6 echo FlycartWooDiscountRulesGeneralHelper::docsURLHTML('introduction/discount-price-rules-settings', 'settings', 'btn btn-info');*/ ?>
                </div>
                <div class="row">
                    <div class="">
                        <br/>
                    </div>
                    <div class="">
                        <div class="row form-group">
                            <div class="col-md-2">
                                <label>
                                    <?php esc_html_e('Choose taxonomies that can be supported in the discount rules (as categories)', 'woo-discount-rules'); ?>
                                </label>
                                <div class="wdr_desc_text_con">
                                    <span class="wdr_desc_text">
                                        <?php esc_html_e('Useful when you want to provide a taxonomy based discount. The selected taxonomies here will be available as categories. So you can use the category specific discount condition to create discounts based on taxonomies.
More examples can be found in the documentation', 'woo-discount-rules'); ?>
                                        (<a href="https://docs.flycart.org" target="_blank">https://docs.flycart.org</a>)
                                    </span>
                                </div>
                            </div>
                            <?php $data['additional_taxonomies'] = (isset($data['additional_taxonomies']) ? $data['additional_taxonomies'] : array()); ?>
                            <div class="col-md-6">
                                <?php
                                $taxonomies = FlycartWooDiscountRulesGeneralHelper::getTaxonomyList();
                                ?>
                                <select class="selectpicker" multiple name="additional_taxonomies[]">
                                    <?php
                                    if(FlycartWooDiscountRulesGeneralHelper::is_countable($taxonomies)){
                                        foreach ($taxonomies as $taxonomy_key => $taxonomy_text){
                                            $selected_taxonomy = '';
                                            if(in_array($taxonomy_key, $data['additional_taxonomies'])){
                                                $selected_taxonomy = ' selected="selected" ';
                                            }
                                            ?>
                                            <option value="<?php echo $taxonomy_key; ?>"<?php echo $selected_taxonomy; ?>><?php echo $taxonomy_text; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="ajax_path" value="<?php echo admin_url('admin-ajax.php') ?>">
            </form>
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