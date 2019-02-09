=== Central Color Palette ===
Contributors: kungtiger
Requires at least: 3.5
Tested up to: 5.0
Stable tag: 1.13.3
Requires PHP: 5.3
Tags: color, customizer, editor, gutenberg, palette, picker, tinymce
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage a site-wide central color palette for an uniform look'n'feel! Supports the new Block Editor, Theme Customizer and many themes and plugins.

== Description ==

This plugin allows you to manage a site-wide central color palette for an uniform look'n'feel. The palette of the new Block Editor and the Theme Customizer are supported, as well as the Classic Editor. You can define this central color palette through the settings menu. All plugins that make use of WordPress' color picker can benefit from this plugin as well.

Also this plugin replaces the color picker for choosing a text or background color found inside the Classic Editor with a bigger and customizable color grid.

For an easy migration between WordPress installations you can export and import your palette settings and colors.

**Theme/Plugin Support**
Central Color Palette supports these plugins and themes:

- [Astra Theme](https://wpastra.com)
- [Beaver Builder](https://www.wpbeaverbuilder.com)
- [Elementor](https://wordpress.org/plugins/elementor)
- [GeneratePress Premium](https://generatepress.com/premium)
- [Hestia Theme](https://wordpress.org/themes/hestia)
- [Neve Theme](https://wordpress.org/themes/neve)
- [OceanWP](https://oceanwp.org)
- [Page Builder Framework](https://wp-pagebuilderframework.com)

== API Documentation ==

Central Color Palette offers some methods, so have a look at the [API Documentation](https://kungtiger.github.io/central-color-palette).

== Privacy Policy ==
This plugin does not collect or use any private data.

== Installation ==

This plugin should work out of the box with a standard installation of WordPress.

**WordPress' Plugin Search**

1. Goto your WordPress and open *Plugins* > *Add New*
2. Search for *Central Color Palette*
3. Click install and activate the plugin

**WordPress' Plugin Repository**

1. Goto [wordpress.org/plugins/kt-tinymce-color-grid](http://wordpress.org/plugins/kt-tinymce-color-grid) and download the zip
2. Goto your WordPress. You can upload a plugin via `/wp-admin/plugin-install.php?tab=upload`
3. Activate the plugin through the *Plugins* menu in WordPress

**Manual installation**

1. Goto [wordpress.org/plugins/kt-tinymce-color-grid](http://wordpress.org/plugins/kt-tinymce-color-grid) and download the zip
2. Upload the directory found inside the zip archive to your /wp-content/plugins/ directory
3. Activate the plugin through the *Plugins* menu in WordPress

== Frequently Asked Questions ==

= Questions? Concerns? Feature Request? =

Please [contact me](http://wordpress.org/support/plugin/kt-tinymce-color-grid) and we'll see what we can do

== Screenshots ==

1. The Color Palette Editor
2. The new Block Editor is supported
3. Custom palette for the Theme Customizer
4. Legacy support for the Classic Editor

== Upgrade Notice ==

= 1.13.3 =
Adds support for Hestia Theme and a public method to manually set the central palette

= 1.13.2 =
Fixes incompatibility with Elementor Pro and added support for Neve Theme

= 1.13.1 =
Fixes broken import

= 1.13 =
Adds Gutenberg/Block Editor support and more export formats

= 1.12.6 =
Customizer palette breaks into rows of 8 rows

= 1.12.5 =
Fixes faulty output on palette save

= 1.12.4 =
Fixes unstable integration with Elementor

= 1.12.3 =
Fixes dynamic styles for color pickers interfering with other parts of TinyMCE

= 1.12.2 =
Support for Astra Theme and Page Builder Framework added

= 1.12.1 =
Fixes a small bug where color names do not show up correctly

= 1.12 =
Adds color integration for Beaver Builder

= 1.11 =
Adds color integration for OceanWP

= 1.10 =
Adds color integration for Elementor and GeneratePress Premium

= 1.9.3 =
Fixes fatal error when updating from an older version than 1.6

= 1.9.2 =
Corrects some palette import errors

= 1.9.1 =
Fixes fatal error on network installations of WordPress

= 1.9 =
Automatic name generation and palette import of various third party formats

= 1.8 =
- All settings and the palette can now be exported and imported
- Added new grid type: only central palette for TinyMCE

= 1.7.2 =
- Fixes JavaScript bug and conflict with Poedit

= 1.7.1 =
Corrects some spelling mistakes

= 1.7 =
Theme Customizer support added and color grid is now adjustable

= 1.6.1 =
Fixed hidden colors after defining more than 13 colors

= 1.6 =
Mayor useability improvements

= 1.5 =
Added clean uninstall routine

= 1.4.4 =
Fixes a possible JavaScript error

= 1.4.3 =
Update for WordPress 4.4

= 1.4.2 =
Repairs header already send error

= 1.4.1 =
Fixes a broken script

= 1.4 =
Improves settings page and fixes security leaks

= 1.3.1 =
Minor changes for WordPress 4.3

= 1.3 =
Added support for custom persistent colors

= 1.2 =
Fixes an error when using more than one TinyMCE

= 1.1 =
Just a few performance improvements

= 1.0 =
Initial release.
