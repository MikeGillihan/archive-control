=== Archive Control ===
Contributors: thejester12
Donate link: http://switchthemes.com/archive-control/
Tags: custom post type, post type, post types, archive, title, order, pagination
Requires at least: 4.1
Tested up to: 4.6.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customize custom post type archive titles, order, pagination, and add editable textareas above and below archive pages.

== Description ==

If you enjoy using custom post types, and find yourself making the same customizations to the archive pages associated with them, then this plugin can help you.

We have carefully separated the settings for the post types, which only administrators should be able to edit from the content, which your site editors and clients should be able to edit.

* **Archive Titles:** If your theme is using the_archive_title() function, then you can control the titles of your archives and either set them to remove "Archives: " from the headline, or override it entirely with something custom.
* **Archive Order:** Instead of messing with code to change the order of a custom post type archive to Title, or a Meta Value, you can customize it easily in this plugin.
* **Pagination:** You may find that your default settings for blog pagination don't apply with your custom post type, maybe you need to show all posts, or maybe you need to show just 2 at a time. The control is yours.
* **Editable Textareas Before/After:** Give your client ability to edit content directly before or after the archive list. You can have it automatically added, or use a theme function to give you more control over their placement.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/archive-control` directory, or install the zipped plugin through the WordPress plugins screen directly.
2. Activate the Archive Control plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Archive Control screen to configure the settings for each custom post type.
4. Once you have saved your settings, you can edit the titles and textareas in the 'Edit Archive Page' submenu for each custom post type.


== Frequently Asked Questions ==

= Why won't my custom post type appear in the Archive Control Settings? =

It depends a lot how your custom post type is created. It must be set to "public", and it must have "has_archive" set to true, or a string. Be sure to check those settings first!

= Why can't I customize the Archive title? =

The archive title functionality requires that a special function is used to display your archive title: the_archive_title(). If your theme does not use this function, and either hardcodes the title or uses a different function, then this plugin won't be able to help.

= How do you add in the content before/after archives? =

The "Automatic" setting uses the "loop_start" and "loop_end" WordPress hooks to inject the content. If you would rather use the "Manual Function" setting, then we give you the functions the_archive_top_content() and the_archive_bottom_content() to place in your theme as you choose.


== Screenshots ==

1. An example of a custom post type archive page.
2. Settings for each custom post type that has an archive.
3. Give your client the ability to edit archive headlines and content before and after the loop.

== Changelog ==

= 1.0 =
* Initial Version
