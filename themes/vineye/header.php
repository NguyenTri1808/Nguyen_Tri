<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/custom.css">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <div class="col-md-3">
            <!-- Logo -->
              <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                  <?php
                  if (has_custom_logo()) {
                      the_custom_logo();
                  } else {
                      bloginfo('name');
                  }
                  ?>
              </a>
            </div>
           

            <!-- Toggle button -->
            <!-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'vineye'); ?>">
                <span class="navbar-toggler-icon"></span>
            </button> -->

              <div class="col-md-6"><!-- Menu -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'depth' => 2, // hỗ trợ dropdown 2 cấp
                        'container' => false,
                        'menu_class' => 'navbar-nav ms-auto mb-2 mb-lg-0',
                        'fallback_cb' => '__return_false',
                        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        // Nếu muốn hỗ trợ dropdown chuẩn Bootstrap thì cần Navwalker
                        // 'walker' => new WP_Bootstrap_Navwalker()
                    ));
                    ?>
                </div>
              </div>
              <div class="col-md-3">
                <!-- Search Form -->
                <?php get_search_form(); ?>
              </div>
        </div>
    </nav>
</header>
