<?php
/**
 * Plugin Name: Site Promo Overlay
 * Description: Hiển thị ảnh quảng cáo dạng overlay có nút đóng (X). Cho phép chọn ảnh/link, độ trễ, tần suất xuất hiện, giới hạn chỉ trang chủ...
 * Version: 1.0.0
 * Author: You
 * Text Domain: site-promo-overlay
 */

if (!defined('ABSPATH')) exit;

final class Site_Promo_Overlay {
    const OPT_GROUP = 'spo_options_group';
    const OPT_NAME  = 'spo_settings';
    const PAGE_SLUG = 'spo-settings';

    public function __construct() {
        // Settings
        add_action('admin_init',  [$this, 'register_settings']);
        add_action('admin_menu',  [$this, 'add_settings_page']);

        // Frontend render
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front_assets']);
        add_action('wp_footer',          [$this, 'render_overlay_markup']);

        // Activate defaults
        register_activation_hook(__FILE__, [$this, 'activate_defaults']);
    }

    public function activate_defaults() {
        $defaults = [
            'enabled'         => 1,
            'image_url'       => '',
            'link_url'        => '',
            'link_target'     => '_blank', // _blank | _self
            'show_on'         => 'all',    // all | home
            'delay_ms'        => 600,      // xuất hiện sau n ms
            'max_width'       => 480,      // px
            'bg_opacity'      => 0.6,      // 0..1
            'frequency'       => 'per_session', // always | per_session | once_day
            'version'         => 1,        // tăng số này để hiển thị lại cho tất cả
            'close_label'     => 'Đóng',
        ];
        if (!get_option(self::OPT_NAME)) {
            add_option(self::OPT_NAME, $defaults);
        }
    }

    /** Settings API */
    public function register_settings() {
        register_setting(self::OPT_GROUP, self::OPT_NAME, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => [],
        ]);

        add_settings_section(
            'spo_main',
            __('Cấu hình Promo Overlay', 'site-promo-overlay'),
            function () { echo '<p>'.esc_html__('Chọn ảnh, link và quy tắc hiển thị.', 'site-promo-overlay').'</p>'; },
            self::PAGE_SLUG
        );

        $fields = [
            'enabled'     => __('Bật/Tắt', 'site-promo-overlay'),
            'image_url'   => __('Ảnh quảng cáo', 'site-promo-overlay'),
            'link_url'    => __('Link khi click ảnh (tùy chọn)', 'site-promo-overlay'),
            'link_target' => __('Mở link', 'site-promo-overlay'),
            'show_on'     => __('Hiển thị ở đâu', 'site-promo-overlay'),
            'delay_ms'    => __('Độ trễ (ms)', 'site-promo-overlay'),
            'max_width'   => __('Chiều rộng tối đa (px)', 'site-promo-overlay'),
            'bg_opacity'  => __('Độ mờ nền (0–1)', 'site-promo-overlay'),
            'frequency'   => __('Tần suất xuất hiện', 'site-promo-overlay'),
            'version'     => __('Phiên bản thông báo', 'site-promo-overlay'),
            'close_label' => __('Nhãn nút đóng (X)', 'site-promo-overlay'),
        ];

        foreach ($fields as $id => $label) {
            add_settings_field($id, $label, [$this, 'render_field'], self::PAGE_SLUG, 'spo_main', ['id' => $id]);
        }
    }

    public function sanitize_settings($input) {
        $out = [];
        $out['enabled']     = !empty($input['enabled']) ? 1 : 0;
        $out['image_url']   = isset($input['image_url']) ? esc_url_raw($input['image_url']) : '';
        $out['link_url']    = isset($input['link_url']) ? esc_url_raw($input['link_url']) : '';
        $out['link_target'] = in_array(($input['link_target'] ?? '_blank'), ['_blank','_self'], true) ? $input['link_target'] : '_blank';
        $out['show_on']     = in_array(($input['show_on'] ?? 'all'), ['all','home'], true) ? $input['show_on'] : 'all';
        $out['delay_ms']    = max(0, intval($input['delay_ms'] ?? 600));
        $out['max_width']   = max(200, intval($input['max_width'] ?? 480));
        $bg                 = is_numeric($input['bg_opacity'] ?? 0.6) ? floatval($input['bg_opacity']) : 0.6;
        $out['bg_opacity']  = min(1, max(0, $bg));
        $out['frequency']   = in_array(($input['frequency'] ?? 'per_session'), ['always','per_session','once_day'], true) ? $input['frequency'] : 'per_session';
        $out['version']     = max(1, intval($input['version'] ?? 1));
        $out['close_label'] = sanitize_text_field($input['close_label'] ?? 'Đóng');
        return $out;
    }

    public function add_settings_page() {
        $hook = add_options_page(
            __('Promo Overlay', 'site-promo-overlay'),
            __('Promo Overlay', 'site-promo-overlay'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );

        // Chỉ enqueue media picker + inline JS tại trang này
        add_action("load-$hook", function () {
            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_media();
                wp_enqueue_script('spo-admin', plugin_dir_url(__FILE__).'spo-admin.js', ['jquery'], '1.0.0', true);
                $inline = <<<JS
jQuery(function($){
  function bindPicker(btn, input, preview){
    $(btn).on('click', function(e){
      e.preventDefault();
      const frame = wp.media({ title: 'Chọn ảnh quảng cáo', multiple: false, library: { type: ['image'] } });
      frame.on('select', function(){
        const f = frame.state().get('selection').first().toJSON();
        $(input).val(f.url).trigger('change');
        if (preview) $(preview).attr('src', f.url).show();
      });
      frame.open();
    });
  }
  bindPicker('#spo_pick_image', '#spo_image_url', '#spo_image_preview');
});
JS;
                wp_add_inline_script('spo-admin', $inline);
                $css = '.spo-field img{max-height:120px;border:1px solid #ddd;padding:4px;background:#fff;border-radius:8px;display:block;margin-top:6px}';
                wp_register_style('spo-admin-css', false);
                wp_enqueue_style('spo-admin-css');
                wp_add_inline_style('spo-admin-css', $css);
            });
        });
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) return; ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Promo Overlay', 'site-promo-overlay'); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields(self::OPT_GROUP);
                    do_settings_sections(self::PAGE_SLUG);
                    submit_button(__('Lưu thay đổi', 'site-promo-overlay'));
                ?>
            </form>
        </div>
    <?php }

    public function render_field($args) {
        $id  = $args['id'];
        $opt = get_option(self::OPT_NAME, []);
        $val = $opt[$id] ?? '';

        echo '<div class="spo-field">';
        switch ($id) {
            case 'enabled':
                printf(
                    '<label><input type="checkbox" name="%1$s[enabled]" value="1" %2$s> %3$s</label>',
                    esc_attr(self::OPT_NAME),
                    checked(1, !empty($val), false),
                    esc_html__('Bật overlay quảng cáo', 'site-promo-overlay')
                );
                break;

            case 'image_url':
                printf(
                    '<input type="text" id="spo_image_url" name="%1$s[image_url]" value="%2$s" class="regular-text" placeholder="https://...">%3$s<br><button id="spo_pick_image" class="button">%4$s</button>',
                    esc_attr(self::OPT_NAME),
                    esc_attr($val),
                    $val ? '<img id="spo_image_preview" src="'.esc_url($val).'" alt="preview">' : '<img id="spo_image_preview" style="display:none" alt="preview">',
                    esc_html__('Chọn ảnh', 'site-promo-overlay')
                );
                echo '<p class="description">'.esc_html__('Khuyên dùng JPG/PNG cỡ rộng ~800–1200px, nén ảnh để tải nhanh.', 'site-promo-overlay').'</p>';
                break;

            case 'link_url':
                printf('<input type="url" name="%1$s[link_url]" value="%2$s" class="regular-text" placeholder="https://...">', esc_attr(self::OPT_NAME), esc_attr($val));
                break;

            case 'link_target':
                $choices = ['_blank' => 'Mở tab mới', '_self' => 'Mở tại trang hiện tại'];
                echo '<select name="'.esc_attr(self::OPT_NAME).'[link_target]">';
                foreach ($choices as $k => $label) {
                    printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($val ?: '_blank', $k, false), esc_html($label));
                }
                echo '</select>';
                break;

            case 'show_on':
                $choices = [
                    'all'  => __('Mọi trang', 'site-promo-overlay'),
                    'home' => __('Chỉ trang chủ', 'site-promo-overlay'),
                ];
                echo '<select name="'.esc_attr(self::OPT_NAME).'[show_on]">';
                foreach ($choices as $k => $label) {
                    printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($val ?: 'all', $k, false), esc_html($label));
                }
                echo '</select>';
                break;

            case 'delay_ms':
                printf('<input type="number" min="0" step="100" name="%1$s[delay_ms]" value="%2$d" class="small-text"> ms', esc_attr(self::OPT_NAME), intval($val ?: 600));
                break;

            case 'max_width':
                printf('<input type="number" min="200" step="10" name="%1$s[max_width]" value="%2$d" class="small-text"> px', esc_attr(self::OPT_NAME), intval($val ?: 480));
                break;

            case 'bg_opacity':
                printf('<input type="number" min="0" max="1" step="0.05" name="%1$s[bg_opacity]" value="%2$s" class="small-text">', esc_attr(self::OPT_NAME), esc_attr($val !== '' ? $val : 0.6));
                break;

            case 'frequency':
                $choices = [
                    'always'      => __('Luôn hiển thị (không lưu trạng thái đóng)', 'site-promo-overlay'),
                    'per_session' => __('Một lần mỗi phiên (session)', 'site-promo-overlay'),
                    'once_day'    => __('Một lần mỗi ngày', 'site-promo-overlay'),
                ];
                echo '<select name="'.esc_attr(self::OPT_NAME).'[frequency]">';
                foreach ($choices as $k => $label) {
                    printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($val ?: 'per_session', $k, false), esc_html($label));
                }
                echo '</select>';
                echo '<p class="description">'.esc_html__('Đổi "Phiên bản thông báo" để buộc hiển thị lại với mọi người.', 'site-promo-overlay').'</p>';
                break;

            case 'version':
                printf('<input type="number" min="1" step="1" name="%1$s[version]" value="%2$d" class="small-text">', esc_attr(self::OPT_NAME), intval($val ?: 1));
                break;

            case 'close_label':
                printf('<input type="text" name="%1$s[close_label]" value="%2$s" class="regular-text">', esc_attr(self::OPT_NAME), esc_attr($val ?: 'Đóng'));
                break;
        }
        echo '</div>';
    }

    /** Front assets */
    public function enqueue_front_assets() {
        $opt = get_option(self::OPT_NAME, []);
        if (empty($opt['enabled'])) return;

        // Nếu chỉ trang chủ mà không phải home => thôi
        if (($opt['show_on'] ?? 'all') === 'home' && !is_front_page() && !is_home()) return;

        // CSS tối giản
        $css = '
#spo-overlay{position:fixed;inset:0;background:rgba(0,0,0,VAR_OPACITY);display:none;z-index:999999}
#spo-overlay .spo-wrap{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);max-width:VAR_MAXWpx;width:calc(100% - 32px)}
#spo-overlay .spo-card{position:relative;border-radius:12px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.35);background:#fff}
#spo-overlay .spo-close{position:absolute;right:8px;top:8px;line-height:1;border:none;background:rgba(0,0,0,.6);color:#fff;padding:6px 9px;border-radius:999px;cursor:pointer;font-size:14px}
#spo-overlay .spo-close:hover{background:rgba(0,0,0,.8)}
#spo-overlay img{display:block;width:100%;height:auto}
';
        $css = str_replace('VAR_OPACITY', esc_html($opt['bg_opacity'] ?? 0.6), $css);
        $css = str_replace('VAR_MAXW', intval($opt['max_width'] ?? 480), $css);

        wp_register_style('spo-front', false);
        wp_enqueue_style('spo-front');
        wp_add_inline_style('spo-front', $css);

        // Data cho JS
        $data = [
            'enabled'     => !empty($opt['enabled']),
            'imageUrl'    => $opt['image_url'] ?? '',
            'linkUrl'     => $opt['link_url'] ?? '',
            'linkTarget'  => ($opt['link_target'] ?? '_blank') === '_self' ? '_self' : '_blank',
            'delayMs'     => intval($opt['delay_ms'] ?? 600),
            'frequency'   => $opt['frequency'] ?? 'per_session',
            'version'     => intval($opt['version'] ?? 1),
            'closeLabel'  => $opt['close_label'] ?? 'Đóng',
            'homeOnly'    => ($opt['show_on'] ?? 'all') === 'home',
        ];

        wp_enqueue_script('spo-front', plugin_dir_url(__FILE__).'spo-front.js', [], '1.0.0', true);
        wp_add_inline_script('spo-front', 'window.SPO_DATA = '.wp_json_encode($data).';');

        // Inline JS triển khai logic (để bạn không cần thêm file riêng cũng vẫn chạy)
        $inline = <<<JS
(function(){
  var D = window.SPO_DATA || {};
  if(!D.enabled) return;
  if(!D.imageUrl) return;

  var storageKey = 'spo_dismiss_v' + (D.version||1);
  var now = Date.now();

  function shouldShow(){
    if(D.frequency === 'always') return true;
    try {
      if(D.frequency === 'per_session'){
        return !sessionStorage.getItem(storageKey);
      }
      if(D.frequency === 'once_day'){
        var raw = localStorage.getItem(storageKey);
        if(!raw) return true;
        var last = parseInt(raw, 10) || 0;
        return (now - last) > 86400000; // > 1 ngày
      }
    } catch(e){}
    return true;
  }

  function rememberClosed(){
    try {
      if(D.frequency === 'per_session'){
        sessionStorage.setItem(storageKey, '1');
      } else if(D.frequency === 'once_day'){
        localStorage.setItem(storageKey, String(Date.now()));
      } // 'always' thì không lưu
    } catch(e){}
  }

  function build(){
    var overlay = document.createElement('div');
    overlay.id = 'spo-overlay';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-label', 'Promo Overlay');
    overlay.innerHTML = '<div class="spo-wrap"><div class="spo-card"><button type="button" class="spo-close" aria-label="'+(D.closeLabel||'Close')+'">×</button></div></div>';

    var card = overlay.querySelector('.spo-card');
    var btn  = overlay.querySelector('.spo-close');

    var img = document.createElement('img');
    img.src = D.imageUrl;
    img.alt = 'promotion';

    if(D.linkUrl){
      var a = document.createElement('a');
      a.href = D.linkUrl;
      a.target = (D.linkTarget === '_self') ? '_self' : '_blank';
      a.rel = 'nofollow noopener';
      a.appendChild(img);
      card.appendChild(a);
    } else {
      card.appendChild(img);
    }

    btn.addEventListener('click', function(){
      overlay.style.display = 'none';
      rememberClosed();
    });

    overlay.addEventListener('click', function(e){
      if(e.target === overlay){ // click ra ngoài
        overlay.style.display = 'none';
        rememberClosed();
      }
    });

    document.body.appendChild(overlay);

    setTimeout(function(){
      if(shouldShow()){
        overlay.style.display = 'block';
      }
    }, Math.max(0, parseInt(D.delayMs||0,10)));
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', build);
  } else {
    build();
  }
})();
JS;
        wp_add_inline_script('spo-front', $inline);
    }

    /** Markup backup (không cần, nhưng giữ hook để mở rộng) */
    public function render_overlay_markup() {
        // intentionally empty; mọi thứ tạo bằng JS cho gọn và chỉ khi đủ điều kiện
    }
}

new Site_Promo_Overlay();
