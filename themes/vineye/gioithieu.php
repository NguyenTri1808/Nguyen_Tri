<?php
/**
 * Template Name: Gioi Thieu
 */
defined('ABSPATH') || exit;

get_header();

/**
 * =========================
 *  Helpers (auto-render ACF Group 'text' – ẩn mọi label)
 * =========================
 */

if (!function_exists('acf_get_target_for_context')) {
    function acf_get_target_for_context($context = null) {
        if ($context === 'option' || $context === 'options') return 'option';
        return $context ?: get_the_ID();
    }
}

if (!function_exists('acf_render_field_value_auto')) {
    /**
     * Render 1 field theo type, KHÔNG in label.
     * @param array $field  field object ACF
     * @param mixed $value  giá trị từ get_field(...)
     * @param array $opts   ['show_labels' => false] (dùng cho mở rộng sau này)
     */
    function acf_render_field_value_auto(array $field, $value, array $opts = []) {
        $show_labels = $opts['show_labels'] ?? false; // mặc định false (ẩn nhãn)

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            if (!is_numeric($value)) return; // cho phép 0/'0'
        }

        $type  = $field['type'] ?? 'text';
        $name  = $field['name'] ?? 'field';
        // $label = $field['label'] ?? '';  // KHÔNG dùng, vì không hiển thị label

        echo '<div class="acf-field acf-' . esc_attr($type) . ' field-' . esc_attr($name) . '">';

        switch ($type) {
            case 'wysiwyg':
                echo '<div class="acf-wysiwyg-content">';
                echo apply_filters('the_content', (string)$value);
                echo '</div>';
                break;

            case 'textarea':
            case 'text':
            case 'email':
            case 'number':
            case 'message':
                echo '<p class="acf-text">' . nl2br(esc_html((string)$value)) . '</p>';
                break;

            case 'true_false':
                // Không hiển thị gì cho boolean để tránh lộ nhãn
                break;

            case 'select':
            case 'checkbox':
            case 'radio':
                if (is_array($value)) {
                    echo '<ul class="acf-choices">';
                    foreach ($value as $v) {
                        echo '<li>' . esc_html((string)$v) . '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p class="acf-choice">' . esc_html((string)$value) . '</p>';
                }
                break;

            case 'url':
                echo '<p class="acf-url"><a href="' . esc_url((string)$value) . '" target="_blank" rel="noopener">' . esc_html((string)$value) . '</a></p>';
                break;

            case 'link':
                if (is_array($value) && !empty($value['url'])) {
                    $title  = $value['title'] ?: $value['url'];
                    $target = $value['target'] ?: '_self';
                    echo '<p class="acf-link"><a href="' . esc_url($value['url']) . '" target="' . esc_attr($target) . '">' . esc_html($title) . '</a></p>';
                }
                break;

            case 'file':
                if (is_array($value) && !empty($value['url'])) {
                    $title = $value['title'] ?? basename($value['url']);
                    echo '<p class="acf-file"><a href="' . esc_url($value['url']) . '" target="_blank" rel="noopener">' . esc_html($title) . '</a></p>';
                }
                break;

            case 'image':
                if (is_array($value) && !empty($value['url'])) {
                    $alt = $value['alt'] ?? '';
                    echo '<figure class="acf-image"><img src="' . esc_url($value['url']) . '" alt="' . esc_attr($alt) . '"></figure>';
                    // KHÔNG in figcaption/label
                }
                break;

            case 'gallery':
                if (is_array($value) && !empty($value)) {
                    echo '<div class="acf-gallery">';
                    foreach ($value as $img) {
                        if (!empty($img['url'])) {
                            $alt = $img['alt'] ?? '';
                            echo '<figure><img src="' . esc_url($img['url']) . '" alt="' . esc_attr($alt) . '"></figure>';
                        }
                    }
                    echo '</div>';
                }
                break;

            case 'oembed':
                echo '<div class="acf-oembed">' . $value . '</div>';
                break;

            case 'group':
                // Render đệ quy, KHÔNG in heading/label cho group cha
                $sub_fields = $field['sub_fields'] ?? [];
                if (!empty($sub_fields) && is_array($value)) {
                    echo '<div class="acf-group-children">';
                    acf_render_fields_collection($sub_fields, $value, $opts);
                    echo '</div>';
                }
                break;

            case 'repeater':
                $sub_fields = $field['sub_fields'] ?? [];
                if (!empty($value) && !empty($sub_fields)) {
                    echo '<div class="acf-repeater">';
                    $i = 0;
                    foreach ($value as $row) {
                        $i++;
                        echo '<div class="acf-repeater-row" data-index="' . esc_attr($i) . '">';
                        acf_render_fields_collection($sub_fields, $row, $opts);
                        echo '</div>';
                    }
                    echo '</div>';
                }
                break;

            default:
                if (is_scalar($value)) {
                    echo '<p>' . esc_html((string)$value) . '</p>';
                } elseif (is_array($value)) {
                    // Có thể đổi sang render đẹp hơn theo nhu cầu
                    echo '<pre class="acf-dump">' . esc_html(wp_json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
                }
                break;
        }

        echo '</div>'; // .acf-field
    }
}

if (!function_exists('acf_render_fields_collection')) {
    function acf_render_fields_collection(array $sub_fields, array $values, array $opts = []) {
        foreach ($sub_fields as $sf) {
            $name  = $sf['name'] ?? '';
            $value = $name !== '' && array_key_exists($name, $values) ? $values[$name] : null;
            acf_render_field_value_auto($sf, $value, $opts);
        }
    }
}

if (!function_exists('acf_render_group_auto')) {
    /**
     * Tự động render toàn bộ subfield trong ACF Group (vd: 'text'), mặc định ẩn mọi label.
     * @param string          $group_field_name
     * @param int|string|null $context
     * @param array           $opts ['show_labels' => false, 'show_group_label' => false]
     */
    function acf_render_group_auto($group_field_name = 'text', $context = null, array $opts = []) {
        if (!function_exists('get_field_object')) return;

        // đảm bảo luôn ẩn label nếu không set
        $opts = array_merge([
            'show_labels'      => false,
            'show_group_label' => false,
        ], $opts);

        $target     = acf_get_target_for_context($context);
        $field_obj  = get_field_object($group_field_name, $target);
        $field_data = get_field($group_field_name, $target);

        if (!$field_obj || $field_obj['type'] !== 'group') return;
        if (!is_array($field_data)) $field_data = [];

        $sub_fields = $field_obj['sub_fields'] ?? [];
        if (empty($sub_fields)) return;

        echo '<section class="acf-group acf-group-' . esc_attr($group_field_name) . '">';
        // KHÔNG in group label dù có, vì show_group_label=false
        acf_render_fields_collection($sub_fields, $field_data, $opts);
        echo '</section>';
    }
}

?>

<main id="primary" class="site-main page-gioithieu">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <section class="gt-hero container">
      <?php
        // Tự động render toàn bộ subfields trong group 'text' (kể cả group lồng/repeater)
        acf_render_group_auto('text', null);
      ?>
    </section>

  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
