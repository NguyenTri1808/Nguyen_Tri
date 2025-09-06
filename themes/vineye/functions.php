<?php
// ===== Load CSS & JS =====
function vineye_enqueue_scripts() {
    // Bootstrap CSS
    wp_enqueue_style(
        'bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
    );

    // Theme style.css
    wp_enqueue_style('vineye-style', get_stylesheet_uri());

    // Bootstrap JS
    wp_enqueue_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
        array('jquery'),
        null,
        true
    );

    // Custom script
    wp_enqueue_script(
        'vineye-custom',
        get_template_directory_uri() . '/assets/js/custom.js',
        array('jquery'),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'vineye_enqueue_scripts');

// ===== Theme setup =====
function vineye_theme_setup() {
    // Hỗ trợ <title>
    add_theme_support('title-tag');

    // Hỗ trợ logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Hỗ trợ menu
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'vineye'),
    ));
}
add_action('after_setup_theme', 'vineye_theme_setup');




// 2) Nạp Bootstrap CDN và custom.css
add_action('wp_enqueue_scripts', function () {
    $theme_version = wp_get_theme()->get('Version');

    // Nạp Bootstrap CSS
    wp_enqueue_style(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3',
        'all'
    );

    // Nạp Font Awesome
    wp_enqueue_style(
        'fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css',
        [],
        '7.0.1',
        'all'
    );

    // Nạp CSS tùy chỉnh (custom.css nằm ngoài cùng thư mục theme)
    wp_enqueue_style(
        'wonderland-custom',
        get_template_directory_uri() . '/custom.css',
        ['bootstrap'],
        $theme_version,
        'all'
    );

    // Nạp Bootstrap JS
    wp_enqueue_script(
        'bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        ['jquery'],
        '5.3.3',
        true
    );
    // Đảm bảo jQuery được nạp (nếu cần)
    wp_enqueue_script('jquery');
});