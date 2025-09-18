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
     // (tuỳ chọn) JS đổi icon toggler
      wp_enqueue_script(
        'nav-toggle-icons',
        get_stylesheet_directory_uri() . '/js/nav-toggle-icons.js',
        ['bootstrap-bundle'], '1.0', true
      );

    // Custom script
    wp_enqueue_script(
        'vineye-custom',
        get_template_directory_uri() . '/js/custom.js',
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

// Tạo sidebar tùy chỉnh
function my_custom_sidebar() {
    register_sidebar( array(
        'name'          => 'Custom Sidebar',
        'id'            => 'custom-sidebar',
        'description'   => 'Sidebar riêng để chứa widget',
        'before_widget' => '<div class="widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action( 'widgets_init', 'my_custom_sidebar' );
// Tạo footer
// Lấy ID trang "Footer Settings" theo slug, có fallback theo title
// ---- CONFIG ----
if (!defined('VE_FOOTER_PAGE_SLUG')) {
  define('VE_FOOTER_PAGE_SLUG', 'footer-settings');
}

// Lấy ID trang "Footer Settings"
if (!function_exists('ve_footer_settings_id')) {
  function ve_footer_settings_id() {
    $page = get_page_by_path(VE_FOOTER_PAGE_SLUG, OBJECT, 'page');
    if ($page instanceof WP_Post) return (int)$page->ID;

    $pages = get_posts([
      'post_type'   => 'page',
      'title'       => 'Footer Settings',
      'post_status' => 'any',
      'numberposts' => 1,
    ]);
    return $pages ? (int)$pages[0]->ID : 0;
  }
}

// Helper: chuyển Image (Array/ID/URL) -> URL
if (!function_exists('ve_img_url')) {
  function ve_img_url($img) {
    if (is_array($img) && !empty($img['url'])) return $img['url'];
    if (is_numeric($img)) return wp_get_attachment_image_url((int)$img, 'full');
    if (is_string($img))  return $img;
    return '';
  }
}

// (tùy chọn) helper đọc field theo trang Footer Settings
if (!function_exists('ve_footer_get')) {
  function ve_footer_get($key) {
    $sid = ve_footer_settings_id();
    $v = $sid ? get_field($key, $sid) : null;
    if (!$v) $v = get_field($key, 'option');
    return $v;
  }
}
if (!function_exists('ve_footer_get_any')) {
  function ve_footer_get_any(array $keys) {
    foreach ($keys as $k) {
      $v = ve_footer_get($k);
      if (!empty($v)) return $v;
    }
    return null;
  }
}

// ============ ACF: lien_he (left_content + map) ============
add_action('acf/init', function () {
  if (!function_exists('acf_add_local_field_group')) return;

  acf_add_local_field_group([
    'key'    => 'group_lien_he',
    'title'  => 'Liên hệ',
    'fields' => [
      [
        'key'   => 'field_lh_left',
        'label' => 'Left content',
        'name'  => 'left_content',
        'type'  => 'group',
        'sub_fields' => [
          [
            'key'   => 'field_lh_left_title',
            'label' => 'Title',
            'name'  => 'title',
            'type'  => 'text',
            'default_value' => 'BỆNH VIỆN QUỐC TẾ VINEYES',
          ],
          [
            'key'   => 'field_lh_left_content',
            'label' => 'Content',
            'name'  => 'content',
            'type'  => 'wysiwyg',   // hoặc 'textarea' nếu bạn muốn đơn giản
            'tabs'  => 'all',
            'media_upload' => 0,
          ],
        ],
      ],
      [
        'key'   => 'field_lh_map',
        'label' => 'Map (Google Maps iframe)',
        'name'  => 'map',
        'type'  => 'textarea',
        'instructions' => 'Dán thẳng mã <iframe> embed Google Maps vào đây.',
      ],
      [
        'key'   => 'field_lh_map_title',
        'label' => 'Map title',
        'name'  => 'map_title',
        'type'  => 'text',
        'default_value' => 'BẢN ĐỒ GOOGLE MAPS',
      ],
    ],
    'location' => [[['param'=>'page_template','operator'=>'==','value'=>'page-contact-simple.php']]],
  ]);
});





// // Tắt admin bar trên frontend
add_filter('show_admin_bar', '__return_false');