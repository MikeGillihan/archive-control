=== Archive Control ===
Contributors: thejester12
Donate link: http://switchthemes.com/archive-control/
Tags: custom post type, post type, post types, archive, title, order, pagination
Requires at least: 4.1
Tested up to: 4.6.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customize CPT archive titles, order, pagination, and add editable content above and below their archive pages.

== Description ==

If you enjoy using custom post types, and find yourself making the same customizations to the archive (multiple listing) pages associated with them, then this plugin can help you and your site editors.

Upon installing the plugin, you'll be able to set/enable these options for each custom post type that has the "has_archive" setting enabled. If you enable some content to be editable, a new settings screen will appear in the menu under that custom post type.

*   **Archive Titles:** If your theme is using the_archive_title() function, then you can modify the your archive titles.
    * Remove Labels (Archive, Category, Tag, etc.) - Sometimes you just want to get rid of the annoying default words.
    * Custom Override - Allow an editor to write something custom for an archive headline.
*   **Archive Featured Image:** You can allow for a custom featured image that applies to an archive page and have it added automatically, or add it yourself via a theme function.
*   **Content Before or After Archive List:** Give your site editors the ability to edit content directly before or after the archive list. You can have it automatically added, or use a theme function to give you more control over their placement (see "Functions" tab).
*   **Archive Order By:** Instead of messing with code to change the order of a custom post type archive, now you can do it in a few clicks.
    * Date Published
    * Title
    * Date Modified
    * Menu Order
    * Random
    * ID
    * Author
    * Post Slug
    * Post Type
    * Comment Count
    * Parent
    * Meta Value
    * Meta Value (Numeric)
    * No Order
*   **Archive Order:** In addition to the order by value, you'll also want to change the order.
    * Ascending
    * Descending
*   **Archive Pagination:** The pagination settings are easy to change without changing code.
    * Show Everything
    * Custom Posts Per Page

The plugin doesn't add any CSS or javascript to the front end. The styling of the archive page is left completely up to you. This plugin should be friendly for power users and developers alike. You can use it entirely without changing theme code, or you can control the placement and functionality more exactly using the provided functions. See the "Functions" tab.


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

= 1.1 =
* Added Archive Featured Image functionality
* Allowed more customization with functions for developers
= 1.0 =
* Initial Version

== Upgrade Notice ==

= 1.1 =
Added Archive Featured Image functionality and functions for developers

= 1.0 =
Initial Version

== Functions ==

### Functions Provided by the Plugin ###

**the_archive_top_content( _boolean_ $html = true)**
> Displays the _top_ archive content on an archive page. Additional html markup can be removed by setting false.

**archive_top_content( _boolean_ $html = true, _string_ $post_type = null)**
> Displays the _top_ archive content for any post type anywhere on the site. Additional html markup can be removed by setting false. Post type is automatic if on an archive page.

**get_archive_top_content( _string_ $post_type = null)**
> Returns the _top_ archive content for any post type anywhere on the site. Post type is automatic if on an archive page.

**the_archive_bottom_content( _boolean_ $html = true)**
> Displays the _bottom_ archive content on an archive page. Additional html markup can be removed by setting false.

**archive_bottom_content( _boolean_ $html = true, _string_ $post_type = null)**
> Displays the _bottom_ archive content for any post type anywhere on the site. Additional html markup can be removed by setting false. Post type is automatic if on an archive page.

**get_archive_bottom_content( _string_ $post_type = null)**
> Returns the _top_ archive content for any post type anywhere on the site. Post type is automatic if on an archive page.

**the_archive_thumbnail( _string_ $size = 'large', _string_ $post_type = null)**
> Display the archive thumbnail. Default size is large but first parameter can set any valid image size, or an array of width and height values in pixels (in that order). Post type is automatic if on an archive page.

**get_archive_thumbnail_src( _string_ $size = 'large', _string_ $post_type = null)**
> Returns the archive image url source. Default size is large but first parameter can set any valid image size, or an array of width and height values in pixels (in that order). Post type is automatic if on an archive page.

**get_archive_thumbnail_id( _string_ $post_type = null)**
> Returns the archive thumbnail id. if you want to use other common WordPress attachment functions to retrieve data about the image. Post type is automatic if on an archive page.
