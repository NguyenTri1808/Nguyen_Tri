<?php
/** 
 * Template Name: Footer Template
 */
get_header();
?>
<footer class="site-footer">
<?php if (function_exists('get_field')):

  $SID = ve_footer_settings_id();

  // ======================= CTA (GROUP: dangky) =======================
  $cta = get_field('dangky', $SID);
  if (!is_array($cta)) $cta = [];

  $cta_bg_url = ve_img_url($cta['cta_bg_image'] ?? '');
  $cta_op     = (float)($cta['cta_overlay_opacity'] ?? 0.9);
  $cta_title  = $cta['cta_title'] ?? '';
  $cta_bul    = $cta['cta_bullets_text'] ?? ($cta['text'] ?? ''); // textarea: mỗi dòng 1 bullet
  $cta_sc_raw = $cta['cta_form_shortcode'] ?? '';
  if (is_array($cta_sc_raw)) $cta_sc_raw = reset($cta_sc_raw);
  $cta_sc     = is_string($cta_sc_raw) ? trim($cta_sc_raw) : '';

  // =================== INFO (GROUP: thong_tin_benh_vien) ===================
  $info = get_field('thong_tin_benh_vien', $SID);
  if (!is_array($info)) $info = [];

  $info_bg_url = ve_img_url($info['info_bg_image'] ?? '');
  $info_op     = (float)($info['info_overlay_opacity'] ?? 0.8);
  $logo_url    = ve_img_url($info['info_logo'] ?? '');

  $heading  = $info['info_heading']   ?? '';
  $tagline  = $info['info_tagline']   ?? '';
  $hl       = $info['info_highlight'] ?? '';
  $hotline  = preg_replace('/\s+/', ' ', trim($info['info_hotline'] ?? ''));

  // cột phải (bạn đang dùng 3 field riêng)
  $addr     = $info['info_address'] ?? '';
  $time     = $info['info_time']    ?? '';
  $company  = $info['company_text'] ?? '';

  // DEBUG nhỏ (chỉ admin thấy)
  if (current_user_can('manage_options')) {
    echo "<!-- Footer DEBUG: SID={$SID}; cta_title=".esc_html($cta_title ?: '(empty)')."; info_heading=".esc_html($heading ?: '(empty)')." -->";
  }
?>

  <!-- ===== CTA SECTION ===== -->
  <section class="footer-cta has-bg"
           style="--bg:url('<?php echo esc_url($cta_bg_url); ?>'); --ov: <?php echo $cta_op; ?>;">
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-12 col-lg-6">
          <?php if ($cta_title): ?>
            <h3 class="cta-title"><?php echo esc_html($cta_title); ?></h3>
          <?php endif; ?>

          <?php
            $lines = preg_split('/\r\n|\r|\n/', (string)$cta_bul);
            $lines = array_filter(array_map('trim', (array)$lines));
            if ($lines):
              echo '<ul class="cta-benefits">';
              foreach ($lines as $line) echo '<li>'. esc_html($line) .'</li>';
              echo '</ul>';
            endif;
          ?>
        </div>

        <div class="col-12 col-lg-6">
          <div class="cta-form card-like">
            <?php
              if ($cta_sc) {
                echo do_shortcode($cta_sc);
              } else {
                echo '<p class="text-light small m-0">Dán shortcode form vào field <code>cta_form_shortcode</code> (trong nhóm <code>dangky</code> của trang Footer Settings).</p>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== INFO SECTION ===== -->
  <section class="footer-info has-bg"
           style="--bg:url('<?php echo esc_url($info_bg_url); ?>'); --ov: <?php echo $info_op; ?>;">
    <div class="container">
      <div class="row gy-4">
        <div class="col-12">
          <div class="info-top d-flex align-items-center gap-3">
            <?php if ($logo_url): ?>
              <img class="info-logo" src="<?php echo esc_url($logo_url); ?>" alt="Logo">
            <?php endif; ?>
            <div class="flex-grow-1"><hr class="info-hr m-0"></div>
          </div>
        </div>

        <div class="col-12 col-lg-7">
          <?php if ($heading):  ?><h3 class="info-heading"><?php echo esc_html($heading); ?></h3><?php endif; ?>
          <?php if ($tagline):  ?><p class="info-tagline"><?php echo esc_html($tagline); ?></p><?php endif; ?>
          <?php if ($hl):       ?><p class="info-highlight"><?php echo esc_html($hl); ?></p><?php endif; ?>
          <?php if ($hotline):  ?>
            <a class="hotline-btn" href="tel:<?php echo esc_attr(str_replace(' ','',$hotline)); ?>">
              <svg width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.11.37 2.31.57 3.58.57a1 1 0 011 1V21a1 1 0 01-1 1C10.85 22 2 13.15 2 2a1 1 0 011-1h3.5a1 1 0 011 1c0 1.27.2 2.47.57 3.58a1 1 0 01-.24 1.01l-2.2 2.2z"/></svg>
              <span><?php echo esc_html($hotline); ?></span>
            </a>
          <?php endif; ?>
        </div>

        <div class="col-12 col-lg-5">
          <ul class="info-list">
            <?php if ($addr): ?>
              <li class="info-item">
                <span class="info-icon"><svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7zm0 9.5A2.5 2.5 0 119.5 9 2.5 2.5 0 0112 11.5z"/></svg></span>
                <div class="info-text"><strong>Địa chỉ:</strong><span><?php echo nl2br(esc_html($addr)); ?></span></div>
              </li>
            <?php endif; ?>
            <?php if ($hotline): ?>
              <li class="info-item">
                <span class="info-icon"><svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.11.37 2.31.57 3.58.57a1 1 0 011 1V21a1 1 0 01-1 1C10.85 22 2 13.15 2 2a1 1 0 011-1h3.5a1 1 0 011 1c0 1.27.2 2.47.57 3.58a1 1 0 01-.24 1.01l-2.2 2.2z"/></svg></span>
                <div class="info-text"><strong>Hotline:</strong><span><?php echo esc_html($hotline); ?></span></div>
              </li>
            <?php endif; ?>
            <?php if ($time): ?>
              <li class="info-item">
                <span class="info-icon"><svg width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 1a11 11 0 1011 11A11 11 0 0012 1zm1 11H7V10h5V5h2z"/></svg></span>
                <div class="info-text"><strong>Giờ làm việc:</strong><span><?php echo nl2br(esc_html($time)); ?></span></div>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <?php if ($company): ?>
          <div class="col-12">
            <p class="company-text"><?php echo nl2br(esc_html($company)); ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

<?php endif; ?>
<?php wp_footer(); ?>
</footer>
</body></html>
