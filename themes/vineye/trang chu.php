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

                            echo '<div class="col-md-2 logo-container">
                                    <div class="logo-item">
                                        <img loading="lazy" decoding="async" width="200" height="92" src="' . $src . '" />
                                    </div>
                                </div>';
                        }
                    }
                }
                ?>
            </div>
        </section>


    </div>
</div>

<?php
get_footer();
?>
