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
        const HOOK = 'kt/central_palette/';
        const NONCE = 'kt-tinymce-color-grid-save-editor';
        const INTEGRATE = 'kt_color_grid_integrate';
        const MAP = 'kt_color_grid_map';
        const GRID_TYPE = 'kt_color_grid_type';
        const ROWS = 'kt_color_grid_rows';
        const COLS = 'kt_color_grid_cols';
        const LUMA = 'kt_color_grid_luma';
        const BLOCKS = 'kt_color_grid_blocks';
        const SIZE = 'kt_color_grid_block_size';
        const AXIS = 'kt_color_grid_block_axis';
        const GROUPS = 2;
        const SPREAD = 'kt_color_grid_spread';
        const CLAMP = 'kt_color_grid_clamp';
        const CLAMPS = 'kt_color_grid_clamps';
        const PALETTE = 'kt_color_grid_palette';
        const ACTIVE_VERSION = 'kt_color_grid_version';
        const AUTONAME = 'kt_color_grid_autoname';
        const ALPHA = 'kt_color_grid_alpha';
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
        const DEFAULT_GRID_TYPE = 'rainbow';
        const DEFAULT_LUMA = 'natural';
        const MAX_FILE_SIZE = 256000;

        protected $registry = array();
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
            add_action('init', array($this, 'init_plugin'));
            add_action('plugins_loaded', array($this, 'load_textdomain'));
            add_action('admin_menu', array($this, 'add_settings_page'));
            add_filter('plugin_action_links', array($this, 'add_action_link'), 10, 2);

            register_activation_hook(__FILE__, array($this, 'plugin_activation'));

            $this->update_plugin();
        }

        public function plugin_activation() {
            $this->render_map();
        }

        /**
         *
         * Check if any integration needs an alpha channel
         * @since 2.0
         * @ignore
         * @return boolean
         */
        protected function support_alpha() {
            $support = false;
            $integrations = $this->get_integrations();
            foreach ($integrations as $int) {
                $fn = $int['alpha'];
                if (is_callable($fn)) {
                    $args = isset($int['args']) ? $int['args'] : $this;
                    if ($this->call($fn, $args)) {
                        $support = true;
                        break;
                    }
                }

                if ($fn) {
                    $support = true;
                    break;
                }
            }
            return $support;
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
                        if (is_array($map)) {
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
                    case '1.12':
                        $version = '1.12.1';

                        # fixes misplaced color names
                        $this->render_map();
                        break;

                    case '1.12.1':
                    case '1.12.2':
                    case '1.12.3':
                    case '1.12.4':
                    case '1.12.5':
                        $version = '2.0';

                        $palette = get_option('kt_color_grid_palette', array());
                        $_palette = array();
                        foreach ($palette as $set) {
                            list($color, $alpha, $name) = $set;
                            $color = '#' . ltrim($color, '#');
                            $slug = $this->get_color_slug($color, $name);
                            $_palette[] = compact('color', 'alpha', 'name', 'slug');
                        }
                        update_option('kt_color_grid_palette', $_palette);

                        $map = get_option('kt_color_grid_map', array());
                        list($map, $columns, $extra, $mono, $rows) = $map;
                        $columns += $mono + $extra;
                        update_option('kt_color_grid_map', compact('map', 'rows', 'columns'));

                        # all integrations are now stored in one option
                        $integrations = array();
                        $map = array(
                            'visual' => 'tinymce',
                            'customizer' => 'customizer',
                            'elementor' => 'elementor',
                            'gp' => 'generatepress',
                            'oceanwp' => 'oceanwp',
                        );
                        foreach ($map as $option => $integration) {
                            $option = "kt_color_grid_{$option}";
                            if (get_option($option)) {
                                $integrations[] = $integration;
                            }
                            delete_option($option);
                        }
                        update_option('kt_color_grid_integrate', $integrations);
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
            add_action(self::HOOK . 'init_integrations', array($this, 'default_integrations'));

            /**
             * Add integrations
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'init_integrations', $this);

            $integrations = $this->get_integrations();
            foreach ($integrations as $id => $integration) {
                if ($this->is_integration_active($id) && $integration['integrate']) {
                    $args = isset($integration['args']) ? $integration['args'] : $this;
                    $this->call($integration['integrate'], $args);
                }
            }
        }

        /**
         * Add an object to the registry
         * @since 2.0
         * @ignore
         * @param mixed $group
         * @param mixed $id
         * @param mixed $object
         * @return boolean
         */
        protected function registry_add($group, $id, $object) {
            # compare value and type since null and false cast to empty strings
            if (sanitize_key($id) !== $id) {
                return false;
            }
            if ($this->registry_has($group, $id)) {
                return false;
            }
            $this->registry[$group][$id] = $object;
            return true;
        }

        /**
         * Check if the registry has an object
         * @since 2.0
         * @ignore
         * @param mixed $group
         * @param mixed $id
         * @return boolean
         */
        protected function registry_has($group, $id) {
            if ($group && !isset($this->registry[$group])) {
                $this->registry[$group] = array();
            }
            return $id && isset($this->registry[$group][$id]);
        }

        /**
         * Get an object from the registry
         * @since 2.0
         * @ignore
         * @param mixed $group
         * @param mixed $id
         * @param mixed $default
         * @return mixed
         */
        protected function registry_get($group, $id = null, $default = null) {
            if (func_num_args() == 1) {
                return isset($this->registry[$group]) ? $this->registry[$group] : array();
            }
            if ($this->registry_has($group, $id)) {
                return $this->registry[$group][$id];
            }
            return $default;
        }

        /**
         * Remove an object from the registry
         * @since 2.0
         * @ignore
         * @param mixed $group
         * @param mixed $id
         * @return boolean
         */
        protected function registry_remove($group, $id) {
            if ($this->registry_has($group, $id)) {
                unset($this->registry[$group][$id]);
                return true;
            }
            return false;
        }

        /**
         * Add an integration
         * @since 2.0
         * @param mixed $id
         * @param string|array $options
         * @return boolean
         */
        public function add_integration($id, $options = '') {
            if ($this->has_integration($id)) {
                return false;
            }
            $integration = wp_parse_args($options, array(
                'enabled' => true,
                'alpha' => false,
                'checkbox' => true,
                'form' => null,
                '_internal' => false,
            ));

            if (!isset($integration['integrate']) || !is_callable($integration['integrate'])) {
                $integration['integrate'] = false;
            }

            if (!isset($integration['name']) || !$integration['name']) {
                $integration['name'] = ucfirst($id);
            }

            return $this->registry_add('integration', $id, $integration);
        }

        /**
         * Check for an integration
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function has_integration($id) {
            return $this->registry_has('integration', $id);
        }

        /**
         * Get an integration
         * @since 2.0
         * @param mixed $id
         * @return array|null
         */
        public function get_integration($id) {
            return $this->registry_get('integration', $id);
        }

        /**
         * Get all integrations
         * @since 2.0
         * @param string $output Optional filter, defaults to 'all'
         * @return array
         */
        public function get_integrations($output = 'all') {
            $integrations = $this->registry_get('integration');
            switch ($output) {
                case 'id': return array_keys($integrations);
                case 'name':
                case 'alpha':
                case 'integrate':
                    $map = array();
                    foreach ($integrations as $id => $integration) {
                        $map[$id] = $integration[$output];
                    }
                    return $map;
            }
            return $integrations;
        }

        /**
         * Remove an integration
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function remove_integration($id) {
            $integration = $this->get_integration($id);
            if (!$integration || $integration['_internal']) {
                return false;
            }
            return $this->registry_remove('integration', $id);
        }

        /**
         * Check if an integration is enabled
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function is_integration_enabled($id) {
            $integration = $this->get_integration($id);
            if (!$integration) {
                return false;
            }

            $enabled = $integration['enabled'];
            if (is_callable($enabled)) {
                $args = isset($integration['args']) ? $integration['args'] : $this;
                $enabled = $this->call($enabled, $args);
            }
            return (bool) $enabled;
        }

        /**
         * Check if an integration is active
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function is_integration_active($id) {
            $integration = $this->get_integration($id);
            if (!$integration) {
                return null;
            }

            if (!$integration['integrate']) {
                return $this->is_integration_active('customizer');
            }

            $integrate = get_option(self::INTEGRATE, array());
            return in_array($id, $integrate);
        }

        public function is_native_integration($id) {
            $integration = $this->get_integration($id);
            if (!$integration) {
                return null;
            }
            return $integration['integrate'] === false;
        }

        /**
         * Activate an integration
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function activate_integration($id) {
            if ($this->is_integration_active($id)) {
                return false;
            }
            $integrations = get_option(self::INTEGRATE, array());
            $integrations[] = $id;
            update_option(self::INTEGRATE, $integrations);
            return true;
        }

        /**
         * Deactivate an integration
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function deactivate_integration($id) {
            if (!$this->is_integration_active($id)) {
                return false;
            }
            $integrations = get_option(self::INTEGRATE, array());
            $integrations = array_diff($integrations, array($id));
            update_option(self::INTEGRATE, $integrations);
            return true;
        }

        /**
         * Add a grid type
         * @since 2.0
         * @param mixed $id
         * @param string|array $options
         * @return boolean
         */
        public function add_grid_type($id, $options = '') {
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

        /**
         * Check for a grid type
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function has_grid_type($id) {
            return $id == 'default' || $this->registry_has('grid_type', $id);
        }

        /**
         * Return default grid type
         * @since 2.0
         * @ignore
         * @return array
         */
        protected function get_default_grid_type() {
            return array(
                'name' => __('Default', 'kt-tinymce-color-grid'),
                'render' => null,
            );
        }

        /**
         * Get grid type
         * @since 2.0
         * @param mixed $id
         * @return array|null
         */
        public function get_grid_type($id) {
            if ($id == 'default') {
                return $this->get_default_grid_type();
            }
            return $this->registry_get('grid_type', $id);
        }

        /**
         * Get all grid types
         * @since 2.0
         * @param string $output Optional filter, defaults to 'all'
         * @return array
         */
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

        /**
         * Get id of current grid type
         * @since 2.0
         * @return string
         */
        public function get_current_grid_type() {
            $ids = array(
                get_option(self::GRID_TYPE),
                self::DEFAULT_GRID_TYPE,
            );
            foreach ($ids as $id) {
                if ($this->has_grid_type($id)) {
                    return $id;
                }
            }
            return 'default';
        }

        /**
         * Remove grid type
         * @param mixed $id
         * @return boolean
         */
        public function remove_grid_type($id) {
            if ($id == 'default') {
                return false;
            }
            return $this->registry_remove('grid_type', $id);
        }

        /**
         * Add luma correction
         * @since 2.0
         * @param mixed $id
         * @param string|array $options
         * @return boolean
         */
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

        /**
         * Check for luma correction
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function has_luma_type($id) {
            return $id == 'linear' || $this->registry_has('luma_type', $id);
        }

        /**
         * Get (default) linear luma type
         * @since 2.0
         * @ignore
         * @return array
         */
        protected function get_linear_luma_type() {
            return array(
                'name' => __('Linear', 'kt-tinymce-color-grid'),
                'fn' => null,
            );
        }

        /**
         * Get luma correction
         * @since 2.0
         * @param mixed $id
         * @return array|null
         */
        public function get_luma_type($id) {
            if ($id == 'linear') {
                return $this->get_linear_luma_type();
            }
            return $this->registry_get('luma_type', $id);
        }

        /**
         * Get all luma corrections
         * @since 2.0
         * @param string $output Optional filter, defaults to 'all'
         * @return array
         */
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

        /**
         * Get id of current luma correction
         * Since 2.0
         * @return string
         */
        public function get_current_luma_type() {
            $ids = array(
                get_option(self::LUMA),
                self::DEFAULT_LUMA,
            );
            foreach ($ids as $id) {
                if ($this->has_luma_type($id)) {
                    return $id;
                }
            }
            return 'linear';
        }

        /**
         * Remove luma correction
         * @since 2.0
         * @param mixed $id
         * @return boolean
         */
        public function remove_luma_type($id) {
            if ($id == 'linear') {
                return false;
            }
            return $this->registry_remove('luma_type', $id);
        }

        public function add_export_format($id, $options = '') {
            if ($this->has_export_format($id)) {
                return false;
            }
            $format = wp_parse_args($options, array(
                'form' => false,
                'prefix' => '',
            ));

            if (!isset($format['export']) || !is_callable($format['export'])) {
                return false;
            }

            if (!isset($format['extension']) || !$format['extension']) {
                $format['extension'] = $id;
            }

            if (!isset($format['name']) || !$format['name']) {
                $format['name'] = ucfirst($id);
            }

            return $this->registry_add('export', $id, $format);
        }

        public function has_export_format($id) {
            return $this->registry_has('export', $id);
        }

        public function get_export_format($id) {
            return $this->registry_get('export', $id);
        }

        public function get_export_formats($output = 'all') {
            $formats = $this->registry_get('export');
            switch ($output) {
                case 'id': return array_keys($formats);
                case 'name':
                case 'form':
                case 'export':
                    $map = array();
                    foreach ($formats as $id => $type) {
                        $map[$id] = $type[$output];
                    }
                    return $map;
            }
            return $formats;
        }

        public function remove_export_format($id) {
            if ($id == 'base64') {
                return false;
            }
            return $this->registry_remove('export', $id);
        }

        public function add_import_format($id, $options = '') {
            if ($this->has_export_format($id)) {
                return false;
            }
            $format = wp_parse_args($options, array(
            ));

            if (!isset($format['parser']) || !is_callable($format['parser'])) {
                return false;
            }

            if (!isset($format['extension']) || !$format['extension']) {
                $format['extension'] = $id;
            }

            if (!isset($format['name']) || !$format['name']) {
                $format['name'] = ucfirst($id);
            }

            return $this->registry_add('export', $id, $format);
        }

        public function has_import_format($id) {
            return $this->registry_has('import', $id);
        }

        public function get_import_format($id) {
            return $this->registry_get('import', $id);
        }

        public function get_import_formats($output = 'all') {
            $formats = $this->registry_get('import');
            switch ($output) {
                case 'id': return array_keys($formats);
                case 'name':
                case 'parser':
                    $map = array();
                    foreach ($formats as $id => $type) {
                        $map[$id] = $type[$output];
                    }
                    return $map;
            }
            return $formats;
        }

        public function remove_import_format($id) {
            if ($id == 'bak') {
                return false;
            }
            return $this->registry_remove('import', $id);
        }

        /**
         * Add default grid types: palette, rainbow and block
         * @since 2.0
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
         * Add default luma corrections: sine, cubic and natural
         * @since 2.0
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

        /**
         * Add default integrations
         * @since 2.0
         * @ignore
         */
        public function default_integrations() {
            $this->add_integration('tinymce', array(
                'label' => __('Add to Visual Editor', 'kt-tinymce-color-grid'),
                'enabled' => true,
                'integrate' => array($this, 'integrate_tinymce'),
                'alpha' => false,
                '_internal' => true,
            ));
            $this->add_integration('customizer', array(
                'label' => array($this, 'print_customizer_label'),
                'enabled' => true,
                'integrate' => array($this, 'integrate_customizer'),
                'alpha' => false,
                '_internal' => true,
            ));
            /*$this->add_integration('gutenberg', array(
                'label' => array($this, 'print_gutenberg_label'),
                'enabled' => array($this, 'support_gutenberg'),
                'integrate' => array($this, 'integrate_gutenberg'),
                'alpha' => false,
                '_internal' => true,
            ));*/
            $this->add_integration('elementor', array(
                'name' => __('Elementor', 'kt-tinymce-color-grid'),
                'enabled' => array($this, 'support_elementor'),
                'integrate' => array($this, 'integrate_elementor'),
                'alpha' => false,
            ));
            $this->add_integration('generatepress', array(
                'name' => __('GeneratePress Premium', 'kt-tinymce-color-grid'),
                'enabled' => array($this, 'support_generatepress'),
                'integrate' => array($this, 'integrate_generatepress'),
                'alpha' => true,
            ));
            $this->add_integration('oceanwp', array(
                'name' => __('OceanWP', 'kt-tinymce-color-grid'),
                'enabled' => array($this, 'support_oceanwp'),
                'integrate' => array($this, 'integrate_oceanwp'),
                'alpha' => true,
            ));
            $this->add_integration('beaverbuilder', array(
                'name' => __('Beaver Builder', 'kt-tinymce-color-grid'),
                'enabled' => array($this, 'support_beaver'),
                'integrate' => array($this, 'integrate_beaver'),
                'alpha' => false,
            ));
            $this->add_integration('astra-theme', array(
                'name' => __('Astra Theme', 'kt-tinymce-color-grid'),
                'enabled' => array($this, 'support_astra'),
            ));
            $this->add_integration('page-builder-framework', array(
                'name' => __('Page Builder Framework', 'kt-tinymce-color-grid'),
                'enabled' => array($this, 'support_pagebuilder'),
            ));
        }

        public function default_export_formats() {
            $this->add_export_format('base64', array(
                'extension' => 'bak',
                'name' => __('Backup', 'kt-tinymce-color-grid'),
                'form' => array($this, 'print_base64_export_form'),
                'export' => array($this, 'export_base64'),
            ));
            $this->add_export_format('css', array(
                'extension' => 'css',
                'name' => __('CSS', 'kt-tinymce-color-grid'),
                'form' => array($this, 'print_css_export_form'),
                'export' => array($this, 'export_css'),
            ));
            $this->add_export_format('json', array(
                'extension' => 'json',
                'name' => __('JSON', 'kt-tinymce-color-grid'),
                'form' => array($this, 'print_base64_export_form'),
                'export' => array($this, 'export_json'),
            ));
            $this->add_export_format('scss-partial', array(
                'extension' => 'scss',
                'prefix' => '_',
                'name' => __('SCSS Partial', 'kt-tinymce-color-grid'),
                'form' => array($this, 'print_css_export_form'),
                'export' => array($this, 'export_css'),
            ));
            $this->add_export_format('scss-vars', array(
                'extension' => 'scss',
                'prefix' => '_',
                'name' => __('SCSS Variables', 'kt-tinymce-color-grid'),
                'form' => array($this, 'print_scss_export_form'),
                'export' => array($this, 'export_scss'),
            ));
        }

        public function default_import_formats() {
            $this->add_import_format('bak', array(
                'extension' => 'bak',
                'parser' => array($this, 'import_bak'),
            ));
            $this->add_import_format('json', array(
                'extension' => 'json',
                'parser' => array($this, 'import_json'),
            ));
        }

        public function support_gutenberg() {
            return defined('GUTENBERG_VERSION');
        }

        /**
         * Check for Beaver Builder
         * @since 2.0
         * @ignore
         * @return boolean
         */
        public function support_beaver() {
            return defined('FL_BUILDER_VERSION') && version_compare(FL_BUILDER_VERSION, '1.7.6', '>=');
        }

        /**
         * Check for OceanWP
         * @since 2.0
         * @ignore
         * @return boolean
         */
        public function support_oceanwp() {
            return defined('OCEANWP_THEME_VERSION') && version_compare(OCEANWP_THEME_VERSION, '1.4.9', '>=');
        }

        /**
         * Check for Elementor
         * @since 2.0
         * @ignore
         * @return boolean
         */
        public function support_elementor() {
            return defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '1.0.0', '>=');
        }

        /**
         * Check for GeneratePress
         * @since 2.0
         * @ignore
         * @return boolean
         */
        public function support_generatepress() {
            return defined('GP_PREMIUM_VERSION') && version_compare(GP_PREMIUM_VERSION, '1.2.93', '>=');
        }

        public function support_astra() {
            return defined('ASTRA_THEME_VERSION');
        }

        public function support_pagebuilder() {
            return defined('WPBF_VERSION');
        }

        /**
         * Add filter for integration with Beaver Builder
         * @since 2.0
         * @ignore
         */
        public function integrate_beaver() {
            add_filter('fl_builder_color_presets', array($this, 'beaver_integration'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_beaver_style'));
        }

        /**
         * Add filters and actions for integration with Elementor
         * @since 2.0
         * @ignore
         */
        public function integrate_elementor() {
            add_filter('elementor/editor/localize_settings', array($this, 'elementor_integration'), 100);
            add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_elementor_styles'));
        }

        /**
         * Add filter for integration with GeneratePress
         * @since 2.0
         * @ignore
         */
        public function integrate_generatepress() {
            add_filter('generate_default_color_palettes', array($this, 'generatepress_integration'));
        }

        /**
         * Add filter for integration with OceanWP
         * @since 2.0
         * @ignore
         */
        public function integrate_oceanwp() {
            add_filter('ocean_default_color_palettes', array($this, 'oceanwp_integration'));
        }

        public function integrate_tinymce() {
            if (get_option(self::GRID_TYPE, self::DEFAULT_GRID_TYPE) != 'default') {
                add_filter('tiny_mce_before_init', array($this, 'tinymce_integration'));
                add_action('after_wp_tiny_mce', array($this, 'print_tinymce_style'));
            }
        }

        public function integrate_customizer() {
            $fn = array($this, 'iris_integration');
            add_action('admin_print_scripts', $fn);
            add_action('admin_print_footer_scripts', $fn);
            add_action('customize_controls_print_scripts', $fn);
            add_action('customize_controls_print_footer_scripts', $fn);
        }

        public function integrate_gutenberg() {
            $palette = get_option(self::PALETTE, array());
            if (!$palette) {
                return;
            }

            foreach ($palette as $i => $set) {
                if ($set['name'] === '') {
                    $set['color'] = trim($set['color'], '#');
                }
                unset($palette['alpha']);
                $palette[$i] = $set;
            }

            add_theme_support('editor-color-palette', $palette);
            add_action('wp_print_styles', array($this, 'print_gutenberg_style'));
            add_action('admin_print_styles', array($this, 'print_gutenberg_style'));
        }

        public function print_gutenberg_style() {
            if (is_admin()) {
                $screen = get_current_screen();
                if ($screen->base != 'post') {
                    return;
                }
            }

            $palette = get_option(self::PALETTE, array());
            if (!$palette) {
                return;
            }

            print '
<style type="text/css">';
            foreach ($palette as $set) {
                printf('
  .has-%1$s-color {
    color: %2$s }
  .has-%1$s-background-color {
    background-color: %2$s }', $set['slug'], $set['color']);
            }
            print '
</style>';
        }

        /**
         * Get the color palette
         * @since 1.11
         * @since 2.0 Introduced $options argument
         * @param string|array $options
         * @return array
         */
        public function get_palette($options = '') {
            $options = wp_parse_args($options, array(
                'alpha' => get_option(self::ALPHA),
                'min' => 6,
                'pad' => '#FFFFFF',
                'hash' => true,
                'default' => array(),
            ));
            $palette = get_option(self::PALETTE, array());
            $_palette = array();
            foreach ($palette as $set) {
                $color = $set['color'];
                if ($options['alpha'] && $set['alpha'] < 100) {
                    $color = $this->hex2rgba($color, $set['alpha']);
                } else if (!$options['hash']) {
                    $color = ltrim($color, '#');
                }
                $_palette[] = $color;
            }
            if ($options['min'] > 0) {
                $pad = ltrim($options['pad'], '#');
                if ($options['hash']) {
                    $pad = "#$pad";
                }
                $_palette = array_pad($_palette, $options['min'], $pad);
            }
            if (!count($_palette) && is_array($options['default'])) {
                $_palette = $options['default'];
            }

            /**
             * Filter the palette
             * @since 2.0
             * @param array $palette
             * @param array $raw
             * @param string|array $options
             * @param kt_Central_Palette $instance
             */
            return apply_filters(self::HOOK . 'get_palette', $_palette, $palette, $options, $this);
        }

        /**
         * Enqueue a stylesheet for Beaver Builder
         * @since 1.12
         * @ignore
         */
        public function enqueue_beaver_style() {
            if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
                wp_enqueue_style(self::KEY . '-beaver', KT_CENTRAL_PALETTE_URL . 'css/beaver.css', null, KT_CENTRAL_PALETTE);
            }
        }

        /**
         * Beaver Builder integration
         * @since 1.12
         * @ignore
         * @param array $palette
         * @return array
         */
        public function beaver_integration($palette) {
            return $this->get_palette(array(
                    'min' => false,
                    'hash' => false,
                    'alpha' => false,
                    'default' => $palette,
            ));
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
        public function enqueue_elementor_styles() {
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
         * @since 1.12.3 Change css selector
         * @ignore
         */
        public function print_tinymce_style() {
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
         * @ignore
         * @param array $init Wordpress' TinyMCE inits
         * @return array
         */
        public function tinymce_integration($init) {
            $map = get_option(self::MAP);
            if (is_array($map) && $map['rows']) {
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

            add_action(self::HOOK . 'init_grid_types', array($this, 'default_grid_types'));
            add_action(self::HOOK . 'init_luma_types', array($this, 'default_luma_types'));
            add_action(self::HOOK . 'init_export_formats', array($this, 'default_export_formats'));
            add_action(self::HOOK . 'init_import_formats', array($this, 'default_import_formats'));

            $natives = array(
                'astra' => __('Astra Theme', 'kt-tinymce-color-grid'),
                'page-builder-framework' => __('Page Builder Framework', 'kt-tinymce-color-grid'),
            );

            /**
             * Add grid types
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'init_grid_types', $this);

            /**
             * Register luma types
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'init_luma_types', $this);

            /**
             * Register export formats
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'init_export_formats', $this);

            /**
             * Register import formats
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'init_import_formats', $this);

            $this->save_settings();

            add_action(self::HOOK . 'grid_metabox', array($this, 'print_grid_type_options'), 10);

            add_action(self::HOOK . 'palette_metabox', array($this, 'print_integration_forms'), 10);
            add_action(self::HOOK . 'palette_metabox', array($this, 'print_color_toolbar'), 20);
            add_action(self::HOOK . 'palette_metabox', array($this, 'print_color_editor'), 30);

            add_action(self::HOOK . 'palette_toolbar', array($this, 'print_add_color_button'), 10);
            add_action(self::HOOK . 'palette_toolbar', array($this, 'print_transparency_switch'), 20);
            add_action(self::HOOK . 'palette_toolbar', array($this, 'print_autoname_switch'), 30);

            add_action(self::HOOK . 'backup_metabox', array($this, 'print_export_form'), 10);
            add_action(self::HOOK . 'backup_metabox', array($this, 'print_import_form'), 20);

            $this->add_help();

            $boxes = array(
                'grid' => __('TinyMCE Color Picker', 'kt-tinymce-color-grid'),
                'palette' => __('Color Palette', 'kt-tinymce-color-grid'),
                'backup' => __('Backup', 'kt-tinymce-color-grid'),
            );
            foreach ($boxes as $key => $title) {
                add_meta_box("kt_{$key}_metabox", $title, array($this, "print_{$key}_metabox"));
            }

            /**
             *
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'init', $this);
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
            $args[] = 'kt-save-error';
            $args[] = 'kt-import-error';
            $args[] = 'kt-export-error';
            return $args;
        }

        /**
         * Pass a HTTP request value through a filter and store it as option
         * @since 1.7
         * @ignore
         * @param array $option
         * @return mixed
         */
        protected function set_option($option) {
            list($key, $constrain, $name, $default) = $option;
            $value = $this->get_request("kt_{$key}", $default);
            if (is_array($constrain)) {
                $value = in_array($value, $constrain) ? $value : $default;
            } else if (is_bool($constrain)) {
                $value = $value ? '1' : '';
            }
            update_option($name, $value);
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

            $this->save_integrations();
            $action = $this->save_palette();

            $options = array(
                array('alpha', true, self::ALPHA, null),
                array('rows', $this->rows, self::ROWS, self::DEFAULT_ROWS),
                array('cols', $this->columns, self::COLS, self::DEFAULT_COLS),
                array('luma', $this->get_luma_types('id'), self::LUMA, self::DEFAULT_LUMA),
                array('blocks', $this->blocks, self::BLOCKS, self::DEFAULT_BLOCKS),
                array('block_size', $this->sizes, self::SIZE, self::DEFAULT_SIZE),
                array('axis', $this->axes, self::AXIS, self::DEFAULT_AXIS),
                array('spread', $this->spread, self::SPREAD, self::DEFAULT_SPREAD),
                array('clamp', $this->clamp, self::CLAMP, self::DEFAULT_CLAMP),
            );
            foreach ($options as $option) {
                $this->set_option($option);
            }

            $clamps = intval($this->get_request('kt_clamps'));
            if ($clamps < self::MIN_CLAMP || $clamps > self::MAX_CLAMP) {
                $clamps = self::DEFAULT_CLAMPS;
            }
            update_option(self::CLAMPS, $clamps);

            list($error, $_action) = $this->handle_backup($action);

            /**
             * Fires after settings were saved
             * @since 2.0
             * @param string $action
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'save_settings', $action, $this);

            if (!$error || $error == 'ok') {
                $this->render_map();
            }

            if ($error) {
                $url = add_query_arg("kt-{$_action}-error", $error);
                wp_redirect($url);
                exit;
            }

            wp_redirect(add_query_arg('updated', $_action == 'save' ? '1' : false));
            exit;
        }

        /**
         * Save integrations
         * @since 2.0
         * @ignore
         */
        protected function save_integrations() {
            $integrate = array();
            $_integrate = $this->get_request('kt_integrate', array());
            $ids = $this->get_integrations('id');
            foreach ($_integrate as $id) {
                if (in_array($id, $ids)) {
                    $integrate[] = $id;
                }
            }
            update_option(self::INTEGRATE, $integrate);
        }

        /**
         * Save the palette from form submitted data
         * @since 2.0
         * @ignore
         * @return string
         */
        protected function save_palette() {
            list($palette, $action) = $this->compile_palette();

            $type = $this->get_request('kt_type');
            $types = $this->get_grid_types('id');
            if (!in_array($type, $types)) {
                $type = self::DEFAULT_GRID_TYPE;
            }
            if ($type == 'palette' && !$palette) {
                $type = 'default';
                $this->deactivate_integration('tinymce');
            }
            update_option(self::GRID_TYPE, $type);
            update_option(self::PALETTE, $palette);

            return $action;
        }

        /**
         * Compile palette from form submitted data
         * @since 2.0
         * @ignore
         * @return array
         */
        protected function compile_palette() {
            $palette = array();
            $colors = $this->get_request('kt_colors', array());
            $alphas = $this->get_request('kt_alphas', array());
            $names = $this->get_request('kt_names', array());
            $ntc = $this->get_request('kt_ntc_names', array());
            foreach ($names as $i => $name) {
                $color = $this->sanitize_color($colors[$i]);
                if ($color) {
                    $alpha = $this->sanitize_alpha($alphas[$i]);
                    $name = sanitize_text_field(stripslashes($name));
                    $_name = sanitize_text_field(stripslashes($ntc[$i]));
                    $slug = $this->get_color_slug($color, $name, $_name);
                    $palette[] = compact('color', 'alpha', 'name', 'slug');
                }
            }
            return $this->edit_palette($palette);
        }

        protected function get_color_slug($color, $name, $_name = '') {
            $slug = $this->sanitize_html_class($name);
            if ($slug === '') {
                $slug = $this->sanitize_html_class($_name);
                if ($slug === '') {
                    $slug = trim($color, '#');
                }
            }
            return $slug;
        }

        /**
         * Perform edit options on the palette
         * @since 2.0
         * @ignore
         * @param array $palette
         * @return array
         */
        protected function edit_palette($palette) {
            $m = null;
            $l = count($palette);
            $action = $this->get_request('kt_action', $this->get_request('kt_hidden_action'));
            if ($action == 'add') {
                $this->palette_add($palette, '#000000', 100, '', 'Black');
            } else if ($l > 0 && preg_match('~(remove)-(\d+)~', $action, $m) && isset($palette[$m[2]])) {
                array_splice($palette, $m[2], 1);
                $action = $m[1];
            } else if ($l > 1 && preg_match('~(sort)-(\d+)-(up|down)~', $action, $m) && isset($palette[$m[2]])) {
                list($action, $i, $dir) = array_slice($m, 1);
                $j = $i;
                if ($dir == 'up' && $i > 0) {
                    $j = $i - 1;
                } else if ($dir == 'down' && $i < ($l - 1)) {
                    $j = $i + 1;
                }
                if ($i != $j) {
                    $temp = $palette[$i];
                    $palette[$i] = $palette[$j];
                    $palette[$j] = $temp;
                }
            }
            return array($palette, $action);
        }

        /**
         * Handle backup
         * @since 2.0
         * @ignore
         * @param string $action
         * @return array [status, action]
         */
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

            if (is_wp_error($error)) {
                $error = $error->get_error_code();
            }

            /**
             * Filter the settings error code
             * @since 2.0
             * @param string|boolean $code
             * @param string $action
             * @param kt_Central_Palette $instance
             */
            $error = apply_filters(self::HOOK . 'error_code', $error, $action, $this);

            return array($error, $action);
        }

        /**
         * Return all options as an array
         * @since 1.8
         * @since 1.9 Partial options
         * @ignore
         * @param array $parts
         * @return array
         */
        protected function compile_backup($parts = null) {
            $options = array(
                self::ACTIVE_VERSION => KT_CENTRAL_PALETTE
            );
            $settings = array(
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
         * Add a color to the palette
         * @param array $palette Passed by reference
         * @param string $color
         * @param int $alpha [optional] 100
         * @param string $name [optional] ""
         */
        protected function palette_add(&$palette, $color, $alpha = 100, $name = '', $slug = '') {
            $color = $this->sanitize_color($color);
            if ($color) {
                $alpha = $this->sanitize_alpha($alpha);
                $palette[] = compact('color', 'alpha', 'name', 'slug');
            }
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
                    return new WP_Error($status);
                }
                $base64 = file_get_contents($file['tmp_name']);
            } else if (isset($_REQUEST['kt_import'])) {
                $base64 = $_REQUEST['kt_import'];
            } else {
                return new WP_Error('no-import');
            }

            if (substr($base64, 0, 1) == '#') {
                $colors = preg_split('~[^#0-9A-Fa-f]+~', $base64);
                $palette = array();
                foreach ($colors as $color) {
                    $this->palette_add($palette, $color);
                }
                if (!count($palette)) {
                    return new WP_Error('empty');
                }
                update_option(self::PALETTE, $palette);
                $this->render_map();
                return;
            }

            $crc32 = substr($base64, -8);
            if (strlen($crc32) != 8) {
                return new WP_Error('empty');
            }
            $base64 = substr($base64, 0, -8);
            if (dechex(crc32($base64)) != $crc32) {
                return new WP_Error('corrupt');
            }
            $json = base64_decode($base64);
            $options = json_decode($json, true);
            if (!is_array($options)) {
                return new WP_Error('funny');
            }
            if (!isset($options[self::ACTIVE_VERSION])) {
                $options[self::ACTIVE_VERSION] = 180;
            }
            $this->update_import($options);

            $names = array_keys($this->compile_backup());
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
            do_action(self::HOOK . 'import', $options, $this);
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
                    case '1.12':
                    case '1.12.1':
                    case '1.12.2':
                    case '1.12.3':
                    case '1.12.4':
                    case '1.12.5':
                        $version = '2.0';
                        $key = 'kt_color_grid_palette';
                        $palette = array();
                        foreach ($options[$key] as $set) {
                            list($color, $alpha, $name) = $set;
                            $color = '#' . ltrim($color, '#');
                            $slug = $this->get_color_slug($color, $name);
                            $palette[] = compact('color', 'alpha', 'name', 'slug');
                        }
                        $options[$key] = $palette;
                        break;

                    default:
                        $version = KT_CENTRAL_PALETTE;
                }
            }
        }

        /**
         * Get file name for export file download
         * @since 2.0
         * @param array $format A export format
         * @return string The filename
         */
        protected function get_export_filename($format) {
            $ext = $format['extension'];
            $prefix = $format['prefix'];
            $name = get_bloginfo('name');
            $suffix = sanitize_file_name(preg_replace('~"~', '', $name));
            if ($suffix) {
                $suffix = "_$suffix";
            }
            return "{$prefix}central-palette{$suffix}.{$ext}";
        }

        /**
         * Export settings and trigger a file download
         * @since 1.8
         * @since 2.0 Added export types: json, css, scss
         * @since 2.0 Changed return type to WP_Error
         * @return WP_Error
         */
        protected function download_export() {
            $payload = null;
            $id = $this->get_request('kt_export_format');
            $format = $this->get_export_format($id);
            if (!$format) {
                return new WP_Error('format');
            }

            $args = isset($format['args']) ? $format['args'] : null;
            $payload = $this->call($format['export'], $this, $args);

            if (!$payload) {
                return new WP_Error('empty');
            }
            if (is_wp_error($payload)) {
                return $payload;
            }

            $filename = $this->get_export_filename($format);
            header('Content-Type: plain/text');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            #print '<xmp>'.$payload;
            exit;
        }

        /**
         * Export as base64 backup file
         * @since 2.0
         * @return string|WP_Error
         */
        protected function export_base64() {
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

        /**
         * Export as json string
         * @since 2.0
         * @return string|WP_Error
         */
        protected function export_json() {
            $parts = $this->get_request('kt_export');
            if (!is_array($parts) || !$parts) {
                return new WP_Error('no-export');
            }
            $options = $this->compile_backup($parts);
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
            $options = apply_filters(self::HOOK . 'export', $options, $parts, $this);

            $json = json_encode($options);
            if (!$json) {
                return new WP_Error('json');
            }
            return $json;
        }

        /**
         * Export as CSS
         * @since 2.0
         * @return string
         */
        protected function export_css() {
            $css = array();
            $palette = get_option(self::PALETTE, array());
            $add_alpha = $this->get_request('kt_export_css_alpha');
            $prefix = $this->get_request('kt_export_css_prefix');
            $suffix = $this->get_request('kt_export_css_suffix');
            foreach ($palette as $set) {
                $hex = $set['color'];
                $alpha = $set['alpha'];
                $name = $this->get_css_class_name($set['slug'], $prefix, $suffix);
                $css[] = ".$name {
  color: $hex" . ($add_alpha && $alpha < 100 ? ';
  color: ' . $this->hex2rgba($hex, $alpha) : '') . " }\n";
            }
            return implode("\n", $css);
        }

        protected function export_scss() {
            $vars = array();
            $palette = get_option(self::PALETTE, array());
            $prefix = $this->get_request('kt_export_scss_prefix');
            $suffix = $this->get_request('kt_export_scss_suffix');
            foreach ($palette as $set) {
                $hex = $set['color'];
                $alpha = $set['alpha'];
                $name = $this->get_css_class_name($set['slug'], $prefix, $suffix);
                $vars[] = "\$$name: " . $this->hex2rgba($hex, $alpha) . ";\n";
            }
            return implode('', $vars);
        }

        protected function get_css_class_name($slug, $prefix = '', $suffix = '') {
            static $duplicates = array();

            $prefix = sanitize_html_class($prefix);
            $suffix = sanitize_html_class($suffix);
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

        protected function sanitize_html_class($in) {
            $out = strtolower($in);
            $out = preg_replace('~%[a-fA-F0-9]{2}~', '', $out);
            $out = preg_replace('~\s+~', '-', $out);
            return preg_replace('~[^a-zA-Z0-9_-]~', '', $out);
        }

        /**
         * Renders color map
         * @since 1.7
         * @ignore
         */
        protected function render_map() {
            $current_type = $this->get_current_grid_type();
            if ($current_type == 'default') {
                return;
            }

            $grid_type = $this->get_grid_type($current_type);
            $args = isset($grid_type['args']) ? $grid_type['args'] : $this;
            $map = $this->call($grid_type['render'], $args);
            $map = $this->sanitize_map($map);
            update_option(self::MAP, $map);
        }

        /**
         * Sanitize the color map
         * @since 2.0
         * @ignore
         * @param array $map
         * @return array
         */
        protected function sanitize_map($map) {
            $default = array(
                'map' => '[]',
                'columns' => 0,
                'rows' => 0,
            );
            if (!is_array($map) || !isset($map['map']) || !is_array($map['map'])) {
                return $default;
            }

            $n = count($map['map']);
            if (!$n) {
                return $default;
            }
            if ($n % 2 == 1) {
                $map['map'][] = '';
            }

            $_map = array();
            for ($i = 0; $i < $n; $i += 2) {
                $color = $this->sanitize_color($map['map'][$i], false);
                if ($color) {
                    $_map[] = $color;
                    $_map[] = $map['map'][$i + 1];
                }
            }

            $n = count($_map);
            if (!$n) {
                return $default;
            }
            $_map = implode('","', array_map('esc_js', $_map));
            $map['map'] = '["' . $_map . '"]';


            $count = $n / 2;
            $no_rows = !isset($map['rows']) || $map['rows'] < 1;
            $no_cols = !isset($map['columns']) || $map['columns'] < 1;
            if ($no_rows && $no_cols) {
                $map['columns'] = self::TINYMCE_COLS;
            } else if ($no_cols) {
                $map['columns'] = ceil($count / $map['rows']);
            }
            $map['rows'] = ceil($count / $map['columns']);

            return $map;
        }

        /**
         * Chunk palette into columns of constant size
         * @since 1.7
         * @ignore
         * @return array [palette, rows, cols]
         */
        protected function chunk_palette() {
            $palette = array();
            list($rows, $cols) = $this->get_palette_size();
            if ($this->is_integration_active('tinymce')) {
                $palette = get_option(self::PALETTE, array());
                if ($palette) {
                    $palette = array_chunk($palette, $rows);
                    $last = count($palette) - 1;
                    $pad = array(
                        'color' => '#FFFFFF',
                        'alpha' => 100,
                        'name' => '',
                        'slug' => '#FFFFFF',
                    );
                    $palette[$last] = array_pad($palette[$last], $rows, $pad);
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
                $map[] = $palette[$col][$row]['color'];
                $map[] = $palette[$col][$row]['name'];
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
            $c = $this->float2hex($row / ($rows - 2));
            $map[] = "$c$c$c";
            $map[] = "";
        }

        /**
         * Render TinyMCE palette color map
         * @since 1.8
         * @ignore
         * @return array
         */
        protected function render_palette() {
            list($palette, $rows, $columns) = $this->chunk_palette();
            $map = array();
            for ($row = 0; $row < $rows; $row++) {
                $this->add_row_to_map($map, $palette, $row);
            }
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
         * @ignore
         * @return array
         */
        protected function render_rainbow() {
            list($palette, $rows, $columns) = $this->chunk_palette();
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
                    $map[] = $this->rgb2hex($_rgb);
                    $map[] = "";
                }

                $this->add_monocroma($map, $row, $rows);
            }

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
            // TODO: Luma is broken
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
            $args = isset($luma_type['args']) ? $luma_type['args'] : $this;
            $_luma = $this->call($luma_type['fn'], $luma, $args);
            return max(-1, min($_luma, 1));
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
  <$head>" . esc_html__('Central Color Palette', 'kt-tinymce-color-grid') . "</$head>";
            $this->print_settings_error();
            $this->print_settings_css();

            $support_alpha = $this->support_alpha() && get_option(self::ALPHA) ? ' class="support-alpha"' : '';

            print "
  <form id='kt_color_grid'$support_alpha action='options-general.php?page=" . self::KEY . "' method='post' enctype='multipart/form-data'>
    <input type='hidden' name='MAX_FILE_SIZE' value='" . self::MAX_FILE_SIZE . "'/>
    <input type='hidden' id='kt_action' name='kt_hidden_action' value='save'/>";
            wp_nonce_field(self::NONCE, 'kt_settings_nonce', false);
            wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
            wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);

            /**
             * Fires inside the settings form before any metabox is printed
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'settings_form', $this);

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
        }

        /**
         * Render settings error
         * @since 1.9
         * @ignore
         */
        protected function print_settings_error() {
            $action = 'save';
            $notice = $code = '';
            if (isset($_GET['kt-import-error'])) {
                $action = 'import';
                $code = $_GET['kt-import-error'];
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
                $notice = __('Import failed.', 'kt-tinymce-color-grid');
                if (isset($import_errors[$code])) {
                    $notice = $import_errors[$code];
                }
            } else if (isset($_GET['kt-export-error'])) {
                $action = 'export';
                $code = $_GET['kt-export-error'];
                $export_errors = array(
                    'format' => __('Unknown export format.', 'kt-tinymce-color-grid'),
                    'empty' => __('No export data.', 'kt-tinymce-color-grid'),
                    'no-export' => __('Please select which parts you would like to backup.', 'kt-tinymce-color-grid'),
                    'json' => __('Could not pack settings into JSON.', 'kt-tinymce-color-grid'),
                    'base64' => __('Could not convert settings.', 'kt-tinymce-color-grid'),
                );
                $notice = __('Export failed.', 'kt-tinymce-color-grid');
                if (isset($export_errors[$code])) {
                    $notice = $export_errors[$code];
                }
            } else if (isset($_GET['kt-save-error'])) {
                $code = $_GET['kt-save-error'];
            }

            /**
             * Filters the settings error notice
             * @since 2.0
             * @param string $notice The error notice
             * @param string $code The error code
             * @param string $action The current action
             * @param kt_Central_Palette $instance
             */
            $notice = apply_filters(self::HOOK . 'error_notice', $notice, $code, $action, $this);

            if ($notice) {
                $type = $code == 'ok' ? 'updated' : 'error';
                print "<div id='setting-error-import' class='$type settings-error notice is-dismissible'><p><strong>$notice</strong></p></div>";
            }
        }

        /**
         *
         * @since 2.0
         * @ignore
         */
        protected function print_settings_css() {
            $grid_types = $this->get_grid_types('id');
            if (empty($grid_types)) {
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
            /**
             * Fires inside the grid metabox
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'grid_metabox', $this);
        }

        /**
         * Print grid type options
         * @since 2.0
         * @ignore
         */
        public function print_grid_type_options() {
            $grid_types = $this->get_grid_types();
            print "
<p><label id='kt_grid_type'>Type</label>
  <span class='button-group toggle-button-group'>";
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
                        $args = isset($grid_type['args']) ? $grid_type['args'] : $this;
                        $form = $this->call($form, $args);
                    }
                    print "
<div id='kt_grid_type_{$id}_form' class='grid-type-form'>$form</div>";
                }
            }
        }

        /**
         * Print central palette grid type options
         * @since 2.0
         * @ignore
         */
        public function print_palette_form() {
            $clamp = array(
                'row' => __('row', 'kt-tinymce-color-grid'),
                'column' => __('column', 'kt-tinymce-color-grid'),
            );
            $_spread = get_option(self::SPREAD, self::DEFAULT_SPREAD);
            $_clamp = get_option(self::CLAMP, self::DEFAULT_CLAMP);
            $_clamps = get_option(self::CLAMPS, self::DEFAULT_CLAMPS);
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
<p>
  <input type='radio' id='$id' name='kt_spread' value='$value'$checked/>
  <label for='$id'>$label</label>
</p>";
            }
        }

        /**
         * Print rainbow grid type options
         * @since 2.0
         * @ignore
         */
        public function print_rainbow_form() {
            $_cols = get_option(self::COLS, self::DEFAULT_COLS);
            $_rows = get_option(self::ROWS, self::DEFAULT_ROWS);
            $cols = $this->selectbox('kt_cols', $this->columns, $_cols);
            $rows = $this->selectbox('kt_rows', $this->rows, $_rows);
            print "
<p>
  <label for='kt_rows'>" . esc_html__('Rows', 'kt-tinymce-color-grid') . "</label>$rows
  <label for='kt_cols'>" . esc_html__('Columns', 'kt-tinymce-color-grid') . "</label>$cols";
            $lumas = $this->get_luma_types('name');
            if (count($lumas) > 1) {
                $current_luma = get_option(self::LUMA, self::DEFAULT_LUMA);
                $luma = $this->selectbox('kt_luma', $lumas, $current_luma);
                print "
  <label for='kt_luma'>" . esc_html__('Luma', 'kt-tinymce-color-grid') . "</label>$luma";
            }
            print '
</p>';
        }

        /**
         * Print block grid type options
         * @since 2.0
         * @ignore
         */
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
            /**
             * Fires inside the palette metabox
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'palette_metabox', $this);
        }

        /**
         * Print external integration forms fields
         * @since 2.0
         * @ignore
         */
        public function print_integration_forms() {
            $_label = __('Integrate with %s', 'kt-tinymce-color-grid');
            $integrations = $this->get_integrations();
            uasort($integrations, array($this, 'sort_integrations'));
            foreach ($integrations as $id => $integration) {
                if (!$this->is_integration_enabled($id) || $this->is_native_integration($id)) {
                    continue;
                }

                $args = isset($integration['args']) ? $integration['args'] : $this;

                print "
<p class='integrate-wrap' id='kt_integrate_{$id}_wrap'>";
                if ($integration['checkbox']) {
                    $checked = $this->is_integration_active($id) ? ' checked="checked"' : '';
                    if (isset($integration['label'])) {
                        $label = $integration['label'];
                        if (is_callable($label)) {
                            $label = $this->call($label, $args);
                        }
                    } else {
                        $label = esc_html(sprintf($_label, $integration['name']));
                    }
                    printf('
  <input type="checkbox" id="kt_integrate_%1$s" name="kt_integrate[]" tabindex="10" value="%1$s"%2$s />
  <label for="kt_integrate_%1$s">%3$s</label>
', $id, $checked, $label);
                }
                if ($integration['form']) {
                    $form = $integration['form'];
                    if (is_callable($form)) {
                        $form = $this->call($form, $args);
                    }
                    print $form;
                }
                print '</p>';
            }
        }

        /**
         * Callback for sorting integrations by name
         * @since 2.0
         * @ignore
         * @param array $a
         * @param array $b
         * @return int
         */
        public function sort_integrations($a, $b) {
            if ($a['_internal'] && !$b['_internal']) {
                return -1;
            } else if ($b['_internal'] && !$a['_internal']) {
                return 1;
            }
            return strnatcasecmp($a['name'], $b['name']);
        }

        /**
         * Print label for Theme Customizer integration with additional
         * themes/plugins names appended appropriately
         * @since 2.0
         */
        public function print_customizer_label() {
            $natives = '';
            $integrations = $this->get_integrations('name');
            foreach ($integrations as $id => $name) {
                if ($this->is_integration_enabled($id) && $this->is_native_integration($id)) {
                    $natives .= " / $name";
                }
            }
            $label = esc_html__('Add to Theme Customizer', 'kt-tinymce-color-grid');
            if ($natives !== '') {
                $label .= $natives;
            }
            print $label;
        }

        public function print_gutenberg_label() {
            vprintf('<span title="%s">%s <sup style="color:#900">beta!</sup>', array(
                esc_attr__('Gutenberg is still in development. There will be dragons!', 'kt-tinymce-color-grid'),
                esc_html__('Integrate with Gutenberg', 'kt-tinymce-color-grid'),
            ));
        }

        /**
         * Print the color editor toolbar
         * @since 2.0
         * @ignore
         */
        public function print_color_toolbar() {
            print "
<div id='kt_toolbar' role='toolbar'>";

            /**
             * Fires inside the palette toolbar
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'palette_toolbar', $this);

            print '
</div>';
        }

        /**
         * Print the toolbar button for adding a color
         * @since 2.0
         * @ignore
         */
        public function print_add_color_button() {
            $add_text = __('Add Color', 'kt-tinymce-color-grid');
            print "
  <button id='kt_add' type='submit' tabindex='8' name='kt_action' value='add' class='button' aria-controls='kt_colors' accesskey='" . _x('A', 'accesskey for adding color', 'kt-tinymce-color-grid') . "' title='" . esc_attr($add_text) . "'>
    <span class='dashicons dashicons-plus-alt2'></span>
    <span class='screen-reader-text'>" . esc_html($add_text) . '</span>
  </button>';
        }

        /**
         * Print the checkbox for toggle auto-naming
         * @since 2.0
         * @ignore
         */
        public function print_transparency_switch() {
            if ($this->support_alpha()) {
                $alpha = get_option(self::ALPHA);
                print "
  <span class='switch-wrap alignright'>
    <input type='checkbox' id='kt_alpha' name='kt_alpha' value='1'" . checked($alpha, 1, 0) . "/>
    <label for='kt_alpha'>" . esc_html__('Transparency', 'kt-tinymce-color-grid') . '</label>
  </span>';
            }
        }

        /**
         * Print checkbox for toggle transparency UI
         * @since 2.0
         * @ignore
         */
        public function print_autoname_switch() {
            $use_autoname = $this->get_cookie(self::AUTONAME, self::DEFAULT_AUTONAME);
            print "
  <span class='switch-wrap alignright hide-if-no-js'>
    <input type='checkbox' id='kt_autoname'" . checked($use_autoname, 1, 0) . "/>
    <label for='kt_autoname'>" . esc_html__('Automatic Names', 'kt-tinymce-color-grid') . '</label>
  </span>';
        }

        /**
         * Print the color editor
         * @since 2.0
         * @ignore
         */
        public function print_color_editor() {
            $autoname = $this->get_cookie(self::AUTONAME, self::DEFAULT_AUTONAME) ? ' class="autoname"' : '';
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
  <input class="ntc_name" type="hidden" name="kt_ntc_names[]" value="%18$s" />
  <button type="button" class="autoname button hide-if-no-js" title="%17$s">
    <i class="dashicons dashicons-editor-break"></i>
    <span class="screen-reader-text">%17$s</span>
  </button>
  <button type="submit" name="kt_action" value="remove-%5$s" tabindex="3" class="remove button" title="%14$s"">
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
                11 => '%7$s', // placeholder
                12 => esc_attr__('Name of Color', 'kt-tinymce-color-grid'),
                13 => esc_html__('Delete', 'kt-tinymce-color-grid'),
                14 => esc_attr__('Three hexadecimal numbers between 00 and FF', 'kt-tinymce-color-grid'),
                15 => esc_attr__('Transparency between 0 and 100', 'kt-tinymce-color-grid'),
                16 => esc_attr__('Automatic Name', 'kt-tinymce-color-grid'),
                17 => '%8$s', // slug
            ));

            $placeholder = esc_attr__('Unnamed Color', 'kt-tinymce-color-grid');
            $palette = get_option(self::PALETTE, array());
            foreach ($palette as $index => $set) {
                $color = $set['color'];
                $alpha = $set['alpha'];
                $name = $set['name'];
                $slug = $set['slug'];
                vprintf($list_entry, array(
                    esc_attr($color),
                    $this->hex2rgba($color, $alpha),
                    esc_attr($alpha),
                    esc_attr($name),
                    $index,
                    $name ? '' : ' autofill',
                    $placeholder,
                    esc_attr($slug),
                ));
            }

            vprintf("</div>
<script type='text/template' id='tmpl-kt_list_entry'>$list_entry</script>", array(
                '#000000',
                'rgba(0,0,0,1)',
                100,
                '',
                'x',
                ' autoname',
                'Black',
                'Black',
            ));
        }

        /**
         * Print backup metabox
         * @since 1.9
         * @ignore
         */
        public function print_backup_metabox() {
            /**
             * Fires inside the backup metabox
             * @since 2.0
             * @param kt_Central_Palette $instance
             */
            do_action(self::HOOK . 'backup_metabox', $this);
        }

        function print_export_form() {
            print '
<p>' . esc_html__('Here you can download an export.', 'kt-tinymce-color-grid') . '</p>';
            $formats = $this->get_export_formats();
            $labels = '';
            $current_format = $this->get_cookie('kt_export_format', 'base64');
            foreach ($formats as $id => $format) {
                $for = "kt_export_format_$id";
                $label = esc_html($format['name']);
                print "
<input type='radio' id='$for' name='kt_export_format' value='$id'" . checked($id, $current_format, false) . " />
<label for='$for' class='screen-reader-text'>$label</label>";
                $labels .= "
    <label for='$for' class='button'>$label</label>
    <label for='$for' class='button button-primary'>$label</label>";
            }
            print '
<p id="kt_export_format_wrap">
  <label for="kt_export_format">' . esc_html__('Format', 'kt-tinymce-color-grid') . '</label>
  <span class="button-group toggle-button-group">' . $labels . '
  </span>
</p>';

            $printed = array();
            $styles = '';
            foreach ($formats as $id => $format) {
                $form = $format['form'];
                if (!$form) {
                    continue;
                }

                if (is_callable($form)) {
                    $args = isset($format['args']) ? $format['args'] : $this;
                    $form = $this->call($form, $args);
                }

                $form_id = false;
                if ($form) {
                    $form_id = $id;
                    $printed[$id] = $format['form'];
                    print "
<div id='kt_export_format_{$id}_form' class='export-format-form'>$form</div>";
                } else if (in_array($format['form'], $printed)) {
                    $keys = array_keys($printed, $format['form']);
                    $form_id = array_shift($keys);
                }

                $radio_id = "kt_export_format_$id";
                $styles .= "
#$radio_id:checked ~ #kt_export_format_wrap .button[for='$radio_id'] {
  display: none }
#$radio_id:checked ~ #kt_export_format_wrap .button-primary[for='$radio_id'] {
  display: inline-block }";
                if ($form_id) {
                    $styles .= "
#$radio_id:checked ~ #kt_export_format_{$form_id}_form {
  display: block }";
                }
            }

            print "
<style type='text/css'>$styles
</style>";

            print "
<p><button type=submit' id='kt_action_export' class='button' name='kt_action' value='export' tabindex='9'>" . esc_html__('Download Export', 'kt-tinymce-color-grid') . '</button></p>
<hr/>';
        }

        function print_import_form() {
            print '
<p>';
            if ($this->can_upload()) {
                print esc_html__('Here you can upload an import.', 'kt-tinymce-color-grid') . '</p>
<p class="hide-if-no-js">
  <label id="kt_upload_label" for="kt_upload" class="button" tabindex="10">
    <span class="spinner"></span>
    <span class="label">' . esc_html__('Choose Import', 'kt-tinymce-color-grid') . '&hellip;</span>
    <span class="loading">' . esc_html__('Uploading', 'kt-tinymce-color-grid') . '&hellip;</span>
  </label>
</p>
<p class="hide-if-js"><input type="file" id="kt_upload" name="kt_upload" /></p>
<p class="hide-if-js"><button type="submit" id="kt_action_import" class="button" name="kt_action" value="import" tabindex="9">' . esc_html__('Download Export', 'kt-tinymce-color-grid') . '</button>';
            } else {
                print esc_html__('Your device is not supporting file uploads. Open your import in a simple text editor and paste its content into this textfield.', 'kt-tinymce-color-grid') . '</p>
<p><textarea name="kt_import" class="widefat" rows="5"></textarea></p>
<p><button type="submit" class="button" name="kt_action" value="import" tabindex="10">' . esc_html__('Import', 'kt-tinymce-color-grid') . '</button>';
            }
            print '
</p>';
        }

        function print_base64_export_form() {
            static $printed = false;
            if ($printed) {
                return;
            }
            $printed = true;

            $partials = array(
                'settings' => __('Settings', 'kt-tinymce-color-grid'),
                'palette' => __('Palette', 'kt-tinymce-color-grid'),
            );

            /**
             * Filters the backup partials
             * @since 2.0
             * @param array $partials
             */
            $partials = apply_filters(self::HOOK . 'partials', $partials);

            if ($partials) {
                print '
<p>';
                foreach ($partials as $key => $label) {
                    $key = esc_attr($key);
                    $partial = "kt_export_{$key}";
                    $checked = $this->get_cookie($partial, 1);
                    print "
  <input type='checkbox' id='$partial' name='kt_export[]' value='$key'" . checked($checked, 1, 0) . "/>
  <label for='$partial'>" . esc_html($label) . "</label>";
                }
                print '
</p>';
            }
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

        function print_css_export_form() {
            static $printed = false;
            if ($printed) {
                return;
            }
            $printed = true;

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
  color: rgba($rgba)<# } #> }
</script>";
        }

        function print_scss_export_form() {
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
         * @param string $name
         * @param array $data
         * @param mixed $selected
         * @param bool $disabled
         * @return string
         */
        public function selectbox($name, $data, $selected = null, $disabled = false) {
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
        protected function get_cookie($name, $default = null) {
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
         * Call a callable.
         *
         * Suppresses any printed output and returns those instead.
         * @since 2.0
         * @ignore
         * @param callable $fn
         * @return mixed
         */
        protected function call($fn) {
            $args = func_get_args();
            array_pop($args);
            if (!is_callable($fn)) {
                return null;
            }
            ob_start();
            $return = call_user_func_array($fn, $args);
            $print = ob_get_contents();
            ob_end_clean();
            if ($print !== '') {
                return $print;
            }
            return $return;
        }

        /**
         * Sanitize a string to #RRGGBB
         * @since 1.4
         * @since 2.0 optionally prepends a #
         * @param string $string String to be checked
         * @param boolean $prepend_hash [optional] Prepend resulting string with a hash
         * @return string|boolean Returns a color of #RRGGBB or false on failure
         */
        public function sanitize_color($string, $prepend_hash = true) {
            $string = strtoupper($string);
            $hex = null;
            if (preg_match('~#?([0-9A-F]{6}|[0-9A-F]{3})~', $string, $hex)) {
                $hex = $hex[1];
                if (strlen($hex) == 3) {
                    $hex = preg_replace('~[0-9A-F]~', '\1\1', $hex);
                }
                return $prepend_hash ? "#$hex" : $hex;
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
            $hue--;
            return array(1, 0, 1 - $hue);
        }

        /**
         * Convert a RGB vector to a HEX string
         * @since 1.9
         * @param array $rgb RGB vector [red, gree, blue] of floats [0..1]
         * @return string|false Returns false if any of the components is outside [0..1]
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

        /**
         * Convert a HEX string and an alpha value into RGBA notation
         * @param string $hex Hexadecimal color
         * @param int $alpha Alpha value [0..100]
         * @return string
         */
        public function hex2rgba($hex, $alpha) {
            $color = $this->sanitize_color($hex, false);
            $hex = str_split($color, 2);
            list($r, $g, $b) = array_map('hexdec', $hex);
            $a = round($alpha / 100, 2);
            return "rgba($r,$g,$b,$a)";
        }

    }

    kt_Central_Palette::instance();
}
