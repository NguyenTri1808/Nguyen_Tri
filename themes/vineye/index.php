<?php
/**
 * Generic page layout (i18n-ready)
 */
$td = 'vineye'; // đổi theo text domain của theme bạn
get_header(); ?>

<main role="main" aria-label="<?php echo esc_attr_x( 'Main content', 'ARIA label', $td ); ?>">
    <div id="main-content" class="container">
        <div class="row">
            <div class="col-md-9 py-3">
                <?php
        if ( have_posts() ) :
          while ( have_posts() ) : the_post();
            the_title( '<h2 style="font-size: 2rem; margin-bottom: 1.5rem;">', '</h2>' );
            the_content();
          endwhile;
        else :
          // Thông báo khi không có bài viết
          echo '<p>' . esc_html__( 'No posts found.', $td ) . '</p>';
        endif;
        ?>
            </div>

            <div class="col-md-3">
                <?php if ( is_active_sidebar( 'custom-sidebar' ) ) : ?>
                <aside id="sidebar" class="sidebar"
                    aria-label="<?php echo esc_attr_x( 'Sidebar', 'ARIA label', $td ); ?>">
                    <?php dynamic_sidebar( 'custom-sidebar' ); ?>
                </aside>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>