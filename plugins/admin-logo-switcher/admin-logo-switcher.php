<?php
/**
 * Plugin Name: Admin Logo Switcher
 * Description: Đổi logo trang đăng nhập và logo góc trái thanh admin của WordPress, có trang cài đặt chọn ảnh từ Media Library.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: admin-logo-switcher
 */

if (!defined('ABSPATH')) exit;

class Admin_Logo_Switcher {
    const OPT_GROUP = 'als_options';
    const OPT_NAME  = 'als_settings';
    const PAGE_SLUG = 'als-admin-logo';

    public function __construct() {
        // Options
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);

        // Login screen
        add_action('login_enqueue_scripts', [$this, 'login_logo_css']);
        add_filter('login_headerurl',  [$this, 'login_logo_url']);
        add_filter('login_headertext', [$this, 'login_logo_title']);

        // Admin bar
        add_action('admin_bar_menu', [$this, 'adminbar_replace_wp_logo'], 999);
        add_action('admin_head',      [$this, 'adminbar_logo_css']);
    }

    // --- Settings ---

    public function register_settings() {
        register_setting(self::OPT_GROUP, self::OPT_NAME, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default'           => [
                'login_logo'      => '',
                'login_url'       => home_url('/'),
                'login_title'     => get_bloginfo('name'),
                'adminbar_logo'   => '',
            ],
        ]);

        add_settings_section('als_section_main', __('Thiết lập logo', 'admin-logo-switcher'), function () {
            echo '<p>'.esc_html__('Chọn logo cho trang đăng nhập và thanh admin.', 'admin-logo-switcher').'</p>';
        }, self::PAGE_SLUG);

        $fields = [
            'login_logo'    => __('Login logo', 'admin-logo-switcher'),
            'adminbar_logo' => __('Admin bar logo', 'admin-logo-switcher'),
            'login_url'     => __('Link khi click logo (trang đăng nhập)', 'admin-logo-switcher'),
            'login_title'   => __('Title/Tooltip logo (trang đăng nhập)', 'admin-logo-switcher'),
        ];

        foreach ($fields as $id => $label) {
            add_settings_field($id, $label, [$this, 'render_field'], self::PAGE_SLUG, 'als_section_main', ['id' => $id]);
        }
    }

    public function sanitize_settings($input) {
        $out = [];
        $out['login_logo']    = isset($input['login_logo']) ? esc_url_raw($input['login_logo']) : '';
        $out['adminbar_logo'] = isset($input['adminbar_logo']) ? esc_url_raw($input['adminbar_logo']) : '';
        $out['login_url']     = isset($input['login_url']) ? esc_url_raw($input['login_url']) : home_url('/');
        $out['login_title']   = isset($input['login_title']) ? sanitize_text_field($input['login_title']) : get_bloginfo('name');
        return $out;
    }

    public function add_settings_page() {
        $hook = add_options_page(
            __('Admin Logo', 'admin-logo-switcher'),
            __('Admin Logo', 'admin-logo-switcher'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
        // Enqueue media + JS chỉ tại trang này
        add_action("load-$hook", function () {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        });
    }

    public function enqueue_admin_assets() {
        wp_enqueue_media();
        wp_enqueue_script(
            'als-admin-js',
            plugin_dir_url(__FILE__) . 'als-admin.js',
            ['jquery'],
            '1.0.0',
            true
        );
        // Inline JS nho nhỏ (khỏi tạo file riêng vẫn OK)
        $js = <<<JS
jQuery(function($){
  function bindMediaPicker(btn, input, preview){
    $(btn).on('click', function(e){
      e.preventDefault();
      const frame = wp.media({ title: 'Chọn ảnh', multiple: false, library: { type: ['image'] } });
      frame.on('select', function(){
        const file = frame.state().get('selection').first().toJSON();
        $(input).val(file.url).trigger('change');
        if (preview) $(preview).attr('src', file.url).show();
      });
      frame.open();
    });
  }
  bindMediaPicker('#als_pick_login_logo',    '#als_login_logo',    '#als_login_logo_preview');
  bindMediaPicker('#als_pick_adminbar_logo', '#als_adminbar_logo', '#als_adminbar_logo_preview');
});
JS;
        wp_add_inline_script('als-admin-js', $js);
        // Một ít CSS cho đẹp
        $css = '
.als-field-row img {max-height:60px;display:block;margin-top:6px;border:1px solid #ddd;padding:4px;background:#fff;border-radius:6px}
.als-field-row .button {margin-top:6px}
';
        wp_register_style('als-admin-css', false);
        wp_enqueue_style('als-admin-css');
        wp_add_inline_style('als-admin-css', $css);
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
          <h1><?php echo esc_html__('Admin Logo', 'admin-logo-switcher'); ?></h1>
          <form method="post" action="options.php">
            <?php
              settings_fields(self::OPT_GROUP);
              do_settings_sections(self::PAGE_SLUG);
              submit_button();
            ?>
          </form>
        </div>
        <?php
    }

    public function render_field($args) {
        $id  = $args['id'];
        $opt = get_option(self::OPT_NAME);
        $val = isset($opt[$id]) ? $opt[$id] : '';
        echo '<div class="als-field-row">';
        switch ($id) {
            case 'login_logo':
                printf(
                    '<input type="text" id="als_login_logo" name="%1$s[login_logo]" value="%2$s" class="regular-text" placeholder="https://...">%3$s <br><button id="als_pick_login_logo" class="button">'.esc_html__('Chọn ảnh', 'admin-logo-switcher').'</button>',
                    esc_attr(self::OPT_NAME),
                    esc_attr($val),
                    $val ? '<img id="als_login_logo_preview" src="'.esc_url($val).'" alt="preview">' : '<img id="als_login_logo_preview" style="display:none" alt="preview">'
                );
                echo '<p class="description">'.esc_html__('Gợi ý: PNG/SVG nền trong suốt, chiều ngang ~320px.', 'admin-logo-switcher').'</p>';
                break;

            case 'adminbar_logo':
                printf(
                    '<input type="text" id="als_adminbar_logo" name="%1$s[adminbar_logo]" value="%2$s" class="regular-text" placeholder="https://...">%3$s <br><button id="als_pick_adminbar_logo" class="button">'.esc_html__('Chọn ảnh', 'admin-logo-switcher').'</button>',
                    esc_attr(self::OPT_NAME),
                    esc_attr($val),
                    $val ? '<img id="als_adminbar_logo_preview" src="'.esc_url($val).'" alt="preview">' : '<img id="als_adminbar_logo_preview" style="display:none" alt="preview">'
                );
                echo '<p class="description">'.esc_html__('Gợi ý: 20–32px, hình vuông.', 'admin-logo-switcher').'</p>';
                break;

            case 'login_url':
                printf(
                    '<input type="url" name="%1$s[login_url]" value="%2$s" class="regular-text" placeholder="%3$s">',
                    esc_attr(self::OPT_NAME),
                    esc_attr($val),
                    esc_attr(home_url('/'))
                );
                break;

            case 'login_title':
                printf(
                    '<input type="text" name="%1$s[login_title]" value="%2$s" class="regular-text" placeholder="%3$s">',
                    esc_attr(self::OPT_NAME),
                    esc_attr($val),
                    esc_attr(get_bloginfo('name'))
                );
                break;
        }
        echo '</div>';
    }

    // --- Login screen ---

    public function login_logo_css() {
        $opt = get_option(self::OPT_NAME);
        $logo = isset($opt['login_logo']) ? esc_url($opt['login_logo']) : '';
        if (!$logo) return;
        ?>
        <style>
            body.login div#login h1 a {
                background-image: url('<?php echo $logo; ?>');
                background-size: contain;
                background-position: center;
                width: 320px;
                height: 120px;
            }
        </style>
        <?php
    }

    public function login_logo_url($url) {
        $opt = get_option(self::OPT_NAME);
        return !empty($opt['login_url']) ? esc_url($opt['login_url']) : home_url('/');
    }

    public function login_logo_title($title) {
        $opt = get_option(self::OPT_NAME);
        return !empty($opt['login_title']) ? esc_html($opt['login_title']) : get_bloginfo('name');
    }

    // --- Admin bar ---

    public function adminbar_replace_wp_logo($wp_admin_bar) {
        // 1) Bỏ logo WordPress mặc định
        $wp_admin_bar->remove_node('wp-logo');

        // 2) Thêm node logo mới (chỉ icon)
        $opt = get_option(self::OPT_NAME);
        $href = admin_url();
        $wp_admin_bar->add_node([
            'id'    => 'als-logo',
            'title' => '<span class="ab-icon"></span>',
            'href'  => $href,
            'meta'  => ['class' => 'als-logo-node', 'title' => get_bloginfo('name')]
        ]);
    }

    public function adminbar_logo_css() {
        $opt = get_option(self::OPT_NAME);
        $logo = isset($opt['adminbar_logo']) ? esc_url($opt['adminbar_logo']) : '';
        if (!$logo) return;
        ?>
        <style>
            #wpadminbar #wp-admin-bar-als-logo > .ab-item .ab-icon:before {
                content: "";
            }
            #wpadminbar #wp-admin-bar-als-logo > .ab-item .ab-icon {
                background-image: url('<?php echo $logo; ?>');
                background-repeat: no-repeat;
                background-position: center;
                background-size: contain;
                width: 20px;
                height: 20px;
                margin-top: 6px; /* căn giữa theo chiều dọc */
            }
        </style>
        <?php
    }
}

new Admin_Logo_Switcher();
