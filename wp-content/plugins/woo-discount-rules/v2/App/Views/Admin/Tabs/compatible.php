    <?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    ?>
    <br>

    <div id="wpbody-content" class="awdr-container">
        <div class="awdr-compatible-form">
            <form name="compatible_form" id="compatible-form" method="post">
                <?php $has_compatibility_plugin = false; ?>
                <h1><?php _e('Compatibility for Woo Discount Rules', 'woo-discount-rules') ?></h1>
                <div class="awdr-compatible-field-container">
                    <?php
                        $base->loadFields($has_compatibility_plugin);
                     ?>
                </div>
                <?php
                if($has_compatibility_plugin){
                    ?>
                    <div class="save-configuration">
                        <p class="submit">
                            <button type="submit" name="awdr_compatibility_submit" id="submit" class="button button-primary save-compatibility-submit"
                                    value="1"><?php esc_html_e('Save Changes', 'woo-discount-rules'); ?></button></p>
                    </div>
                <?php
                } else{
                    ?>
                    <div class="">
                        <?php esc_html_e('This section lists plugins that require a compatibility with discount rules to resolve conflicts. 
Please tick them (by checking the box) and then save. This will help discount rules to run smoothly along with these plugins', 'woo-discount-rules'); ?>
                    </div>
                <?php
                }
                ?>
            </form>
        </div>
        <div class="clear"></div>
    </div>





