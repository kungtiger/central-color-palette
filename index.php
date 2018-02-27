<?php

/*
 * Plugin Name: Central Color Palette
 * Plugin URI: https://wordpress.org/plugins/kt-tinymce-color-grid
 * Description: Take full control over color pickers of TinyMCE and the palette of the Theme Customizer. Create a central color palette for an uniform look'n'feel!
 * Version: 1.11
 * Author: Gáravo
 * Author URI: http://profiles.wordpress.org/kungtiger
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kt-tinymce-color-grid
 */

if (defined('ABSPATH') && !class_exists('kt_Central_Palette')) {
    define('KT_CENTRAL_PALETTE', '1.11');
    define('KT_CENTRAL_PALETTE_DIR', plugin_dir_path(__FILE__));
    define('KT_CENTRAL_PALETTE_URL', plugin_dir_url(__FILE__));
    define('KT_CENTRAL_PALETTE_BASENAME', plugin_basename(__FILE__));

    class kt_Central_Palette {

        const KEY = 'kt_tinymce_color_grid';
        const NONCE = 'kt-tinymce-color-grid-save-editor';
        const MAP = 'kt_color_grid_map';
        const TYPE = 'kt_color_grid_type';
        const ROWS = 'kt_color_grid_rows';
        const COLS = 'kt_color_grid_cols';
        const LUMA = 'kt_color_grid_luma';
        const VISUAL = 'kt_color_grid_visual';
        const BLOCKS = 'kt_color_grid_blocks';
        const SIZE = 'kt_color_grid_block_size';
        const AXIS = 'kt_color_grid_block_axis';
        const GROUPS = 2;
        const SPREAD = 'kt_color_grid_spread';
        const CLAMP = 'kt_color_grid_clamp';
        const CLAMPS = 'kt_color_grid_clamps';
        const PALETTE = 'kt_color_grid_palette';
        const CUSTOMIZER = 'kt_color_grid_customizer';
        const ELEMENTOR = 'kt_color_grid_elementor';
        const GENERATEPRESS = 'kt_color_grid_gp';
        const OCEANWP = 'kt_color_grid_oceanwp';
        const ACTIVE_VERSION = 'kt_color_grid_version';
        const AUTONAME = 'kt_color_grid_autoname';
        const ALPHA = 'kt_color_grid_alpha';
        const TINYMCE_ROWS = 5;
        const TINYMCE_COLS = 8;
        const DEFAULT_AUTONAME = true;
        const DEFAULT_SPREAD = 'even';
        const DEFAULT_CLAMP = 'column';
        const DEFAULT_CLAMPS = 8;
        const DEFAULT_SIZE = 5;
        const DEFAULT_ROWS = 9;
        const DEFAULT_COLS = 12;
        const DEFAULT_BLOCKS = 6;
        const DEFAULT_AXIS = 'rgb';
        const DEFAULT_TYPE = 'rainbow';
        const DEFAULT_LUMA = 'natural';
        const MAX_FILE_SIZE = 256000;

        protected $blocks = array(4, 6);
        protected $sizes = array(4, 5, 6);
        protected $spread = array('even', 'odd');
        protected $clamp = array('row', 'column');
        protected $columns = array(6, 12, 18);
        protected $rows = array(5, 7, 9, 11, 13);
        protected $types = array('default', 'palette', 'rainbow', 'block');
        protected $lumas = array('linear', 'cubic', 'sine', 'natural');
        protected $views = array('list', 'grid');
        protected $axes = array('rgb', 'rbg', 'grb', 'gbr', 'brg', 'bgr');

        /**
         *
         * @var kt_Central_Palette
         */
        protected static $Instance;

        /**
         * Singleton Design
         * @return kt_Central_Palette
         */
        public static function instance() {
            if (!self::$Instance) {
                self::$Instance = new self();
            }
            return self::$Instance;
        }

        /**
         * Here we go ...
         *
         * Adds action and filter callbacks
         * @since 1.3
         */
        public function __construct() {
            if (self::$Instance) {
                return;
            }

            add_filter('plugin_action_links', array($this, 'add_action_link'), 10, 2);
            add_filter('tiny_mce_before_init', array($this, 'tinymce_integration'));
            add_action('after_wp_tiny_mce', array($this, 'print_tinymce_style'));
            add_action('admin_menu', array($this, 'add_settings_page'));
            add_action('plugins_loaded', array($this, 'init_l10n'));
            add_action('after_setup_theme', array($this, 'init_integrations'));

            $this->update_plugin();
        }

        /**
         * Check for a feature, plugin or theme
         * @since 1.10
         * @param string $key
         * @return boolean
         */
        protected function supports($key) {
            switch ($key) {
                case 'upload':
                    if (function_exists('wp_is_mobile')) {
                        return !(function_exists('_device_can_upload') && !_device_can_upload());
                    }
                    return true;
                case 'alpha-channel':
                    $keys = array('generatepress', 'oceanwp');
                    foreach ($keys as $key) {
                        if ($this->supports($key)) {
                            return true;
                        }
                    }
                    break;
                case 'elementor': return defined('ELEMENTOR_VERSION');
                case 'generatepress': return defined('GP_PREMIUM_VERSION');
                case 'oceanwp': return defined('OCEANWP_THEME_VERSION');
            }
            return false;
        }

        /**
         * Update procedures
         * @since 1.6
         */
        protected function update_plugin() {
            $version = get_option(self::ACTIVE_VERSION, 0);
            if ($version == KT_CENTRAL_PALETTE) {
                return;
            }
            while ($version != KT_CENTRAL_PALETTE) {
                switch ($version) {
                    case 0:
                        $sets = get_option('kt_color_grid_sets', array());
                        if ($sets) {
                            foreach ($sets as &$set) {
                                $set[0] = str_replace('#', '', $set[0]);
                            }
                            update_option('kt_color_grid_sets', $sets);
                        }
                        $version = 16;
                        break;
                    case 16:
                    case 161:
                        if (get_option('kt_color_grid_custom')) {
                            update_option('kt_color_grid_visual', '1');
                        }
                        $sets = get_option('kt_color_grid_sets', array());
                        if ($sets) {
                            update_option('kt_color_grid_palette', $sets);
                        }
                        delete_option('kt_color_grid_custom');
                        delete_option('kt_color_grid_sets');
                        $map = $this->render_rainbow();
                        if ($map) {
                            update_option('kt_color_grid_map', $map);
                        }
                        $version = 170;
                        break;
                    case 171:
                    case 172:
                    case 18:
                    case 181:
                    case 19:
                    case 191:
                    case 192:
                    case 193:
                        $palette = get_option('kt_color_grid_palette', array());
                        foreach ($palette as &$set) {
                            $set = array($set[0], 100, $set[1]);
                        }
                        update_option('kt_color_grid_palette', $palette);
                        $version = 1100;
                        break;
                    default:
                        $version = KT_CENTRAL_PALETTE;
                }
            }
            update_option(self::ACTIVE_VERSION, KT_CENTRAL_PALETTE);
        }

        /**
         * Compare against WP version
         * @global string $wp_version
         * @param string $operator
         * @param string $version
         * @return boolean
         */
        protected function version_compare($operator, $version) {
            global $wp_version;
            return version_compare($wp_version, $version, $operator);
        }

        /**
         * Load translation for older WP versions
         * @since 1.11
         */
        public function init_l10n() {
            // load_plugin_textdomain is obsolete since WordPress 4.6
            if ($this->version_compare('<', '4.6')) {
                load_plugin_textdomain('kt-tinymce-color-grid');
            }
        }

        /**
         * Init integration
         * @since 1.11
         */
        public function init_integrations() {
            if (get_option(self::CUSTOMIZER)) {
                $fn = array($this, 'iris_integration');
                add_action('admin_print_scripts', $fn);
                add_action('admin_print_footer_scripts', $fn);
                add_action('customize_controls_print_scripts', $fn);
                add_action('customize_controls_print_footer_scripts', $fn);
            }

            if ($this->supports('elementor') && get_option(self::ELEMENTOR)) {
                add_filter('elementor/editor/localize_settings', array($this, 'elementor_integration'));
                add_action('elementor/editor/after_enqueue_scripts', array($this, 'elementor_styles'));
            }

            if ($this->supports('generatepress') && get_option(self::GENERATEPRESS)) {
                add_filter('generate_default_color_palettes', array($this, 'generatepress_integration'));
            }

            if ($this->supports('oceanwp') && get_option(self::OCEANWP)) {
                add_filter('ocean_default_color_palettes', array($this, 'oceanwp_integration'));
            }
        }

        /**
         * Get the color palette
         * @since 1.11
         * @param bool $alpha
         * @param int $min
         * @param string $pad
         * @return array
         */
        public function get_palette($alpha = false, $min = 6, $pad = '#FFFFFF') {
            $palette = get_option(self::PALETTE, array());
            $_palette = array();
            foreach ($palette as $set) {
                $color = '#' . $set[0];
                if ($alpha) {
                    $hex = str_split($set[0], 2);
                    list($r, $g, $b) = array_map('hexdec', $hex);
                    $a = round($set[1] / 100, 2);
                    $color = "rgba($r,$g,$b,$a)";
                }
                $_palette[] = $color;
            }
            return array_pad($_palette, $min, $pad);
        }

        /**
         * GeneratePress Premium integration
         * @since 1.10
         * @param array $palette
         * @return array
         */
        public function generatepress_integration($palette) {
            $_palette = $this->get_palette(get_option(self::ALPHA));
            return $_palette ? $_palette : $palette;
        }

        /**
         * OceanWP integration
         * @since 1.11
         * @param array $palette
         * @return array
         */
        public function oceanwp_integration($palette) {
            return $this->generatepress_integration($palette);
        }

        /**
         * Enqueue a stylesheet for Elementor
         * @since 1.10
         */
        public function elementor_styles() {
            wp_enqueue_style(self::KEY . '-elementor', KT_CENTRAL_PALETTE_URL . 'css/elementor.css', null, KT_CENTRAL_PALETTE);
        }

        /**
         * Elementor integration
         * @since 1.10
         * @param array $config
         * @return array
         */
        public function elementor_integration($config) {
            $config['schemes'] = array(
                'items' => array(
                    'color-picker' => array(
                        'items' => $this->elementor_palette(),
                    ),
                ),
            );
            return $config;
        }

        /**
         * Prepare colors for Elementor
         * @since 1.10
         * @return array
         */
        public function elementor_palette() {
            $palette = get_option(self::PALETTE, array());
            $_palette = array();
            for ($i = 1, $n = count($palette); $i <= $n; $i++) {
                $_palette[$i] = array('value' => '#' . $palette[$i - 1][0]);
            }
            if ($n < 6) {
                for (; $i <= 6; $i++) {
                    $_palette[$i] = array('value' => '#FFF');
                }
            }
            return $_palette;
        }

        /**
         * wpColorPicker/Iris integration
         * @since 1.7
         */
        public function iris_integration() {
            static $printed = false;
            if ($printed || !wp_script_is('wp-color-picker', 'done')) {
                return;
            }
            $printed = true;
            $palette = get_option(self::PALETTE);
            if (!$palette) {
                return;
            }
            $printed = true;
            $colors = array();
            foreach ($palette as $set) {
                $colors[] = '"#' . esc_js($set[0]) . '"';
            }
            $colors = array_pad($colors, 6, '"transparent"');
            $colors = implode(',', $colors);
            print '<script type="text/javascript">
jQuery.wp.wpColorPicker.prototype.options.palettes = [' . $colors . '];
</script>
';
        }

        /**
         * Add dynamic CSS for TinyMCE
         * @since 1.3
         */
        public function print_tinymce_style() {
            if (get_option(self::TYPE, self::DEFAULT_TYPE) == 'default') {
                return;
            }
            $map = get_option(self::MAP);
            if (!$map || !$map[4]) {
                return;
            }
            $rows = $map[4];
            print "<style type='text/css'>
.mce-grid {border-spacing: 0; border-collapse: collapse}
.mce-grid td {padding: 0}
.mce-grid td.mce-grid-cell div {border-style: solid none none solid}
.mce-grid td.mce-grid-cell:last-child div {border-right-style: solid}
.mce-grid tr:nth-child($rows) td.mce-grid-cell div,
.mce-grid tr:last-child td.mce-grid-cell div {border-bottom-style: solid}
.mce-grid tr:nth-child($rows) td {padding-bottom: 4px}
</style>";
        }

        /**
         * Pass color map to TinyMCE
         * @since 1.3
         * @param array $init Wordpress' TinyMCE inits
         * @return array
         */
        public function tinymce_integration($init) {
            $type = get_option(self::TYPE, self::DEFAULT_TYPE);
            if ($type == 'default') {
                return $init;
            }
            $map = get_option(self::MAP);
            if ($map) {
                list($map, $cols, $extra, $mono, $rows) = $map;
                if (!$rows) {
                    return $init;
                }
                $init['textcolor_map'] = $map;
                $init['textcolor_cols'] = $cols + $extra + $mono;
                $init['textcolor_rows'] = $rows;
            }
            return $init;
        }

        /**
         * Add a link to the plugin listing
         * @since 1.4
         * @param array $links Array holding HTML
         * @param string $file Current name of plugin file
         * @return array Modified array
         */
        public function add_action_link($links, $file) {
            if (plugin_basename($file) == KT_CENTRAL_PALETTE_BASENAME) {
                $links[] = '<a href="options-general.php?page=' . self::KEY . '" class="dashicons-before dashicons-admin-settings" title="' . esc_attr__('Opens the settings page for this plugin', 'kt-tinymce-color-grid') . '"> ' . esc_html__('Color Palette', 'kt-tinymce-color-grid') . '</a>';
            }
            return $links;
        }

        /**
         * Add settings page to WordPress' admin menu
         * @since 1.3
         */
        public function add_settings_page() {
            $name = __('Central Color Palette', 'kt-tinymce-color-grid');
            $hook = add_options_page($name, $name, 'manage_options', self::KEY, array($this, 'print_settings_page'));
            add_action("load-$hook", array($this, 'init_settings_page'));
        }

        /**
         * Add removable query arguments for this plugin
         * @since 1.8
         * @param array $args
         * @return array
         */
        public function add_removeable_args($args) {
            $args[] = 'kt-import-backup-error';
            $args[] = 'kt-export-backup-error';
            return $args;
        }

        /**
         * Initialize settings page
         * @since 1.4.4
         */
        public function init_settings_page() {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_settings_scripts'));
            add_filter('removable_query_args', array($this, 'add_removeable_args'));
            add_action('kt_add_luma_transformation', array($this, 'default_luma_transformations'));

            /**
             * Register luma transformations
             * @since 1.9
             */
            do_action('kt_add_luma_transformation');

            $this->save_settings();
            $this->add_help();
            $this->add_metaboxes();
        }

        /**
         * Enqueue JavaScript and CSS files
         * @since 1.3
         */
        public function enqueue_settings_scripts() {
            if (!wp_script_is('name-that-color', 'registered')) {
                /**
                 * Name that Color JavaScript
                 * @author Chirag Mehta
                 * @link http://chir.ag/projects/ntc/
                 * @license http://creativecommons.org/licenses/by/2.5/ Creative Commons Attribution 2.5
                 */
                wp_register_script('name-that-color', KT_CENTRAL_PALETTE_URL . 'js/ntc.js', null, '1.0');
            }

            wp_enqueue_script(self::KEY, KT_CENTRAL_PALETTE_URL . 'js/settings.js', array('wp-util', 'postbox', 'jquery-ui-position', 'jquery-ui-sortable', 'name-that-color'), KT_CENTRAL_PALETTE);
            wp_enqueue_style(self::KEY, KT_CENTRAL_PALETTE_URL . 'css/settings.css', null, KT_CENTRAL_PALETTE);
        }

        /**
         * Add metaboxes to settings page
         * @since 1.9
         */
        protected function add_metaboxes() {
            $boxes = array(
                'grid' => __('TinyMCE Color Picker', 'kt-tinymce-color-grid'),
                'palette' => __('Color Palette', 'kt-tinymce-color-grid'),
                'backup' => __('Backup', 'kt-tinymce-color-grid'),
            );
            foreach ($boxes as $key => $title) {
                add_meta_box("kt_{$key}_metabox", $title, array($this, "print_{$key}_metabox"));
            }
        }

        /**
         * Sanitize and saves settings
         * @since 1.7
         */
        protected function save_settings() {
            if (!wp_verify_nonce($this->get('kt_settings_nonce'), self::NONCE)) {
                return;
            }
            $status = false;
            $action = $this->get('kt_action', $this->get('kt_hidden_action'));
            $type = $this->get('kt_type');
            if (!in_array($type, $this->types)) {
                $type = self::DEFAULT_TYPE;
            }
            $visual = $type == 'palette' || $this->get('kt_visual') ? '1' : false;

            $booleans = array(
                'kt_alpha' => self::ALPHA,
                'kt_customizer' => self::CUSTOMIZER,
                'kt_elementor' => self::ELEMENTOR,
                'kt_generatepress' => self::GENERATEPRESS,
                'kt_oceanwp' => self::OCEANWP,
            );
            foreach ($booleans as $field => $option) {
                update_option($option, $this->get($field) ? '1' : false);
            }

            $palette = array();
            $colors = $this->get('kt_colors', array());
            $alphas = $this->get('kt_alphas', array());
            $names = $this->get('kt_names', array());
            foreach ($names as $i => $name) {
                $color = $this->sanitize_color($colors[$i]);
                if ($color) {
                    $name = sanitize_text_field(stripslashes($name));
                    $alpha = $this->sanitize_alpha($alphas[$i]);
                    $palette[] = array($color, $alpha, $name);
                }
            }
            $m = null;
            $l = count($palette);
            if ($action == 'add') {
                $palette[] = array('000000', '');
            } else if ($l > 0) {
                $i = $this->preg_get('~remove-(\d+)~', $action);
                if ($i !== null && key_exists($i, $palette)) {
                    array_splice($palette, $i, 1);
                }
            } else if ($l > 1 && preg_match('~sort-(\d+)-(up|down)~', $action, $m) && key_exists($m[1], $palette)) {
                $i = $j = $m[1];
                if ($m[2] == 'up' && $i > 0) {
                    $j = $i - 1;
                } else if ($m[2] == 'down' && $i < ($l - 1)) {
                    $j = $i + 1;
                }
                if ($i != $j) {
                    $temp = $palette[$i];
                    $palette[$i] = $palette[$j];
                    $palette[$j] = $temp;
                }
            }
            if ($type == 'palette' && !$palette) {
                $type = 'default';
                $visual = '';
            }
            update_option(self::TYPE, $type);
            update_option(self::VISUAL, $visual);
            update_option(self::PALETTE, $palette);

            $lumas = array('linear') + $this->get_luma_transformations('ids');

            $this->set('kt_rows', $this->rows, self::ROWS, self::DEFAULT_ROWS);
            $this->set('kt_cols', $this->columns, self::COLS, self::DEFAULT_COLS);
            $this->set('kt_luma', $lumas, self::LUMA, self::DEFAULT_LUMA);
            $this->set('kt_blocks', $this->blocks, self::BLOCKS, self::DEFAULT_BLOCKS);
            $this->set('kt_block_size', $this->sizes, self::SIZE, self::DEFAULT_SIZE);
            $this->set('kt_axis', $this->axes, self::AXIS, self::DEFAULT_AXIS);
            $this->set('kt_spread', $this->spread, self::SPREAD, self::DEFAULT_SPREAD);
            $this->set('kt_clamp', $this->clamp, self::CLAMP, self::DEFAULT_CLAMP);
            $clamps = intval($this->get('kt_clamps'));
            if ($clamps < 4 || $clamps > 18) {
                $clamps = self::DEFAULT_CLAMPS;
            }
            update_option(self::CLAMPS, $clamps);

            switch ($action) {
                case 'export-backup':
                    $status = $this->export_backup();
                    break;
                case 'import-backup':
                    $status = $this->import_backup();
                    break;
                default: $action = 'save';
            }

            if (!$status || $status == 'ok') {
                $this->render_map();
            }

            $file_actions = array('import-backup', 'export-backup');
            if ($status && in_array($action, $file_actions)) {
                $url = add_query_arg("kt-{$action}-error", $status);
                wp_redirect($url);
                exit;
            }
            wp_redirect(add_query_arg('updated', $action == 'save' ? '1' : false));
            exit;
        }

        /**
         * Return all options as an array
         * @since 1.8
         * @since 1.9 Partial options
         * @param $parts
         * @return array
         */
        protected function default_options($parts = null) {
            $options = array(
                self::ACTIVE_VERSION => KT_CENTRAL_PALETTE
            );
            $settings = array(
                self::VISUAL => false,
                self::CUSTOMIZER => false,
                self::ELEMENTOR => false,
                self::OCEANWP => false,
                self::ALPHA => false,
                self::TYPE => self::DEFAULT_TYPE,
                self::ROWS => self::DEFAULT_ROWS,
                self::COLS => self::DEFAULT_COLS,
                self::LUMA => self::DEFAULT_LUMA,
                self::BLOCKS => self::DEFAULT_BLOCKS,
                self::SIZE => self::DEFAULT_SIZE,
                self::AXIS => self::DEFAULT_AXIS,
                self::SPREAD => self::DEFAULT_SPREAD,
                self::CLAMP => self::DEFAULT_CLAMP,
                self::CLAMPS => self::DEFAULT_CLAMPS
            );
            if ($parts) {
                foreach ($parts as $part) {
                    switch ($part) {
                        case 'settings':
                            $options += $settings;
                            break;
                        case 'palette':
                            $options[self::PALETTE] = array();
                            break;
                    }
                }
                return $options;
            }
            return array(
                self::ACTIVE_VERSION => KT_CENTRAL_PALETTE,
                self::PALETTE => array()
                ) + $settings;
        }

        /**
         * Import settings from file upload
         * @since 1.8
         * @return string
         */
        protected function import_backup() {
            if (isset($_FILES['kt_upload'])) {
                $file = $_FILES['kt_upload'];
                $status = $this->verify_upload($file);
                if ($status != 'ok') {
                    return $status;
                }
                $base64 = file_get_contents($file['tmp_name']);
            } else if (isset($_REQUEST['kt_base64'])) {
                $base64 = $_REQUEST['kt_base64'];
            } else {
                return 'no-import';
            }

            if (substr($base64, 0, 1) == '#') {
                $colors = preg_split('~[^#0-9A-Fa-f]+~', $base64);
                $palette = array();
                foreach ($colors as $color) {
                    $color = $this->sanitize_color($color);
                    if ($color) {
                        $palette[] = array($color, '');
                    }
                }
                if ($palette) {
                    update_option(self::PALETTE, $palette);
                    $this->render_map();
                    return 'ok';
                }
                return;
            }
            $crc32 = substr($base64, -8);
            if (strlen($crc32) != 8) {
                return 'empty';
            }
            $base64 = substr($base64, 0, -8);
            if (dechex(crc32($base64)) != $crc32) {
                return 'corrupt';
            }
            $json = base64_decode($base64);
            $options = json_decode($json, true);
            if (!is_array($options)) {
                return 'funny';
            }
            if (!isset($options[self::ACTIVE_VERSION])) {
                $options[self::ACTIVE_VERSION] = 180;
            }
            $this->update_import($options);
            $names = array_keys($this->default_options());
            foreach ($names as $name) {
                if (isset($options[$name])) {
                    update_option($name, $options[$name]);
                }
            }
            return 'ok';
        }

        /**
         * Check a file upload
         * @since 1.9
         * @param array $file Element of $_FILES
         * @return string Status code
         */
        protected function verify_upload($file) {
            $upload_error = array(
                false, 'size-php', 'size', 'partially',
                'no-upload', false, 'tmp', 'fs', 'ext'
            );
            if (isset($file['error']) && $file['error']) {
                return $upload_error[$file['error']];
            }
            if (!is_uploaded_file($file['tmp_name'])) {
                return 'no-upload';
            }
            $size = filesize($file['tmp_name']);
            if (!$size) {
                return 'empty';
            }
            if ($size > self::MAX_FILE_SIZE) {
                return 'size';
            }
            return 'ok';
        }

        /**
         * Update procedures for export/import file
         * @since 1.9
         * @param array $options passed by reference
         */
        protected function update_import(&$options) {
            $version = $options[self::ACTIVE_VERSION];
            unset($options[self::ACTIVE_VERSION]);
            while ($version != KT_CENTRAL_PALETTE) {
                switch ($version) {
                    case 19:
                    case 191:
                    case 192:
                    case 193:
                        foreach ($options['kt_color_grid_palette'] as &$set) {
                            $set = array($set[0], 100, $set[1]);
                        }
                        $version = 1100;
                        break;
                    default:
                        $version = KT_CENTRAL_PALETTE;
                }
            }
        }

        /**
         * Export settings and trigger a file download
         * @since 1.8
         * @return string
         */
        protected function export_backup() {
            $parts = $this->get('kt_export');
            if (!is_array($parts) || !$parts) {
                return 'no-export';
            }
            $options = $this->default_options($parts);
            foreach ($options as $name => $default) {
                $options[$name] = get_option($name, $default);
            }
            $json = json_encode($options);
            if (!$json) {
                return 'json';
            }
            $base64 = base64_encode($json);
            if (!$base64) {
                return 'base64';
            }
            $base64 .= dechex(crc32($base64));
            $blogname = get_bloginfo('name');
            $blogname = preg_replace(array('~"~', '~[\s-]+~'), array('', '-'), $blogname);
            $blogname = trim($blogname, '-_.');
            if ($blogname) {
                $blogname = "_$blogname";
            }
            $filename = "colorgrid$blogname.bak";
            header('Content-Type: plain/text');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            print $base64;
            exit;
        }

        /**
         * Pass a HTTP request value through a filter and store it as option
         * @since 1.7
         * @param string $key
         * @param array $constrain
         * @param string $option
         * @param mixed $default
         * @return mixed
         */
        protected function set($key, $constrain, $option, $default) {
            $value = $this->get($key, $default);
            $value = in_array($value, $constrain) ? $value : $default;
            update_option($option, $value);
            return $value;
        }

        /**
         * Renders color map
         * @since 1.7
         */
        protected function render_map() {
            switch (get_option(self::TYPE, self::DEFAULT_TYPE)) {
                case 'palette':
                    $map = $this->render_palette();
                    break;
                case 'rainbow':
                    $map = $this->render_rainbow();
                    break;
                case 'block':
                    $map = $this->render_blocks();
                    break;
                default: return;
            }
            update_option(self::MAP, $map);
        }

        /**
         * Chunk palette into columns of constant size
         * @since 1.7
         * @return array [palette, rows, cols]
         */
        protected function prepare_palette() {
            $palette = array();
            list($rows, $cols) = $this->get_palette_size();
            if (get_option(self::VISUAL)) {
                $palette = get_option(self::PALETTE, array());
                if ($palette) {
                    $palette = array_chunk($palette, $rows);
                    $last = count($palette) - 1;
                    $padded = array_pad($palette[$last], $rows, array('FFFFFF', ''));
                    $palette[$last] = $padded;
                }
            }
            return array($palette, $rows, $cols);
        }

        /**
         * Get palette size depending on its current type
         * @since 1.9
         * @return array [rows, cols]
         */
        protected function get_palette_size() {
            switch (get_option(self::TYPE, self::DEFAULT_TYPE)) {
                case 'palette':
                    $count = count(get_option(self::PALETTE, array()));
                    if ('even' == get_option(self::SPREAD, self::DEFAULT_SPREAD)) {
                        $cols = ceil(sqrt($count));
                        $rows = ceil($count / $cols);
                        return array($rows, $cols);
                    }
                    $fixed = get_option(self::CLAMPS, self::DEFAULT_CLAMPS);
                    $dynamic = ceil($count / $fixed);
                    if ('cols' == get_option(self::CLAMP, self::DEFAULT_CLAMP)) {
                        return array($dynamic, $fixed);
                    }
                    return array($fixed, $dynamic);
                case 'rainbow':
                    $rows = get_option(self::ROWS, self::DEFAULT_ROWS);
                    $cols = get_option(self::COLS, self::DEFAULT_COLS);
                    return array($rows, $cols);
                case 'block':
                    $size = get_option(self::SIZE, self::DEFAULT_SIZE);
                    $blocks = get_option(self::BLOCKS, self::DEFAULT_BLOCKS);
                    return array($size * self::GROUPS, $size * $blocks / self::GROUPS);
            }
            return array(self::TINYMCE_ROWS, self::TINYMCE_COLS);
        }

        /**
         * Add a row from the palette to the color map
         * @since 1.7
         * @param array $map passed by reference
         * @param array $palette passed by reference
         * @param int $row
         */
        protected function add_palette(&$map, &$palette, $row) {
            $cols = count($palette);
            for ($col = 0; $col < $cols; $col++) {
                $color = $palette[$col][$row];
                list($color, $name) = array_map('esc_js', $color);
                $map[] = '"' . $color . '","' . $name . '"';
            }
        }

        /**
         * Add a monocrome/grayscale color to the color map
         * @since 1.7
         * @param array $map passed by reference
         * @param int $row
         * @param int $rows
         */
        protected function add_monocroma(&$map, $row, $rows) {
            if ($row == $rows - 1) {
                return;
            }
            $x = $this->float2hex($row / ($rows - 2));
            $map[] = '"' . "$x$x$x" . '",""';
        }

        /**
         * Render TinyMCE palette color map
         * @since 1.8
         * @return array
         */
        protected function render_palette() {
            list($palette, $rows, $cols) = $this->prepare_palette();
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_palette($map, $palette, $row);
            }
            $map = '[' . implode(',', $map) . ']';
            return array($map, $cols, 0, 0, $rows);
        }

        /**
         * Render TinyMCE block color map
         * @since 1.7
         * @return array
         */
        protected function render_blocks() {
            $blocks = get_option(self::BLOCKS, self::DEFAULT_BLOCKS);
            $size = get_option(self::SIZE, self::DEFAULT_SIZE);
            $axis = get_option(self::AXIS, self::DEFAULT_AXIS);
            $pattern = strtr($axis, array(
                'r' => '%1$s',
                'g' => '%2$s',
                'b' => '%3$s',
            ));
            $per_group = $blocks / self::GROUPS;
            $chunks = $square = array();
            for ($i = 0, $step = 1 / ($size - 1); $i < $size; $i++) {
                $square[] = $this->float2hex($i * $step);
            }
            for ($i = 0, $step = 1 / ($blocks - 1); $i < $blocks; $i++) {
                $chunks[] = $this->float2hex($i * $step);
            }
            list($palette, $rows, $cols) = $this->prepare_palette();
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_palette($map, $palette, $row);

                $b = $square[$row % $size];
                $shift = floor($row / $size) * $per_group;
                for ($col = 0; $col < $cols; $col++) {
                    $g = $square[$col % $size];
                    $r = $chunks[floor($col / $size) + $shift];
                    $map[] = '"' . sprintf($pattern, $r, $g, $b) . '",""';
                }

                $this->add_monocroma($map, $row, $rows);
            }
            $map = '[' . implode(',', $map) . ']';
            return array($map, $cols, count($palette), 1, $rows);
        }

        /**
         * Render TinyMCE rainbow color map
         * @since 1.7
         * @return array
         */
        protected function render_rainbow() {
            list($palette, $rows, $cols) = $this->prepare_palette();

            $rgb = array();
            for ($i = 0; $i < $cols; $i++) {
                $rgb[] = $this->hue2rgb($i / $cols);
            }

            $map = array();
            $type = get_option(self::LUMA, self::DEFAULT_LUMA);
            for ($row = 0; $row < $rows; $row++) {
                $this->add_palette($map, $palette, $row);

                $luma = 2 * ($row + 1) / ($rows + 1) - 1;
                $luma = $this->transform_luma($luma, $type);
                for ($col = 0; $col < $cols; $col++) {
                    $_rgb = $this->apply_luma($luma, $rgb[$col]);
                    $map[] = '"' . $this->rgb2hex($_rgb) . '",""';
                }

                $this->add_monocroma($map, $row, $rows);
            }
            $map = '[' . implode(',', $map) . ']';
            return array($map, $cols, count($palette), 1, $rows);
        }

        /**
         * Add help to settings page
         * @since 1.7
         */
        protected function add_help() {
            $screen = get_current_screen();
            $link = '<a href="%1$s" target="_blank" title="%3$s">%2$s</a>';
            $rgb_url = vsprintf($link, array(
                _x('https://en.wikipedia.org/wiki/RGB_color_model', 'URL to wiki page about RGB', 'kt-tinymce-color-grid'),
                __('RGB cube', 'kt-tinymce-color-grid'),
                __('Wikipedia article about RGB color space', 'kt-tinymce-color-grid'),
            ));
            $hsl_link = vsprintf($link, array(
                _x('https://en.wikipedia.org/wiki/HSL_and_HSV', 'URL to wiki page about HSL', 'kt-tinymce-color-grid'),
                __('HSL space', 'kt-tinymce-color-grid'),
                __('Wikipedia article about HSL color space', 'kt-tinymce-color-grid'),
            ));
            $screen->add_help_tab(array(
                'id' => 'grid',
                'title' => __('TinyMCE Color Picker', 'kt-tinymce-color-grid'),
                'content' => '
<p>' . __("<strong>Default</strong> leaves TinyMCE's color picker untouched.", 'kt-tinymce-color-grid') . '</p>
<p>' . __("<strong>Palette</strong> only takes the colors defined by the Central Palette.") . '</p>
<p>' . sprintf(__("<strong>Rainbow</strong> takes hue and lightness components from the %s and thus creates a rainbow. The <strong>Luma</strong> option controls how the lightness for each hue is spread.", 'kt-tinymce-color-grid'), $hsl_link) . '</p>
<p>' . sprintf(__("<strong>Blocks</strong> takes planes from the %s and places them next to one another. <strong>Block Count</strong> controls how many planes are taken, and <strong>Block Size</strong> determines their size.", 'kt-tinymce-color-grid'), $rgb_url) . '</p>'
            ));
            $screen->add_help_tab(array(
                'id' => 'palette',
                'title' => __('Color Palette', 'kt-tinymce-color-grid'),
                'content' => '
<p>' . __('You can create a color palette and include it to the Visual Editor and/or the Theme Customizer.', 'kt-tinymce-color-grid') . '</p>
<p>' . __('<strong>Add to Visual Editor</strong> adds the palette to the color picker of the text editor of posts and pages. This only works if you choose a color grid other than <strong>Default</strong>.', 'kt-tinymce-color-grid') . '</p>
<p>' . __("<strong>Add to Theme Customizer</strong> makes the palette available to the color picker of the Theme Customizer. This works by altering WordPress' color picker so every plugin using it receives the palette as well.", 'kt-tinymce-color-grid') . '</p>'
            ));
            $screen->add_help_tab(array(
                'id' => 'backup',
                'title' => __('Backup', 'kt-tinymce-color-grid'),
                'content' => '
<p>' . __('If you want to <strong>export</strong> all settings and your palette to a file you can do so by simply clicking <strong>Download Backup</strong> at the bottom of the editor and you will be prompted with a download.', 'kt-tinymce-color-grid') . '</p>
<p>' . __('Likewise you can <strong>import</strong> such a file into this or another install of WordPress by clicking <strong>Choose Backup</strong>. All current settings and your palette will be overwritten, so make sure you made a backup.', 'kt-tinymce-color-grid') . '</p>'
            ));
            $screen->add_help_tab(array(
                'id' => 'aria',
                'title' => __('Accessibility', 'kt-tinymce-color-grid'),
                'content' => '
<p>' . __('The palette editor consists of a toolbar and a list of entries. Every entry has a color picker, two text fields &mdash; one holding a hexadecimal representation of the color, and one for the name of the entry &mdash; and lastly a button to remove the entry.', 'kt-tinymce-color-grid') . '</p>
<p>' . __('You can reorder an entry by pressing the <strong>page</strong> keys. To delete an entry press the <strong>delete</strong> or <strong>backspace</strong> key. If a color picker has focus use the <strong>arrow</strong> keys, and <strong>plus</strong> and <strong>minus</strong> to change the color.', 'kt-tinymce-color-grid') . '</p>'
            ));
            $plugin_url = esc_url(_x('https://wordpress.org/plugins/kt-tinymce-color-grid', 'URL to plugin site', 'kt-tinymce-color-grid'));
            $support_url = esc_url(_x('https://wordpress.org/support/plugin/kt-tinymce-color-grid', 'URL to support forums', 'kt-tinymce-color-grid'));
            $screen->set_help_sidebar('
<p><strong>' . esc_html__('For more information:', 'kt-tinymce-color-grid') . '</strong></p>
<p><a href="' . $plugin_url . '" target="_blank">' . esc_html__('Visit plugin site', 'kt-tinymce-color-grid') . '</a></p>
<p><a href="' . $support_url . '" target="_blank">' . esc_html__('Support Forums', 'kt-tinymce-color-grid') . '</a></p>');
        }

        /**
         * Render settings page
         * @since 1.3
         */
        public function print_settings_page() {
            $head = $this->version_compare('<', '4.3') ? 'h2' : 'h1';
            print "
<div class='wrap'>
  <$head>" . esc_html__('Settings', 'kt-tinymce-color-grid') . ' &rsaquo; ' . esc_html__('Central Color Palette', 'kt-tinymce-color-grid') . "</$head>";
            $this->print_settings_error();

            $support_alpha = $this->supports('alpha-channel') && get_option(self::ALPHA) ? ' class="support-alpha"' : '';

            print "
  <form id='kt_color_grid'$support_alpha action='options-general.php?page=" . self::KEY . "' method='post' enctype='multipart/form-data'>
    <input type='hidden' name='MAX_FILE_SIZE' value='" . self::MAX_FILE_SIZE . "'/>
    <input type='hidden' id='kt_action' name='kt_hidden_action' value='save'/>";
            wp_nonce_field(self::NONCE, 'kt_settings_nonce', false);
            wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
            wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
            print "
    <div class='metabox-holder'>
      <div class='postbox-container'>";
            $type = get_option(self::TYPE, self::DEFAULT_TYPE);
            $this->types = array(
                'default' => __('Default', 'kt-tinymce-color-grid'),
                'palette' => __('Color Palette', 'kt-tinymce-color-grid'),
                'rainbow' => __('Rainbow', 'kt-tinymce-color-grid'),
                'block' => __('Blocks', 'kt-tinymce-color-grid'),
            );
            foreach ($this->types as $value => $label) {
                $id = "kt_type_$value";
                $label = esc_html($label);
                $checked = $value == $type ? ' checked="checked"' : '';
                print "
        <input type='radio' id='$id' name='kt_type' value='$value'$checked/>
        <label for='$id' class='screen-reader-text'>$label</label>";
            }

            $context = 'advanced';
            do_action('add_meta_boxes', null, $context, null);
            do_meta_boxes(null, $context, null);

            $picker_label = esc_attr__('Visual Color Picker', 'kt-tinymce-color-grid');
            $save_key = _x('S', 'accesskey for saving', 'kt-tinymce-color-grid');
            $save_label = $this->underline_accesskey(__('Save', 'kt-tinymce-color-grid'), $save_key);
            print "
      </div>
    </div>
    <p class='submit'>
      <button type='submit' id='kt_save' name='kt_action' value='save' tabindex='9' class='button button-primary button-large' accesskey='$save_key'>$save_label</button>
    </p>
    <div id='kt_picker' class='hidden' aria-hidden='true' aria-label='$picker_label'></div>
  </form>
</div>";
        }

        /**
         * Render settings error
         * @since 1.9
         */
        public function print_settings_error() {
            $feedback = '';
            if (isset($_GET['kt-import-backup-error'])) {
                $error = $_GET['kt-import-backup-error'];
                $import_errors = array(
                    'ok' => __('Backup successfuly imported.', 'kt-tinymce-color-grid'),
                    'no-import' => __('No data to process.', 'kt-tinymce-color-grid'),
                    'corrupt' => __('The uploaded file appears to be damaged or was simply not exported by this plugin.', 'kt-tinymce-color-grid'),
                    'funny' => __('The uploaded file does not contain any useable data.', 'kt-tinymce-color-grid'),
                    'no-upload' => __('No file was uploaded.', 'kt-tinymce-color-grid'),
                    'empty' => __('The uploaded file is empty.', 'kt-tinymce-color-grid'),
                    'partially' => __('The uploaded file was only partially uploaded.', 'kt-tinymce-color-grid'),
                    'size-php' => __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'kt-tinymce-color-grid'),
                    'size' => sprintf(__('The uploaded file is too big. It is limited to %s.', 'kt-tinymce-color-grid'), size_format(self::MAX_FILE_SIZE)),
                    'tmp' => __('Missing a temporary folder.', 'kt-tinymce-color-grid'),
                    'fs' => __('Failed to write file to disk.', 'kt-tinymce-color-grid'),
                    'ext' => __('File upload stopped by PHP extension.', 'kt-tinymce-color-grid'),
                );
                $feedback = __('Import failed.', 'kt-tinymce-color-grid');
                if (isset($import_errors[$error])) {
                    $feedback = $import_errors[$error];
                }
            } else if (isset($_GET['kt-export-backup-error'])) {
                $error = $_GET['kt-export-backup-error'];
                $export_errors = array(
                    'no-export' => __('Please select which parts you would like to backup.', 'kt-tinymce-color-grid'),
                    'json' => __('Could not pack settings into JSON.', 'kt-tinymce-color-grid'),
                    'base64' => __('Could not convert settings.', 'kt-tinymce-color-grid'),
                );
                $feedback = __('Export failed.', 'kt-tinymce-color-grid');
                if (isset($export_errors[$error])) {
                    $feedback = $export_errors[$error];
                }
            }
            if ($feedback) {
                $type = $error == 'ok' ? 'updated' : 'error';
                print "<div id='setting-error-import' class='$type settings-error notice is-dismissible'><p><strong>$feedback</strong></p></div>";
            }
        }

        /**
         * Print grid metabox
         * @since 1.9
         */
        public function print_grid_metabox() {
            $_cols = get_option(self::COLS, self::DEFAULT_COLS);
            $_rows = get_option(self::ROWS, self::DEFAULT_ROWS);
            $_blocks = get_option(self::BLOCKS, self::DEFAULT_BLOCKS);
            $_size = get_option(self::SIZE, self::DEFAULT_SIZE);
            $_axis = get_option(self::AXIS, self::DEFAULT_AXIS);
            $_spread = get_option(self::SPREAD, self::DEFAULT_SPREAD);
            $_clamp = get_option(self::CLAMP, self::DEFAULT_CLAMP);
            $_clamps = get_option(self::CLAMPS, self::DEFAULT_CLAMPS);

            $luma_map = array(
                'linear' => __('Linear', 'kt-tinymce-color-grid'),
                ) + $this->get_luma_transformations('names');
            $size = array(
                4 => __('small', 'kt-tinymce-color-grid'),
                5 => __('medium', 'kt-tinymce-color-grid'),
                6 => __('big', 'kt-tinymce-color-grid'),
            );
            $axes = array(
                'rgb' => __('Blue-Green', 'kt-tinymce-color-grid'),
                'rbg' => __('Green-Blue', 'kt-tinymce-color-grid'),
                'grb' => __('Blue-Red', 'kt-tinymce-color-grid'),
                'brg' => __('Red-Blue', 'kt-tinymce-color-grid'),
                'gbr' => __('Green-Red', 'kt-tinymce-color-grid'),
                'bgr' => __('Red-Green', 'kt-tinymce-color-grid'),
            );
            $clamp = array(
                'row' => __('row', 'kt-tinymce-color-grid'),
                'column' => __('column', 'kt-tinymce-color-grid'),
            );

            $cols = $this->selectbox('kt_cols', $this->columns, $_cols);
            $rows = $this->selectbox('kt_rows', $this->rows, $_rows);
            $luma = '';
            if (count($luma_map) > 1) {
                $luma_label = esc_html__('Luma', 'kt-tinymce-color-grid');
                $current_luma = get_option(self::LUMA, self::DEFAULT_LUMA);
                $luma = $this->selectbox('kt_luma', $luma_map, $current_luma);
                $luma = "
  <label for='kt_luma'>$luma_label</label>$luma";
            }
            $blocks = $this->selectbox('kt_blocks', $this->blocks, $_blocks);
            $size = $this->selectbox('kt_block_size', $size, $_size);
            $axes = $this->selectbox('kt_axis', $axes, $_axis);

            $rows_label = esc_html__('Rows', 'kt-tinymce-color-grid');
            $cols_label = esc_html__('Columns', 'kt-tinymce-color-grid');
            $blocks_label = esc_html__('Block Count', 'kt-tinymce-color-grid');
            $size_label = esc_html__('Block Size', 'kt-tinymce-color-grid');
            $axis_label = esc_html__('Plane Axis', 'kt-tinymce-color-grid');

            print "
<p><label>Type</label>
  <span class='button-group type-chooser'>";
            foreach ($this->types as $value => $label) {
                $id = "kt_type_$value";
                $label = esc_html($label);
                print "
    <label for='$id' class='button'>$label</label>
    <label for='$id' class='button button-primary'>$label</label>";
            }
            print "
  </span>
</p>";

            $clamp = $this->selectbox('kt_clamp', $clamp, $_clamp);
            $clamps = "<input type='number' id='kt_clamps' name='kt_clamps' min='4' max='18' step='1' value='$_clamps'/>";
            $spread = array(
                'even' => esc_html__('Spread colors evenly', 'kt-tinymce-color-grid'),
                'odd' => sprintf(__('Fill each %1$s with %2$s colors', 'kt-tinymce-color-grid'), $clamp, $clamps),
            );
            foreach ($spread as $value => $label) {
                $id = "kt_spread_$value";
                $checked = $_spread == $value ? " checked='checked'" : '';
                print "
<p class='palette-options'>
  <input type='radio' id='$id' name='kt_spread' value='$value'$checked/>
  <label for='$id'>$label</label>
</p>";
            }

            print "
<p class='rainbow-options'>
  <label for='kt_rows'>$rows_label</label>$rows
  <label for='kt_cols'>$cols_label</label>$cols$luma
</p>
<p class='block-options'>
  <label for='kt_blocks'>$blocks_label</label>$blocks
  <label for='kt_block_size'>$size_label</label>$size
  <label for='kt_axis'>$axis_label</label>$axes
</p>";
        }

        /**
         * Print editor metabox
         * @since 1.9
         */
        public function print_palette_metabox() {
            $_type = get_option(self::TYPE, self::DEFAULT_TYPE);
            $_visual = get_option(self::VISUAL);
            $_customizer = get_option(self::CUSTOMIZER);
            if ($_type == 'palette') {
                $_visual = true;
            }
            $visual_checked = $_visual ? ' checked="checked"' : '';
            $customizer_checked = $_customizer ? ' checked="checked"' : '';
            $add_key = _x('A', 'accesskey for adding color', 'kt-tinymce-color-grid');
            $_add = __('Add Color', 'kt-tinymce-color-grid');
            $add_label = esc_html($_add);
            $add_title = esc_attr($_add);

            $autoname = $this->cookie(self::AUTONAME, self::DEFAULT_AUTONAME);
            $empty = esc_attr__('Palette is empty', 'kt-tinymce-color-grid');
            $_autoname = esc_html__('Automatic Names', 'kt-tinymce-color-grid');
            $checked = $autoname ? ' checked="checked"' : '';
            $autoname = $autoname ? ' class="autoname"' : '';

            print "
<p id='kt_visual_option'>
  <input type='checkbox' id='kt_visual' name='kt_visual' tabindex='9' value='1'$visual_checked />
  <label for='kt_visual'>" . esc_html__('Add to Visual Editor', 'kt-tinymce-color-grid') . "</label>
</p>
<p>
  <input type='checkbox' id='kt_customizer' name='kt_customizer' tabindex='10' value='1'$customizer_checked />
  <label for='kt_customizer'>" . esc_html__('Add to Theme Customizer', 'kt-tinymce-color-grid') . "</label>
</p>";

            $integrations = array(
                array('elementor', self::ELEMENTOR, __('Elementor', 'kt-tinymce-color-grid')),
                array('generatepress', self::GENERATEPRESS, __('GeneratePress Premium', 'kt-tinymce-color-grid')),
                array('oceanwp', self::OCEANWP, __('OceanWP', 'kt-tinymce-color-grid')),
            );

            $_add_to = __('Integrate with %s', 'kt-tinymce-color-grid');
            foreach ($integrations as $integration) {
                list($feature, $option_name, $label) = $integration;
                if (!$this->supports($feature)) {
                    continue;
                }
                $checked = get_option($option_name) ? ' checked="checked"' : '';
                $label = esc_html(sprintf($_add_to, $label));
                printf('
<p>
  <input type="checkbox" id="kt_%1$s" name="kt_%1$s" tabindex="10" value="1"%3$s />
  <label for="kt_%1$s">%2$s</label>
</p>', $feature, $label, $checked);
            }

            print "
<div id='kt_toolbar' role='toolbar'>
  <button id='kt_add' type='submit' tabindex='8' name='kt_action' value='add' class='button' aria-controls='kt_colors' accesskey='$add_key' title='$add_title'>
    <span class='dashicons dashicons-plus-alt2'></span>
    <span class='screen-reader-text'>$add_label</span>
  </button>";

            if ($this->supports('alpha-channel')) {
                $_alpha = esc_html__('Transparency', 'kt-tinymce-color-grid');
                $alpha_checked = get_option(self::ALPHA) ? ' checked="checked"' : '';
                print "
  <span class='switch-wrap alignright'>
    <input type='checkbox' id='kt_alpha' name='kt_alpha' value='1'$alpha_checked/>
    <label for='kt_alpha'>$_alpha</label>
  </span>";
            }

            print "
  <span class='switch-wrap alignright hide-if-no-js'>
    <input type='checkbox' id='kt_autoname'$checked/>
    <label for='kt_autoname'>$_autoname</label>
  </span>
</div>
<div id='kt_color_editor' data-empty='$empty'$autoname>";

            $list_entry = vsprintf('<div class="picker" tabindex="2" aria-grabbed="false">
  <span class="sort hide-if-js">
    <button type="submit" name="kt_action" value="sort-%5$s-up" class="sort-up button" tabindex="3" title="%7$s">
      <i class="dashicons dashicons-arrow-up-alt2"></i>
      <span class="screen-reader-text">%7$s</span>
    </button>
    <button type="submit" name="kt_action" value="sort-%5$s-down" class="sort-down button" tabindex="3" title="%8$s">
      <i class="dashicons dashicons-arrow-down-alt2"></i>
      <span class="screen-reader-text">%8$s</span>
    </button>
  </span>
  <button type="button" class="color button hide-if-no-js" tabindex="3" aria-haspopup="true" aria-controls="kt_picker" aria-describedby="contextual-help-link" aria-label="%9$s">
    <span class="sample">
      <span class="rgb" style="background-color:%1$s"></span>
      <span class="rgba" style="background-color:%2$s"></span>
    </span>
  </button>
  <span class="sample hide-if-js">
    <span class="rgb" style="background-color:%1$s"></span>
    <span class="rgba" style="background-color:%2$s"></span>
  </span>
  <span class="screen-reader-text">%10$s</span>
  <input class="hex" type="text" name="kt_colors[]" tabindex="3" value="%1$s" maxlength="7" placeholder="#RRGGBB" autocomplete="off" aria-label="%10$s" pattern="\s*#?([a-fA-F0-9]{3}){1,2}\s*" required="required" title="%15$s" />
  <span class="screen-reader-text">%11$s</span>
  <input class="alpha" type="number" name="kt_alphas[]" tabindex="3" value="%3$s" min="0" max="100" step="1" autocomplete="off" aria-label="%11$s" title="%16$s" />
  <span class="screen-reader-text">%13$s</span>
  <input class="name%6$s" type="text" name="kt_names[]" value="%4$s" tabindex="3" placeholder="%12$s" aria-label="%13$s" />
  <button type="button" class="autoname button hide-if-no-js" title="%17$s">
    <i class="dashicons dashicons-editor-break"></i>
    <span class="screen-reader-text">%17$s</span>
  </button>
  <button type="submit" name="kt_action" value="remove-%5$s" tabindex="3" class="remove button title="%14$s"">
    <i class="dashicons dashicons-no-alt"></i>
    <span class="screen-reader-text">%14$s</span>
  </button>
</div>', array(// hex    rgba   alpha    name   index   autofill
                '%1$s', '%2$s', '%3$s', '%4$s', '%5$s', '%6$s',
                6 => esc_html__('Move up', 'kt-tinymce-color-grid'),
                7 => esc_html__('Move down', 'kt-tinymce-color-grid'),
                8 => esc_attr__('Color Picker', 'kt-tinymce-color-grid'),
                9 => esc_attr__('Hexadecimal Color', 'kt-tinymce-color-grid'),
                10 => esc_attr__('Transparency', 'kt-tinymce-color-grid'),
                11 => esc_attr__('Unnamed Color', 'kt-tinymce-color-grid'),
                12 => esc_attr__('Name of Color', 'kt-tinymce-color-grid'),
                13 => esc_html__('Delete', 'kt-tinymce-color-grid'),
                14 => esc_attr__('Three hexadecimal numbers between 00 and FF', 'kt-tinymce-color-grid'),
                15 => esc_attr__('Transparency between 0 and 100', 'kt-tinymce-color-grid'),
                16 => esc_attr__('Automatic Name', 'kt-tinymce-color-grid'),
            ));

            $palette = get_option(self::PALETTE, array());
            foreach ($palette as $index => $set) {
                list($color, $alpha, $name) = array_map('esc_attr', $set);
                $autofill = $name ? '' : ' autoname';
                list($r, $g, $b) = array_map('hexdec', str_split($color, 2));
                $a = $alpha / 100;
                printf($list_entry, "#$color", "rgba($r,$g,$b,$a)", $alpha, $name, $index, $autofill);
            }

            printf("</div>
<script type='text/template' id='tmpl-kt_list_entry'>$list_entry</script>", '#000000', 'rgba(0,0,0,1)', 100, '', 'x', ' autoname');
        }

        /**
         * Print backup metabox
         * @since 1.9
         */
        public function print_backup_metabox() {
            print '
<p>' . esc_html__('What would you like to backup?', 'kt-tinymce-color-grid') . "</p>
<p id='kt_export'>";
            $parts = array(
                'settings' => __('Settings', 'kt=tinymce-color-grid'),
                'palette' => __('Palette', 'kt=tinymce-color-grid'),
            );
            $export_disabled = true;
            foreach ($parts as $key => $label) {
                $checked = $this->cookie("kt_export_$key", 1) ? ' checked="checked"' : '';
                if ($export_disabled && $checked) {
                    $export_disabled = false;
                }
                print "
  <input type='checkbox' id='kt_export_$key' name='kt_export[]' value='$key'$checked/>
  <label for='kt_export_$key'>$label</label>";
            }
            $upload = esc_html__('Upload Backup', 'kt-tinymce-color-grid');
            $export_disabled = $export_disabled ? ' disabled="disabled"' : '';
            print "</p>
<p class='devider'><button type=submit' id='kt_action_export' class='button' name='kt_action' value='export-backup' tabindex='9'$export_disabled>" . esc_html__('Download Backup', 'kt-tinymce-color-grid') . "</button></p>";
            if (!$this->supports('upload')) {
                print "
<p>" . esc_html__('Your device is not supporting file uploads. Open your backup in a simple text editor and paste its content into this textfield.', 'kt-tinymce-color-grid') . "</p>
<p><textarea name='kt_base64' class='widefat' rows='5'></textarea></p>
<p><button type='submit' class='button' name='kt_action' value='import-backup' tabindex='10'>$upload</button></p>";
            } else {
                print "
<p>" . esc_html__('Here you can upload a backup.', 'kt-tinymce-color-grid') . "</p>
<p class='hide-if-no-js'>
  <label id='kt_upload_label' for='kt_upload' class='button' tabindex='10'>
    <span class='spinner'></span>
    <span class='label'>" . esc_html__('Choose Backup', 'kt-tinymce-color-grid') . "&hellip;</span>
    <span class='loading'>" . esc_html__('Uploading', 'kt-tinymce-color-grid') . "&hellip;</span>
  </label>
</p>
<p class='hide-if-js'>
  <input type='file' id='kt_upload' name='kt_upload' accept='.bak,text/plain' data-action='import-backup'/>";
            }
            print '
</p>';
        }

        /**
         * Highlight an accesskey inside a translated string
         * @since 1.4.4
         * @param string $string Translated string
         * @param string $key Accesskey
         * @return string
         */
        protected function underline_accesskey($string, $key) {
            $pattern = '/(' . preg_quote($key, '/') . ')/i';
            return preg_replace($pattern, '<u>$1</u>', esc_html($string), 1);
        }

        /**
         * Generate HTML markup of a selectbox
         * @since 1.7
         * @param string $name
         * @param array $data
         * @param mixed $selected
         * @param bool $disabled
         * @return string
         */
        protected function selectbox($name, $data, $selected = null, $disabled = false) {
            $options = '';
            if (key($data) === 0) {
                $data = array_combine($data, $data);
            }
            foreach ($data as $value => $label) {
                $sel = $value == $selected ? ' selected="selected"' : '';
                $value = esc_attr($value);
                $label = esc_html($label);
                $options .= "
                <option value='$value'$sel>$label</option>";
            }
            $name = esc_attr($name);
            $disabled = $disabled ? ' disabled="disable"' : '';
            return "
              <select id='$name' name='$name'$disabled>$options
              </select>";
        }

        /**
         * Fetch a HTTP request value
         * @since 1.3
         * @param string $key Name of the value to fetch
         * @param mixed|null $default Default value if $key does not exist
         * @return mixed The value for $key or $default
         */
        protected function get($key, $default = null) {
            return key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
        }

        /**
         * Get a cookie
         * @since 1.9
         * @param string $name Cookie name
         * @param string|null $default Default value if cookie is not set
         * @return string
         */
        protected function cookie($name, $default = null) {
            return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
        }

        /**
         * Perform regular expression match and get first capture group
         * @since 1.9
         * @param string $pattern
         * @param string $subject
         * @param string|null $default Default value if pattern does not match
         * @return string|null
         */
        protected function preg_get($pattern, $subject, $default = null) {
            $matches = null;
            if (preg_match($pattern, $subject, $matches)) {
                return isset($matches[1]) ? $matches[1] : $default;
            }
            return $default;
        }

        protected $luma_transformations = array();

        public function add_luma_transformation($id, $name, $fn) {
            if ($this->has_luma_transformation($id) || !is_callable($fn)) {
                return false;
            }
            $this->luma_transformations[$id] = array($name, $fn);
        }

        public function has_luma_transformation($id) {
            return $id && isset($this->luma_transformations[$id]);
        }

        public function get_luma_transformation($id) {
            if ($this->has_luma_transformation($id)) {
                return $this->luma_transformations[$id];
            }
            return false;
        }

        public function get_luma_transformations($output = 'all') {
            switch ($output) {
                case 'ids': return array_keys($this->luma_transformations);
                case 'names':
                    $transformations = array();
                    foreach ($this->luma_transformations as $id => $transformation) {
                        $transformations[$id] = $transformation[0];
                    }
                    return $transformations;
            }
            return $this->luma_transformations;
        }

        public function default_luma_transformations() {
            $this->add_luma_transformation('sine', __('Sine', 'kt-tinymce-color-grid'), array($this, 'sine_luma'));
            $this->add_luma_transformation('cubic', __('Cubic', 'kt-tinymce-color-grid'), array($this, 'cubic_luma'));
            $this->add_luma_transformation('natural', __('Natural', 'kt-tinymce-color-grid'), array($this, 'natural_luma'));
        }

        /**
         * Apply a transformation on a linear float
         * @since 1.7
         * @param float $luma [-1..1]
         * @param string $type
         * @return float [-1..1]
         */
        public function transform_luma($luma, $type) {
            if (!$this->has_luma_transformation($type)) {
                return $luma;
            }
            list($name, $fn) = $this->get_luma_transformation($type);
            return call_user_func($fn, $luma, $name);
        }

        /**
         * Apply a sine transformation on a linear luma value.
         * @since 1.7
         * @param float $luma [-1..1]
         * @return float [-1..1]
         */
        public function sine_luma($luma) {
            return $luma < 0 ? sin((1 - $luma) * M_PI_2) - 1 : sin($luma * M_PI_2);
        }

        /**
         * Apply a cubic transformation on a linear luma value.
         * @since 1.7
         * @param float $luma [-1..1]
         * @return float [-1..1]
         */
        public function cubic_luma($luma) {
            return $luma < 0 ? pow(($luma + 1), 8 / 11) - 1 : pow($luma, 8 / 13);
        }

        /**
         * Apply a natural transformation on a linear luma value.
         * @since 1.7
         * @param float $luma [-1..1]
         * @return float [-1..1]
         */
        public function natural_luma($luma) {
            return $luma < 0 ? $this->sine_luma($luma) : $this->cubic_luma($luma);
        }

        /**
         * Apply a luma transformation on a RGB vector
         * @since 1.7
         * @param float $luma [-1..1]
         * @param array $rgb RGB vector [red, gree, blue] of [0..1]
         * @return array
         */
        public function apply_luma($luma, $rgb) {
            foreach ($rgb as $i => $c) {
                if ($luma < 0) {
                    $c += $c * $luma;
                } else if ($luma > 0) {
                    $c = $c == 0 ? $luma : $c + (1 - $c) * $luma;
                    $c = max(0, min($c, 1));
                }
                $rgb[$i] = $c;
            }
            return $rgb;
        }

        /**
         * Sanitize a string to RRGGBB
         * @since 1.4
         * @param string $string String to be checked
         * @return string|boolean Returns a color of RRGGBB or false on failure
         */
        public function sanitize_color($string) {
            $string = strtoupper($string);
            $hex = null;
            if (preg_match('~([0-9A-F]{6}|[0-9A-F]{3})~', $string, $hex)) {
                $hex = $hex[1];
                if (strlen($hex) == 3) {
                    return preg_replace('~[0-9A-F]~', '\1\1', $hex);
                }
                return $hex;
            }
            return false;
        }

        /**
         * Sanitize a alpha value
         * @since 1.10
         * @param string $string
         * @return int
         */
        public function sanitize_alpha($string) {
            return intval($this->preg_get('~(100|[1-9][0-9]?|0)~', $string, 100));
        }

        /**
         * Convert a float to a HEX string
         * @since 1.7
         * @param float $p [0..1]
         * @return string
         */
        public function float2hex($p) {
            return $this->int2hex($p * 255);
        }

        /**
         * Convert a integer to a HEX string
         * @since 1.9
         * @param int $i [0..255]
         * @return string
         */
        public function int2hex($i) {
            $s = dechex($i);
            return (strlen($s) == 1 ? '0' : '') . $s;
        }

        /**
         * Return a RGB vector for a hue
         * @since 1.7
         * @param float $hue [0..1]
         * @return array RGB vector [red, gree, blue] of [0..1]
         */
        public function hue2rgb($hue) {
            $hue *= 6;
            if ($hue < 1) {
                return array(1, $hue, 0);
            }
            if (--$hue < 1) {
                return array(1 - $hue, 1, 0);
            }
            if (--$hue < 1) {
                return array(0, 1, $hue);
            }
            if (--$hue < 1) {
                return array(0, 1 - $hue, 1);
            }
            if (--$hue < 1) {
                return array($hue, 0, 1);
            }
            return array(1, 0, 1 - --$hue);
        }

        /**
         * Convert a RGB vector to a HEX string
         * @since 1.9
         * @param array $rgb RGB vector [red, gree, blue] of [0..1]
         * @return string
         */
        public function rgb2hex($rgb) {
            if (!is_array($rgb)) {
                $rgb = func_get_args();
            }
            foreach ($rgb as $i => $x) {
                if ($x < 0 || $x > 1) {
                    return false;
                }
                $rgb[$i] = $this->int2hex($x * 255);
            }
            return implode('', $rgb);
        }

    }

    kt_Central_Palette::instance();
}
