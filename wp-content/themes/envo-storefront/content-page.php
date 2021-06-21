<!-- start content container -->
<div class="row">
    <article class="col-md-<?php envo_storefront_main_content_width_columns(); ?>">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>                          
                <div <?php post_class(); ?>>
                    <?php envo_storefront_thumb_img('envo-storefront-single', '', false, true); ?>
                    <header class="single-head page-head <?php echo esc_attr(has_post_thumbnail() ? 'has-thumbnail' : 'no-thumbnail' ) ?>">                              
                        <?php the_title('<h1 class="single-title">', '</h1>'); ?>
                        <time class="posted-on published" datetime="<?php the_time('Y-m-d'); ?>"></time>                                                        
                    </header>
                    
                    <div class="main-content-page single-content">                            
                        <div class="single-entry-summary">                              
                            <?php do_action('envo_storefront_before_content'); ?>
                            <?php the_content(); ?>
                            <?php do_action('envo_storefront_after_content'); ?>
                        </div>                               
                        <?php wp_link_pages(); ?>                                                                                     
                        <?php comments_template(); ?>
                    </div>
                </div>        
            <?php endwhile; ?>        
        <?php else : ?>            
            <?php get_template_part('content', 'none'); ?>        
        <?php endif; ?>    
    </article>       
    <?php get_sidebar('right'); ?>
</div>
<!-- end content container -->
