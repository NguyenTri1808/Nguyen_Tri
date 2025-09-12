<?php
get_header();

/**
 * Lấy ảnh đầu tiên trong content nếu bài không có featured image
 * - Có cache tạm theo request để tránh parse nhiều lần
 */
if (!function_exists('dv_get_first_image')) {
  function dv_get_first_image($post_id) {
    static $cache = [];
    if (isset($cache[$post_id])) return $cache[$post_id];

    $src = '';
    $content = get_post_field('post_content', $post_id);

    // 1) Tìm <img src="..."> đầu tiên
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
      $src = esc_url_raw($m[1]);
    }

    // 2) Nếu chưa có, thử block core/image (Gutenberg)
    if (!$src && function_exists('parse_blocks')) {
      foreach (parse_blocks($content) as $b) {
        if (!empty($b['blockName']) && $b['blockName'] === 'core/image' && !empty($b['attrs']['url'])) {
          $src = esc_url_raw($b['attrs']['url']);
          break;
        }
      }
    }

    // 3) Nếu vẫn chưa có, lấy ảnh đính kèm
    if (!$src) {
      $imgs = get_attached_media('image', $post_id);
      if (!empty($imgs)) {
        $first = array_shift($imgs);
        $maybe = wp_get_attachment_image_url($first->ID, 'large');
        if ($maybe) $src = $maybe;
      }
    }

    // 4) Placeholder (đổi đường dẫn phù hợp theme của bạn)
    if (!$src) {
      $src = get_template_directory_uri() . '/assets/img/placeholder-800x450.jpg';
    }

    return $cache[$post_id] = esc_url($src);
  }
}

/**
 * Build query cho trang category — tự hoạt động cho mọi category (kể cả mới thêm).
 * Có hook 'dv/category/query_args' để bạn tùy biến per-category mà không sửa file này.
 */
function dv_build_category_query_args(WP_Term $term, $paged) {
  $base = [
    'post_type'           => 'post',
    'posts_per_page'      => 8,             // chỉnh tùy ý
    'paged'               => max(1, (int)$paged),
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => false,         // cần cho paginate_links
    'tax_query'           => [[
      'taxonomy'         => 'category',
      'field'            => 'term_id',
      'terms'            => [$term->term_id],
      'include_children' => true,
    ]],
    'orderby'             => 'date',
    'order'               => 'DESC',
    // Tối ưu cache term/meta (tắt nếu không cần)
    'update_post_term_cache' => true,
    'update_post_meta_cache' => false,
  ];

  /**
   * Cho phép custom rule theo slug category mà KHÔNG cần sửa template:
   * add_filter('dv/category/query_args', function($args, $term) { ... })
   */
  return apply_filters('dv/category/query_args', $base, $term);
}

$term  = get_queried_object();         // category hiện tại
$paged = get_query_var('paged');       // phân trang cho archive
$args  = dv_build_category_query_args($term, $paged);
$q     = new WP_Query($args);
?>

<div class="container my-5">
  <header class="mb-4">
    <h1 class="mb-1"><?php echo esc_html(single_cat_title('', false)); ?></h1>
    <?php if (trim(category_description())): ?>
      <div class="text-muted"><?php echo wp_kses_post(category_description()); ?></div>
    <?php endif; ?>
  </header>

  <div class="row g-4">
    <?php if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); ?>
      <div class="col-12 col-md-6">
        <article class="card h-100 shadow-sm border-0">
          <a href="<?php the_permalink(); ?>" class="dv-thumb d-block">
            <?php if (has_post_thumbnail()):
              the_post_thumbnail('large', ['class' => 'w-100 h-100 object-fit-cover', 'loading' => 'lazy']);
            else:
              $img = dv_get_first_image(get_the_ID());
              echo '<img class="w-100 h-100 object-fit-cover" src="' . esc_url($img) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
            endif; ?>
          </a>
          <div class="card-body">
            <h2 class="h5 card-title mb-2">
              <a class="text-decoration-none" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            <p class="card-text text-muted mb-3">
              <?php
                $text = get_the_excerpt() ?: wp_strip_all_tags(get_the_content());
                echo esc_html(wp_trim_words($text, 28));
              ?>
            </p>
            <a class="fw-semibold text-primary" href="<?php the_permalink(); ?>">XEM CHI TIẾT »</a>
          </div>
        </article>
      </div>
    <?php endwhile; else: ?>
      <p>Chưa có bài viết.</p>
    <?php endif; wp_reset_postdata(); ?>
  </div>

  <?php if (!empty($q->max_num_pages) && $q->max_num_pages > 1): ?>
    <nav class="mt-4">
      <?php echo paginate_links([
        'total'     => $q->max_num_pages,
        'current'   => max(1, (int)$paged),
        'prev_text' => '« Trước',
        'next_text' => 'Sau »',
      ]); ?>
    </nav>
  <?php endif; ?>
</div>

<style>
.dv-thumb{position:relative;aspect-ratio:16/9;overflow:hidden;border-radius:14px}
.object-fit-cover{object-fit:cover}
</style>

<?php get_footer();
