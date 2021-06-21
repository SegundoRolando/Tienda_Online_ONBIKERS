<?php
    if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="wdr">
    <div class="wdr-alert-top-right" id="notify-msg-holder"></div>
    <h2 class="wdr_tabs_container nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_handler) {
            $params = array(
                'page' => WDR_SLUG,
                'tab' => $tab_key
            );
            $target = '';
            $link = admin_url('admin.php?' . http_build_query($params));
           // if ($tab_key === 'help') {
                //$link = 'https://docs.flycart.org/en/collections/2195266-discount-rules-2-0?utm_source=woo-discount-rules-v2&utm_campaign=doc&utm_medium=text-click&utm_content=documentation';
               //  $target = 'target="_blank"';
          //  }
            ?>
            <a class="nav-tab <?php echo($tab_key === $current_tab ? 'nav-tab-active' : ''); ?>"
               style="<?php echo ($tab_key === 'help') ? 'background: cornflowerblue;color: white;' : ''; ?>"
               href="<?php echo $link; ?>" <?php echo $target; ?>><?php echo $tab_handler->title; ?></a>
        <?php } ?>
        <span class="awdr_version_text"> <?php echo 'v' . (defined('WDR_VERSION') ? WDR_VERSION : '2.0.0 + ') . ' '; ?> </span>
        <?php
        if (isset($on_sale_page_rebuild['available']) && $on_sale_page_rebuild['available']) {
            $additional_class_for_rebuild = '';
            if ($on_sale_page_rebuild['required_rebuild'] === true) {
                $additional_class_for_rebuild = ' need_attention';
            }
            ?>
            <span class="awdr_rebuild_on_sale_rule_page_con<?php echo $additional_class_for_rebuild; ?>">
                <button type="button" class="btn btn-danger"
                        id="awdr_rebuild_on_sale_list_on_rule_page" data-awdr_nonce="<?php echo \Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_rule_build_index'); ?>"><?php esc_html_e('Rebuild index', 'woo-discount-rules'); ?></button>
            </span>
            <?php
        }

        do_action('advanced_woo_discount_rules_content_next_to_tabs');
        ?>
    </h2>

    <div class="wdr_settings">
        <?php
        \Wdr\App\Helpers\Helper::displayCompatibleCheckMessages();
        ?>
        <div class="wdr_settings_container">
            <?php
            $handler->render($page);
            ?>
        </div>
        <div class="woo_discount_loader">
            <div class="lds-ripple">
                <div></div><div></div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>