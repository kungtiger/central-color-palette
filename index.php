<?php

/*
 * Plugin Name: Central Color Palette
 * Plugin URI: https://wordpress.org/plugins/kt-tinymce-color-grid
 * Description: Manage a site-wide central color palette for an uniform look'n'feel! Supports the new block editor, theme customizer and many themes and plugins.
 * Version: 1.13.11
 * Author: Daniel Schneider
 * Author URI: http://profiles.wordpress.org/kungtiger
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kt-tinymce-color-grid
 */

if (defined('ABSPATH') && !class_exists('kt_Central_Palette')) {
    define('KT_CENTRAL_PALETTE', '1.13.11');
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
        const FONTPRESS = 'kt_color_grid_fontpress';
        const ELEMENTOR = 'kt_color_grid_elementor';
        const GENERATEPRESS = 'kt_color_grid_gp';
        const GENERATEPRESS_ALPHA = 'kt_color_grid_gp_alpha';
        const OCEANWP = 'kt_color_grid_oceanwp';
        const OCEANWP_ALPHA = 'kt_color_grid_oceanwp_alpha';
        const BEAVERBUILDER = 'kt_color_grid_beaverbuilder';
        const GUTENBERG = 'kt_color_grid_gutenberg';
        const GUTENBERG_MERGE = 'kt_color_grid_gutenberg_merge';
        const GUTENBERG_FORCE = 'kt_color_grid_gutenberg_force';
        const ACTIVE_VERSION = 'kt_color_grid_version';
        const AUTONAME = 'kt_color_grid_autoname';
        const NEXT_INDEX = 'kt_color_grid_next_index';
        const TINYMCE_ROWS = 5;
        const TINYMCE_COLS = 8;
        const MIN_CLAMP = 4;
        const MAX_CLAMP = 18;
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
        const COLOR_ACTIVE = 1;
        const COLOR_INACTIVE = 2;
        const COLOR_DELETED = 3;

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
         * Hold those themes/plugins that support palette integration
         * without an explicit integration
         * @var array
         * @since 1.12.2
         */
        protected $native_palette_support = array();
        protected $export_formats = array();

        /**
         * Singleton Design
         * @var kt_Central_Palette
         */
        protected static $instance = null;

        /**
         * Singleton Design
         * @return kt_Central_Palette
         */
        public static function instance() {
            if (self::$instance == null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Here we go ...
         *
         * Adds action and filter callbacks
         * @since 1.3
         * @ignore
         */
        private function __construct() {
            add_action('plugins_loaded', array($this, 'init_l10n'));
            add_action('after_setup_theme', array($this, 'init_integrations'), 999);
            add_action('admin_menu', array($this, 'add_settings_page'));
            add_filter('plugin_action_links', array($this, 'add_action_link'), 10, 2);

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

                case 'gutenberg': return $this->version_compare('>=', '5.0');
                case 'elementor': return defined('ELEMENTOR_VERSION');
                case 'generatepress': return defined('GP_PREMIUM_VERSION');
                case 'oceanwp': return defined('OCEANWP_THEME_VERSION');
                case 'beaverbuilder': return defined('FL_BUILDER_VERSION') && version_compare(FL_BUILDER_VERSION, '1.7.6', '>=');
                case 'astra': return defined('ASTRA_THEME_VERSION');
                case 'page-builder-framework': return defined('WPBF_VERSION');
                case 'hestia-theme': return defined('HESTIA_VERSION');
                case 'neve-theme': return defined('NEVE_VERSION');
                case 'fontpress': return defined('FP_VER') && version_compare(FP_VER, '3.03', '>=');
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
                        $version = 16;

                        $sets = get_option('kt_color_grid_sets', array());
                        if ($sets) {
                            foreach ($sets as &$set) {
                                $set[0] = str_replace('#', '', $set[0]);
                            }
                            update_option('kt_color_grid_sets', $sets);
                        }
                        break;

                    case 16:
                    case 161:
                        $version = 170;

                        if (get_option('kt_color_grid_custom')) {
                            update_option('kt_color_grid_visual', '1');
                        }
                        $sets = get_option('kt_color_grid_sets', array());
                        if ($sets) {
                            update_option('kt_color_grid_palette', $sets);
                        }
                        delete_option('kt_color_grid_custom');
                        delete_option('kt_color_grid_sets');
                        break;

                    case 170:
                    case 171:
                    case 172:
                    case 18:
                    case 181:
                    case 19:
                    case 191:
                    case 192:
                    case 193:
                        $version = 1100;

                        $palette = get_option('kt_color_grid_palette', array());
                        $_palette = array();
                        foreach ($palette as $set) {
                            $_palette[] = array($set[0], 100, $set[1]);
                        }
                        update_option('kt_color_grid_palette', $_palette);
                        break;

                    case 1100:
                    case 1110:
                    case '1.11':
                    case '1.12':
                    case '1.12.1':
                    case '1.12.2':
                    case '1.12.3':
                    case '1.12.4':
                    case '1.12.5':
                    case '1.12.6':
                        $version = '1.13';

                        $palette = (array) get_option('kt_color_grid_palette');
                        $_palette = array();
                        $index = 1;
                        $status = 1;
                        foreach ($palette as $set) {
                            if (!$set) {
                                continue;
                            }

                            list($color, $alpha, $name) = $set;
                            $color = '#' . ltrim($color, '#');
                            $_palette[] = compact('color', 'alpha', 'name', 'index', 'status');
                            $index++;
                        }
                        update_option('kt_color_grid_palette', $_palette);

                        $alpha = 'kt_color_grid_alpha';
                        if (get_option($alpha)) {
                            update_option('kt_color_grid_oceanwp_alpha', 1);
                            update_option('kt_color_grid_gp_alpha', 1);
                        }
                        delete_option($alpha);

                        # re-render for new data formats
                        $this->render_map();
                        break;

                    case '1.13':
                    case '1.13.1':
                    case '1.13.2':
                    case '1.13.3':
                    case '1.13.4':
                    case '1.13.5':
                    case '1.13.6':
                    case '1.13.7':
                    case '1.13.8':
                    case '1.13.9':
                        $version = '1.13.10';

                        # re-render for obligatory "reset color"
                        if (get_option('kt_color_grid_type') == 'palette') {
                            $this->render_map();
                        }
                        delete_option('kt_color_grid_mce_reset');
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
            add_filter('tiny_mce_before_init', array($this, 'tinymce_integration'), 10, 2);
            add_action('after_wp_tiny_mce', array($this, 'print_tinymce_style'));

            $integrate_customizer = get_option(self::CUSTOMIZER);
            if ($integrate_customizer) {
                $fn = array($this, 'iris_integration');
                add_action('admin_print_scripts', $fn);
                add_action('admin_print_footer_scripts', $fn);
                add_action('customize_controls_print_scripts', $fn);
                add_action('customize_controls_print_footer_scripts', $fn);
            }

            if ($this->supports('elementor') && get_option(self::ELEMENTOR)) {
                add_filter('elementor/editor/localize_settings', array($this, 'elementor_integration'), 100);
                add_action('elementor/editor/after_enqueue_scripts', array($this, 'elementor_styles'));
                add_action('elementor/editor/after_enqueue_styles', array($this, 'print_iris_style'));
            }

            if ($this->supports('generatepress') && get_option(self::GENERATEPRESS)) {
                add_filter('generate_default_color_palettes', array($this, 'generatepress_integration'));
            }

            if ($this->supports('oceanwp') && get_option(self::OCEANWP)) {
                add_filter('ocean_default_color_palettes', array($this, 'oceanwp_integration'));
            }

            if ($this->supports('beaverbuilder') && get_option(self::BEAVERBUILDER)) {
                add_filter('fl_builder_color_presets', array($this, 'beaver_integration'));
                add_action('wp_enqueue_scripts', array($this, 'beaver_style'));
            }

            if ($this->supports('gutenberg') && get_option(self::GUTENBERG)) {
                $this->integrate_gutenberg();
            }

            if ($this->supports('hestia-theme') && $integrate_customizer) {
                add_filter('hestia_accent_color_palette', array($this, 'hestia_integration'));
            }

            if ($this->supports('fontpress') && get_option(self::FONTPRESS)) {
                add_action('admin_enqueue_scripts', array($this, 'fontpress_enqueue_scripts'));
            }
        }

        /**
         * FontPress scripts
         * @since 1.13.11
         */
        public function fontpress_enqueue_scripts() {
            $screen = get_current_screen();
            if (!$screen) {
                return;
            }

            // enqueue scripts for the rule manager and any post/page editor
            if ($screen->id == 'toplevel_page_fp_settings' || $screen->base == 'post') {
                wp_enqueue_style(self::KEY . '-colpicker', KT_CENTRAL_PALETTE_URL . 'css/fontpress-colpicker.css', array('fp-colpick'), KT_CENTRAL_PALETTE);
                wp_enqueue_script(self::KEY . '-colpicker', KT_CENTRAL_PALETTE_URL . 'js/fontpress-colpicker.js', array('jquery'), KT_CENTRAL_PALETTE, true);
                wp_localize_script(self::KEY . '-colpicker', 'kt_fontpress_palette', $this->get_palette(array(
                    'status' => self::COLOR_ACTIVE,
                )));
            }
        }

        /**
         * Integrate with Hestia Theme
         * @since 1.13.3
         * @param bool|array $palette
         * @return bool|array
         */
        public function hestia_integration($palette) {
            return $this->get_colors(array(
                        'default' => $palette,
            ));
        }

        protected function integrate_gutenberg() {
            $palette = $this->get_gutenberg_palette();
            if ($palette) {
                if (get_option(self::GUTENBERG_MERGE)) {
                    $existing_palette = get_theme_support('editor-color-palette');
                    if (is_array($existing_palette)) {
                        $palette = array_merge(reset($existing_palette), $palette);
                    }
                }

                // 8 is just after wp_print_styles, 99 just before wp_custom_css_cb
                $priority = get_option(self::GUTENBERG_FORCE) ? 99 : 8;

                add_theme_support('editor-color-palette', $palette);
                add_action('wp_head', array($this, 'print_gutenberg_style'), $priority);
                add_action('admin_print_styles', array($this, 'print_gutenberg_style'), 21);
            }
        }

        public function print_gutenberg_style() {
            if (is_admin()) {
                $screen = get_current_screen();
                if (!$screen || !$screen->is_block_editor()) {
                    return;
                }
            }

            $palette = $this->get_gutenberg_palette();
            if (!$palette) {
                return;
            }

            $debug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;
            $force = get_option(self::GUTENBERG_FORCE) ? ' !important' : '';

            print '<style id="kt_central_palette_gutenberg_css" type="text/css">';
            foreach ($palette as $set) {
                $slug = $set['slug'];
                $color = $set['color'];
                if ($debug) {
                    print "
  .has-$slug-color {
    color: $color$force }
  .has-$slug-background-color {
    background-color: $color$force }";
                } else {
                    print ".has-$slug-color{color:$color$force}.has-$slug-background-color{background-color:$color$force}";
                }
            }
            print '
</style>
';
        }

        protected function get_gutenberg_palette() {
            $palette = array();
            $_palette = $this->get_palette(array(
                'status' => self::COLOR_ACTIVE,
            ));
            foreach ($_palette as $set) {
                $name = $set['name'];
                if ($name === '') {
                    $name = $set['color'];
                }

                $palette[] = array(
                    'color' => $set['color'],
                    'name' => $name,
                    'slug' => 'central-palette-' . $set['index'],
                );
            }
            return $palette;
        }

        public function beaver_style() {
            if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
                wp_enqueue_style(self::KEY . '-beaver', KT_CENTRAL_PALETTE_URL . 'css/beaver.css', null, KT_CENTRAL_PALETTE);
            }
        }

        public function beaver_integration() {
            return $this->get_colors(array(
                        'hash' => false,
            ));
        }

        /**
         * Get palette
         * @since 1.13
         * @param string|array $options
         * @return array
         */
        public function get_palette($options = '') {
            $options = wp_parse_args($options, array(
                '_palette' => null,
                'status' => array(self::COLOR_ACTIVE, self::COLOR_INACTIVE),
                'chunk' => false,
                'pad' => array(
                    'color' => '#FFFFFF',
                    'alpha' => 100,
                    'name' => '',
                    'index' => '',
                    'status' => self::COLOR_ACTIVE,
                ),
            ));

            $status = false;
            if ($options['status']) {
                $status = $options['status'];
                if (!is_array($status)) {
                    $status = array($status);
                }
            }

            $_palette = $options['_palette'];
            if (!is_array($_palette)) {
                $_palette = (array) get_option(self::PALETTE);
            }

            $palette = array();
            foreach ($_palette as $color) {
                if (!$color) {
                    continue;
                }
                if (!($status === false || in_array($color['status'], $status))) {
                    continue;
                }
                $palette[] = $color;
            }

            if (count($palette) && $options['chunk'] > 0) {
                $chunk = (int) $options['chunk'];
                $palette = array_chunk($palette, $chunk);
                $last = count($palette) - 1;
                $palette[$last] = array_pad($palette[$last], $chunk, $options['pad']);
            }

            return $palette;
        }

        /**
         * Set the palette.
         * @since 1.13.3
         * @param null|array $new_palette
         * @param bool|float $merge_threshold
         */
        public function set_palette($new_palette, $merge_threshold = .25) {
            if (!is_array($new_palette)) {
                return array();
            }

            $palette = array();
            $next_index = 1;
            $statii = array(self::COLOR_ACTIVE, self::COLOR_INACTIVE);
            foreach ($new_palette as $data) {
                if (is_string($data)) {
                    $data = array('color' => $data);
                }
                if (!is_array($data) || !isset($data['color'])) {
                    continue;
                }

                $color = $this->sanitize_color($data['color']);
                if ($color === false) {
                    continue;
                }

                $name = isset($data['name']) ? $this->sanitize_textfield($data['name']) : '';
                $alpha = isset($data['alpha']) ? $this->sanitize_alpha($data['alpha']) : 100;

                $index = 0;
                if ($merge_threshold === false) {
                    if (isset($data['index']) && is_numeric($data['index'])) {
                        $index = intval($data['index']);
                    } else {
                        $index = $next_index++;
                    }
                }

                $status = self::COLOR_ACTIVE;
                if (isset($data['status']) && in_array($data['status'], $statii)) {
                    $status = $data['status'];
                }
                $palette[] = compact('color', 'name', 'alpha', 'index', 'status');
            }

            if ($merge_threshold !== false) {
                $palette = $this->merge_palette($palette, $merge_threshold);
            }
            update_option(self::PALETTE, $palette);
            $this->render_map();
            return $palette;
        }

        /**
         * Get the color palette
         * @since 1.11
         * @since 1.13 Introduced $options argument
         * @param string|array $options
         * @return array
         */
        public function get_colors($options = '') {
            $options = wp_parse_args($options, array(
                'alpha' => false,
                'min' => false,
                'pad' => '#FFFFFF',
                'hash' => true,
                'default' => array(),
                '_name' => false,
            ));

            $palette = array();
            $_palette = $this->get_palette(array(
                'status' => self::COLOR_ACTIVE,
            ));
            foreach ($_palette as $set) {
                $color = $set['color'];
                if ($options['alpha'] && $set['alpha'] < 100) {
                    $color = $this->hex2rgba($color, $set['alpha']);
                } else if (!$options['hash']) {
                    $color = ltrim($color, '#');
                }
                if ($options['_name']) {
                    $color = array(
                        'color' => $color,
                        'name' => $set['name'],
                    );
                }
                $palette[] = $color;
            }

            if ($options['min'] > 0) {
                $palette = array_pad($palette, $options['min'], $options['pad']);
            }

            if (!count($palette) && is_array($options['default'])) {
                $palette = $options['default'];
            }

            return $palette;
        }

        /**
         * GeneratePress Premium integration
         * @since 1.10
         * @param array $palette
         * @return array
         */
        public function generatepress_integration($palette) {
            return $this->get_colors(array(
                        'alpha' => get_option(self::GENERATEPRESS_ALPHA),
                        'default' => $palette,
            ));
        }

        /**
         * OceanWP integration
         * @since 1.11
         * @param array $palette
         * @return array
         */
        public function oceanwp_integration($palette) {
            return $this->get_colors(array(
                        'alpha' => get_option(self::OCEANWP_ALPHA),
                        'default' => $palette,
            ));
        }

        /**
         * Enqueue a stylesheet for Elementor
         * @since 1.10
         */
        public function elementor_styles() {
            wp_enqueue_style(self::KEY . '-elementor', KT_CENTRAL_PALETTE_URL . 'css/elementor.css', null, KT_CENTRAL_PALETTE);
            wp_enqueue_script(self::KEY . '-iris', KT_CENTRAL_PALETTE_URL . 'js/iris.js', array('iris'), KT_CENTRAL_PALETTE);
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
            $colors = $this->get_colors(array(
                'pad' => '#FFF',
                'min' => 6,
                'alpha' => true,
                '_name' => true,
            ));
            $palette = array();
            for ($i = 1, $n = count($colors); $i <= $n; $i++) {
                $palette[$i] = array('value' => $colors[$i - 1]);
            }
            return $palette;
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

            $colors = $this->get_colors();
            if (!$colors) {
                return;
            }

            $this->print_iris_style();

            $colors = implode('","', array_map('esc_js', $colors));
            print '
<script id="kt_central_palette_iris_integration" type="text/javascript">
jQuery.wp.wpColorPicker.prototype.options.palettes = ["' . $colors . '"];
</script>
';
        }

        public function print_iris_style() {
            $n = count($this->get_colors());
            if (!$n) {
                return;
            }

            $padding_bottom = (ceil($n / 8) * 23);

            print '
<style id="kt_central_palette_iris_style" type="text/css">
.wp-picker-active .iris-picker {
  padding-bottom: ' . $padding_bottom . 'px !important }
.wp-picker-active .iris-picker  .iris-palette {
  width: 20px !important;
  height: 20px !important;
  margin: 3px 0 0 3px !important }
.wp-picker-active .iris-picker .iris-palette-container {
    bottom: 5px !important }
.wp-picker-active .iris-picker .iris-palette:first-child,
.wp-picker-active .iris-picker .iris-palette:nth-child(8n+1) {
  margin-left: 0 !important;
  clear: left }
.wp-picker-active .iris-picker .iris-slider {
  height: ' . ($padding_bottom + 183) . 'px !important }
</style>';
        }

        /**
         * Add dynamic CSS for TinyMCE
         * @since 1.3
         * @since 1.12.3 Change css selector
         */
        public function print_tinymce_style() {
            if (get_option(self::TYPE, self::DEFAULT_TYPE) == 'default') {
                return;
            }
            $map = get_option(self::MAP);
            if (!is_array($map) || !$map['rows']) {
                return;
            }
            $rows = $map['rows'];
            print "<style type='text/css'>
.mce-colorbutton-grid {border-spacing: 0; border-collapse: collapse}
.mce-colorbutton-grid td {padding: 0}
.mce-colorbutton-grid td.mce-grid-cell div {border-style: solid none none solid}
.mce-colorbutton-grid td.mce-grid-cell:last-child div {border-right-style: solid}
.mce-colorbutton-grid tr:nth-child($rows) td.mce-grid-cell div,
.mce-colorbutton-grid tr:last-child td.mce-grid-cell div {border-bottom-style: solid}
.mce-colorbutton-grid tr:nth-child($rows) td {padding-bottom: 4px}
</style>";
        }

        /**
         * Pass color map to TinyMCE
         * @since 1.3
         * @since 1.13 Gutenberg support
         * @param array $init Wordpress' TinyMCE inits
         * @param string $id Id of TinyMCE instance
         * @return array
         */
        public function tinymce_integration($init, $id) {
            // Gutenberg uses 'editor' as id and wp_localize_script which
            // does JSON encoding for us, so we return an array.
            // For any other id we asume a classic editor and return a string.

            $type = get_option(self::TYPE, self::DEFAULT_TYPE);
            if ($type == 'default') {
                return $init;
            }

            $mce = get_option(self::MAP);
            if (is_array($mce) && $mce['rows']) {
                if ($id != 'editor') {
                    $map = array_map('esc_js', $mce['map']);
                    $mce['map'] = '["' . implode('","', $map) . '"]';
                }
                $init['textcolor_map'] = $mce['map'];
                $init['textcolor_cols'] = $mce['columns'];
                $init['textcolor_rows'] = $mce['rows'];
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
            if (plugin_basename($file) == plugin_basename(__FILE__)) {
                $links[] = '<a href="options-general.php?page=' . self::KEY . '" title="' . esc_attr__('Opens the settings page for this plugin', 'kt-tinymce-color-grid') . '"> ' . esc_html__('Color Palette', 'kt-tinymce-color-grid') . '</a>';
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
            $args[] = 'kt-import-error';
            $args[] = 'kt-export-error';
            return $args;
        }

        /**
         * Initialize settings page
         * @since 1.4.4
         */
        public function init_settings_page() {
            $this->native_palette_support = array(
                'astra' => __('Astra Theme', 'kt-tinymce-color-grid'),
                'page-builder-framework' => __('Page Builder Framework', 'kt-tinymce-color-grid'),
                'hestia-theme' => __('Hestia Theme', 'kt-tinymce-color-grid'),
                'neve-theme' => __('Neve Theme', 'kt-tinymce-color-grid'),
            );

            $this->export_formats = array(
                'base64' => array(
                    'extension' => 'bak',
                    'name' => __('Backup', 'kt-tinymce-color-grid'),
                    'form' => 'json',
                    'export' => 'base64',
                ),
                'json' => array(
                    'extension' => 'json',
                    'name' => __('JSON', 'kt-tinymce-color-grid'),
                    'form' => 'json',
                    'export' => 'json',
                ),
                'css' => array(
                    'extension' => 'css',
                    'name' => __('CSS', 'kt-tinymce-color-grid'),
                    'form' => 'css',
                    'export' => 'css',
                ),
                'scss-partial' => array(
                    'extension' => 'scss',
                    'prefix' => '_',
                    'name' => __('SCSS Partial', 'kt-tinymce-color-grid'),
                    'form' => 'css',
                    'export' => 'css',
                ),
                'scss-vars' => array(
                    'extension' => 'scss',
                    'prefix' => '_',
                    'name' => __('SCSS Variables', 'kt-tinymce-color-grid'),
                    'form' => 'scss',
                    'export' => 'scss',
                ),
            );

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
                wp_register_script('name-that-color', KT_CENTRAL_PALETTE_URL . "js/ntc.js", null, '1.0');
            }

            wp_enqueue_script(self::KEY, KT_CENTRAL_PALETTE_URL . "js/settings.js", array('wp-util', 'postbox', 'jquery-ui-position', 'jquery-ui-sortable', 'name-that-color'), KT_CENTRAL_PALETTE);
            wp_enqueue_style(self::KEY, KT_CENTRAL_PALETTE_URL . 'css/settings.css', null, KT_CENTRAL_PALETTE);
        }

        /**
         * Add metaboxes to settings page
         * @since 1.9
         */
        protected function add_metaboxes() {
            $boxes = array(
                'palette' => __('Color Palette', 'kt-tinymce-color-grid'),
                'grid' => __('Classic Editor: Color Picker', 'kt-tinymce-color-grid'),
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
            if (!wp_verify_nonce($this->get_request('kt_settings_nonce'), self::NONCE)) {
                return;
            }

            $action = $this->get_request('kt_action', $this->get_request('kt_hidden_action'));
            $type = $this->get_request('kt_type');
            if (!in_array($type, $this->types)) {
                $type = self::DEFAULT_TYPE;
            }
            $visual = $type == 'palette' || $this->get_request('kt_visual') ? '1' : false;

            $booleans = array(
                'kt_customizer' => self::CUSTOMIZER,
                'kt_elementor' => self::ELEMENTOR,
                'kt_fontpress' => self::FONTPRESS,
                'kt_generatepress' => self::GENERATEPRESS,
                'kt_generatepress_alpha' => self::GENERATEPRESS_ALPHA,
                'kt_oceanwp' => self::OCEANWP,
                'kt_oceanwp_alpha' => self::OCEANWP_ALPHA,
                'kt_beaverbuilder' => self::BEAVERBUILDER,
                'kt_gutenberg' => self::GUTENBERG,
                'kt_gutenberg_merge' => self::GUTENBERG_MERGE,
                'kt_gutenberg_force' => self::GUTENBERG_FORCE,
            );
            foreach ($booleans as $field => $option) {
                update_option($option, $this->get_request($field) ? '1' : false);
            }

            $palette_saved = $this->save_palette();

            if (!$palette_saved && $type == 'palette') {
                $type = 'default';
                $visual = '';
            }
            update_option(self::TYPE, $type);
            update_option(self::VISUAL, $visual);

            $lumas = array('linear') + $this->get_luma_transformations('ids');

            $this->set('kt_rows', $this->rows, self::ROWS, self::DEFAULT_ROWS);
            $this->set('kt_cols', $this->columns, self::COLS, self::DEFAULT_COLS);
            $this->set('kt_luma', $lumas, self::LUMA, self::DEFAULT_LUMA);
            $this->set('kt_blocks', $this->blocks, self::BLOCKS, self::DEFAULT_BLOCKS);
            $this->set('kt_block_size', $this->sizes, self::SIZE, self::DEFAULT_SIZE);
            $this->set('kt_axis', $this->axes, self::AXIS, self::DEFAULT_AXIS);
            $this->set('kt_spread', $this->spread, self::SPREAD, self::DEFAULT_SPREAD);
            $this->set('kt_clamp', $this->clamp, self::CLAMP, self::DEFAULT_CLAMP);
            $clamps = intval($this->get_request('kt_clamps'));
            if ($clamps < 4 || $clamps > 18) {
                $clamps = self::DEFAULT_CLAMPS;
            }
            update_option(self::CLAMPS, $clamps);

            list($error, $action) = $this->handle_backup($action);

            if (!$error || $error == 'ok') {
                $this->render_map();
            }

            if ($error) {
                $url = add_query_arg("kt-{$action}-error", $error);
                wp_redirect($url);
                exit;
            }
            wp_redirect(add_query_arg('updated', $action == 'save' ? '1' : false));
            exit;
        }

        /**
         * Save the palette from form data
         * @since 1.13.3
         * @return boolean
         */
        protected function save_palette() {
            $palette = array();
            $next_index = get_option(self::NEXT_INDEX, 1);
            $data = (array) $this->get_request('kt_palette');
            if (isset($data['color']) && is_array($data['color'])) {
                foreach ($data['color'] as $i => $color) {
                    $color = $this->sanitize_color($color);
                    if (!$color) {
                        continue;
                    }

                    $alpha = $this->sanitize_alpha($data['alpha'][$i]);
                    $name = $this->sanitize_textfield($data['name'][$i]);
                    $index = $data['index'][$i];
                    if (!is_numeric($index) || $index < 1) {
                        $index = $next_index;
                        $next_index++;
                    }
                    $status = $data['status'][$i];
                    $palette[] = compact('color', 'name', 'alpha', 'index', 'status');
                }
            }
            update_option(self::NEXT_INDEX, $next_index);
            update_option(self::PALETTE, $palette);
            return count($palette) > 0;
        }

        protected function handle_backup($action) {
            $error = false;
            switch ($action) {
                case 'export':
                    $error = $this->download_export();
                    break;

                case 'import':
                    $error = $this->import_backup();
                    break;

                default:
                    $action = 'save';
            }
            return array($error, $action);
        }

        /**
         * Debug function
         * @since 1.13
         * @ignore
         * @param mixed $x
         * @param bool $exit
         */
        public function xmp($x, $exit = true) {
            print '<xmp>';
            print_r($x);
            print '</xmp>';
            if ($exit) {
                exit;
            }
        }

        protected function sanitize_textfield($string) {
            return sanitize_text_field(stripslashes($string));
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
                self::FONTPRESS => false,
                self::OCEANWP => false,
                self::OCEANWP_ALPHA => false,
                self::GENERATEPRESS => false,
                self::GENERATEPRESS_ALPHA => false,
                self::BEAVERBUILDER => false,
                self::GUTENBERG => false,
                self::GUTENBERG_MERGE => false,
                self::GUTENBERG_FORCE => false,
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
            $palette = array(
                self::PALETTE => array(),
            );

            if ($parts) {
                foreach ($parts as $part) {
                    switch ($part) {
                        case 'settings':
                            $options += $settings;
                            break;
                        case 'palette':
                            $options += $palette;
                            break;
                    }
                }
                return $options;
            }

            return $options + $settings + $palette;
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
                $payload = file_get_contents($file['tmp_name']);
            } else if (isset($_REQUEST['kt_raw_upload'])) {
                $payload = $_REQUEST['kt_raw_upload'];
            } else {
                return 'no-import';
            }

            $options = null;
            if ($this->import_is_json($payload)) {
                $options = json_decode($payload, true);
            } else if ($this->import_is_base64($payload)) {
                $payload = base64_decode(substr($payload, 0, -8));
                $options = json_decode($payload, true);
            } else {
                return 'format';
            }
            if (!is_array($options)) {
                return 'funny';
            }

            if (!isset($options[self::ACTIVE_VERSION])) {
                $options[self::ACTIVE_VERSION] = 180;
            }
            $this->update_import($options);
            $options[self::PALETTE] = $this->merge_palette($options[self::PALETTE]);
            $names = array_keys($this->default_options());
            foreach ($names as $name) {
                if (isset($options[$name])) {
                    update_option($name, $options[$name]);
                }
            }
            return 'ok';
        }

        protected function import_is_json($payload) {
            return substr($payload, 0, 1) == '{' && substr($payload, -1) == '}';
        }

        protected function import_is_base64($payload) {
            return dechex(crc32(substr($payload, 0, -8))) == substr($payload, -8);
        }

        /**
         * Tries to reuse existing colors and their indices during an import
         * @since 1.13
         * @since 1.13.3 Added $threshold parameter
         * @param array $palette
         * @param bool|float $threshold
         * @return array
         */
        protected function merge_palette($palette, $threshold = .25) {
            $next_index = get_option(self::NEXT_INDEX, 1);
            $current_palette = (array) get_option(self::PALETTE);
            if ($threshold === true) {
                $threshold = .25;
            }
            if ($threshold === false) {
                $threshold = 0;
            }

            foreach ($palette as $i => $new) {
                $shortest_distance = $threshold;
                $reuse = false;
                $new_color = strtoupper($new['color']);
                $new_rgb = $this->hex2rgb($new_color);
                foreach ($current_palette as $j => $current) {
                    if (!$current) {
                        continue;
                    }

                    // excact match, we're done
                    $current_color = strtoupper($current['color']);
                    if ($new['alpha'] == $current['alpha'] && $new_color == $current_color) {
                        $reuse = $j;
                        break;
                    }

                    // too different in transparency, skip
                    if (5 < abs($new['alpha'] - $current['alpha'])) {
                        continue;
                    }

                    if (!isset($current['rgb'])) {
                        $current['rgb'] = $this->hex2rgb($current['color']);
                    }
                    $distance = $this->rgb_distance($new_rgb, $current['rgb']);
                    if ($distance < $shortest_distance) {
                        $shortest_distance = $distance;
                        $reuse = $j;
                    }
                }

                if ($reuse !== false) {
                    $palette[$i]['index'] = $current_palette[$reuse]['index'];

                    // make sure we don't change the status of the color or active
                    // colors may disappear or inactive colors reappear inside the block editor
                    $palette[$i]['status'] = $current_palette[$reuse]['status'];

                    $current_palette[$reuse] = null;
                } else {
                    $palette[$i]['index'] = $next_index;
                    $next_index++;
                }
            }

            // pluck out all remaining inactive colors and add them to the import
            $inactives = array();
            foreach ($current_palette as $current) {
                if (!$current || $current['status'] != self::COLOR_INACTIVE) {
                    continue;
                }
                $inactives[] = $current;
            }
            $palette = array_merge($inactives, $palette);
            update_option(self::NEXT_INDEX, $next_index);
            return $palette;
        }

        /**
         * Calculate euclidean distance between two RGB vectors
         * @param array $a
         * @param array $b
         * @return float [0..100]
         */
        public function rgb_distance($a, $b) {
            return (pow($a[0] - $b[0], 2) + pow($a[1] - $b[1], 2) + pow($a[2] - $b[2], 2)) / 1950.75;
        }

        /**
         * Check a file upload
         * @since 1.9
         * @param array $file Element of $_FILES
         * @return string Status code
         */
        protected function verify_upload($file) {
            $upload_error = array(
                UPLOAD_ERR_INI_SIZE => 'size-php',
                UPLOAD_ERR_FORM_SIZE => 'size',
                UPLOAD_ERR_PARTIAL => 'partially',
                UPLOAD_ERR_NO_FILE => 'no-upload',
                UPLOAD_ERR_NO_TMP_DIR => 'tmp',
                UPLOAD_ERR_CANT_WRITE => 'fs',
                UPLOAD_ERR_EXTENSION => 'ext',
            );
            if (!isset($file['error'])) {
                $file['error'] = UPLOAD_ERR_NO_FILE;
            }
            if (isset($upload_error[$file['error']])) {
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
                        $version = 1100;
                        $key = 'kt_color_grid_palette';
                        if (!is_array($options[$key])) {
                            break;
                        }

                        $palette = array();
                        foreach ($options[$key] as $set) {
                            $palette[] = array($set[0], 100, $set[1]);
                        }
                        $options[$key] = $palette;
                        break;

                    case 1100:
                    case 1110:
                    case '1.11':
                    case '1.12':
                    case '1.12.1':
                    case '1.12.2':
                    case '1.12.3':
                    case '1.12.4':
                    case '1.12.5':
                    case '1.12.6':
                        $version = '1.13';

                        $index = 0;
                        $key = 'kt_color_grid_palette';
                        if (!is_array($options[$key])) {
                            break;
                        }

                        $palette = array();
                        foreach ($options[$key] as $set) {
                            list($color, $alpha, $name) = $set;
                            $color = '#' . ltrim($color, '#');
                            $index++;
                            $status = self::COLOR_ACTIVE;
                            $palette[] = compact('color', 'alpha', 'name', 'index', 'status');
                        }
                        $options[$key] = $palette;

                        $alpha = 'kt_color_grid_alpha';
                        if (isset($options[$alpha]) && $options[$alpha]) {
                            $options['kt_color_grid_oceanwp_alpha'] = 1;
                            $options['kt_color_grid_gp_alpha'] = 1;
                        }
                        unset($options[$alpha]);
                        break;

                    default:
                        $version = KT_CENTRAL_PALETTE;
                }
            }
        }

        /**
         * Export settings and trigger a file download
         * @since 1.13
         * @return string
         */
        protected function download_export() {
            $id = $this->get_request('kt_export_format');
            if (!isset($this->export_formats[$id])) {
                return 'format';
            }
            $fn = $this->export_formats[$id]['export'];
            $payload = call_user_func(array($this, "export_$fn"));

            if (!$payload) {
                return 'empty';
            }
            if (is_wp_error($payload)) {
                return $payload->get_error_code();
            }

            $filename = $this->get_export_filename($id);
            header('Content-Type: plain/text');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            print $payload;
            exit;
        }

        /**
         * Get file name for export file download
         * @since 1.13
         * @return string The filename
         */
        protected function get_export_filename($id) {
            $format = $this->export_formats[$id];
            $ext = $format['extension'];
            $prefix = isset($format['prefix']) ? $format['prefix'] : '';
            $name = get_bloginfo('name');
            $suffix = sanitize_file_name(str_replace('"', '', $name));
            if ($suffix) {
                $suffix = "_$suffix";
            }
            return "{$prefix}central-palette{$suffix}.{$ext}";
        }

        public function export_base64() {
            $json = $this->export_json();
            if (is_wp_error($json)) {
                return $json;
            }

            $base64 = base64_encode($json);
            if (!$base64) {
                return new WP_Error('base64');
            }

            return $base64 . dechex(crc32($base64));
        }

        public function export_json() {
            $parts = $this->get_request('kt_export_parts');
            if (!is_array($parts) || empty($parts)) {
                return new WP_Error('no-parts');
            }

            $options = $this->default_options($parts);
            foreach ($options as $name => $default) {
                $options[$name] = get_option($name, $default);
            }

            $json = json_encode($options);
            if (!$json) {
                return new WP_Error('json');
            }

            return $json;
        }

        public function export_css() {
            $css = array();
            $add_alpha = $this->get_request('kt_export_css_alpha');
            $prefix = $this->get_request('kt_export_css_prefix');
            $suffix = $this->get_request('kt_export_css_suffix');
            $palette = $this->get_palette();
            foreach ($palette as $set) {
                $hex = $set['color'];
                $alpha = $set['alpha'];
                $name = $this->get_css_class_name($set, $prefix, $suffix);
                $css[] = ".$name {
  color: $hex" . ($add_alpha && $alpha < 100 ? ';
  color: ' . $this->hex2rgba($hex, $alpha) : '') . " }\n";
            }
            return implode("\n", $css);
        }

        public function export_scss() {
            $vars = '';
            $prefix = $this->get_request('kt_export_scss_prefix');
            $suffix = $this->get_request('kt_export_scss_suffix');
            $palette = $this->get_palette();
            foreach ($palette as $set) {
                $hex = $set['color'];
                $alpha = $set['alpha'];
                $name = $this->get_css_class_name($set, $prefix, $suffix);
                $vars .= "\$$name: " . $this->hex2rgba($hex, $alpha) . ";\n";
            }
            return $vars;
        }

        protected function get_css_class_name($color, $prefix = '', $suffix = '') {
            static $duplicates = array();

            $prefix = sanitize_html_class($prefix);
            $suffix = sanitize_html_class($suffix);
            $slug = sanitize_html_class($color['name'], 'central-palette-' . $color['index']);
            $name = "$prefix$slug$suffix";
            if (isset($duplicates[$name])) {
                $i = $duplicates[$name];
                $duplicates[$name] += 1;
                $name .= "-$i";
            } else {
                $duplicates[$name] = 1;
            }

            return $name;
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
            $value = $this->get_request($key, $default);
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
        protected function chunk_palette($_palette = null) {
            $palette = array();
            list($rows, $cols) = $this->get_map_size();
            if (get_option(self::VISUAL)) {
                $palette = $this->get_palette(array(
                    'status' => self::COLOR_ACTIVE,
                    'chunk' => $rows,
                    '_palette' => $_palette,
                ));
            }
            return array($palette, $rows, $cols);
        }

        /**
         * Get palette size depending on its current type
         * @since 1.9
         * @return array [rows, cols]
         */
        protected function get_map_size() {
            switch (get_option(self::TYPE, self::DEFAULT_TYPE)) {
                case 'palette':
                    $count = 1 + count($this->get_palette(array(
                                        'status' => self::COLOR_ACTIVE,
                    )));

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
        protected function add_row_to_map(&$map, &$palette, $row) {
            $cols = count($palette);
            for ($col = 0; $col < $cols; $col++) {
                if ($palette[$col][$row]['color'] == 'reset') {
                    continue;
                }
                $map[] = ltrim($palette[$col][$row]['color'], '#');
                $map[] = $palette[$col][$row]['name'];
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
            $c = $this->float2hex($row / ($rows - 2));
            $map[] = "$c$c$c";
            $map[] = "";
        }

        /**
         * Render TinyMCE palette color map
         * @since 1.8
         * @return array
         */
        protected function render_palette() {
            $_palette = $this->get_palette(array(
                'status' => self::COLOR_ACTIVE,
            ));
            $_palette[] = array(
                'color' => 'reset',
                'status' => self::COLOR_ACTIVE,
            );
            list($palette, $rows, $columns) = $this->chunk_palette($_palette);
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);
            }
            return compact('map', 'rows', 'columns');
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
            list($palette, $rows, $columns) = $this->chunk_palette();
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);

                $b = $square[$row % $size];
                $shift = floor($row / $size) * $per_group;
                for ($col = 0; $col < $columns; $col++) {
                    $g = $square[$col % $size];
                    $r = $chunks[floor($col / $size) + $shift];
                    $map[] = sprintf($pattern, $r, $g, $b);
                    $map[] = "";
                }

                $this->add_monocroma($map, $row, $rows);
            }
            $columns += count($palette) + 1;
            return compact('map', 'rows', 'columns');
        }

        /**
         * Render TinyMCE rainbow color map
         * @since 1.7
         * @return array
         */
        protected function render_rainbow() {
            list($palette, $rows, $columns) = $this->chunk_palette();
            $rgb = array();
            for ($i = 0; $i < $columns; $i++) {
                $rgb[] = $this->hue2rgb($i / $columns);
            }

            $map = array();
            $type = get_option(self::LUMA, self::DEFAULT_LUMA);
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);

                $luma = 2 * ($row + 1) / ($rows + 1) - 1;
                $luma = $this->transform_luma($luma, $type);
                for ($col = 0; $col < $columns; $col++) {
                    $_rgb = $this->apply_luma($luma, $rgb[$col]);
                    $map[] = $this->rgb2hex($_rgb, true, false);
                    $map[] = "";
                }

                $this->add_monocroma($map, $row, $rows);
            }
            $columns += count($palette) + 1;
            return compact('map', 'rows', 'columns');
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
                'id' => 'palette',
                'title' => __('Color Palette', 'kt-tinymce-color-grid'),
                'content' => '
<p>' . __('You can create a color palette and include it to the Visual Editor and/or the Theme Customizer.', 'kt-tinymce-color-grid') . '</p>
<p>' . __("<strong>Add to Theme Customizer</strong> makes the palette available to the color picker of the Theme Customizer. This works by altering WordPress' color picker so every plugin using it receives the palette as well.", 'kt-tinymce-color-grid') . '</p>
<p>' . __('<strong>Add to block editor</strong> adds the palette to the color picker of the new block editor.', 'kt-tinymce-color-grid') . '</p>
<p>' . __('<strong>Add to classic editor</strong> adds the palette to the color picker of the classic editor. This only works if you choose a color grid other than <strong>Default</strong>.', 'kt-tinymce-color-grid') . '</p>'
            ));
            $screen->add_help_tab(array(
                'id' => 'grid',
                'title' => __('Classic Editor', 'kt-tinymce-color-grid'),
                'content' => '
<p>' . __("<strong>Default</strong> leaves TinyMCE's color picker untouched.", 'kt-tinymce-color-grid') . '</p>
<p>' . __("<strong>Palette</strong> only takes the colors defined by the Central Palette.") . '</p>
<p>' . sprintf(__("<strong>Rainbow</strong> takes hue and lightness components from the %s and thus creates a rainbow. The <strong>Luma</strong> option controls how the lightness for each hue is spread.", 'kt-tinymce-color-grid'), $hsl_link) . '</p>
<p>' . sprintf(__("<strong>Blocks</strong> takes planes from the %s and places them next to one another. <strong>Block Count</strong> controls how many planes are taken, and <strong>Block Size</strong> determines their size.", 'kt-tinymce-color-grid'), $rgb_url) . '</p>'
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
            $documentation_url = esc_url('https://kungtiger.github.io/central-color-palette');
            $screen->set_help_sidebar('
<p><strong>' . esc_html__('For more information:', 'kt-tinymce-color-grid') . '</strong></p>
<p><a href="' . $plugin_url . '" target="_blank">' . esc_html__('Visit plugin site', 'kt-tinymce-color-grid') . '</a></p>
<p><a href="' . $support_url . '" target="_blank">' . esc_html__('Support Forums', 'kt-tinymce-color-grid') . '</a></p>
<p><a href="' . $documentation_url . '" target="_blank">' . esc_html__('API Documentation', 'kt-tinymce-color-grid') . '</a></p>');
        }

        /**
         * Render settings page
         * @since 1.3
         */
        public function print_settings_page() {
            $head = $this->version_compare('<', '4.3') ? 'h2' : 'h1';
            print "
<div class='wrap'>
  <$head>" . get_admin_page_title() . "</$head>";
            $this->print_settings_error();
            print "
  <div class='notice notice-warning hide-if-js'><p>" . esc_html__('You need to enable JavaScript to use the palette editor.', 'kt-tinymce-color-grid') . "</p></div>
  <form id='kt_color_grid' class='hide-if-no-js' action='options-general.php?page=" . self::KEY . "' method='post' enctype='multipart/form-data'>
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

            do_meta_boxes(get_current_screen(), 'advanced', $this);

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
            $feedback = $error = '';
            if (isset($_GET['kt-import-error'])) {
                $error = $_GET['kt-import-error'];
                $import_errors = array(
                    'ok' => __('Backup successfully imported.', 'kt-tinymce-color-grid'),
                    'no-import' => __('No data to process.', 'kt-tinymce-color-grid'),
                    'format' => __('The uploaded file format is not supported.', 'kt-tinymce-color-grid'),
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
            } else if (isset($_GET['kt-export-error'])) {
                $error = $_GET['kt-export-error'];
                $export_errors = array(
                    'no-parts' => __('Please select which parts you would like to backup.', 'kt-tinymce-color-grid'),
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
                print "
<div id='setting-error-import' class='$type settings-error notice is-dismissible'>
  <p><strong>$feedback</strong></p>
</div>";
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
            $clamps = "<input type='number' id='kt_clamps' name='kt_clamps' min='" . self::MIN_CLAMP . "' max='" . self::MAX_CLAMP . "' step='1' value='$_clamps'/>";
            $spread = array(
                'even' => esc_html__('Spread colors evenly', 'kt-tinymce-color-grid'),
                /* translators: %1 selectbox for row or column, %2 input for number */
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
            $customizer_checked = $_customizer ? ' checked="checked"' : '';
            $visual_checked = $_visual ? ' checked="checked"' : '';
            $add_key = _x('A', 'accesskey for adding color', 'kt-tinymce-color-grid');
            $_add = __('Add Color', 'kt-tinymce-color-grid');
            $add_label = esc_html($_add);
            $add_title = esc_attr($_add);

            $add_to_customizer = esc_html__('Add to Theme Customizer', 'kt-tinymce-color-grid');
            foreach ($this->native_palette_support as $native => $name) {
                if ($this->supports($native)) {
                    $add_to_customizer .= " / $name";
                }
            }

            print "
<div class='columns'>
  <div class='column'>
    <p class='integrate-toggle'>
      <input type='checkbox' id='kt_customizer' name='kt_customizer' tabindex='10' value='1'$customizer_checked />
      <label for='kt_customizer'>$add_to_customizer</label>
    </p>";
            $has_gutenberg = $this->supports('gutenberg');
            if ($has_gutenberg) {
                $_gutenberg = get_option(self::GUTENBERG);
                $_gutenberg_merge = get_option(self::GUTENBERG_MERGE);
                $_gutenberg_force = get_option(self::GUTENBERG_FORCE);
                $gutenberg_checked = $_gutenberg ? ' checked="checked"' : '';
                $gutenberg_merge_checked = $_gutenberg_merge ? ' checked="checked"' : '';
                $gutenberg_force_checked = $_gutenberg_force ? ' checked="checked"' : '';
                $merge_hidden = $_gutenberg ? '' : ' hide-if-js';
                $force_hint = esc_attr__("Try this if your colors won't get applied to your theme", 'kt-tinymce-color-grid');
                print "
    <p class='integrate-toggle'>
      <input type='checkbox' id='kt_gutenberg' name='kt_gutenberg' tabindex='9' value='1'$gutenberg_checked data-form='1' />
      <label for='kt_gutenberg'>" . esc_html__('Add to block editor', 'kt-tinymce-color-grid') . "</label>
    </p>
    <p class='integrate-form$merge_hidden' id='kt_gutenberg_form'>
      <input type='checkbox' id='kt_gutenberg_merge' name='kt_gutenberg_merge' tabindex='9' value='1'$gutenberg_merge_checked />
      <label for='kt_gutenberg_merge'>" . esc_html__('Append to existing palette', 'kt-tinymce-color-grid') . "</label><br>
      <input type='checkbox' id='kt_gutenberg_force' name='kt_gutenberg_force' tabindex='9' value='1'$gutenberg_force_checked/>
      <label for='kt_gutenberg_force' title='$force_hint'>" . esc_html__('Enforce colors', 'kt-tinymce-color-grid') . "</label>
    </p>";
            }
            print "
    <p class='integrate-toggle' id='kt_visual_option'>
      <input type='checkbox' id='kt_visual' name='kt_visual' tabindex='9' value='1'$visual_checked />
      <label for='kt_visual'>" . esc_html__('Add to classic editor', 'kt-tinymce-color-grid') . "</label>
    </p>
  </div>";

            $integrations = array(
                array('beaverbuilder', self::BEAVERBUILDER, __('Beaver Builder', 'kt-tinymce-color-grid')),
                array('elementor', self::ELEMENTOR, __('Elementor', 'kt-tinymce-color-grid')),
                array('fontpress', self::FONTPRESS, __('FontPress', 'kt-tinymce-color-grid')),
                array('generatepress', self::GENERATEPRESS, __('GeneratePress Premium', 'kt-tinymce-color-grid')),
                array('oceanwp', self::OCEANWP, __('OceanWP', 'kt-tinymce-color-grid')),
            );
            $supported_integrations = array();
            foreach ($integrations as $integration) {
                if ($this->supports($integration[0])) {
                    $supported_integrations[] = $integration;
                }
            }
            $alpha_channel = array(
                'generatepress' => self::GENERATEPRESS_ALPHA,
                'oceanwp' => self::OCEANWP_ALPHA,
            );

            if (count($supported_integrations)) {
                print '
  <div class="column">';
                $_add_to = __('Integrate with %s', 'kt-tinymce-color-grid');
                $_use_alpha = __('Add transparent colors to %s', 'kt-tinymce-color-grid');
                foreach ($supported_integrations as $integration) {
                    list($id, $option_name, $label) = $integration;
                    $is_active = get_option($option_name);
                    $checked = $is_active ? ' checked="checked"' : '';
                    $has_alpha = isset($alpha_channel[$id]);

                    printf('
    <p class="integrate-toggle">
      <input type="checkbox" id="kt_%1$s" name="kt_%1$s" tabindex="10" value="1"%3$s%4$s />
      <label for="kt_%1$s">%2$s</label>
    </p>', $id, sprintf($_add_to, $label), $checked, $has_alpha ? ' data-form="1"' : '');

                    if ($has_alpha) {
                        $alpha = get_option($alpha_channel[$id]);
                        $hidden = $is_active ? '' : ' hide-if-js';
                        $checked = $alpha ? ' checked="checked"' : '';

                        printf('
    <p class="integrate-form%2$s" id="kt_%1$s_form">
      <input type="checkbox" id="kt_%1$s_alpha" name="kt_%1$s_alpha" tabindex="10" value="1"%3$s />
      <label for="kt_%1$s_alpha">%4$s</label>
    </p>', $id, $hidden, $checked, sprintf($_use_alpha, $label));
                    }
                }
                print '
  </div>';
            }
            print '
</div>';

            $classes = array('show-palette');
            $autoname = $this->get_cookie(self::AUTONAME, self::DEFAULT_AUTONAME);
            $_autoname = esc_html__('Automatic Names', 'kt-tinymce-color-grid');
            $checked = $autoname ? ' checked="checked"' : '';
            if ($autoname) {
                $classes[] = 'autoname';
            }

            $palette = (array) get_option(self::PALETTE);
            $counts = array(self::COLOR_ACTIVE => 0, self::COLOR_INACTIVE => 0);
            foreach ($palette as $set) {
                if ($set) {
                    $counts[$set['status']] += 1;
                }
            }
            if (!$counts[self::COLOR_ACTIVE]) {
                $classes[] = 'no-active';
            }
            if (!$counts[self::COLOR_INACTIVE]) {
                $classes[] = 'no-inactive';
            }

            $classes = implode(' ', $classes);

            print "
<div id='kt_toolbar' role='toolbar'>
  <button id='kt_add' type='button' tabindex='8' class='button' aria-controls='kt_colors' accesskey='$add_key' title='$add_title'>
    <span class='dashicons dashicons-plus-alt2'></span>
    <span class='screen-reader-text'>$add_label</span>
  </button>
  <span class='tab-wrap'>
    <button id='kt_tab_palette' type='button' class='tab tab-active'>
      <span class='dashicons dashicons-visibility'></span>
      <span class='label'>" . __('Active', 'kt-tinymce-color-grid') . "</span>
      <span class='count'>(" . $counts[self::COLOR_ACTIVE] . ")</span>
    </button>
    <button id='kt_tab_trash' type='button' class='tab'>
      <span class='dashicons dashicons-hidden'></span>
      <span class='label'>" . __('Inactive', 'kt-tinymce-color-grid') . "</span>
      <span class='count'>(" . $counts[self::COLOR_INACTIVE] . ")</span>
    </button>
  </span>
  <span class='switch-wrap alignright'>
    <input type='checkbox' id='kt_autoname'$checked/>
    <label for='kt_autoname'>$_autoname</label>
  </span>
</div>
<div id='kt_color_editor' class='$classes'>
  <div class='empty-editor'>
    <span class='empty-note no-active'>" . esc_attr__('Palette is empty', 'kt-tinymce-color-grid') . "</span>
    <span class='empty-note no-inactive'>" . esc_attr__('No inactive colors', 'kt-tinymce-color-grid') . "</span>
  </div>";

            $index_debug = '';
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $index_debug = '
  <span class="index-debug">%5$s</span>';
            }

            $list_entry = vsprintf('
<div class="picker %8$s" tabindex="2" aria-grabbed="false">
  <input type="hidden" name="kt_palette[index][]" value="%5$s" class="color-index"/>' . $index_debug . '
  <input type="hidden" name="kt_palette[status][]" value="%7$s" class="color-status"/>
  <svg class="grip" width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false">
    <circle cx="5" cy="7" r="1"/><circle cx="5" cy="11" r="1"/>
    <circle cx="13" cy="7" r="1"/><circle cx="13" cy="11" r="1"/>
    <circle cx="9" cy="11" r="1"/><circle cx="9" cy="7" r="1"/>
  </svg>
  <button type="button" class="color" tabindex="4" aria-haspopup="true" aria-controls="kt_picker" aria-describedby="contextual-help-link" aria-label="%9$s">
    <span class="sample">
      <span class="rgb" style="background-color:%1$s"></span>
      <span class="rgba" style="background-color:%2$s"></span>
    </span>
  </button>
  <input class="hex" type="text" name="kt_palette[color][]" tabindex="4" value="%1$s" maxlength="7" placeholder="#RRGGBB" autocomplete="off" aria-label="%10$s" pattern="\s*#?([a-fA-F0-9]{3}){1,2}\s*" required="required" title="%11$s" />
  <input class="alpha" type="number" name="kt_palette[alpha][]" tabindex="4" value="%3$s" min="0" max="100" step="1" autocomplete="off" aria-label="%12$s" title="%13$s" />
  <input class="name%6$s" type="text" name="kt_palette[name][]" value="%4$s" tabindex="5" placeholder="%14$s" aria-label="%15$s" />
  <button type="button" class="autoname" tabindex="5" title="%16$s">
    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" role="img" aria-hidden="true" focusable="false"><path d="M16 4h2v9h-11v3l-5-4 5-4v3h9v-7z"/></svg>
  </button>
  <span class="buttons">
    <button type="button" class="sort-up" tabindex="3" title="%17$s">
      <span class="dashicons dashicons-arrow-up-alt2"></span>
      <span class="screen-reader-text">%17$s</span>
    </button>
    <button type="button" class="sort-down" tabindex="3" title="%18$s">
      <span class="dashicons dashicons-arrow-down-alt2"></span>
      <span class="screen-reader-text">%18$s</span>
    </button>
    <button type="button" class="deactivate" tabindex="6" title="%19$s"">
      <span class="dashicons dashicons-hidden"></span>
      <span class="screen-reader-text">%19$s</span>
    </button>
    <button type="button" class="activate" tabindex="6" title="%20$s"">
      <span class="dashicons dashicons-visibility"></span>
      <span class="screen-reader-text">%20$s</span>
    </button>
    <button type="button" class="remove" tabindex="6" title="%21$s"">
      <span class="dashicons dashicons-no-alt"></span>
      <span class="screen-reader-text">%21$s</span>
    </button>
  </span>
</div>', array(// hex    rgba   alpha    name   index  autoname status  classes
                '%1$s', '%2$s', '%3$s', '%4$s', '%5$s', '%6$s', '%7$s', '%8$s',
                8 => esc_attr__('Color Picker', 'kt-tinymce-color-grid'),
                9 => esc_attr__('Hexadecimal Color', 'kt-tinymce-color-grid'),
                10 => esc_attr__('Three hexadecimal numbers between 00 and FF', 'kt-tinymce-color-grid'),
                11 => esc_attr__('Transparency', 'kt-tinymce-color-grid'),
                12 => esc_attr__('Transparency between 0 and 100', 'kt-tinymce-color-grid'),
                13 => esc_attr__('Unnamed Color', 'kt-tinymce-color-grid'),
                14 => esc_attr__('Name of Color', 'kt-tinymce-color-grid'),
                15 => esc_attr__('Automatic Name', 'kt-tinymce-color-grid'),
                16 => esc_html__('Move up', 'kt-tinymce-color-grid'),
                17 => esc_html__('Move down', 'kt-tinymce-color-grid'),
                18 => esc_html__('Deactivate', 'kt-tinymce-color-grid'),
                19 => esc_html__('Activate', 'kt-tinymce-color-grid'),
                20 => esc_html__('Remove', 'kt-tinymce-color-grid'),
            ));

            $i = array(self::COLOR_ACTIVE => 0, self::COLOR_INACTIVE => 0);
            foreach ($palette as $set) {
                if (!$set || !$set['color']) {
                    continue;
                }
                extract($set);
                $classes = array();
                $classes[] = 'picker-' . ($status == self::COLOR_ACTIVE ? 'active' : 'inactive');
                if ($i[$status] == 0) {
                    $classes[] = 'first-picker';
                }
                if ($i[$status] == $counts[$status] - 1) {
                    $classes[] = 'last-picker';
                }
                vprintf($list_entry, array(
                    esc_attr($color),
                    $this->hex2rgba($color, $alpha),
                    esc_attr($alpha),
                    esc_attr($name),
                    esc_attr($index),
                    $name ? '' : ' autoname',
                    $status,
                    implode(' ', $classes)
                ));
                $i[$status] += 1;
            }

            vprintf("</div>
<script type='text/template' id='tmpl-kt_color_entry'>$list_entry</script>", array(
                '#000000',
                'rgba(0,0,0,1)',
                100,
                '',
                0,
                ' autoname',
                self::COLOR_ACTIVE,
                'picker-active',
            ));
        }

        /**
         * Print backup metabox
         * @since 1.9
         */
        public function print_backup_metabox() {
            $this->print_export_forms();
            $this->print_import_forms();
        }

        public function print_export_forms() {
            print '
<h4>' . esc_html__('Export', 'kt-tinymce-color-grid') . '</h4>
<p>
  <label for="kt_export_format">' . esc_html__('Format', 'kt-tinymce-color-grid') . '</label>
  <select id="kt_export_format" name="kt_export_format">';
            $current_format = $this->get_cookie('kt_export_format', 'base64');
            $forms = array();
            foreach ($this->export_formats as $id => $export_format) {
                $label = esc_html($export_format['name']);
                $selected = $id == $current_format ? ' selected="selected"' : '';

                $form = '';
                if (isset($export_format['form'])) {
                    $form = $export_format['form'];
                    if (!isset($forms[$form])) {
                        $forms[$form] = array(
                            'label' => array(),
                        );
                    }
                    $forms[$form]['label'][] = $label;
                    $forms[$form]['active'] = $forms[$form]['active'] || $id == $current_format;
                }

                print "
    <option value='$id' data-form='$form'$selected>$label</option>";
            }
            print '
  </select>
</p>';

            foreach ($forms as $id => $form) {
                $label = sprintf(__('Options for %s', 'kt-tinymce-color-grid'), implode(' / ', $form['label']));
                $active = $form['active'] ? '' : ' hide-if-js';
                print "
<div id='kt_export_{$id}_form' class='export-format-form$active'>
  <h4 class='screen-reader-text'>$label</h4>";
                call_user_func(array($this, "print_{$id}_export_form"));
                print '
</div>';
            }

            print "
<p><button type=submit' id='kt_action_export' class='button' name='kt_action' value='export' tabindex='9'>" . esc_html__('Download Export', 'kt-tinymce-color-grid') . '</button></p>
<hr/>';
        }

        public function print_import_forms() {
            $_note = '
<p class="description">' . esc_html__('Currently only files in Backup and JSON format are supported.', 'kt-tinymce-color-grid') . '</p>';
            print '
<h4>' . esc_html__('Import', 'kt-tinymce-color-grid') . '</h4>';
            if ($this->supports('upload')) {
                print $_note . '
<p>
  <label id="kt_upload_label" for="kt_upload" class="button" tabindex="10">
    <span class="spinner"></span>
    <span class="label">' . esc_html__('Choose Import', 'kt-tinymce-color-grid') . "&hellip;</span>
    <span class='loading'>" . esc_html__('Uploading', 'kt-tinymce-color-grid') . "&hellip;</span>
  </label>
</p>
<p class='hide-if-js'>
  <input type='file' id='kt_upload' name='kt_upload' accept='.bak,.json'/>
</p>";
            } else {
                print '
<p>' . esc_html__('Your device seems not to support file uploads. Open your import in a simple text editor and paste its content into this textfield.', 'kt-tinymce-color-grid') . "</p>$_note
<p><textarea name='kt_raw_upload' class='widefat' rows='5'></textarea></p>
<p><button type='submit' class='button' name='kt_action' value='import' tabindex='10'>" . esc_html__('Upload Import', 'kt-tinymce-color-grid') . '</button></p>';
            }
        }

        public function print_json_export_form() {
            $parts = array(
                'settings' => __('Settings', 'kt-tinymce-color-grid'),
                'palette' => __('Palette', 'kt-tinymce-color-grid'),
            );
            print '
<p>';
            foreach ($parts as $key => $label) {
                $id = "kt_export_{$key}";
                $checked = $this->get_cookie($id, 1);
                $key = esc_attr($key);
                print "
  <input type='checkbox' id='$id' name='kt_export_parts[]' value='$key'" . checked($checked, 1, 0) . "/>
  <label for='$id'>" . esc_html($label) . "</label>";
            }
            print '
</p>';
        }

        public function print_css_export_form() {
            $affixes = array(
                'prefix' => __('Class Prefix', 'kt-tinymce-color-grid'),
                'suffix' => __('Class Suffix', 'kt-tinymce-color-grid'),
            );
            $_none = __('None', 'kt-tinymce-color-grid');
            foreach ($affixes as $affix => $label) {
                $id = "kt_export_css_{$affix}";
                $cookie = esc_attr($this->get_cookie($id));
                $label = esc_html($label);
                print "
<p>
  <label for='$id'>$label</label>
  <input type='text' id='$id' name='$id' value='$cookie' placeholder='$_none' />
</p>";
            }

            $alpha = 'kt_export_css_alpha';
            list($key, $hex, $rgba) = $this->get_preview_color();
            $alpha_checked = checked($this->get_cookie($alpha), 1, false);
            print "
<p>
  <input type='checkbox' id='$alpha' name='$alpha' value='1'$alpha_checked />
  <label for='$alpha'>" . esc_html__('Add Transparency Values', 'kt-tinymce-color-grid') . "</label>
</p>
<div id='kt_export_css_preview' class='export-preview'>
  <strong>" . esc_html__('Preview', 'kt-tinymce-color-grid') . "</strong>
  <pre></pre>
</div>
<script type='text/template' id='tmpl-kt_export_css_preview'>.{{ data.prefix }}$key{{ data.suffix }} {
  color: #$hex<# if (data.alpha) { #>;
  color: rgba($rgba)<# } #> }</script>";
        }

        public function print_scss_export_form() {
            $affixes = array(
                'prefix' => __('Variable Prefix', 'kt-tinymce-color-grid'),
                'suffix' => __('Variable Suffix', 'kt-tinymce-color-grid'),
            );
            $_none = __('None', 'kt-tinymce-color-grid');
            foreach ($affixes as $affix => $label) {
                $id = "kt_export_scss_{$affix}";
                $cookie = esc_attr($this->get_cookie($id));
                $label = esc_html($label);
                print "
<p>
  <label for='$id'>$label</label>
  <input type='text' id='$id' name='$id' value='$cookie' placeholder='$_none' />
</p>";
            }
            list($key, $_, $rgba) = $this->get_preview_color();
            print "
<div id='kt_export_scss_preview' class='export-preview'>
  <strong>" . esc_html__('Preview', 'kt-tinymce-color-grid') . "</strong>
  <pre></pre>
</div>
<script type='text/template' id='tmpl-kt_export_scss_preview'>\${{ data.prefix }}$key{{ data.suffix }}: rgba($rgba);</script>";
        }

        protected function get_preview_color() {
            $colors = array(
                array('pomgranate', 'E64B17', '230, 75, 23, 0.57'),
                array('indigo', '4469C8', '68, 105, 200, 0.11'),
                array('emerald', '51C556', '81, 197, 86, 0.82'),
                array('amethyst', '9B44C8', '155, 68, 200, 0.41'),
                array('irish-coffee', '6A3526', '106, 53, 38, 0.68'),
            );
            static $i = null;
            if (!$i) {
                $i = array_rand($colors);
            }
            return $colors[$i];
        }

        /**
         * Highlight an accesskey inside a translated string
         * @since 1.4.4
         * @param string $string Translated string
         * @param string $key Accesskey
         * @return string
         */
        protected function underline_accesskey($string, $key) {
            $pattern = '~(' . preg_quote($key, '~') . ')~i';
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
        protected function get_request($key, $default = null) {
            return key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
        }

        /**
         * Get a cookie
         * @since 1.9
         * @param string $name Cookie name
         * @param mixed $default Default value if cookie is not set
         * @return string
         */
        protected function get_cookie($name, $default = null) {
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
         * Sanitize a string to #RRGGBB
         * @since 1.4
         * @since 1.13 optionally prepends a #
         * @param string $string String to be checked
         * @param boolean $prepend_hash [optional] Prepend resulting string with a hash
         * @return string|boolean Returns a color of #RRGGBB or false on failure
         */
        public function sanitize_color($string, $prepend_hash = true) {
            $string = strtoupper($string);
            $hex = $this->preg_get('~#?([0-9A-F]{6}|[0-9A-F]{3})~', $string);
            if ($hex === null) {
                return false;
            }
            if (strlen($hex) == 3) {
                $hex = preg_replace('~([0-9A-F])~', '\1\1', $hex);
            }
            return $prepend_hash ? "#$hex" : $hex;
        }

        /**
         * Sanitize an alpha value
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
         * @param int|float $i [0..255]
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
         * @param array $rgb RGB vector [red, gree, blue] of floats [0..1]
         * @param bool $floats Return vector of floats
         * @return string|false Returns false if any of the components is outside [0..1]
         */
        public function rgb2hex($rgb, $floats = false, $prepend_hash = true) {
            foreach ($rgb as $i => $x) {
                if ($floats) {
                    $x *= 255;
                }
                if ($x < 0 || $x > 255) {
                    return false;
                }
                $rgb[$i] = $this->int2hex($x);
            }
            return ($prepend_hash ? '#' : '') . implode('', $rgb);
        }

        /**
         * Convert a HEX string to a RGB vector
         * @since 1.13
         * @param string $hex
         * @return array
         */
        public function hex2rgb($hex) {
            $color = (string) $this->sanitize_color($hex, false);
            $hex = str_split($color, 2);
            return array_map('hexdec', $hex);
        }

        /**
         * Convert a HEX string and an alpha value into RGBA notation
         * @since 1.13
         * @param string $hex Hexadecimal color
         * @param int $alpha Alpha value [0..100]
         * @return string
         */
        public function hex2rgba($hex, $alpha) {
            list($r, $g, $b) = $this->hex2rgb($hex);
            $a = round($alpha / 100, 2);
            return "rgba($r,$g,$b,$a)";
        }

    }

    kt_Central_Palette::instance();
}
