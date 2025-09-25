<?php
get_header(); ?>

<main>
    <div id="main-content" class="container">
        <div class="row">
            <div class="col-md-9 py-3">
                <?php
                if ( have_posts() ) :
                    while ( have_posts() ) : the_post();
                        the_title('<h2 style="font-size: 2rem; margin-bottom: 1.5rem;">','</h2>');
                        the_content();
        endwhile;
    endif;
    ?>
            </div>
            <div class="col-md-3">
                <?php if ( is_active_sidebar( 'custom-sidebar' ) ) : ?>
                <aside id="sidebar" class="sidebar">
                    <?php dynamic_sidebar( 'custom-sidebar' ); ?>
                </aside>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>