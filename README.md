# Central Color Palette 
**Contributors:** kungtiger  
**Requires at least:** 3.5  
**Tested up to:** 4.9  
**Stable tag:** 2.0  
**Tags:** color, customizer, editor, iris, palette, picker, tinymce  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Take full control over color pickers of TinyMCE and the palette of the Theme Customizer. Create a central color palette for an uniform look'n'feel!


## Description 

This plugin replaces the color picker for choosing a text or background color found inside the TinyMCE toolbar with a bigger and customizable color grid. The palette of the color picker found especially in the Theme Customizer can also be set through a central palette. You can define this central color palette through the settings menu. All plugins that make use of WordPress' color picker can benefit from this plugin as well.

## Installation 

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


## Frequently Asked Questions 


### Questions? Concerns? Feature Request? 

Please [open an issue](https://github.com/kungtiger/central-color-palette/issues) or [contribute on 
GitHub](https://github.com/kungtiger/central-color-palette/tree/master)


## Changelog 

### 2.0


### 1.12
- Added support for [Beaver Builder](https://www.wpbeaverbuilder.com)
- Fixed some bugs and typos


### 1.11 
- Added support for [OceanWP](https://oceanwp.org)
- Fixed alpha picker for Chrome
- Removed palette import, it's to buggy.


### 1.10 
- Added support for [Elementor](https://wordpress.org/plugins/elementor)
- Added support for [GeneratePress Premium](https://generatepress.com/premium)


### 1.9.3 
- Fixed fatal error when updating from an older version than 1.6
- Removed dependancy on farbtastic; now the color picker ships self-contained


### 1.9.2 
Corrected some palette import bugs


### 1.9.1 
Fixed fatal error due to missing wp_is_mobile on network installations of WordPress


### 1.9 
- Import third party palettes
- Automatic color names thanks to Chirag Mehta's [Name that Color](http://chir.ag/projects/ntc)
- Improved backup


### 1.8 
- All settings and the palette can now be exported and imported
- Added new grid type: only central palette for TinyMCE
- Added CSS for right-to-left locales


### 1.7.2 
- Fixes JavaSript bug
- Fixes conflict with Poedit


### 1.7.1 
Corrected typos and some translation strings


### 1.7 
- Theme Customizer support added
- Color grid is now adjustable in type and size


### 1.6.1 
Fixed hidden colors after defining more than 13 colors


### 1.6 
- Useability improvements
- Faster grid generation by using pre-calculated colors
- Namespaced CSS IDs and HTML form variables to avoid name collisions
- Fixed CSS errors


### 1.5 
Added clean uninstall routine


### 1.4.4 
- Fixed possible JavaScript error
- Reduced overhead
- Removed language files; now handled by GlotPress


### 1.4.3 
Update for WordPress 4.4


### 1.4.2 
- Repaired headers already send error
- Preparation for GlotPress


### 1.4.1 
Fixed a broken script


### 1.4 
- Improved settings page
- Improved accessibility
- Fixed security leaks
- Fixed translation bugs


### 1.3.1 
Changed HTML to conform with WordPress 4.3


### 1.3 
Added support for custom persistent colors


### 1.2 
Fixes an error when using more than one TinyMCE


### 1.1 
- Stylesheet gets enqueued only on pages with a TinyMCE
- Reduced overhead


### 1.0 
Initial release.
