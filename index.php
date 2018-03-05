<?php

/*
 * Plugin Name: Central Color Palette
 * Plugin URI: https://wordpress.org/plugins/kt-tinymce-color-grid
 * Description: Take full control over color pickers of TinyMCE and the palette of the Theme Customizer. Create a central color palette for an uniform look'n'feel!
 * Version: 2.0
 * Author: Gáravo
 * Author URI: http://profiles.wordpress.org/kungtiger
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kt-tinymce-color-grid
 */

if (defined('ABSPATH') && !class_exists('kt_Central_Palette')) {
    define('KT_CENTRAL_PALETTE', '2.0');
    define('KT_CENTRAL_PALETTE_URL', plugin_dir_url(__FILE__));

    class kt_Central_Palette {

        const KEY = 'kt_tinymce_color_grid';
        const NONCE = 'kt-tinymce-color-grid-save-editor';
        const INTEGRATE = 'kt_color_grid_i9n';
        const MAP = 'kt_color_grid_map';
        const GRID_TYPE = 'kt_color_grid_type';
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
        const DEFAULT_GRID_TYPE = 'rainbow';
        const DEFAULT_LUMA = 'natural';
        const MAX_FILE_SIZE = 256000;

        protected $blocks = array(4, 6);
        protected $sizes = array(4, 5, 6);
        protected $spread = array('even', 'odd');
        protected $clamp = array('row', 'column');
        protected $columns = array(6, 12, 18);
        protected $rows = array(5, 7, 9, 11, 13);
        protected $axes = array('rgb', 'rbg', 'grb', 'gbr', 'brg', 'bgr');

        /**
         * Singleton Design
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
         * @ignore
         */
        public function __construct() {
            if (self::$Instance) {
                return;
            }

            add_action('init', array($this, 'init_plugin'));
            add_action('plugins_loaded', array($this, 'load_textdomain'));
            add_action('admin_menu', array($this, 'add_settings_page'));
            add_filter('plugin_action_links', array($this, 'add_action_link'), 10, 2);

            $this->update_plugin();
        }

        /**
         *
         * Check if any integration needs an alpha channel
         * @since 2.0
         * @ignore
         * @staticvar null|boolean $result
         * @return boolean
         */
        protected function support_alpha() {
            static $result = null;
            if ($result !== null) {
                return $result;
            }
            $result = false;
            $integrations = $this->get_integrations('id');
            foreach ($integrations as $id) {
                if ($this->integration_enabled($id)) {
                    $result = true;
                    break;
                }
            }
            return $result;
        }

        /**
         * Wrapper for _device_can_upload
         * @since 2.0
         * @ignore
         * @return boolean
         */
        protected function can_upload() {
            if (function_exists('wp_is_mobile')) {
                return !(function_exists('_device_can_upload') && !_device_can_upload());
            }
            return true;
        }

        /**
         * Update procedures
         * @since 1.6
         * @ignore
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
                        $map = $this->render_rainbow();
                        if ($map) {
                            update_option('kt_color_grid_map', $map);
                        }
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
                        $version = '2.0';

                        $palette = get_option('kt_color_grid_palette', array());
                        $_palette = array();
                        foreach ($palette as $set) {
                            list($color, $alpha, $name) = $set;
                            if (substr($color, 0, 1) != '#') {
                                $color = "#$color";
                            }
                            $_palette[] = compact('color', 'alpha', 'name');
                        }
                        update_option('kt_color_grid_palette', $_palette);

                        $map = get_option('kt_color_grid_map', array());
                        list($map, $columns, $extra, $mono, $rows) = $map;
                        $columns += $mono + $extra;
                        update_option('kt_color_grid_map', compact('map', 'rows', 'columns'));

                        $i9n = array();
                        $map = array(
                            'elementor' => 'elementor',
                            'gp' => 'generatepress',
                            'oceanwp' => 'oceanwp'
                        );
                        foreach ($map as $option => $id) {
                            $option = "kt_color_grid_{$option}";
                            if (get_option($option)) {
                                $i9n[] = $id;
                            }
                            delete_option($option);
                        }
                        update_option('kt_color_grid_i9n', $i9n);
                        break;

                    default:
                        $version = KT_CENTRAL_PALETTE;
                }
            }
            update_option(self::ACTIVE_VERSION, KT_CENTRAL_PALETTE);
        }

        /**
         * Compare against WP version
         * @since 1.11
         * @ignore
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
         * @since 2.0
         * @ignore
         */
        public function load_textdomain() {
            // load_plugin_textdomain is obsolete since WordPress 4.6
            if ($this->version_compare('<', '4.6')) {
                load_plugin_textdomain('kt-tinymce-color-grid');
            }
        }

        /**
         * Init
         * @since 1.4.4
         * @ignore
         */
        public function init_plugin() {
            if (get_option(self::GRID_TYPE, self::DEFAULT_GRID_TYPE) != 'default') {
                add_filter('tiny_mce_before_init', array($this, 'tinymce_integration'));
                add_action('after_wp_tiny_mce', array($this, 'print_tinymce_style'));
            }

            if (get_option(self::CUSTOMIZER)) {
                $fn = array($this, 'iris_integration');
                add_action('admin_print_scripts', $fn);
                add_action('admin_print_footer_scripts', $fn);
                add_action('customize_controls_print_scripts', $fn);
                add_action('customize_controls_print_footer_scripts', $fn);
            }

            add_action('kt/central_palette/add_integration', array($this, 'default_integrations'));

            /**
             *
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action('kt/central_palette/add_integration', $this);

            $integrations = $this->get_integrations('palette');
            foreach ($integrations as $id => $fn) {
                if ($this->integration_active($id)) {
                    call_user_func($fn, $this);
                }
            }
        }

        protected $registry = array();

        protected function registry_add($group, $id, $object) {
            if (sanitize_key($id) != $id) {
                return false;
            }
            if ($this->registry_has($group, $id)) {
                return false;
            }
            $this->registry[$group][$id] = $object;
            return true;
        }

        protected function registry_has($group, $id) {
            if ($group && !isset($this->registry[$group])) {
                $this->registry[$group] = array();
            }
            return $id && isset($this->registry[$group][$id]);
        }

        protected function registry_get($group, $id = null, $default = null) {
            if (func_num_args() == 1) {
                return isset($this->registry[$group]) ? $this->registry[$group] : array();
            }
            if ($this->registry_has($group, $id)) {
                return $this->registry[$group][$id];
            }
            return $default;
        }

        protected function registry_remove($group, $id) {
            if ($this->registry_has($group, $id)) {
                unset($this->registry[$group][$id]);
                return true;
            }
            return false;
        }

        public function add_integration($id, $options = '') {
            if ($this->has_integration($id)) {
                return false;
            }
            $integration = wp_parse_args($options);

            if (!isset($integration['palette']) || !is_callable($integration['palette'])) {
                return false;
            }

            if (!isset($integration['enabled'])) {
                $integration['enabled'] = true;
            }

            if (!isset($integration['name']) || !$integration['name']) {
                $integration['name'] = ucfirst($id);
            }

            if (!isset($integration['alpha'])) {
                $integration['alpha'] = false;
            }
            $integration['alpha'] = (bool) $integration['alpha'];

            return $this->registry_add('integration', $id, $integration);
        }

        public function has_integration($id) {
            return $this->registry_has('integration', $id);
        }

        public function get_integration($id) {
            return $this->registry_get('integration', $id);
        }

        public function get_integrations($output = 'all') {
            $integrations = $this->registry_get('integration');
            switch ($output) {
                case 'id': return array_keys($integrations);
                case 'palette':
                    $map = array();
                    foreach ($integrations as $id => $integration) {
                        $map[$id] = $integration[$output];
                    }
                    return $map;
            }
            return $integrations;
        }

        public function remove_integration($id) {
            return $this->registry_remove('integration', $id);
        }

        public function integration_enabled($id) {
            $integration = $this->get_integration($id);
            if (!$integration) {
                return null;
            }
            $enabled = $integration['enabled'];
            if (is_callable($enabled)) {
                $enabled = call_user_func($enabled, $this);
            }
            return (bool) $enabled;
        }

        public function integration_active($id) {
            if (!$this->has_integration($id)) {
                return null;
            }
            $integrate = get_option(self::INTEGRATE, array());
            return in_array($id, $integrate);
        }

        public function add_grid_type($id, $options = null) {
            if ($this->has_grid_type($id)) {
                return false;
            }
            $grid_type = wp_parse_args($options);
            if (!isset($grid_type['render']) || !is_callable($grid_type['render'])) {
                return false;
            }

            if (!isset($grid_type['name'])) {
                $grid_type['name'] = ucfirst($id);
            }
            return $this->registry_add('grid_type', $id, $grid_type);
        }

        public function has_grid_type($id) {
            return $id == 'default' || $this->registry_has('grid_type', $id);
        }

        protected function get_default_grid_type() {
            return array(
                'name' => __('Default', 'kt-tinymce-color-grid'),
                'render' => null,
            );
        }

        public function get_grid_type($id) {
            if ($id == 'default') {
                return $this->get_default_grid_type();
            }
            return $this->registry_get('grid_type', $id);
        }

        public function get_grid_types($output = 'all') {
            $types = array(
                'default' => $this->get_default_grid_type(),
            );
            $types += $this->registry_get('grid_type');
            switch ($output) {
                case 'id': return array_keys($types);
                case 'name':
                case 'render':
                    $map = array();
                    foreach ($types as $id => $type) {
                        $map[$id] = $type[$output];
                    }
                    return $map;
            }
            return $types;
        }

        public function get_current_grid_type() {
            $ids = array(
                get_option(self::GRID_TYPE, self::DEFAULT_GRID_TYPE),
                self::DEFAULT_GRID_TYPE,
            );
            foreach ($ids as $id) {
                if ($this->has_grid_type($id)) {
                    return $id;
                }
            }
            return 'default';
        }

        public function remove_grid_type($id) {
            if ($id == 'default') {
                return false;
            }
            return $this->registry_remove('grid_type', $id);
        }

        public function add_luma_type($id, $options = '') {
            if ($this->has_luma_type($id)) {
                return false;
            }
            $luma_type = wp_parse_args($options);
            if (!isset($luma_type['fn']) || !is_callable($luma_type['fn'])) {
                return false;
            }
            if (!isset($luma_type['name']) || !$luma_type['name']) {
                $luma_type['name'] = ucfirst($id);
            }
            return $this->registry_add('luma_type', $id, $luma_type);
        }

        public function has_luma_type($id) {
            return $id == 'linear' || $this->registry_has('luma_type', $id);
        }

        protected function get_linear_luma_type() {
            return array(
                'name' => __('Linear', 'kt-tinymce-color-grid'),
                'fn' => null,
            );
        }

        public function get_luma_type($id) {
            if ($id == 'linear') {
                return $this->get_linear_luma_type();
            }
            return $this->registry_get('luma_type', $id);
        }

        public function get_luma_types($output = 'all') {
            $types = array(
                'linear' => $this->get_linear_luma_type(),
            );
            $types += $this->registry_get('luma_type');
            switch ($output) {
                case 'id': return array_keys($types);
                case 'name':
                case 'fn':
                    $map = array();
                    foreach ($types as $id => $type) {
                        $map[$id] = $type[$output];
                    }
                    return $map;
            }
            return $types;
        }

        public function get_current_luma_type() {
            $ids = array(
                get_option(self::LUMA, self::DEFAULT_LUMA),
                self::DEFAULT_LUMA,
            );
            foreach ($ids as $id) {
                if ($this->has_luma_type($id)) {
                    return $id;
                }
            }
            return 'linear';
        }

        public function remove_luma_type($id) {
            if ($id == 'linear') {
                return false;
            }
            return $this->registry_remove('luma_type', $id);
        }

        /**
         *
         * @ignore
         */
        public function default_grid_types() {
            $this->add_grid_type('palette', array(
                'name' => __('Central Palette', 'kt-tinymce-color-grid'),
                'render' => array($this, 'render_palette'),
                'form' => array($this, 'print_palette_form'),
            ));
            $this->add_grid_type('rainbow', array(
                'name' => __('Rainbow', 'kt-tinymce-color-grid'),
                'render' => array($this, 'render_rainbow'),
                'form' => array($this, 'print_rainbow_form'),
            ));
            $this->add_grid_type('block', array(
                'name' => __('Blocks', 'kt-tinymce-color-grid'),
                'render' => array($this, 'render_blocks'),
                'form' => array($this, 'print_blocks_form'),
            ));
        }

        /**
         *
         * @ignore
         */
        public function default_luma_types() {
            $this->add_luma_type('sine', array(
                'name' => __('Sine', 'kt-tinymce-color-grid'),
                'fn' => array($this, 'sine_luma')
            ));
            $this->add_luma_type('cubic', array(
                'name' => __('Cubic', 'kt-tinymce-color-grid'),
                'fn' => array($this, 'cubic_luma'),
            ));
            $this->add_luma_type('natural', array(
                'name' => __('Natural', 'kt-tinymce-color-grid'),
                'fn' => array($this, 'natural_luma'),
            ));
        }

        public function default_integrations() {
            $this->add_integration('elementor', array(
                'name' => __('Elementor', 'kt-tinymce-color-grid'),
                'enabled' => defined('ELEMENTOR_VERSION'),
                'palette' => array($this, 'integrate_elementor'),
                'alpha' => false,
            ));
            $this->add_integration('generatepress', array(
                'name' => __('GeneratePress Premium', 'kt-tinymce-color-grid'),
                'enabled' => defined('GP_PREMIUM_VERSION'),
                'palette' => array($this, 'integrate_generatepress'),
                'alpha' => true,
            ));
            $this->add_integration('oceanwp', array(
                'name' => __('OceanWP', 'kt-tinymce-color-grid'),
                'enabled' => defined('OCEANWP_THEME_VERSION'),
                'palette' => array($this, 'integrate_oceanwp'),
                'alpha' => true,
            ));
        }

        public function integrate_elementor() {
            add_filter('elementor/editor/localize_settings', array($this, 'elementor_integration'));
            add_action('elementor/editor/after_enqueue_scripts', array($this, 'elementor_styles'));
        }

        public function integrate_generatepress() {
            add_filter('generate_default_color_palettes', array($this, 'generatepress_integration'));
        }

        public function integrate_oceanwp() {
            add_filter('ocean_default_color_palettes', array($this, 'oceanwp_integration'));
        }

        /**
         * Get the color palette
         * @since 1.11
         * @param string|array $options
         * @return array
         */
        public function get_palette($options = '') {
            $options = wp_parse_args($options, array(
                'alpha' => get_option(self::ALPHA),
                'min' => 6,
                'pad' => '#FFFFFF',
            ));
            $palette = get_option(self::PALETTE, array());
            $_palette = array();
            foreach ($palette as $set) {
                $color = $set['color'];
                if ($options['alpha'] && $set['alpha'] < 100) {
                    $color = $this->hex2rgba($color, $set['alpha']);
                }
                $_palette[] = $color;
            }
            $_palette = array_pad($_palette, $options['min'], $options['pad']);
            return apply_filters('kt/central_palette/get_palette', $_palette, $palette, $options);
        }

        /**
         * GeneratePress Premium integration
         * @since 1.10
         * @ignore
         * @param array $palette
         * @return array
         */
        public function generatepress_integration($palette) {
            $_palette = $this->get_palette();
            return $_palette ? $_palette : $palette;
        }

        /**
         * OceanWP integration
         * @since 1.11
         * @ignore
         * @param array $palette
         * @return array
         */
        public function oceanwp_integration($palette) {
            return $this->generatepress_integration($palette);
        }

        /**
         * Enqueue a stylesheet for Elementor
         * @since 1.10
         * @ignore
         */
        public function elementor_styles() {
            wp_enqueue_style(self::KEY . '-elementor', KT_CENTRAL_PALETTE_URL . 'css/elementor.css', null, KT_CENTRAL_PALETTE);
        }

        /**
         * Elementor integration
         * @since 1.10
         * @ignore
         * @param array $config
         * @return array
         */
        public function elementor_integration($config) {
            $palette = get_option(self::PALETTE, array());
            $_palette = array();
            for ($i = 1, $n = count($palette); $i <= $n; $i++) {
                $_palette[$i] = array('value' => $palette[$i - 1]['color']);
            }
            if ($n < 6) {
                for (; $i <= 6; $i++) {
                    $_palette[$i] = array('value' => '#FFF');
                }
            }
            $config['schemes'] = array(
                'items' => array(
                    'color-picker' => array(
                        'items' => $_palette,
                    ),
                ),
            );
            return $config;
        }

        /**
         * wpColorPicker/Iris integration
         * @since 1.7
         * @ignore
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
                $colors[] = '"' . esc_js($set['color']) . '"';
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
         * @ignore
         */
        public function print_tinymce_style() {
            $map = get_option(self::MAP);
            if (!$map || !$map['rows']) {
                return;
            }
            $rows = $map['rows'];
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
         * @ignore
         * @param array $init Wordpress' TinyMCE inits
         * @return array
         */
        public function tinymce_integration($init) {
            $map = get_option(self::MAP);
            if ($map) {
                if (!$map['rows']) {
                    return $init;
                }
                $init['textcolor_map'] = $map['map'];
                $init['textcolor_cols'] = $map['columns'];
                $init['textcolor_rows'] = $map['rows'];
            }
            return $init;
        }

        /**
         * Add a link to the plugin listing
         * @since 1.4
         * @ignore
         * @param array $links Array holding HTML
         * @param string $file Current name of plugin file
         * @return array Modified array
         */
        public function add_action_link($links, $file) {
            if (plugin_basename($file) == plugin_basename(__FILE__)) {
                $links[] = '<a href="options-general.php?page=' . self::KEY . '" class="dashicons-before dashicons-admin-settings" title="' . esc_attr__('Opens the settings page for this plugin', 'kt-tinymce-color-grid') . '"> ' . esc_html__('Color Palette', 'kt-tinymce-color-grid') . '</a>';
            }
            return $links;
        }

        /**
         * Add settings page to WordPress' admin menu
         * @since 1.3
         * @ignore
         */
        public function add_settings_page() {
            $name = __('Central Color Palette', 'kt-tinymce-color-grid');
            $hook = add_options_page($name, $name, 'manage_options', self::KEY, array($this, 'print_settings_page'));
            add_action("load-$hook", array($this, 'init_settings_page'));
        }

        /**
         * Initialize settings page
         * @since 1.4.4
         * @ignore
         */
        public function init_settings_page() {
            add_filter('removable_query_args', array($this, 'add_removeable_args'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_settings_scripts'));
            add_action('kt/central_palette/add_grid_type', array($this, 'default_grid_types'));
            add_action('kt/central_palette/add_luma_type', array($this, 'default_luma_types'));

            /**
             * Add grid types
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action('kt/central_palette/add_grid_type', $this);

            /**
             * Register luma types
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action('kt/central_palette/add_luma_type', $this);

            $this->save_settings();
            $this->add_help();
            $this->add_metaboxes();

            /**
             *
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action('kt/central_palette/init', $this);
        }

        /**
         * Enqueue JavaScript and CSS files
         * @since 1.3
         * @ignore
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
         * Add removable query arguments for this plugin
         * @since 1.8
         * @ignore
         * @param array $args
         * @return array
         */
        public function add_removeable_args($args) {
            $args[] = 'kt-import-error';
            $args[] = 'kt-export-error';
            return $args;
        }

        /**
         * Add metaboxes to settings page
         * @since 1.9
         * @ignore
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
         * Pass a HTTP request value through a filter and store it as option
         * @since 1.7
         * @ignore
         * @param string $key
         * @param array $constrain
         * @param string $option
         * @param mixed $default
         * @return mixed
         */
        protected function set_option($key, $constrain, $option, $default) {
            $value = $this->get_request($key, $default);
            $value = in_array($value, $constrain) ? $value : $default;
            update_option($option, $value);
            return $value;
        }

        /**
         * Sanitize and save settings
         * @since 1.7
         * @ignore
         */
        protected function save_settings() {
            if (!wp_verify_nonce($this->get_request('kt_settings_nonce'), self::NONCE)) {
                return;
            }

            $action = $this->get_request('kt_action', $this->get_request('kt_hidden_action'));
            $type = $this->get_request('kt_type');
            $types = $this->get_grid_types('id');
            if (!in_array($type, $types)) {
                $type = self::DEFAULT_GRID_TYPE;
            }
            $visual = $type == 'palette' || $this->get_request('kt_visual') ? '1' : false;

            $booleans = array(
                'kt_alpha' => self::ALPHA,
                'kt_customizer' => self::CUSTOMIZER,
            );
            foreach ($booleans as $field => $option) {
                update_option($option, $this->get_request($field) ? '1' : false);
            }

            $integrate = array();
            $_integrate = $this->get_request('kt_integrate', array());
            $ids = $this->get_integrations('id');
            foreach ($_integrate as $id) {
                if (in_array($id, $ids)) {
                    $integrate[] = $id;
                }
            }
            update_option(self::INTEGRATE, $integrate);

            $palette = array();
            $colors = $this->get_request('kt_colors', array());
            $alphas = $this->get_request('kt_alphas', array());
            $names = $this->get_request('kt_names', array());
            foreach ($names as $i => $name) {
                $color = $this->sanitize_color($colors[$i]);
                if ($color) {
                    $name = sanitize_text_field(stripslashes($name));
                    $alpha = $this->sanitize_alpha($alphas[$i]);
                    $palette[] = compact('color', 'alpha', 'name');
                }
            }
            $m = null;
            $l = count($palette);
            if ($action == 'add') {
                $color = '#000000';
                $alpha = 100;
                $name = '';
                $palette[] = compact('color', 'alpha', 'name');
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
            update_option(self::GRID_TYPE, $type);
            update_option(self::VISUAL, $visual);
            update_option(self::PALETTE, $palette);

            $lumas = $this->get_luma_types('id');

            $this->set_option('kt_rows', $this->rows, self::ROWS, self::DEFAULT_ROWS);
            $this->set_option('kt_cols', $this->columns, self::COLS, self::DEFAULT_COLS);
            $this->set_option('kt_luma', $lumas, self::LUMA, self::DEFAULT_LUMA);
            $this->set_option('kt_blocks', $this->blocks, self::BLOCKS, self::DEFAULT_BLOCKS);
            $this->set_option('kt_block_size', $this->sizes, self::SIZE, self::DEFAULT_SIZE);
            $this->set_option('kt_axis', $this->axes, self::AXIS, self::DEFAULT_AXIS);
            $this->set_option('kt_spread', $this->spread, self::SPREAD, self::DEFAULT_SPREAD);
            $this->set_option('kt_clamp', $this->clamp, self::CLAMP, self::DEFAULT_CLAMP);

            $clamps = intval($this->get_request('kt_clamps'));
            if ($clamps < 4 || $clamps > 18) {
                $clamps = self::DEFAULT_CLAMPS;
            }
            update_option(self::CLAMPS, $clamps);

            $error = $this->handle_backup($action);

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

        protected function handle_backup(&$action) {
            $backup_actions = array('import', 'export');
            if (!in_array($action, $backup_actions)) {
                $action = 'save';
                return false;
            }

            switch ($action) {
                case 'export': return $this->export_backup();
                case 'import': return $this->import_backup();
            }
        }

        /**
         * Return all options as an array
         * @since 1.8
         * @since 1.9 Partial options
         * @ignore
         * @param array $parts
         * @return array
         */
        protected function default_options($parts = null) {
            $options = array(
                self::ACTIVE_VERSION => KT_CENTRAL_PALETTE
            );
            $settings = array(
                self::VISUAL => false,
                self::CUSTOMIZER => false,
                self::INTEGRATE => array(),
                self::ALPHA => false,
                self::GRID_TYPE => self::DEFAULT_GRID_TYPE,
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
         * @ignore
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
            } else if (isset($_REQUEST['kt_import'])) {
                $base64 = $_REQUEST['kt_import'];
            } else {
                return 'no-import';
            }

            if (substr($base64, 0, 1) == '#') {
                $colors = preg_split('~[^#0-9A-Fa-f]+~', $base64);
                $palette = array();
                $alpha = 100;
                $name = '';
                foreach ($colors as $color) {
                    $color = $this->sanitize_color($color);
                    if ($color) {
                        $palette[] = compact('color', 'alpha', 'name');
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

            /**
             * Filters the import data
             * @since 2.0
             * @param array $options
             * @param kt_Central_Palette $instance
             */
            $options = apply_filters('kt/central_palette/import_backup', $options, $this);

            foreach ($names as $name) {
                if (isset($options[$name])) {
                    update_option($name, $options[$name]);
                }
            }
            /**
             * Fires after an import
             * @since 2.0
             * @param array $options
             * @param kt_Central_Palette $instance
             */
            do_action('kt/central_palette/import_backup', $options, $this);

            return 'ok';
        }

        /**
         * Check a file upload
         * @since 1.9
         * @ignore
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
         * @ignore
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
                        $palette = array();
                        foreach ($options[$key] as $set) {
                            $palette[] = array($set[0], 100, $set[1]);
                        }
                        $options[$key] = $palette;
                        break;

                    case 1100:
                    case 1110:
                    case '1.11':
                        $version = '2.0';
                        $key = 'kt_color_grid_palette';
                        $palette = array();
                        foreach ($options[$key] as $set) {
                            list($color, $alpha, $name) = $set;
                            if (substr($color, 0, 1) != '#') {
                                $color = "#$color";
                            }
                            $palette[] = compact('color', 'alpha', 'name');
                        }
                        $options[$key] = $palette;
                        break;

                    default:
                        $version = KT_CENTRAL_PALETTE;
                }
            }
        }

        /**
         * Export settings and trigger a file download
         * @since 1.8
         * @ignore
         * @return string
         */
        protected function export_backup() {
            $parts = $this->get_request('kt_export');
            if (!is_array($parts) || !$parts) {
                return 'no-export';
            }
            $options = $this->default_options($parts);
            foreach ($options as $name => $default) {
                $options[$name] = get_option($name, $default);
            }

            /**
             * Filter the export data
             * @since 2.0
             * @param array $options
             * @parts array $parts
             * @param kt_Central_Palette $instance
             */
            $options = apply_filters('kt/central_palette/export_backup', $options, $parts, $this);

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
            $filename = "central-palette$blogname.bak";
            header('Content-Type: plain/text');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            print $base64;
            exit;
        }

        /**
         * Renders color map
         * @since 1.7
         * @ignore
         */
        protected function render_map() {
            $type = $this->get_current_grid_type();
            if ($type == 'default') {
                return;
            }

            $type = $this->get_grid_type($type);
            $unhash = array($this, 'unhash_color_palette');
            add_filter('option_' . self::PALETTE, $unhash);
            $map = call_user_func($type['render'], $this);
            remove_filter('option_' . self::PALETTE, $unhash);

            update_option(self::MAP, $map);
        }

        public function unhash_color_palette($palette) {
            $_palette = array();
            foreach ($palette as $set) {
                $set['color'] = str_replace('#', '', $set['color']);
                $_palette[] = $set;
            }
            return $_palette;
        }

        /**
         * Chunk palette into columns of constant size
         * @since 1.7
         * @ignore
         * @return array [palette, rows, cols]
         */
        protected function prepare_palette() {
            $palette = array();
            list($rows, $cols) = $this->get_palette_size();
            if (get_option(self::VISUAL)) {
                $palette = get_option(self::PALETTE, array());
                #print '<xmp>';print_r($palette);exit;
                if ($palette) {
                    $palette = array_chunk($palette, $rows);
                    $last = count($palette) - 1;
                    $pad = array('color' => 'FFFFFF', 'alpha' => 100, 'name' => '');
                    $padded = array_pad($palette[$last], $rows, $pad);
                    $palette[$last] = $padded;
                }
            }
            return array($palette, $rows, $cols);
        }

        /**
         * Get palette size depending on its current type
         * @since 1.9
         * @ignore
         * @return array [rows, cols]
         */
        protected function get_palette_size() {
            switch (get_option(self::GRID_TYPE, self::DEFAULT_GRID_TYPE)) {
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
         * @ignore
         * @param array $map passed by reference
         * @param array $palette passed by reference
         * @param int $row
         */
        protected function add_row_to_map(&$map, &$palette, $row) {
            $cols = count($palette);
            for ($col = 0; $col < $cols; $col++) {
                $set = array_map('esc_js', $palette[$col][$row]);
                $map[] = '"' . $set['color'] . '","' . $set['name'] . '"';
            }
        }

        /**
         * Add a monocrome/grayscale color to the color map
         * @since 1.7
         * @ignore
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
         * @ignore
         * @return array
         */
        protected function render_palette() {
            list($palette, $rows, $columns) = $this->prepare_palette();
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);
            }
            $map = '[' . implode(',', $map) . ']';
            return compact('map', 'rows', 'columns');
        }

        /**
         * Render TinyMCE block color map
         * @since 1.7
         * @ignore
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
            list($palette, $rows, $columns) = $this->prepare_palette();
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);

                $b = $square[$row % $size];
                $shift = floor($row / $size) * $per_group;
                for ($col = 0; $col < $columns; $col++) {
                    $g = $square[$col % $size];
                    $r = $chunks[floor($col / $size) + $shift];
                    $map[] = '"' . sprintf($pattern, $r, $g, $b) . '",""';
                }

                $this->add_monocroma($map, $row, $rows);
            }

            $map = '[' . implode(',', $map) . ']';
            $columns += count($palette) + 1;
            return compact('map', 'rows', 'columns');
        }

        /**
         * Render TinyMCE rainbow color map
         * @since 1.7
         * @ignore
         * @return array
         */
        protected function render_rainbow() {
            list($palette, $rows, $columns) = $this->prepare_palette();
            $rgb = array();
            for ($i = 0; $i < $columns; $i++) {
                $rgb[] = $this->hue2rgb($i / $columns);
            }

            $map = array();
            $luma_type = $this->get_current_luma_type();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);

                $luma = 2 * ($row + 1) / ($rows + 1) - 1;
                $luma = $this->transform_luma($luma, $luma_type);
                for ($col = 0; $col < $columns; $col++) {
                    $_rgb = $this->apply_luma($luma, $rgb[$col]);
                    $map[] = '"' . $this->rgb2hex($_rgb) . '",""';
                }

                $this->add_monocroma($map, $row, $rows);
            }

            $map = '[' . implode(',', $map) . ']';
            $columns += count($palette) + 1;
            return compact('map', 'rows', 'columns');
        }

        /**
         * Apply a luma transformation on a RGB vector
         * @since 1.7
         * @ignore
         * @param float $luma [-1..1]
         * @param array $rgb RGB vector [red, gree, blue] of [0..1]
         * @return array
         */
        protected function apply_luma($luma, $rgb) {
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
         * Apply a transformation on a linear float
         * @since 1.7
         * @ignore
         * @param float $luma [-1..1]
         * @param string $type
         * @return float [-1..1]
         */
        protected function transform_luma($luma, $type) {
            if ($type == 'linear') {
                return $luma;
            }
            $luma_type = $this->get_luma_type($type);
            return call_user_func($luma_type['fn'], $luma, $luma_type);
        }

        /**
         * Apply a sine transformation on a linear luma value.
         * @since 1.7
         * @ignore
         * @param float $luma [-1..1]
         * @return float [-1..1]
         */
        public function sine_luma($luma) {
            return $luma < 0 ? sin((1 - $luma) * M_PI_2) - 1 : sin($luma * M_PI_2);
        }

        /**
         * Apply a cubic transformation on a linear luma value.
         * @since 1.7
         * @ignore
         * @param float $luma [-1..1]
         * @return float [-1..1]
         */
        public function cubic_luma($luma) {
            return $luma < 0 ? pow(($luma + 1), 8 / 11) - 1 : pow($luma, 8 / 13);
        }

        /**
         * Apply a natural transformation on a linear luma value.
         * @since 1.7
         * @ignore
         * @param float $luma [-1..1]
         * @return float [-1..1]
         */
        public function natural_luma($luma) {
            return $luma < 0 ? $this->sine_luma($luma) : $this->cubic_luma($luma);
        }

        /**
         * Add help to settings page
         * @since 1.7
         * @ignore
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
            $github_url = esc_url('https://github.com/kungtiger/central-color-palette');
            $github_io = esc_url('https://kungtiger.github.io/central-color-palette/');
            $screen->set_help_sidebar('
<p><strong>' . esc_html__('For more information:', 'kt-tinymce-color-grid') . '</strong></p>
<p><a href="' . $plugin_url . '" target="_blank">' . esc_html__('Visit plugin site', 'kt-tinymce-color-grid') . '</a></p>
<p><a href="' . $support_url . '" target="_blank">' . esc_html__('Support Forums', 'kt-tinymce-color-grid') . '</a></p>
<p><a href="' . $github_url . '" target="_blank">' . esc_html__('Source Code on GitHub', 'kt-tinymce-color-grid') . '</a></p>
<p><a href="' . $github_io . '" target="_blank">' . esc_html__('Documentation & API', 'kt-tinymce-color-grid') . '</a></p>');
        }

        /**
         * Render settings page
         * @since 1.3
         * @ignore
         */
        public function print_settings_page() {
            $head = $this->version_compare('<', '4.3') ? 'h2' : 'h1';
            print "
<div class='wrap'>
  <$head>" . esc_html__('Settings', 'kt-tinymce-color-grid') . ' &rsaquo; ' . esc_html__('Central Color Palette', 'kt-tinymce-color-grid') . "</$head>";
            $this->print_settings_error();

            $support_alpha = $this->support_alpha() && get_option(self::ALPHA) ? ' class="support-alpha"' : '';

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

            $grid_types = $this->get_grid_types('name');
            $current_grid_type = $this->get_current_grid_type();
            foreach ($grid_types as $type => $name) {
                $id = "kt_grid_type_$type";
                $name = esc_html($name);
                print "
        <input type='radio' id='$id' name='kt_type' value='$type'" . checked($type, $current_grid_type, 0) . "/>
        <label for='$id' class='screen-reader-text'>$name</label>";
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

            $this->print_settings_css();
        }

        /**
         * Render settings error
         * @since 1.9
         * @ignore
         */
        protected function print_settings_error() {
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
         *
         * @since 2.0
         * @ignore
         */
        protected function print_settings_css() {
            $grid_types = $this->get_grid_types('id');
            if (!$grid_types) {
                return;
            }

            print "
<style type='text/css' id='" . self::KEY . "_style'>";
            foreach ($grid_types as $id) {
                $id = "kt_grid_type_{$id}";
                print "
#$id:checked ~ .meta-box-sortables .button[for='$id'] {
  display: none }
#$id:checked ~ .meta-box-sortables .button-primary[for='$id'] {
  display: inline-block }
#$id:checked ~ .meta-box-sortables #{$id}_form {
  display: block }";
            }
            print '
</style>';
        }

        /**
         * Print grid metabox
         * @since 1.9
         * @ignore
         */
        public function print_grid_metabox() {
            $grid_types = $this->get_grid_types();
            print "
<p><label>Type</label>
  <span class='button-group type-chooser'>";
            foreach ($grid_types as $id => $grid_type) {
                $id = "kt_grid_type_$id";
                $label = esc_html($grid_type['name']);
                print "
    <label for='$id' class='button'>$label</label>
    <label for='$id' class='button button-primary'>$label</label>";
            }
            print "
  </span>
</p>";

            foreach ($grid_types as $id => $grid_type) {
                if (isset($grid_type['form'])) {
                    $form = $grid_type['form'];
                    if (is_callable($form)) {
                        ob_start();
                        $form = call_user_func($form, $this);
                        $_form = ob_get_contents();
                        ob_end_clean();
                        $form = $_form ? $_form : $form;
                    }
                    print "
<div id='kt_grid_type_{$id}_form' class='grid-type-form'>$form</div>";
                }
            }
        }

        public function print_palette_form() {
            $clamp = array(
                'row' => __('row', 'kt-tinymce-color-grid'),
                'column' => __('column', 'kt-tinymce-color-grid'),
            );
            $_spread = get_option(self::SPREAD, self::DEFAULT_SPREAD);
            $_clamp = get_option(self::CLAMP, self::DEFAULT_CLAMP);
            $_clamps = get_option(self::CLAMPS, self::DEFAULT_CLAMPS);
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
<p>
  <input type='radio' id='$id' name='kt_spread' value='$value'$checked/>
  <label for='$id'>$label</label>
</p>";
            }
        }

        public function print_rainbow_form() {
            $_cols = get_option(self::COLS, self::DEFAULT_COLS);
            $_rows = get_option(self::ROWS, self::DEFAULT_ROWS);
            $cols = $this->selectbox('kt_cols', $this->columns, $_cols);
            $rows = $this->selectbox('kt_rows', $this->rows, $_rows);
            print "
<p>
  <label for='kt_rows'>" . esc_html__('Rows', 'kt-tinymce-color-grid') . "</label>$rows
  <label for='kt_cols'>" . esc_html__('Columns', 'kt-tinymce-color-grid') . "</label>$cols";
            $luma_map = $this->get_luma_types('name');
            if (count($luma_map) > 1) {
                $current_luma = get_option(self::LUMA, self::DEFAULT_LUMA);
                $luma = $this->selectbox('kt_luma', $luma_map, $current_luma);
                print "
  <label for='kt_luma'>" . esc_html__('Luma', 'kt-tinymce-color-grid') . "</label>$luma";
            }
            print '
</p>';
        }

        public function print_blocks_form() {
            $_blocks = get_option(self::BLOCKS, self::DEFAULT_BLOCKS);
            $_size = get_option(self::SIZE, self::DEFAULT_SIZE);
            $_axis = get_option(self::AXIS, self::DEFAULT_AXIS);

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

            $blocks = $this->selectbox('kt_blocks', $this->blocks, $_blocks);
            $size = $this->selectbox('kt_block_size', $size, $_size);
            $axes = $this->selectbox('kt_axis', $axes, $_axis);
            print "
<p>
  <label for='kt_blocks'>" . esc_html__('Block Count', 'kt-tinymce-color-grid') . "</label>$blocks
  <label for='kt_block_size'>" . esc_html__('Block Size', 'kt-tinymce-color-grid') . "</label>$size
  <label for='kt_axis'>" . esc_html__('Plane Axis', 'kt-tinymce-color-grid') . "</label>$axes
</p>";
        }

        /**
         * Print editor metabox
         * @since 1.9
         * @ignore
         */
        public function print_palette_metabox() {
            $this->print_default_options();
            $this->print_integration_options();
            $this->print_color_toolbar();
            $this->print_color_editor();
        }

        protected function print_default_options() {
            $tabindex = 9;
            $grid_type = get_option(self::GRID_TYPE, self::DEFAULT_GRID_TYPE);
            $visual = get_option(self::VISUAL);
            $customizer = get_option(self::CUSTOMIZER);
            if ($grid_type == 'palette') {
                $visual = true;
            }

            print "
<p id='kt_visual_option'>
  <input type='checkbox' id='kt_visual' name='kt_visual' tabindex='$tabindex' value='1'" . checked($visual, 1, 0) . " />
  <label for='kt_visual'>" . esc_html__('Add to Visual Editor', 'kt-tinymce-color-grid') . "</label>
</p>
<p>
  <input type='checkbox' id='kt_customizer' name='kt_customizer' tabindex='$tabindex' value='1'" . checked($customizer, 1, 0) . " />
  <label for='kt_customizer'>" . esc_html__('Add to Theme Customizer', 'kt-tinymce-color-grid') . "</label>
</p>";
        }

        protected function print_integration_options() {
            $_label = __('Integrate with %s', 'kt-tinymce-color-grid');
            $integrations = $this->get_integrations();
            foreach ($integrations as $id => $integration) {
                if (!$this->integration_enabled($id)) {
                    continue;
                }

                print "
<p class='integrate-wrap' id='kt_integrate_{$id}_wrap'>";
                if (isset($integration['form'])) {
                    $form = $integration['form'];
                    if (is_callable($form)) {
                        $form = call_user_func($form, $this);
                    }
                    print $form;
                } else {
                    $checked = $this->integration_active($id) ? ' checked="checked"' : '';
                    if (isset($integration['label'])) {
                        $label = $integration['label'];
                    } else {
                        $label = sprintf($_label, $integration['name']);
                    }
                    printf('
  <input type="checkbox" id="kt_integrate_%1$s" name="kt_integrate[]" tabindex="10" value="%1$s"%2$s />
  <label for="kt_integrate_%1$s">%3$s</label>
', $id, $checked, esc_html($label));
                }
                print '</p>';
            }
        }

        protected function print_color_toolbar() {
            $add_text = __('Add Color', 'kt-tinymce-color-grid');
            print "
<div id='kt_toolbar' role='toolbar'>
  <button id='kt_add' type='submit' tabindex='8' name='kt_action' value='add' class='button' aria-controls='kt_colors' accesskey='" . _x('A', 'accesskey for adding color', 'kt-tinymce-color-grid') . "' title='" . esc_attr($add_text) . "'>
    <span class='dashicons dashicons-plus-alt2'></span>
    <span class='screen-reader-text'>" . esc_html($add_text) . "</span>
  </button>";

            if ($this->support_alpha()) {
                $alpha = get_option(self::ALPHA);
                print "
  <span class='switch-wrap alignright'>
    <input type='checkbox' id='kt_alpha' name='kt_alpha' value='1'" . checked($alpha, 1, 0) . "/>
    <label for='kt_alpha'>" . esc_html__('Transparency', 'kt-tinymce-color-grid') . "</label>
  </span>";
            }

            $use_autoname = $this->cookie(self::AUTONAME, self::DEFAULT_AUTONAME);
            print "
  <span class='switch-wrap alignright hide-if-no-js'>
    <input type='checkbox' id='kt_autoname'" . checked($use_autoname, 1, 0) . "/>
    <label for='kt_autoname'>" . esc_html__('Automatic Names', 'kt-tinymce-color-grid') . "</label>
  </span>
</div>";
        }

        protected function print_color_editor() {
            $autoname = $this->cookie(self::AUTONAME, self::DEFAULT_AUTONAME) ? ' class="autoname"' : '';
            print "
<div id='kt_color_editor' data-empty='" . esc_attr__('Palette is empty', 'kt-tinymce-color-grid') . "'$autoname>";

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
                $set = array_map('esc_attr', $set);
                extract($set, EXTR_PREFIX_ALL, 'c');
                $autofill = $c_name ? '' : ' autoname';
                $rgba = $this->hex2rgba($c_color, $c_alpha);
                printf($list_entry, $c_color, $rgba, $c_alpha, $c_name, $index, $autofill);
            }

            printf("</div>
<script type='text/template' id='tmpl-kt_list_entry'>$list_entry</script>", '#000000', 'rgba(0,0,0,1)', 100, '', 'x', ' autoname');
        }

        /**
         * Print backup metabox
         * @since 1.9
         * @ignore
         */
        public function print_backup_metabox() {
            print '
<p>' . esc_html__('What would you like to backup?', 'kt-tinymce-color-grid') . "</p>
<p id='kt_export'>";

            $parts = array(
                'settings' => __('Settings', 'kt-tinymce-color-grid'),
                'palette' => __('Palette', 'kt-tinymce-color-grid'),
            );
            foreach ($parts as $key => $label) {
                $checked = $this->cookie("kt_export_$key", 1);
                print "
  <input type='checkbox' id='kt_export_$key' name='kt_export[]' value='$key'" . checked($checked, 1, 0) . "/>
  <label for='kt_export_$key'>$label</label>";
            }

            print "</p>
<p class='devider'><button type=submit' id='kt_action_export' class='button' name='kt_action' value='export' tabindex='9'>" . esc_html__('Download Backup', 'kt-tinymce-color-grid') . "</button></p>";

            if ($this->can_upload()) {
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
  <input type='file' id='kt_upload' name='kt_upload' accept='.bak,text/plain'/>";
            } else {
                print "
<p>" . esc_html__('Your device is not supporting file uploads. Open your backup in a simple text editor and paste its content into this textfield.', 'kt-tinymce-color-grid') . "</p>
<p><textarea name='kt_import' class='widefat' rows='5'></textarea></p>
<p><button type='submit' class='button' name='kt_action' value='import' tabindex='10'>" . esc_html__('Upload Backup', 'kt-tinymce-color-grid') . "</button>";
            }
            print '
</p>';
        }

        /**
         * Highlight an accesskey inside a translated string
         * @since 1.4.4
         * @ignore
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
         * @ignore
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
                $value = esc_attr($value);
                $label = esc_html($label);
                $options .= "
                <option value='$value'" . selected($value, $selected, 0) . ">$label</option>";
            }
            $name = esc_attr($name);
            return "
              <select id='$name' name='$name'" . disabled($disabled, 1, 0) . ">$options
              </select>";
        }

        /**
         * Fetch a HTTP request value
         * @since 1.3
         * @ignore
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
         * @ignore
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
         * @ignore
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

        /**
         * Sanitize a string to #RRGGBB
         * @since 1.4
         * @since 2.0 prepends a #
         * @ignore
         * @param string $string String to be checked
         * @return string|boolean Returns a color of RRGGBB or false on failure
         */
        public function sanitize_color($string) {
            $string = strtoupper($string);
            $hex = null;
            if (preg_match('~([0-9A-F]{6}|[0-9A-F]{3})~', $string, $hex)) {
                $hex = $hex[1];
                if (strlen($hex) == 3) {
                    $hex = preg_replace('~[0-9A-F]~', '\1\1', $hex);
                }
                return "#$hex";
            }
            return false;
        }

        /**
         * Sanitize an alpha value
         * @since 1.10
         * @ignore
         * @param string $string
         * @return int
         */
        public function sanitize_alpha($string) {
            return intval($this->preg_get('~(100|[1-9][0-9]?|0)~', $string, 100));
        }

        /**
         * Convert a float to a HEX string
         * @since 1.7
         * @ignore
         * @param float $p [0..1]
         * @return string
         */
        public function float2hex($p) {
            return $this->int2hex($p * 255);
        }

        /**
         * Convert a integer to a HEX string
         * @since 1.9
         * @ignore
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
         * @ignore
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
         * @ignore
         * @param array $rgb RGB vector [red, gree, blue] of [0..1]
         * @return string
         */
        public function rgb2hex($rgb) {
            foreach ($rgb as $i => $x) {
                if ($x < 0 || $x > 1) {
                    return false;
                }
                $rgb[$i] = $this->int2hex($x * 255);
            }
            return implode('', $rgb);
        }

        public function hex2rgba($hex, $alpha) {
            $color = $this->sanitize_color($hex);
            $hex = str_split(substr($color, 1), 2);
            list($r, $g, $b) = array_map('hexdec', $hex);
            $a = round($alpha / 100, 2);
            return "rgba($r,$g,$b,$a)";
        }

    }

    kt_Central_Palette::instance();
}
