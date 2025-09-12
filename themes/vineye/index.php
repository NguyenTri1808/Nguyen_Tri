<?php
get_header(); ?>

<main>
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <?php
                if ( have_posts() ) :
                    while ( have_posts() ) : the_post();
                        the_title('<h2>','</h2>');
                        the_content();
        endwhile;
    endif;
    ?>
    </div>
    <div class="col-md-3">
        <?php get_sidebar(); ?>
    </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
