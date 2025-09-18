<?php
/**
 * Plugin Name: Welcome Admin Notice
 * Description: Hiển thị thông báo chào mừng tùy chỉnh trong Dashboard; nội dung cấu hình qua Settings API.
 * Version: 1.0.0
 * Author: You
 * Text Domain: welcome-admin-notice
 */

if (!defined('ABSPATH')) exit;

final class Welcome_Admin_Notice {
    const OPT_GROUP = 'wan_options_group';
    const OPT_NAME  = 'wan_settings';
    const PAGE_SLUG = 'wan-settings';

    public function __construct() {
        // Đăng ký & render trang cài đặt
        add_action('admin_init',  [$this, 'register_settings']);
        add_action('admin_menu',  [$this, 'add_settings_page']);

        // Hiển thị thông báo tại Dashboard (và toàn bộ admin – bạn có thể giới hạn lại)
        add_action('admin_notices', [$this, 'render_admin_notice']);

        // Thiết lập mặc định khi kích hoạt
        register_activation_hook(__FILE__, [$this, 'activate_defaults']);
    }

    /** Thiết lập mặc định */
    public function activate_defaults() {
        $defaults = [
            'message'   => 'Chào mừng bạn quay lại Dashboard! Chúc một ngày làm việc hiệu quả 💪',
            'type'      => 'updated', // updated | error | warning | info (WP có sẵn .notice-*)
            'show_on'   => 'all',     // all | dashboard
            'roles'     => [],        // mảng role được phép xem; [] = tất cả
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
            'wan_main',
            __('Cấu hình thông báo', 'welcome-admin-notice'),
            function () {
                echo '<p>'.esc_html__('Nhập nội dung và chọn phạm vi hiển thị.', 'welcome-admin-notice').'</p>';
            },
            self::PAGE_SLUG
        );

        add_settings_field('message', __('Nội dung chào mừng', 'welcome-admin-notice'),
            [$this, 'field_message'], self::PAGE_SLUG, 'wan_main');
        add_settings_field('type', __('Kiểu thông báo', 'welcome-admin-notice'),
            [$this, 'field_type'], self::PAGE_SLUG, 'wan_main');
        add_settings_field('show_on', __('Hiển thị ở đâu', 'welcome-admin-notice'),
            [$this, 'field_show_on'], self::PAGE_SLUG, 'wan_main');
        add_settings_field('roles', __('Giới hạn theo vai trò (tùy chọn)', 'welcome-admin-notice'),
            [$this, 'field_roles'], self::PAGE_SLUG, 'wan_main');
    }

    public function sanitize_settings($input) {
        $out = [];
        $out['message'] = isset($input['message']) ? wp_kses_post($input['message']) : '';
        $allowed_types  = ['updated','error','warning','info'];
        $out['type']    = in_array(($input['type'] ?? 'updated'), $allowed_types, true) ? $input['type'] : 'updated';
        $allowed_where  = ['all','dashboard'];
        $out['show_on'] = in_array(($input['show_on'] ?? 'all'), $allowed_where, true) ? $input['show_on'] : 'all';

        // Roles: chỉ giữ các role hợp lệ
        $roles_input = isset($input['roles']) && is_array($input['roles']) ? array_map('sanitize_text_field', $input['roles']) : [];
        $wp_roles = wp_roles();
        $valid = array_keys($wp_roles->roles);
        $out['roles'] = array_values(array_intersect($roles_input, $valid));

        return $out;
    }

    /** Menu Settings */
    public function add_settings_page() {
        add_options_page(
            __('Welcome Notice', 'welcome-admin-notice'),
            __('Welcome Notice', 'welcome-admin-notice'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    /** Trang Settings */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Welcome Notice', 'welcome-admin-notice'); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields(self::OPT_GROUP);
                    do_settings_sections(self::PAGE_SLUG);
                    submit_button(__('Lưu thay đổi', 'welcome-admin-notice'));
                ?>
            </form>
        </div>
        <?php
    }

    // === Fields ===
    public function field_message() {
        $opt = get_option(self::OPT_NAME, []);
        $val = $opt['message'] ?? '';
        printf(
            '<textarea name="%1$s[message]" rows="5" class="large-text" placeholder="%2$s">%3$s</textarea>
             <p class="description">%4$s</p>',
            esc_attr(self::OPT_NAME),
            esc_attr__('Ví dụ: Chào mừng {display_name} đã đăng nhập!', 'welcome-admin-notice'),
            esc_textarea($val),
            esc_html__('Mẹo: Có thể dùng biến {display_name} để chèn tên hiển thị người dùng.', 'welcome-admin-notice')
        );
    }

    public function field_type() {
        $opt = get_option(self::OPT_NAME, []);
        $val = $opt['type'] ?? 'updated';
        $choices = [
            'updated' => 'Success/Updated',
            'info'    => 'Info',
            'warning' => 'Warning',
            'error'   => 'Error'
        ];
        echo '<select name="'.esc_attr(self::OPT_NAME).'[type]">';
        foreach ($choices as $k => $label) {
            printf('<option value="%s" %s>%s</option>',
                esc_attr($k),
                selected($val, $k, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }

    public function field_show_on() {
        $opt = get_option(self::OPT_NAME, []);
        $val = $opt['show_on'] ?? 'all';
        $choices = [
            'all'       => __('Mọi trang admin', 'welcome-admin-notice'),
            'dashboard' => __('Chỉ Dashboard', 'welcome-admin-notice'),
        ];
        echo '<select name="'.esc_attr(self::OPT_NAME).'[show_on]">';
        foreach ($choices as $k => $label) {
            printf('<option value="%s" %s>%s</option>',
                esc_attr($k),
                selected($val, $k, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }

    public function field_roles() {
        $opt = get_option(self::OPT_NAME, []);
        $vals = $opt['roles'] ?? [];
        $wp_roles = wp_roles();
        echo '<fieldset>';
        foreach ($wp_roles->roles as $role_key => $role_data) {
            printf(
                '<label style="display:inline-block;margin-right:12px;">
                    <input type="checkbox" name="%1$s[roles][]" value="%2$s" %3$s> %4$s
                 </label>',
                esc_attr(self::OPT_NAME),
                esc_attr($role_key),
                checked(in_array($role_key, $vals, true), true, false),
                esc_html($role_data['name'])
            );
        }
        echo '<p class="description">'.esc_html__('Để trống = tất cả vai trò nhìn thấy thông báo.', 'welcome-admin-notice').'</p>';
        echo '</fieldset>';
    }

    /** Hook hiển thị notice */
    public function render_admin_notice() {
        // Giới hạn trang: nếu chọn chỉ Dashboard
        $opt = get_option(self::OPT_NAME, []);
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (($opt['show_on'] ?? 'all') === 'dashboard' && $screen && $screen->id !== 'dashboard') {
            return;
        }

        // Giới hạn theo role (nếu có)
        if (!empty($opt['roles']) && is_array($opt['roles'])) {
            $can_see = false;
            foreach ($opt['roles'] as $role) {
                if (current_user_can($role)) { $can_see = true; break; }
            }
            if (!$can_see) return;
        }

        // Nội dung
        $type    = $opt['type'] ?? 'updated';
        $message = $opt['message'] ?? '';
        if (!$message) return;

        // Thay thế biến {display_name}
        $current = wp_get_current_user();
        $display = $current ? $current->display_name : '';
        $message = str_replace('{display_name}', esc_html($display), $message);

        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($type),
            wp_kses_post($message)
        );
    }
}

new Welcome_Admin_Notice();
