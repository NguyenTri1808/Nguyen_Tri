<?php
/** 
 * Template Name: Trang Chu
 */
get_header();
?>

<?php
// Lấy group banners (ACF Free: return mảng các subfields)
$group = get_field('banners');   // tên field group = banners

if ($group && is_array($group)) {
  // Gom các ảnh có key image1..imageN (không cần biết trước N)
  $images = [];
  foreach ($group as $key => $val) {
    if (preg_match('/^image(\d+)$/', $key, $m)) {
      // Chuẩn hóa về URL cho đủ mọi format ACF (url/id/array)
      $url = '';
      if (is_array($val)) {
        // Nếu subfield là Image (array)
        $url = $val['url'] ?? ($val['sizes']['1536x1536'] ?? $val['sizes']['large'] ?? $val['url'] ?? '');
        if (!$url && !empty($val['ID'])) {
          $url = wp_get_attachment_image_url((int)$val['ID'], 'full');
        }
      } elseif (is_numeric($val)) {
        // Nếu subfield trả về ID
        $url = wp_get_attachment_image_url((int)$val, 'full');
      } else {
        // Nếu subfield trả về URL
        $url = $val;
      }
      if ($url) $images[(int)$m[1]] = esc_url($url);
    }
  }
  ksort($images); // sắp xếp theo số thứ tự

  if (!empty($images)) : ?>
<section class="hero-slider position-relative">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover">
        <div class="carousel-inner">
            <?php $i=0; foreach ($images as $idx => $src): $i++; ?>
            <div class="carousel-item <?php echo $i===1 ? 'active' : ''; ?>">
                <img src="<?php echo $src; ?>" class="d-block w-100 hero-img" alt="banner <?php echo (int)$idx; ?>"
                    loading="<?php echo $i===1 ? 'eager':'lazy'; ?>"
                    fetchpriority="<?php echo $i===1 ? 'high':'auto'; ?>">
                <div class="hero-overlay"></div>
                <!-- Nếu muốn caption, thêm HTML vào đây -->
                <!--
              <div class="container">
                <div class="hero-caption text-white">
                  <h2 class="display-6 fw-bold">Tiêu đề</h2>
                  <p class="lead">Mô tả</p>
                </div>
              </div>
              -->
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Nút điều hướng -->
        <button class="carousel-control-prev hero-control" type="button" data-bs-target="#heroCarousel"
            data-bs-slide="prev" aria-label="Prev">
            <span class="hero-arrow">&lsaquo;</span>
        </button>
        <button class="carousel-control-next hero-control" type="button" data-bs-target="#heroCarousel"
            data-bs-slide="next" aria-label="Next">
            <span class="hero-arrow">&rsaquo;</span>
        </button>
    </div>
</section>
<?php endif;
}
?>


<div class="container">
    <div class="row">
        <?php
        // ====== THÊM: Hàm chuẩn hoá SVG ======
        if (!function_exists('normalize_svg')) {
        function normalize_svg($svg){
            if (strpos($svg, '<svg') === false) return $svg;

            // Bỏ width/height inline để có thể scale bằng CSS
            $svg = preg_replace('/\s(width|height)\s*=\s*"[^"]*"/i', '', $svg);

            // Thêm class="svg-icon" (nếu chưa có)
            if (!preg_match('/<svg[^>]*class=/i', $svg)) {
            $svg = preg_replace('/<svg/i', '<svg class="svg-icon"', $svg, 1);
            } else {
            $svg = preg_replace('/(<svg[^>]*class=")([^"]*)"/i', '$1$2 svg-icon"', $svg, 1);
            }

            // Thêm preserveAspectRatio nếu thiếu
            if (!preg_match('/preserveAspectRatio=/i', $svg)) {
            $svg = preg_replace('/<svg/i', '<svg preserveAspectRatio="xMidYMid meet"', $svg, 1);
            }

            // (Tuỳ chọn) Cho phép đổi màu bằng currentColor nếu SVG không set ở <path>
            if (!preg_match('/<svg[^>]*\sfill=/i', $svg)) {
            $svg = preg_replace('/<svg/i', '<svg fill="currentColor"', $svg, 1);
            }

            return $svg;
        }
        }

        // ====== PHẦN CODE CỦA BẠN – ĐÃ SỬA CHỖ TẠO $icon_html ======
        $content1 = get_field('content1');
        $blocks   = [];
        foreach (['bn1','bn2','bn3'] as $k) {
        if (!empty($content1[$k])) $blocks[] = $content1[$k];
        }

        if ($blocks):
        ?>
        <!-- Swiper CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

        <div id="swiper-container" class="swiper-container">
            <div class="swiper bv-features-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($blocks as $b):
                $title = $b['title'] ?? '';
                $desc  = $b['desc']  ?? '';
                $icon  = $b['icon']  ?? '';

                // === CHỖ NÀY ĐÃ SỬA: CHUẨN HOÁ SVG TRƯỚC KHI ECHO ===
                $icon_html = '';
                if (is_array($icon)) {
                // ACF image array
                $src = $icon['url'] ?? '';
                if ($src) $icon_html = '<img src="'.esc_url($src).'" alt="" class="fi-img">';
                } else {
                if (filter_var($icon, FILTER_VALIDATE_URL)) {
                    // URL ảnh / SVG file
                    $icon_html = '<img src="'.esc_url($icon).'" alt="" class="fi-img">';
                } else {
                    // SVG thô -> chuẩn hoá
                    $icon_html = normalize_svg((string)$icon);
                }
                }
            ?>
                    <div class="swiper-slide">
                        <div class="card feature-card h-100 shadow-sm border-0">
                            <div class="card-body p-4 text-center d-flex flex-column">
                                <?php if ($icon_html): ?>
                                <div class="feature-icon mx-auto mb-3">
                                    <?php
                    echo wp_kses($icon_html, [
                        'svg'  => [
                        'class'=>true,'xmlns'=>true,'viewBox'=>true,'preserveAspectRatio'=>true,
                        'fill'=>true,'role'=>true,'aria-hidden'=>true
                        ],
                        'path' => [
                        'd'=>true,'fill'=>true,'fill-rule'=>true,'clip-rule'=>true,
                        'stroke'=>true,'stroke-width'=>true,'stroke-linecap'=>true,'stroke-linejoin'=>true
                        ],
                        'g'    => ['fill'=>true,'stroke'=>true,'stroke-width'=>true,'clip-path'=>true],
                        'img'  => ['src'=>true,'alt'=>true,'width'=>true,'height'=>true,'loading'=>true,'decoding'=>true,'class'=>true]
                    ]);
                    ?>
                                </div>
                                <?php endif; ?>

                                <?php if ($title): ?>
                                <h3 class="h5 fw-bold text-primary mb-3"><?php echo esc_html($title); ?></h3>
                                <?php endif; ?>

                                <?php if ($desc): ?>
                                <div class="text-secondary lh-lg flex-grow-1">
                                    <?php echo wpautop(wp_kses_post($desc)); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Nút điều hướng -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination d-lg-none"></div>
            </div>
        </div>

        <!-- Swiper JS -->
        <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
        <script>
        new Swiper('.bv-features-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            grabCursor: true,
            loop: false,
            navigation: {
                nextEl: '.bv-features-swiper .swiper-button-next',
                prevEl: '.bv-features-swiper .swiper-button-prev',
            },
            pagination: {
                el: '.bv-features-swiper .swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 24
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 28
                }
            }
        });
        </script>
        <?php endif; ?>



        <!-- Báo Chí Nói Gì Về Chúng Tôi -->
        <section class="partner-logos">
            <?php
                // LẤY GROUP CHA "baochi" (phòng thủ kiểu dữ liệu)
                $bc_raw = get_field('baochi');
                $bc     = is_array($bc_raw) ? $bc_raw : [];

                // LẤY GROUP CON "title" (nếu không có thì để mảng rỗng)
                $title  = (isset($bc['title']) && is_array($bc['title'])) ? $bc['title'] : [];

                // 2 field con: title1, title2 (text)
                $bc_t1  = isset($title['title1']) ? wp_strip_all_tags($title['title1']) : '';
                $bc_t2  = isset($title['title2']) ? wp_strip_all_tags($title['title2']) : '';
                ?>
            <div class="title-baochi">
                <?php if ($bc_t1 !== ''): ?>
                <h2 id="h2-baochi-home"><?php echo esc_html($bc_t1); ?></h2>
                <?php endif; ?>
                <?php if ($bc_t2 !== ''): ?>
                <h2 id="h2-baochi-home2"><?php echo esc_html($bc_t2); ?></h2>
                <?php endif; ?>
            </div>


            <div class="logo-container row">
                <?php
                $baochi = get_field('baochi');

                if ($baochi && is_array($baochi)) {
                    foreach ($baochi as $field_name => $image) {
                        // BỎ QUA NHÓM TIÊU ĐỀ MỚI THÊM
                        if ($field_name === 'title') {
                            continue;
                        }

                        // XỬ LÝ CHỈ KHI GIỐNG ẢNH
                        $src = '';

                        // ACF Image (array có 'url')
                        if (is_array($image) && isset($image['url'])) {
                            $src = esc_url($image['url']);
                        }
                        // ACF Image (ID)
                        elseif (is_numeric($image)) {
                            $src = esc_url(wp_get_attachment_url($image));
                        }
                        // URL chuỗi
                        elseif (is_string($image) && $image !== '') {
                            // đảm bảo là URL hợp lệ
                            if (filter_var($image, FILTER_VALIDATE_URL)) {
                                $src = esc_url($image);
                            }
                        }

                        if ($src) {
                            echo '<div class="col-md-2">
                                    <div class="logo-item">
                                        <a href="https://alobacsi.com/trung-tam-mat-quoc-te-vin-eye-dong-hanh-giai-phong-tam-nhin-cho-hang-trieu-nguoi.html?gidzl=QeEnU7oSAYbdch1xUOCiA6ETj7XZZNmjBiFWVJNQSYrzaRegP8acV7YMkdeqZN0eBfFlVJKkAAjbTfWY90">
                                        <img loading="lazy" decoding="async" style="margin-top:20px" width="200" height="92" src="' . $src . '" alt="Logo"/>
                                        </a>
                                    </div>
                                    </div>';
                        }
                    }
                }
                ?>
            </div>

        </section>

        <!-- BÁC SĨ & CHUYÊN GIA MẮT -->
        <section class="doctors-section py-5">
            <div class="container">

                <?php
                // LẤY GROUP CHA "nhanvien"
                $nv_raw = get_field('nhanvien');
                $nv     = is_array($nv_raw) ? $nv_raw : [];

                // --- TIÊU ĐỀ & MÔ TẢ ---
                $nv_title   = (isset($nv['title']) && is_array($nv['title'])) ? $nv['title'] : [];
                $nv_t1      = isset($nv_title['title1']) ? wp_strip_all_tags($nv_title['title1']) : '';
                $nv_t2      = isset($nv_title['title2']) ? wp_strip_all_tags($nv_title['title2']) : '';
                $nv_content = isset($nv['content']) ? $nv['content'] : ''; // WYSIWYG/textarea
                ?>

                <div class="title-doctors text-center mb-4">
                    <?php if ($nv_t1 !== ''): ?>
                    <h2 id="h2-bacsi-home"><?php echo esc_html($nv_t1); ?></h2>
                    <?php endif; ?>
                    <?php if ($nv_t2 !== ''): ?>
                    <h2 id="h2-bacsi-home3"><?php echo esc_html($nv_t2); ?></h2>
                    <?php endif; ?>
                </div>

                <div class="text-doctors text-center mb-5">
                    <?php if (!empty($nv_content)): ?>
                    <p><?php echo wp_kses_post($nv_content); ?></p>
                    <?php endif; ?>
                </div>

                <div class="row doctors-container">
                    <?php
                // --- DANH SÁCH NHÂN VIÊN ---
                // Bạn có thể đang dùng repeater cố định (vd: 'list' hoặc 'items').
                // Đoạn dưới tự động:
                // 1) Nếu tồn tại $nv['list'] (hoặc 'items') thì dùng nó.
                // 2) Nếu không, fallback: dùng chính mảng $nv nhưng BỎ QUA 'title' và 'content',
                //    và chỉ giữ những row có 'image'/'desc-nhanvien'.

                $staff = [];
                if (isset($nv['list']) && is_array($nv['list'])) {
                    $staff = $nv['list'];
                } elseif (isset($nv['items']) && is_array($nv['items'])) {
                    $staff = $nv['items'];
                } elseif (!empty($nv)) {
                    // Fallback: lọc ra các phần tử giống cấu trúc cũ (image, desc-nhanvien)
                    $staff = array_filter($nv, function($row, $key) {
                    if ($key === 'title' || $key === 'content') return false;
                    return is_array($row) && (isset($row['image']) || isset($row['desc-nhanvien']));
                    }, ARRAY_FILTER_USE_BOTH);
                }

                if (!empty($staff)) :
                    foreach ($staff as $group) :
                    $image = !empty($group['image']) ? $group['image'] : '';
                    $desc  = !empty($group['desc-nhanvien']) ? $group['desc-nhanvien'] : '';

                    // Chuẩn hoá ảnh: ACF Image (array/ID/URL)
                    $img_src = '';
                    if (is_array($image) && isset($image['url'])) {
                        $img_src = esc_url($image['url']);
                    } elseif (is_numeric($image)) {
                        $img_src = esc_url(wp_get_attachment_url($image));
                    } elseif (is_string($image)) {
                        $img_src = esc_url($image);
                    }

                    if ($img_src || $desc) :
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card staff-card">
                            <div class="image-wrapper">
                                <?php if ($img_src): ?>
                                <img src="<?php echo $img_src; ?>" alt="" class="img-fluid staff-img">
                                <?php endif; ?>
                                <?php if ($desc): ?>
                                <div class="overlay">
                                    <p><?php echo nl2br(esc_html($desc)); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    endif;
                    endforeach;
                endif;
                ?>
                </div>
            </div>
        </section>


        <!-- DỊCH VỤ UY TÍN TẠI VIN EYE -->
        <section>
            <?php
            // LẤY GROUP CHA "dichvu"
            $dv_raw = get_field('dichvu');
            $dv     = is_array($dv_raw) ? $dv_raw : [];

            // --- TIÊU ĐỀ & MÔ TẢ ---
            $dv_title   = (isset($dv['title']) && is_array($dv['title'])) ? $dv['title'] : [];
            $dv_t1      = isset($dv_title['title1']) ? wp_strip_all_tags($dv_title['title1']) : '';
            $dv_t2      = isset($dv_title['title2']) ? wp_strip_all_tags($dv_title['title2']) : '';
            $dv_content = isset($dv['content']) ? $dv['content'] : ''; // WYSIWYG/textarea
            ?>
            <div class="title-doctors">
                <?php if ($dv_t1 !== ''): ?>
                <h2 id="h2-bacsi-home"><?php echo esc_html($dv_t1); ?></h2>
                <?php endif; ?>
                <?php if ($dv_t2 !== ''): ?>
                <h2 id="h2-bacsi-home3"><?php echo esc_html($dv_t2); ?></h2>
                <?php endif; ?>
            </div>

            <div class="text-doctors">
                <?php if (!empty($dv_content)): ?>
                <p><?php echo wp_kses_post($dv_content); ?></p>
                <?php endif; ?>
            </div>

            <div class="row dichvu-container">
                <?php
                // --- DANH SÁCH DỊCH VỤ ---
                // Nếu bạn có repeater rõ ràng (vd: $dv['list']), dùng nó:
                $items = [];
                if (isset($dv['list']) && is_array($dv['list'])) {
                $items = $dv['list'];
                } else {
                // Fallback: duyệt trực tiếp $dv nhưng BỎ QUA 'title', 'content'
                $items = array_filter($dv, function($row, $key) {
                    if ($key === 'title' || $key === 'content') return false;
                    return is_array($row) && (isset($row['name-dichvu']) || isset($row['image-dichvu']));
                }, ARRAY_FILTER_USE_BOTH);
                }

                if (!empty($items)) :
                foreach ($items as $group) :
                    $name  = isset($group['name-dichvu'])  ? $group['name-dichvu']  : '';
                    $image = isset($group['image-dichvu']) ? $group['image-dichvu'] : '';

                    // Chuẩn hoá ảnh: ACF Image (array/ID/URL)
                    $img_src = '';
                    if (is_array($image) && isset($image['url'])) {
                    $img_src = esc_url($image['url']);
                    } elseif (is_numeric($image)) {
                    $img_src = esc_url(wp_get_attachment_url($image));
                    } elseif (is_string($image)) {
                    $img_src = esc_url($image);
                    }

                    if ($name || $img_src) :
                ?>
                <div class="col-md-3 col-6 mb-4">
                    <div class="dichvu-box text-center">
                        <?php if ($img_src): ?>
                        <div class="dichvu-img">
                            <img src="<?php echo $img_src; ?>" alt="dich_vu" class="img-fluid">
                        </div>
                        <?php endif; ?>

                        <?php if ($name): ?>
                        <div class="dichvu-name">
                            <h5><?php echo esc_html($name); ?></h5>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                    endif;
                endforeach;
                endif;
                ?>
            </div>
        </section>


    </div>
</div>


<!-- CƠ SỞ VẬT CHẤT TẠI VIN EYE -->
<section class="facility-section py-5">
    <div class="container">
        <?php
        // LẤY GROUP CHA "vatchat"
        $vc_raw = get_field('vatchat');
        $vc     = is_array($vc_raw) ? $vc_raw : [];

        // LẤY TRỰC TIẾP 2 FIELD: title, content
        $vc_title   = isset($vc['title'])   ? wp_strip_all_tags($vc['title']) : '';
        $vc_content = isset($vc['content']) ? $vc['content']                  : '';
        ?>
        <!-- Tiêu đề -->
        <div id="text-center-vatchat" class="col medium-8 small-12 large-8 mx-auto text-center mb-4">
            <?php if ($vc_title !== ''): ?>
            <h3 class="facility-title"><?php echo esc_html($vc_title); ?></h3>
            <?php endif; ?>
            <?php if ($vc_content !== ''): ?>
            <p class="facility-subtitle"><?php echo wp_kses_post($vc_content); ?></p>
            <?php endif; ?>
        </div>

        <!-- Hình ảnh -->
        <div id="box-vatchat" class="row justify-content-center bg-white p-4 rounded shadow-sm mx-2">
            <?php
        // Nếu bạn có repeater rõ ràng (vd: $vc['list']), ưu tiên dùng:
        $items = [];
        if (isset($vc['list']) && is_array($vc['list'])) {
            $items = $vc['list'];
        } else {
            // Fallback: duyệt trực tiếp $vc, BỎ QUA 'title' & 'content'
            $items = array_filter($vc, function($row, $key) {
            if ($key === 'title' || $key === 'content') return false;
            return is_array($row) && !empty($row['image-vatchat']);
            }, ARRAY_FILTER_USE_BOTH);
        }

        if (!empty($items)) :
            foreach ($items as $group) :
            $image = $group['image-vatchat'] ?? '';

            // Chuẩn hoá ảnh: hỗ trợ ACF Image array/ID/URL
            $img_src = '';
            if (is_array($image) && isset($image['url'])) {
                $img_src = esc_url($image['url']);
            } elseif (is_numeric($image)) {
                $img_src = esc_url(wp_get_attachment_url($image));
            } elseif (is_string($image)) {
                $img_src = esc_url($image);
            }

            if ($img_src):
        ?>
            <div class="col-md-3 col-6">
                <div class="facility-item">
                    <img src="<?php echo $img_src; ?>" alt="Cơ sở vật chất" class="img-fluid rounded shadow"
                        id="img-vatchat-home">
                </div>
            </div>
            <?php
            endif;
            endforeach;
        endif;
        ?>
        </div>
    </div>
</section>


<div class="container">
    <div class="row">

        <!-- Kết quả của khách hàng -->
        <section>
            <?php
            // ====== LẤY GROUP CHA "ketqua" ======
            $kq_raw = get_field('ketqua');
            $kq     = is_array($kq_raw) ? $kq_raw : [];

            // --- TIÊU ĐỀ & MÔ TẢ ---
            $kq_title   = (isset($kq['title']) && is_array($kq['title'])) ? $kq['title'] : [];
            $kq_t1      = isset($kq_title['title1']) ? wp_strip_all_tags($kq_title['title1']) : '';
            $kq_t2      = isset($kq_title['title2']) ? wp_strip_all_tags($kq_title['title2']) : '';
            $kq_content = isset($kq['content']) ? $kq['content'] : ''; // WYSIWYG/textarea
            ?>

            <div class="title-doctors">
                <?php if ($kq_t1 !== ''): ?>
                <h2 id="h2-bacsi-home3"><?php echo esc_html($kq_t1); ?></h2>
                <?php endif; ?>
                <?php if ($kq_t2 !== ''): ?>
                <h2 id="h2-bacsi-home"><?php echo esc_html($kq_t2); ?></h2>
                <?php endif; ?>
            </div>

            <div class="text-doctors">
                <?php if (!empty($kq_content)): ?>
                <p><?php echo wp_kses_post($kq_content); ?></p>
                <?php endif; ?>
            </div>

            <?php
            // ====== HELPER ẢNH (giữ như cũ) ======
            if (!function_exists('ve_img_tag')) {
                function ve_img_tag($raw, $size = 'large', $class = '') {
                if (empty($raw)) return '';
                if (is_array($raw)) {
                    $id  = $raw['ID'] ?? ($raw['id'] ?? null);
                    $url = $raw['url'] ?? null;
                    $alt = isset($raw['alt']) ? $raw['alt'] : '';
                    if ($id)  return wp_get_attachment_image((int)$id, $size, false, ['class'=>$class, 'loading'=>'lazy']);
                    if ($url) return '<img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" class="'.esc_attr($class).'" loading="lazy">';
                    return '';
                }
                if (is_numeric($raw)) {
                    return wp_get_attachment_image((int)$raw, $size, false, ['class'=>$class, 'loading'=>'lazy']);
                }
                if (filter_var($raw, FILTER_VALIDATE_URL)) {
                    return '<img src="'.esc_url($raw).'" alt="" class="'.esc_attr($class).'" loading="lazy">';
                }
                return '';
                }
            }

            // ====== QUÉT CÁC FIELD imageN TRONG 'ketqua' ======
            $imgs = [];
            if (!empty($kq) && is_array($kq)) {
                foreach ($kq as $key => $val) {
                if (preg_match('/^image(\d+)$/i', $key, $m)) {
                    $order = (int) $m[1];
                    $imgs[$order] = $val;
                }
                }
            }

            if (!empty($imgs)) :
                ksort($imgs, SORT_NUMERIC);
                $imgs = array_values($imgs);
                $big  = array_shift($imgs);
            ?>
            <section class="ketqua-gallery">
                <div class="ketqua-grid">
                    <div class="ketqua-left">
                        <?php echo ve_img_tag($big, 'x-large', 'ketqua-big'); ?>
                    </div>

                    <div class="ketqua-right">
                        <?php foreach ($imgs as $img): ?>
                        <div class="ketqua-thumb">
                            <?php echo ve_img_tag($img, 'medium_large', 'ketqua-thumb-img'); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </section>

        <!-- CHƯƠNG TRÌNH KHUYẾN MẠI  -->
        <section>
            <?php
                // LẤY DỮ LIỆU TỪ GROUP CHA "khuyenmai"
                // Nếu dùng Options Page thì đổi: $km = get_field('khuyenmai', 'option');
                $km = get_field('khuyenmai');

                $t1   = $km['title']['title1'] ?? '';
                $t2   = $km['title']['title2'] ?? '';
                $desc = $km['content'] ?? '';
                ?>
            <div class="title-doctors">
                <?php if ($t1): ?>
                <h2 id="h2-bacsi-home3"><?php echo esc_html($t1); ?></h2>
                <?php endif; ?>
                <?php if ($t2): ?>
                <h2 id="h2-bacsi-home"><?php echo esc_html($t2); ?></h2>
                <?php endif; ?>
            </div>

            <div class="text-doctors">
                <?php if ($desc): ?>
                <p><?php echo wp_kses_post($desc); /* dùng WYSIWYG thì giữ HTML */ ?></p>
                <?php endif; ?>
            </div>
            <?php
                if (!function_exists('get_first_image_url_from_content')) {
                function get_first_image_url_from_content($post_id = null) {
                    $post = get_post($post_id ?: get_the_ID());
                    if (!$post) return '';
                    // Bắt <img ... src="...">
                    if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $post->post_content, $m)) {
                    return $m[1];
                    }
                    return '';
                }
                }

                // Query 3 bài thuộc category 'home'
                $q = new WP_Query([
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'category_name'       => 'khuyenmai,special_offer', // đổi nếu cần
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
                ]);

                if ($q->have_posts()) : ?>
            <div id="box-khuyenmai" class="row g-4">
                <?php while ($q->have_posts()) : $q->the_post(); ?>
                <div class="col-12 col-md-4">
                    <article <?php post_class('card h-100'); ?>>

                        <?php
                        // 1) Ảnh đại diện (featured) nếu có
                        $thumb_html = '';
                        if (has_post_thumbnail()) {
                            $thumb_html = get_the_post_thumbnail(null, 'medium', [
                            'class'   => 'w-100 h-100',
                            'style'   => 'object-fit:cover;',
                            'loading' => 'lazy',
                            ]);
                        } else {
                            // 2) Fallback: lấy ảnh đầu tiên trong nội dung
                            $first_img = get_first_image_url_from_content();
                            if ($first_img) {
                            $thumb_html = '<img src="' . esc_url($first_img) . '" alt="' . esc_attr(get_the_title()) . '" class="w-100 h-100" style="object-fit:cover;" loading="lazy">';
                            }
                        }

                        if ($thumb_html) : ?>
                        <a href="<?php the_permalink(); ?>" class="ratio ratio-16x9 d-block overflow-hidden">
                            <?php echo $thumb_html; ?>
                        </a>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title mb-2">
                                <a href="<?php the_permalink(); ?>" class="stretched-link text-decoration-none">
                                    <?php the_title(); ?>
                                </a>
                            </h5>
                            <p class="card-text small text-muted mb-2">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                    <?php echo esc_html(get_the_date()); ?>
                                </time>
                            </p>
                            <p class="card-text">
                                <?php echo esc_html(wp_trim_words(get_the_excerpt(), 25)); ?>
                            </p>
                        </div>
                    </article>
                </div>
                <?php endwhile; ?>
            </div>
            <?php wp_reset_postdata(); ?>
            <?php else : ?>
            <p>Chưa có bài viết.</p>
            <?php endif; ?>


        </section>
    </div>
</div>

<?php
get_footer();
?>