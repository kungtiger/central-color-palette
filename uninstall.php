<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit(403);
}

foreach (array('customizer', 'block_size', 'block_axis', 'gutenberg', 'elementor', 'gp', 'gp_alpha', 'oceanwp', 'oceanwp_alpha', 'beaverbuilder', 'palette', 'version', 'spread', 'clamps', 'blocks', 'visual', 'clamp', 'luma', 'cols', 'rows', 'type', 'map') as $key) {
    delete_option("kt_color_grid_{$key}");
}

foreach (array('closedpostboxes', 'metaboxhidden', 'meta-box-order', 'screen_layout') as $key) {
    delete_metadata('user', null, "{$key}_settings_page_kt_tinymce_color_grid", null, true);
}

foreach (array('color_grid_autoname', 'export_settings', 'export_palette') as $key) {
    setcookie("kt_{$key}", '', 1);
}
