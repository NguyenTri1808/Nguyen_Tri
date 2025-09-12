
<?php
/**
 * Template Name: Dich Vu
 */
get_header();

/**
 * Helper: Lấy URL ảnh đầu tiên của bài viết
 * - Ưu tiên thumbnail
 * - Nếu không có: quét <img> đầu tiên trong content
 * - Nếu vẫn không có: tìm attachment ảnh đầu tiên
 * - Nếu vẫn không có: trả về ảnh fallback (tùy chỉnh)
 */
function dv_get_first_image_url( $post_id, $fallback = '' ) {
  // 1) Thumbnail
  $thumb = get_the_post_thumbnail_url( $post_id, 'large' );
  if ( $thumb ) return $thumb;

  // 2) Ảnh <img> đầu tiên trong content
  $content = get_post_field( 'post_content', $post_id );
  if ( $content && preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $m ) ) {
    return esc_url( $m[1] );
  }

  // 3) Attachment đầu tiên
  $attachments = get_children( [
    'post_parent' => $post_id,
    'post_type'   => 'attachment',
    'post_mime_type' => 'image',
    'numberposts' => 1,
    'orderby'     => 'menu_order',
    'order'       => 'ASC',
  ] );
  if ( $attachments ) {
    $att = array_shift( $attachments );
    $src = wp_get_attachment_image_src( $att->ID, 'large' );
    if ( ! empty( $src[0] ) ) return esc_url( $src[0] );
  }

  // 4) Ảnh fallback (đặt file bạn muốn trong theme)
  if ( ! $fallback ) {
    $fallback = get_stylesheet_directory_uri() . '/assets/images/placeholder-16x9.jpg';
  }
  return esc_url( $fallback );
}

// Lấy slug category từ URL (?cat=slug). Nếu bạn thích dùng ID: ?cat_id=123
$cat_slug = isset($_GET['cat']) ? sanitize_title( wp_unslash($_GET['cat']) ) : '';
$cat_id   = 0;
if ( $cat_slug ) {
  $term = get_category_by_slug( $cat_slug );
  if ( $term ) $cat_id = (int) $term->term_id;
} elseif ( isset($_GET['cat_id']) ) {
  $cat_id = absint( $_GET['cat_id'] );
}

// Phân trang
$paged = max( 1, get_query_var('paged'), get_query_var('page') );

// Query bài viết theo category (nếu có)
$args = [
  'post_type'           => 'post',
  'posts_per_page'      => 10,
  'paged'               => $paged,
  'ignore_sticky_posts' => true,
];
if ( $cat_id ) {
  $args['cat'] = $cat_id;
}
$q = new WP_Query( $args );

// Lấy danh sách category để hiện thanh lọc (tuỳ chỉnh: chỉ hiện child của 1 cat gốc)
$root_cat_slug = ''; // nếu bạn có cat gốc “dich-vu”, set = 'dich-vu' để chỉ hiện các chuyên mục con
$cats = [];
if ( $root_cat_slug ) {
  $root = get_category_by_slug( $root_cat_slug );
  if ( $root ) {
    $cats = get_categories([
      'parent'     => $root->term_id,
      'hide_empty' => false,
      'orderby'    => 'name',
      'order'      => 'ASC',
    ]);
  }
} else {
  $cats = get_categories([
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
  ]);
}

// URL của trang hiện tại để gắn query ?cat=
$current_page_url = get_permalink( get_queried_object_id() );
?>

<style>
  /* Nhẹ nhàng giống layout minh họa */
  .dv-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 16px rgb(0 0 0 / 6%);
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  .dv-card__img {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    display: block;
  }
  .dv-card__body {
    padding: 18px 20px 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .dv-cat-filter a {
    display: inline-block;
    padding: 8px 14px;
    border: 1px solid #cbd3da;
    border-radius: 999px;
    margin: 6px 8px 0 0;
    text-decoration: none;
    font-size: 14px;
    color: #0d6efd;
    background: #fff;
  }
  .dv-cat-filter a.active {
    color: #fff;
    background: #0d6efd;
    border-color: #0d6efd;
  }
</style>

<div class="container my-5">
  <h1 class="mb-4"><?php echo esc_html( get_the_title() ); ?></h1>

  <?php if ( ! empty( $cats ) ) : ?>
    <div class="dv-cat-filter mb-4">
      <!-- Nút “Tất cả” -->
      <a href="<?php echo esc_url( remove_query_arg( ['cat','cat_id'], $current_page_url ) ); ?>"
         class="<?php echo $cat_id ? '' : 'active'; ?>">
        Tất cả
      </a>
      <?php foreach ( $cats as $c ) :
        $url = add_query_arg( [ 'cat' => $c->slug ], $current_page_url );
        $active = ( $cat_id === (int) $c->term_id ) ? 'active' : '';
      ?>
        <a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $active ); ?>">
          <?php echo esc_html( $c->name ); ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ( $cat_id ) :
    $cat_obj = get_category( $cat_id );
    if ( $cat_obj ) : ?>
      <h2 class="mb-4"><?php echo esc_html( $cat_obj->name ); ?></h2>
    <?php endif;
  endif; ?>

  <div class="row g-4">
    <?php if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
      $img = dv_get_first_image_url( get_the_ID() );
    ?>
      <div class="col-12 col-md-6">
        <article class="dv-card">
          <a href="<?php the_permalink(); ?>" class="d-block">
            <img class="dv-card__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
          </a>
          <div class="dv-card__body">
            <h3 class="h5 m-0">
              <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                <?php the_title(); ?>
              </a>
            </h3>
            <div class="text-muted">
              <?php
                $desc = get_the_excerpt();
                if ( ! $desc ) $desc = wp_strip_all_tags( get_the_content() );
                echo esc_html( wp_trim_words( $desc, 28, '…' ) );
              ?>
            </div>
            <div>
              <a class="fw-semibold" href="<?php the_permalink(); ?>">XEM CHI TIẾT »</a>
            </div>
          </div>
        </article>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12">
        <p>Chưa có bài viết nào trong mục này.</p>
      </div>
    <?php endif; wp_reset_postdata(); ?>
  </div>

  <?php
  // Phân trang, giữ lại tham số ?cat hiện tại
  $big = 999999999;
  $paginate = paginate_links( [
    'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format'    => '?paged=%#%',
    'current'   => $paged,
    'total'     => $q->max_num_pages,
    'type'      => 'list',
    'mid_size'  => 2,
    'prev_text' => '«',
    'next_text' => '»',
    'add_args'  => $cat_slug ? [ 'cat' => $cat_slug ] : [],
  ] );
  if ( $paginate ) {
    echo '<div class="mt-4">'.$paginate.'</div>';
  }
  ?>
</div>

<?php get_footer(); ?>

<?php
get_footer();
?>
