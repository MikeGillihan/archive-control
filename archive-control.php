<?php
/**
 * The best option to get your sports team up and running quickly with a custom website.
 * We’ve built in all of the features you need to set up your team’s site so that you can
 * be up and running quickly without having to hire someone or create something yourself.
 * It’s everything you need, and nothing you don’t.
 *
 * @package   Archive_Control
 * @author    Jesse Sutherland
 * @license   GPL-2.0+
 * @link      http://switchthemes.com
 * @copyright 2016 Jesse Sutherland
 *
 * @wordpress-plugin
 * Plugin Name: Archive Control
 * Plugin URI:  http://switchthemes.com/archive-control/
 * Description: Customize custom post type archive titles, order, pagination, and add editable textareas above and below archive pages.
 * Version:     1.3.1
 * Author:      Jesse Sutherland
 * Author URI:  http://jessesutherland.com
 * Text Domain: archive-control
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-archive-control.php' );

register_activation_hook( __FILE__, array( 'Archive_Control', 'activate' ) );

Archive_Control::get_instance();
