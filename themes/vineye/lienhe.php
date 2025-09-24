<?php
/**
 * Template Name: Contact – Simple (ACF)
 */
get_header();
?>
<!--  BACKGROUND -->
<div class="bg-contact">
    <?php
        // Lấy group "lien_he" của trang hiện tại
        $pid     = get_queried_object_id();                  // ID trang hiện tại
        $lien_he = get_field('lien_he', $pid);               // => mảng các field con

        // Lấy group con "bg_content"
        $bg      = is_array($lien_he['bg_content'] ?? null) ? $lien_he['bg_content'] : [];

        // Lấy 2 field trong bg_content
        $bg_title   = $bg['title']   ?? '';
        $bg_content = $bg['content'] ?? '';

        // Render (tuỳ biến HTML theo ý bạn)
        if ($bg_title || $bg_content): ?>
    <section class="bg-content-block py-5">
        <div class="container">
            <?php if ($bg_title): ?>
            <h1 class="display-5 fw-bold text-white mb-3">
                <?php echo esc_html($bg_title); ?>
            </h1>
            <?php endif; ?>

            <?php if ($bg_content): ?>
            <div class="lead text-white-50">
                <?php echo wpautop( wp_kses_post($bg_content) ); ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

</div>
<?php
$lien_he = get_field('lien_he') ?: [];

$left    = is_array($lien_he['left_content'] ?? null) ? $lien_he['left_content'] : [];
$title   = $left['title'] ?? '';
$content = $left['content'] ?? '';

$map_title = $lien_he['map_title'] ?? 'GOOGLE MAPS';
$lat = isset($lien_he['map_lat']) ? (float)$lien_he['map_lat'] : 0;
$lng = isset($lien_he['map_lng']) ? (float)$lien_he['map_lng'] : 0;
$zoom = isset($lien_he['map_zoom']) ? (int)$lien_he['map_zoom'] : 16;

// (fallback) nếu còn field 'map' cũ là chuỗi iframe thì vẫn cho hiển thị
$map_iframe_raw = is_string($lien_he['map'] ?? null) ? $lien_he['map'] : '';

function ve_gmap_embed_src($lat, $lng, $zoom = 16, $lang = 'vi') {
  $lat = (float)$lat; $lng = (float)$lng; $zoom = (int)$zoom;
  if ($zoom < 1 || $zoom > 21) $zoom = 16;
  return sprintf('https://www.google.com/maps?q=%F,%F&z=%d&hl=%s&output=embed', $lat, $lng, $zoom, rawurlencode($lang));
}
?>

<section class="contact-section py-5">
    <div class="container">
        <div class="row g-5 align-items-start">
            <!-- Left -->
            <div class="col-12 col-lg-6">
                <?php if ($title): ?>
                <h2 class="h2 fw-bold text-uppercase mb-4"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php if ($content): ?>
                <div class="contact-left-content">
                    <?php echo wpautop( wp_kses_post($content) ); ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Map -->
            <div class="col-12 col-lg-6">
                <h2 class="h2 fw-bold text-uppercase mb-4"><?php echo esc_html($map_title); ?></h2>
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                    <?php
          if ($lat && $lng) {
            $src = ve_gmap_embed_src($lat, $lng, $zoom, 'en');
            echo '<iframe src="'.esc_url($src).'" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
          } elseif ($map_iframe_raw && stripos($map_iframe_raw, '<iframe') !== false) {
            echo wp_kses($map_iframe_raw, [
              'iframe'=>['src'=>true,'width'=>true,'height'=>true,'style'=>true,'class'=>true,'loading'=>true,'referrerpolicy'=>true,'allowfullscreen'=>true]
            ]);
          } else {
            echo '<div class="d-flex align-items-center justify-content-center bg-light text-muted">Nhập <code>map_lat</code> &amp; <code>map_lng</code> (hoặc dán iframe vào field cũ <code>map</code>).</div>';
          }
          ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>