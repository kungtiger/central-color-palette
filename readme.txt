=== Central Color Palette ===
Contributors: kungtiger
Requires at least: 3.5
Tested up to: 5.2
Stable tag: 1.13.11
Requires PHP: 5.3
Tags: color, customizer, editor, gutenberg, palette, picker, tinymce
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage a site-wide central color palette for an uniform look'n'feel! Supports the new block editor, Theme Customizer and many themes and plugins.

== Description ==

This plugin allows you to manage a site-wide central color palette for an uniform look'n'feel. The palette of the new block editor and the Theme Customizer are supported, as well as the classic editor. You can define this central color palette through the settings menu. All plugins that make use of WordPress' color picker can benefit from this plugin as well.

Also this plugin replaces the color picker for choosing a text or background color found inside the classic editor with a bigger and customizable color grid.

For an easy migration between WordPress installations you can export and import your palette settings and colors.

**Theme/Plugin Support**
Central Color Palette supports these plugins and themes:

- [Astra Theme](https://wpastra.com)
- [Beaver Builder](https://www.wpbeaverbuilder.com)
- [Elementor](https://wordpress.org/plugins/elementor)
- [FontPress](https://lcweb.it/fontpress)
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
2. The new block editor is supported
3. Custom palette for the Theme Customizer
4. Legacy support for the classic editor

== Changelog ==

= 1.13.11 =
Added support for FontPress

= 1.13.10 =
- Added "Clear Color" cell to the picker of the classic editor when only using the palette
- Fixed unset variable warning when saving an empty palette (thanks to @cadiz)
- Fixed syntax error for older PHP versions

= 1.13.9 =
Classic Editor: Added an option to add a missing "Clear Color" cell to the picker when only using the palette

= 1.13.8 =
- Elementor: Fixes broken alpha channel
- Elementor: Adds color names as mouse-over
- Fixes bug which can cause a fatal error on page loads without a WP_Screen instance

= 1.13.7 =
- Gutenberg: Fixed overwritten color classes on front-end (thanks to @t0b1hh)
- Elementor: Fixed palette size and row display for many colors (thanks to @stevewoody82)

= 1.13.6 =
Fixed broken palette upload/import

= 1.13.5 =
Fixed broken download for JSON and Backup formats

= 1.13.4 =
Fixed bug: new palette color are not save due to logic error

= 1.13.3 =
- Added support for Hestia Theme
- Added public method to manually set the central palette (suggested by @mrhasbean)
- Fixed bug in color sanitation method

= 1.13.2 =
- Fixed incompatibility with Elementor Pro
- Added support for Neve Theme

= 1.13.1 =
- Fixed broken Import
- Fixed JavaScript bug

= 1.13 =
- Added Gutenberg/block editor support
- Transparency channel is now always visible inside the palette editor
- Transparency can now be toggled individually for those integrations that use it
- Colors can be deactivated to hide them inside the block editor but retain their assignment on a block level
- Imports try to reuse existing colors to reduce chaos inside the block editor
- Added more export formats
- Added JSON import

= 1.12.6 =
Customizer palette breaks into rows of 8 colors

= 1.12.5 =
Fixed faulty output on palette save (thanks to @cavalierlife)

= 1.12.4 =
Fixed unstable integration with Elementor (thanks to @eplanet)

= 1.12.3 =
- Fixed dynamic styles interfering with other parts of TinyMCE (thanks to @jb2386)
- Added credits to changelog of all those who contributed
- Added GDPR notice to readme

= 1.12.2 =
- Clarified what "Add to Customizer" does
- Added support for [Astra Theme](https://wpastra.com)
- Added support for [Page Builder Framework](https://wp-pagebuilderframework.com)

= 1.12.1 =
Fixed a bug where opacity values are displayed instead of color names

= 1.12 =
- Added support for [Beaver Builder](https://www.wpbeaverbuilder.com)
- Fixed some bugs and typos

= 1.11 =
- Added support for [OceanWP](https://oceanwp.org)
- Fixed alpha picker for Chrome
- Removed palette import, it's to buggy.

= 1.10 =
- Added support for [Elementor](https://wordpress.org/plugins/elementor) (thanks to @blackeye0013)
- Added support for [GeneratePress Premium](https://generatepress.com/premium) (thanks to @eplanet)

= 1.9.3 =
- Fixed fatal error when updating from an older version than 1.6 (thanks to @flixflix)
- Removed dependancy on farbtastic; now the color picker ships self-contained

= 1.9.2 =
Corrected some palette import bugs

= 1.9.1 =
Fixed fatal error due to missing wp_is_mobile on network installations of WordPress (thanks to @kzeni)

= 1.9 =
- Import third party palettes
- Automatic color names thanks to Chirag Mehta's [Name that Color](http://chir.ag/projects/ntc) and @flixflix
- Improved backup

= 1.8 =
- All settings and the palette can now be exported and imported (suggested by @sigersmit)
- Added new grid type: only central palette for TinyMCE (suggested by @jhned)
- Added CSS for right-to-left locales

= 1.7.2 =
- Fixes JavaSript bug (thanks to @hrohh)
- Fixes conflict with Poedit (thanks to @hrohh)

= 1.7.1 =
Corrected typos and some translation strings

= 1.7 =
- Theme Customizer support added (thanks to @kzeni)
- Color grid is now adjustable in type and size

= 1.6.1 =
Fixed hidden colors after defining more than 13 colors (thanks to @wlashack)

= 1.6 =
- Useability improvements
- Faster grid generation by using pre-calculated colors
- Namespaced CSS IDs and HTML form variables to avoid name collisions
- Fixed CSS errors

= 1.5 =
Added clean uninstall routine

= 1.4.4 =
- Fixed possible JavaScript error
- Reduced overhead
- Removed language files; now handled by GlotPress

= 1.4.3 =
Update for WordPress 4.4

= 1.4.2 =
- Repaired headers already send error
- Preparation for GlotPress

= 1.4.1 =
Fixed a broken script

= 1.4 =
- Improved settings page
- Improved accessibility
- Fixed security leaks
- Fixed translation bugs

= 1.3.1 =
Changed HTML to conform with WordPress 4.3

= 1.3 =
Added support for custom persistent colors (thanks to @cash3p)

= 1.2 =
Fixes an error when using more than one TinyMCE

= 1.1 =
- Stylesheet gets enqueued only on pages with a TinyMCE
- Reduced overhead

= 1.0 =
Initial release.

== Upgrade Notice ==

= 1.13.11 =
Adds support for FontPress

= 1.13.10 =
Fixes two PHP bugs, and automatically adds the "Clear Color" cell to the picker of the classic editor when only using the palette

= 1.13.9 =
Adds an option to add a missing "Clear Color" cell to the picker when only using the palette

= 1.13.8 =
Fixes transparency for Element and a fatal error for some sites

= 1.13.7 =
Fixes palette colors not showing on the front-end and a bug with Elementor

= 1.13.6 =
Fixes broken palette upload/import

= 1.13.5 =
Fixes broken download for JSON and Backup formats

= 1.13.4 =
Fixes bug: new palette colors are not saved

= 1.13.3 =
Adds support for Hestia Theme and a public method to manually set the central palette

= 1.13.2 =
Fixes incompatibility with Elementor Pro and added support for Neve Theme

= 1.13.1 =
Fixes broken import

= 1.13 =
Adds Gutenberg/block editor support and more export formats

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
