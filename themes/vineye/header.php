<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="custom.css">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists('wp_body_open') ) wp_body_open(); ?>

<header id="site-header" class="site-header bg-white">
  <!-- Top bar: Call | Logo | Search + Toggler (trôi khi cuộn) -->
  <div class="topbar py-3">
    <div class="container">
      <div class="row align-items-center">
        <!-- Call button -->
        <div class="col-6 col-md-3">
          <a class="btn btn-outline-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 btn-call" href="tel:19009140">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.09 4.2 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.3 1.77.54 2.6a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.48-1.14a2 2 0 0 1 2.11-.45c.83.24 1.7.42 2.6.54A2 2 0 0 1 22 16.92z" fill="currentColor"/></svg>
            <span class="fw-semibold">1900 9140</span>
          </a>
        </div>

        <!-- Logo center -->
        <div class="col-12 col-md-6 text-center my-2 my-md-0">
          <a class="navbar-brand m-0 p-0 d-inline-block" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if (has_custom_logo()) { the_custom_logo(); } else { bloginfo('name'); } ?>
          </a>
        </div>

        <!-- Search + Hamburger -->
        <div class="col-6 col-md-3 d-flex justify-content-end">
          <div class="header-tools w-100 d-flex align-items-center gap-3">
            <!-- Search -->
            <form class="header-search flex-grow-1" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
              <div class="search-wrap position-relative">
                <input class="form-control border-0 border-bottom rounded-0 shadow-none ps-0 pe-5"
                       type="search" name="s" placeholder="<?php esc_attr_e('Tìm kiếm...', 'vineye'); ?>" aria-label="<?php esc_attr_e('Tìm kiếm', 'vineye'); ?>">
                <button class="btn position-absolute end-0 top-50 translate-middle-y p-0 bg-transparent border-0" type="submit" aria-label="<?php esc_attr_e('Tìm', 'vineye'); ?>">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 20L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </form>

            <!-- Hamburger (ẩn desktop) -->
            <!-- Toggler -->
<button class="navbar-toggler d-lg-none ms-auto"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-controls="mainNav"
        aria-expanded="false"
        aria-label="<?php esc_attr_e('Mở menu', 'vineye'); ?>">
  <span class="toggler-icon on"><i class="fa fa-bars" aria-hidden="true"></i></span>
  <span class="toggler-icon off d-none"><i class="fa fa-times" aria-hidden="true"></i></span>
</button>

<!-- Menu -->
<div id="mainNav" class="collapse navbar-collapse">
  <?php /* wp_nav_menu(...) */ ?>
</div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Navbar: luôn dính trên cùng khi cuộn -->
  <nav id="site-nav" class="navbar navbar-expand-lg border-top sticky-top bg-white">
    <div class="container">
      <div id="mainNav" class="collapse navbar-collapse justify-content-center">
        <?php
          wp_nav_menu([
            'theme_location' => 'primary',
            'depth'          => 2,
            'container'      => false,
            'fallback_cb'    => false,
            'items_wrap'     => '<ul id="%1$s" class="navbar-nav mx-auto text-uppercase fw-bold gap-lg-4">%3$s</ul>',
          ]);
        ?>
      </div>
    </div>
  </nav>
</header>
<script>
(function () {
  const nav      = document.getElementById('mainNav');
  const toggler  = document.querySelector('[data-bs-target="#mainNav"]');
  if (!nav || !toggler) return;

  const bsColl   = bootstrap.Collapse.getOrCreateInstance(nav, { toggle: false });
  const iconOn   = toggler.querySelector('.toggler-icon.on');   // bars
  const iconOff  = toggler.querySelector('.toggler-icon.off');  // close

  // Đổi icon khi mở/đóng
  nav.addEventListener('show.bs.collapse', () => {
    iconOn.classList.add('d-none');
    iconOff.classList.remove('d-none');
    toggler.setAttribute('aria-expanded','true');
  });
  nav.addEventListener('hide.bs.collapse', () => {
    iconOn.classList.remove('d-none');
    iconOff.classList.add('d-none');
    toggler.setAttribute('aria-expanded','false');
  });

  // Click vào link menu trên mobile thì auto đóng
  document.querySelectorAll('#mainNav .nav-link').forEach(a => {
    a.addEventListener('click', () => {
      // chỉ đóng khi toggler đang hiển thị (mobile)
      if (getComputedStyle(toggler).display !== 'none') {
        bsColl.hide();
      }
    });
  });
})();
</script>

