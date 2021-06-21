<?php get_header(); ?>

<!-- start content container -->
<div class="row">
    <article class="col-md-<?php envo_storefront_main_content_width_columns(); ?>">
        <?php woocommerce_content(); ?>
    </article>       
    <?php get_sidebar('right'); ?>
</div>
<!-- end content container -->

<?php
get_footer();
