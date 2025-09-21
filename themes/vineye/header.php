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

    <header id="site-header" class="site-header bg-white sticky-top">

        <!-- Thanh header 1 hàng (desktop) + giữ mobile cũ -->
        <div class="topbar border-bottom">
            <div class="container">
                <div class="row">
                    <div class="d-flex align-items-center">

                        <!-- LOGO TRÁI -->
                        <div class="col-md-2">
                            <a class="navbar-brand m-0 p-0 d-inline-block" href="<?php echo esc_url(home_url('/')); ?>">
                                <?php if (has_custom_logo()) { the_custom_logo(); } else { bloginfo('name'); } ?>
                            </a>
                        </div>

                        <!-- MENU GIỮA (CHỈ HIỂN THỊ TRÊN LG+) -->
                        <div class="col-md-8">
                            <nav class="flex-grow-1 d-none d-lg-block">
                                <?php
                                wp_nav_menu([
                                'theme_location' => 'primary',
                                'container'      => false,
                                'fallback_cb'    => false,
                                'depth'          => 2,
                                'items_wrap'     => '<ul id="%1$s" class="navbar-nav mainmenu d-lg-flex flex-lg-row justify-content-lg-center gap-lg-4 text-uppercase fw-bold mb-0">%3$s</ul>',
                                ]);
                            ?>
                            </nav>
                        </div>

                        <!-- TÌM KIẾM + HOTLINE + HAMBURGER (PHẦN PHẢI) -->
                        <div
                            class="col-md-2 ms-auto d-flex align-items-center justify-content-center gap-3 header-tools">


                            <!-- Nút mở menu mobile (giữ nguyên hành vi JS của bạn) -->
                            <button class="mobile-nav-toggle d-lg-none ms-auto me-0" type="button"
                                aria-controls="mobileNav" aria-expanded="false"
                                aria-label="<?php esc_attr_e('Mở menu','vineye'); ?>">
                                <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                            </button>

                            <!-- Hotline DESKTOP -->
                            <a class="btn btn-outline-primary rounded-pill px-3 d-none d-lg-inline-flex align-items-center gap-2 btn-call"
                                href="tel:19009140">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.09 4.2 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.3 1.77.54 2.6a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.48-1.14a2 2 0 0 1 2.11-.45c.83.24 1.7.42 2.6.54A2 2 0 0 1 22 16.92z"
                                        fill="currentColor" />
                                </svg>
                                <span class="fw-semibold">1900 9140</span>
                            </a>




                            <!-- overlay + off-canvas + menu mobile của bạn (GIỮ NGUYÊN) -->
                            <div id="navOverlay" class="nav-overlay" hidden></div>
                            <aside id="mobileNav" class="mobile-nav" aria-hidden="true" tabindex="-1">
                                <div class="mobile-nav__head">
                                    <button class="mobile-nav__close" type="button"
                                        aria-label="<?php esc_attr_e('Đóng menu','vineye'); ?>">&times;</button>
                                </div>
                                <div class="mobile-nav__search">
                                    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                                        <div class="search-wrap position-relative">
                                            <input class="form-control" type="search" name="s"
                                                placeholder="<?php esc_attr_e('Tìm kiếm...', 'vineye'); ?>">
                                            <button class="btn btn-search" type="submit"
                                                aria-label="<?php esc_attr_e('Tìm', 'vineye'); ?>">🔍</button>
                                        </div>
                                    </form>
                                </div>
                                <nav class="mobile-nav__menu" role="navigation"
                                    aria-label="<?php esc_attr_e('Mobile Menu','vineye'); ?>">
                                    <?php
                wp_nav_menu([
                  'theme_location' => 'primary',
                  'container'      => false,
                  'fallback_cb'    => false,
                  'menu_class'     => 'mobile-menu',
                  'depth'          => 3,
                ]);
              ?>
                                </nav>
                                <div class="mobile-nav__footer">
                                    <a class="btn-hotline" href="tel:19009140">1900 9140</a>
                                </div>
                            </aside>

                        </div><!-- /.header-tools -->
                    </div>
                </div>
            </div>

            <!-- Ẩn navbar cũ để tránh trùng menu (nếu muốn có thể xóa hẳn) -->
            <nav id="site-nav" class="navbar navbar-expand-lg border-top bg-white d-none">
                <div class="container">
                    <div id="mainNav" class="collapse navbar-collapse justify-content-center"></div>
                </div>
            </nav>
    </header>


    <!-- ===============scrip mobile============= -->
    <script>
    (function() {
        const qs = (s, r = document) => r.querySelector(s);
        const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

        const toggleBtn = qs('.mobile-nav-toggle');
        const panel = qs('#mobileNav');
        const overlay = qs('#navOverlay');
        const menuRoot = qs('#mobileNav .mobile-menu');

        if (!toggleBtn || !panel || !overlay || !menuRoot) return;

        const openClass = 'nav-open';

        function openNav() {
            document.body.classList.add(openClass);
            overlay.hidden = false;
            panel.setAttribute('aria-hidden', 'false');
            toggleBtn.setAttribute('aria-expanded', 'true');
            // focus input search
            const sf = panel.querySelector('input[type="search"], input[type="text"]');
            if (sf) setTimeout(() => sf.focus(), 120);
        }

        function closeNav() {
            document.body.classList.remove(openClass);
            panel.setAttribute('aria-hidden', 'true');
            toggleBtn.setAttribute('aria-expanded', 'false');
            setTimeout(() => {
                overlay.hidden = true;
            }, 280); // đợi animate
            toggleBtn.focus();
        }

        // Tạo nút mũi tên cho item có submenu
        qsa('.menu-item-has-children', menuRoot).forEach(li => {
            if (!li.querySelector('.submenu-toggle')) {
                const btn = document.createElement('button');
                btn.className = 'submenu-toggle';
                btn.setAttribute('aria-expanded', 'false');
                btn.setAttribute('aria-label', 'Mở menu con');
                const a = li.querySelector('a');
                if (a && a.parentNode) a.parentNode.insertBefore(btn, a.nextSibling);
            }
            const sub = li.querySelector('.sub-menu');
            if (sub) sub.style.display = 'none';
        });

        // Sự kiện mở/đóng panel
        toggleBtn.addEventListener('click', openNav);
        qs('.mobile-nav__close', panel).addEventListener('click', closeNav);
        overlay.addEventListener('click', closeNav);
        document.addEventListener('keydown', e => {
            if ((e.key || '').toLowerCase() === 'escape' && document.body.classList.contains(openClass))
                closeNav();
        });

        // Toggle submenu (uỷ quyền click)
        menuRoot.addEventListener('click', e => {
            const btn = e.target.closest('.submenu-toggle');
            if (!btn) return;
            const li = btn.closest('li');
            const sub = li && li.querySelector('.sub-menu');
            if (!sub) return;
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!expanded));
            sub.style.display = expanded ? 'none' : 'block';
        });
    })();
    </script>