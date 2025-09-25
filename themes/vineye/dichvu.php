<?php
/**
 * Template Name: Services
 */
get_header();

/**
 * Helper: Get the first image URL of a post
 * - Prefer featured image (thumbnail)
 * - Else: scan the first <img> in content
 * - Else: find the first image attachment
 * - Else: return a fallback image (customizable)
 */
if ( ! function_exists('dv_get_first_image_url') ) :
function dv_get_first_image_url( $post_id, $fallback = '' ) {
  // 1) Thumbnail
  $thumb = get_the_post_thumbnail_url( $post_id, 'large' );
  if ( $thumb ) return $thumb;

  // 2) First <img> in content
  $content = get_post_field( 'post_content', $post_id );
  if ( $content && preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $m ) ) {
    return esc_url( $m[1] );
  }

  // 3) First image attachment
  $attachments = get_children( [
    'post_parent'    => $post_id,
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'numberposts'    => 1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
  ] );
  if ( $attachments ) {
    $att = array_shift( $attachments );
    $src = wp_get_attachment_image_src( $att->ID, 'large' );
    if ( ! empty( $src[0] ) ) return esc_url( $src[0] );
  }

  // 4) Fallback image
  if ( ! $fallback ) {
    $fallback = get_stylesheet_directory_uri() . '/assets/images/placeholder-16x9.jpg';
  }
  return esc_url( $fallback );
}
endif;

// Text domain for translations
$td = 'vineye';

// Get category slug from URL (?cat=slug). Or use ID: ?cat_id=123
$cat_slug = isset($_GET['cat']) ? sanitize_title( wp_unslash($_GET['cat']) ) : '';
$cat_id   = 0;
if ( $cat_slug ) {
  $term = get_category_by_slug( $cat_slug );
  if ( $term ) $cat_id = (int) $term->term_id;
} elseif ( isset($_GET['cat_id']) ) {
  $cat_id = absint( $_GET['cat_id'] );
}

// Pagination
$paged = max( 1, get_query_var('paged'), get_query_var('page') );

// Query posts by category (if any)
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

// Build category list for filter (optionally: only children of a root category)
$root_cat_slug = ''; // e.g. 'dich-vu' to show only its child categories
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

// Current page URL to attach ?cat=
$current_page_url = get_permalink( get_queried_object_id() );
?>

<style>
/* Light card layout */
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
        <!-- “All” button -->
        <a href="<?php echo esc_url( remove_query_arg( ['cat','cat_id'], $current_page_url ) ); ?>"
            class="<?php echo $cat_id ? '' : 'active'; ?>">
            <?php esc_html_e( 'All', $td ); ?>
        </a>
        <?php foreach ( $cats as $c ) :
        $url    = add_query_arg( [ 'cat' => $c->slug ], $current_page_url );
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
                    <img class="dv-card__img" src="<?php echo esc_url( $img ); ?>"
                        alt="<?php echo esc_attr( get_the_title() ); ?>">
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
                        <a class="fw-semibold" href="<?php the_permalink(); ?>">
                            <?php esc_html_e( 'View details', $td ); ?> »
                        </a>
                    </div>
                </div>
            </article>
        </div>
        <?php endwhile; else: ?>
        <div class="col-12">
            <p><?php esc_html_e( 'There are no posts in this category yet.', $td ); ?></p>
        </div>
        <?php endif; wp_reset_postdata(); ?>
    </div>

    <?php
  // Pagination, keep current ?cat
  $big = 999999999;
  $paginate = paginate_links( [
    'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format'    => '?paged=%#%',
    'current'   => $paged,
    'total'     => $q->max_num_pages,
    'type'      => 'list',
    'mid_size'  => 2,
    'prev_text' => __( '«', $td ),
    'next_text' => __( '»', $td ),
    'add_args'  => $cat_slug ? [ 'cat' => $cat_slug ] : [],
  ] );
  if ( $paginate ) {
    echo '<div class="mt-4">'.$paginate.'</div>';
  }
  ?>
</div>

<?php get_footer(); ?>