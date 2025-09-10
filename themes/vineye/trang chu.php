<?php
/** 
 * Template Name: Trang Chu
 */
get_header();
?>

<?php echo do_shortcode('[smartslider3 slider="2"]'); ?>

<div class="container">
    <div class="row">
        <div class="section-boxes">
            <?php
            $content1 = get_field('content1');
            if ($content1) :
                foreach ($content1 as $item) : 

                    // Gom 3 nhóm vào mảng để lặp
                    $blocks = [
                        [
                            'title' => $item['title1'] ?? '',
                            'icon'  => $item['icon1'] ?? '',
                            'desc'  => $item['desc1'] ?? '',
                        ],
                        [
                            'title' => $item['title2'] ?? '',
                            'icon'  => $item['icon2'] ?? '',
                            'desc'  => $item['desc2'] ?? '',
                        ],
                        [
                            'title' => $item['title3'] ?? '',
                            'icon'  => $item['icon3'] ?? '',
                            'desc'  => $item['desc3'] ?? '',
                        ],
                    ];

                    foreach ($blocks as $block) :
                        if (!empty($block['title']) || !empty($block['desc']) || !empty($block['icon'])) : ?>

                                <div class="col-md-4" id="col-md-4-bn">
                                    <div class="card mb-4 text-center" id="bn-card-home" >

                                        <!-- Icon -->
                                        <?php if (!empty($block['icon'])) : ?>
                                            <div class="mb-3">
                                                <div class="icon-inner" >
                                                    <?php echo $block['icon']; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>


                                        <!-- Title -->
                                        <?php if (!empty($block['title'])) : ?>
                                            <h3 id="title-bn-home"><?php echo esc_html($block['title']); ?></h3>
                                        <?php endif; ?>

                                        <!-- Desc -->
                                        <?php if (!empty($block['desc'])) : ?>
                                            <p><?php echo esc_html($block['desc']); ?></p>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            
                        <?php endif;
                    endforeach;
                endforeach;
            endif;
            ?>
        </div>
        <!-- Báo Chí Nói Gì Về Chúng Tôi -->
        <section class="partner-logos">
            <div class="title-baochi">
                <h2 id="h2-baochi-home">Báo Chí  </h2>
                <h2 id="h2-baochi-home2"> Nói Gì Về Chúng Tôi</h2>
            </div>
            
            <div class="logo-container row">
                <?php
                $baochi = get_field('baochi');

                if ($baochi) {
                    foreach ($baochi as $field_name => $image) {
                        if (!empty($image)) {
                            // Nếu là array (ACF image field)
                            if (is_array($image) && isset($image['url'])) {
                                $src = esc_url($image['url']);
                            } 
                            // Nếu là ID (ACF image ID)
                            elseif (is_numeric($image)) {
                                $src = esc_url(wp_get_attachment_url($image));
                            } 
                            // Nếu là chuỗi (bạn nhập trực tiếp URL trong Textarea)
                            else {
                                $src = esc_url($image);
                            }

                            echo '<div class="col-md-2">
                                    <div class="logo-item">
                                        <a href="https://alobacsi.com/trung-tam-mat-quoc-te-vin-eye-dong-hanh-giai-phong-tam-nhin-cho-hang-trieu-nguoi.html?gidzl=QeEnU7oSAYbdch1xUOCiA6ETj7XZZNmjBiFWVJNQSYrzaRegP8acV7YMkdeqZN0eBfFlVJKkAAjbTfWY90">
                                        <img loading="lazy" decoding="async" width="200" height="92" src="' . $src . '" />
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
                <div class="title-doctors text-center mb-4">
                    <h2 id="h2-bacsi-home">BÁC SĨ & CHUYÊN GIA </h2>
                    <h2 id="h2-bacsi-home3"> MẮT</h2>
                </div>
                <div class="text-doctors text-center mb-5">
                    <p>ĐỘI NGŨ CHUYÊN SÂU – CHẤT LƯỢNG DẪN ĐẦU</p>
                </div>

                <div class="row doctors-container">
                    <?php 
                    $nhanvien = get_field('nhanvien'); 
                    if( $nhanvien && is_array($nhanvien) ): 
                        foreach( $nhanvien as $group ):
                            $image = !empty($group['image']) ? $group['image'] : '';
                            $desc  = !empty($group['desc-nhanvien']) ? $group['desc-nhanvien'] : '';
                            
                            if( $image || $desc ):
                    ?>
                        <div class="col-md-4 mb-4">
                            <div class="card staff-card">
                                <div class="image-wrapper">
                                    <?php if( $image ): ?>
                                        <img src="<?php echo esc_url($image); ?>" alt="" class="img-fluid staff-img">
                                    <?php endif; ?>
                                    <?php if( $desc ): ?>
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
            <div class="title-doctors">
                <h2 id="h2-bacsi-home">DỊCH VỤ UY TÍN </h2>
                <h2 id="h2-bacsi-home3"> TẠI VIN EYE</h2>
            </div>
            <div class="text-doctors">
                <p>TOP NHỮNG DỊCH VỤ ĐƯỢC KHÁCH HÀNG SỬ DỤNG</p>
            </div>
            <div class="row dichvu-container">
                <?php 
                // Lấy group cha
                $dichvu = get_field('dichvu');

                if ( $dichvu && is_array($dichvu) ) :
                    foreach ($dichvu as $key => $group) :
                        // Mỗi group con chính là dichvu1, dichvu2...
                        $name  = isset($group['name-dichvu']) ? $group['name-dichvu'] : '';
                        $image = isset($group['image-dichvu']) ? $group['image-dichvu'] : '';

                        if ( $name || $image ) :
                ?>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="dichvu-box text-center">
                            <?php if ($image): ?>
                                <div class="dichvu-img">
                                    <img src="<?php echo esc_url($image); ?>" 
                                        alt="" 
                                        class="img-fluid">
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
        <!-- Tiêu đề -->
        <div id="text-center-vatchat" class="col medium-8 small-12 large-8 mx-auto text-center mb-4">
            <h3 class="facility-title">CƠ SỞ VẬT CHẤT 5 SAO</h3>
            <p class="facility-subtitle">
                Hệ thống trang thiết bị y tế hiện đại bậc nhất được nhập khẩu từ các nước Anh, Mỹ, Đức
            </p>
        </div>

        <!-- Hình ảnh -->
        <div id="box-vatchat" class="row justify-content-center bg-white p-4 rounded shadow-sm mx-2">
            <?php 
            // Lấy group cha "vatchat"
            $vatchat = get_field('vatchat');

            if ( $vatchat && is_array($vatchat) ) :
                foreach ($vatchat as $group) :
                    $image = !empty($group['image-vatchat']) ? $group['image-vatchat'] : '';

                    if ($image) :
            ?>
                <div class="col-md-3 col-6">
                    <div class="facility-item">
                        <img src="<?php echo esc_url($image); ?>" 
                             alt="Cơ sở vật chất" 
                             class="img-fluid rounded shadow"
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
<!-- Video tại vineye -->
    <div class="container">
        <div class="row">
            <section>
                <div class="title-doctors">
                    <h2 id="h2-bacsi-home3">VIDEO </h2>
                    <h2 id="h2-bacsi-home"> TẠI VIN EYE</h2>
                </div>
                <?php 
                $group = get_field('video');

                if ($group && is_array($group)) {
                    echo '<div class="row">';

                    foreach ($group as $raw) {
                        if (empty($raw)) continue;

                        $embed_html = '';

                        if (is_array($raw)) {
                            $url = $raw['url'] ?? $raw['link'] ?? '';
                            if ($url) $embed_html = wp_oembed_get($url);
                        } else {
                            // Nếu là chuỗi (URL hoặc iframe HTML)
                            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                                $embed_html = wp_oembed_get($raw);
                            } else {
                                $embed_html = $raw;
                            }
                        }

                        if ($embed_html) {
                            echo '<div class="col-md-6">';
                            echo '  <div class="video-item">';
                            echo        $embed_html;
                            echo '  </div>';
                            echo '</div>';
                        }
                    }

                    echo '</div>'; // end .row
                }
                ?>

        </section>
        <section>
            <div class="title-doctors">
                <h2 id="h2-bacsi-home3">KẾT QUẢ  </h2>
                <h2 id="h2-bacsi-home"> CỦA KHÁCH HÀNG</h2>
            </div>
            <div class="text-doctors">
                <p>TOP NHỮNG DỊCH VỤ ĐƯỢC KHÁCH HÀNG SỬ DỤNG</p>
            </div>
            <?php
            /**
             * Helper: nhận value ACF (ID | URL | array) -> trả về thẻ <img> hoàn chỉnh.
             */
            function ve_img_tag($raw, $size = 'large', $class = '') {
            if (empty($raw)) return '';

            // ACF trả về dạng array
            if (is_array($raw)) {
                $id  = $raw['ID'] ?? ($raw['id'] ?? null);
                $url = $raw['url'] ?? null;
                $alt = isset($raw['alt']) ? $raw['alt'] : '';

                if ($id)  return wp_get_attachment_image((int)$id, $size, false, ['class'=>$class, 'loading'=>'lazy']);
                if ($url) return '<img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" class="'.esc_attr($class).'" loading="lazy">';
                return '';
            }

            // ACF trả về ID
            if (is_numeric($raw)) {
                return wp_get_attachment_image((int)$raw, $size, false, ['class'=>$class, 'loading'=>'lazy']);
            }

            // ACF trả về URL
            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                return '<img src="'.esc_url($raw).'" alt="" class="'.esc_attr($class).'" loading="lazy">';
            }

            return '';
            }

            /**
             * Lấy group 'ketqua' và quét tất cả field có tên imageN
             */
            $group = get_field('ketqua');
            $imgs  = [];

            if (is_array($group)) {
            foreach ($group as $key => $val) {
                // Nhận các key dạng image1, image2, image10...
                if (preg_match('/^image(\d+)$/i', $key, $m)) {
                $order = (int) $m[1];
                $imgs[$order] = $val;
                }
            }
            }

            // Không có gì để hiển thị
            if (empty($imgs)) return;

            // Sắp xếp tăng dần theo số N
            ksort($imgs, SORT_NUMERIC);
            $imgs = array_values($imgs);

            // Ảnh lớn đầu tiên bên trái
            $big  = array_shift($imgs);
            ?>
            <section class="ketqua-gallery">
            <div class="ketqua-grid">
                <div class="ketqua-left">
                <?php echo ve_img_tag($big, 'x-large', 'ketqua-big'); ?>
                </div>

                <div class="ketqua-right">
                <?php
                // Các ảnh còn lại thành lưới 3 cột
                foreach ($imgs as $img) {
                    echo '<div class="ketqua-thumb">'. ve_img_tag($img, 'medium_large', 'ketqua-thumb-img') .'</div>';
                }
                ?>
                </div>
            </div>
            </section>
        </section>
        <!-- CHƯƠNG TRÌNH KHUYẾN MẠI  -->
        <section>
            <div class="title-doctors">
                <h2 id="h2-bacsi-home3">CHƯƠNG TRÌNH </h2>
                <h2 id="h2-bacsi-home"> KHUYẾN MẠI</h2>
            </div>
            <div class="text-doctors">
                <p>HÈ SANG – ƯU ĐÃI NGẬP TRÀN</p>
            </div>
            <?php 
            $khuyenmai = get_field('khuyenmai');

            if ($khuyenmai && is_array($khuyenmai)) {
                echo '<div class="row">';

                foreach ($khuyenmai as $raw) {
                    if (empty($raw)) continue;

                    $embed_html = '';

                    if (is_array($raw)) {
                        $url = $raw['url'] ?? $raw['link'] ?? '';
                        if ($url) $embed_html = wp_oembed_get($url);
                    } else {
                        // Nếu là chuỗi (URL hoặc iframe HTML)
                        if (filter_var($raw, FILTER_VALIDATE_URL)) {
                            $embed_html = wp_oembed_get($raw);
                        } else {
                            $embed_html = $raw;
                        }
                    }

                    if ($embed_html) {
                        echo '<div class="col-md-6">';
                        echo '  <div class="video-item">';
                        echo        $embed_html;
                        echo '  </div>';
                        echo '</div>';
                    }
                }

                echo '</div>'; // end .row
            }
            ?>
        </section>
    </div>
 </div>

<?php
get_footer();
?>
