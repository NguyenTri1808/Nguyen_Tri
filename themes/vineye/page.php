<?php get_header(); ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">

            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post(); ?>
                    
                    <!-- Tiêu đề trang -->
                    <h1 class="mb-4"><?php the_title(); ?></h1>
                    
                    <!-- Nội dung trang -->
                    <div class="page-content mb-5">
                        <?php the_content(); ?>
                    </div>

                <?php endwhile;
            else : ?>
                <p><?php esc_html_e( 'Xin lỗi, không tìm thấy nội dung.', 'vineye' ); ?></p>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php get_footer(); ?>
