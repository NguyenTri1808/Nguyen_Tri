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
// lấy bài post

function short_posts_anywhere($atts) {
  $a = shortcode_atts([
    'per_page' => 6,
    'cat'      => '',   // slug chuyên mục (vd: tin-tuc)
    'tag'      => '',   // slug tag
    'orderby'  => 'date',
    'order'    => 'DESC',
  ], $atts, 'posts_anywhere');

  $paged = max(1, get_query_var('paged'), get_query_var('page'));

  $args = [
    'post_type'      => 'post',
    'posts_per_page' => (int)$a['per_page'],
    'paged'          => $paged,
    'orderby'        => $a['orderby'],
    'order'          => $a['order'],
  ];

  if ($a['cat']) $args['category_name'] = sanitize_title($a['cat']);
  if ($a['tag']) $args['tag']           = sanitize_title($a['tag']);

  $q = new WP_Query($args);

  ob_start();
  if ($q->have_posts()) {
    echo '<div class="posts-grid">';
    while ($q->have_posts()) { $q->the_post();
      echo '<article class="post-card">';
      if (has_post_thumbnail()) {
        echo '<a class="thumb" href="' . esc_url(get_permalink()) . '">';
        the_post_thumbnail('medium');
        echo '</a>';
      }
      echo '<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
      echo '<div class="excerpt">' . esc_html(wp_trim_words(get_the_excerpt(), 25)) . '</div>';
      echo '</article>';
    }
    echo '</div>';

    echo '<nav class="pagination">';
    echo paginate_links([
      'total'   => $q->max_num_pages,
      'current' => $paged,
    ]);
    echo '</nav>';
  } else {
    echo '<p>Chưa có bài viết.</p>';
  }
  wp_reset_postdata();
  return ob_get_clean();
}
add_shortcode('posts_anywhere', 'short_posts_anywhere');

// Category

add_filter('dv/category/query_args', function($args, $term) {
  switch ($term->slug) {

    case 'khammat':
      // Ví dụ: thêm tag liên quan khi ở "khammat"
      // $args['tag_slug__in'] = ['kham-mat', 'nhan-khoa'];
      break;

    case 'cacdichvukhac':
      // Ví dụ: hiển thị cả bài của nhiều category liên quan
      // $args['tax_query'] = [[
      //   'taxonomy' => 'category',
      //   'field'    => 'slug',
      //   'terms'    => ['cacdichvukhac', 'dich-vu-bo-sung'],
      //   'include_children' => true,
      // ]];
      break;

    case 'dieutrilac':
      // Ví dụ: chỉ bài nổi bật (ACF: noi_bat = 1)
      // $args['meta_query'] = [[ 'key' => 'noi_bat', 'value' => '1', 'compare' => '=' ]];
      // $args['orderby'] = 'date';
      break;

    case 'phauthuatglaucoma':
      // Ví dụ: sắp xếp theo lượt xem (meta_key = view)
      // $args['meta_key'] = 'view';
      // $args['orderby']  = 'meta_value_num';
      // $args['order']    = 'DESC';
      break;

    case 'phauthuatphaco':
      // Ví dụ: mới nhất trước (giữ mặc định)
      break;

    case 'taohinhthammy':
      // Ví dụ: ưu tiên bài có ảnh đại diện
      // $args['meta_query'][] = [
      //   'key'     => '_thumbnail_id',
      //   'compare' => 'EXISTS',
      // ];
      break;

    case 'dieutritatkhucxa':
      // Ví dụ: gộp thêm tag 'lasik'
      // $args['tag_slug__in'] = ['lasik','smile','prk'];
      break;

    default:
      // Các category khác tự động dùng rule mặc định (không cần làm gì)
      break;
  }
  return $args;
}, 10, 2);

