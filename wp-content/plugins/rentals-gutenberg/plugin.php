<?php
/**
 * Plugin Name: WpRentals  - Gutenberg Blocks 
 * Plugin URI: https://wprentals.org
 * Description:  WpRentals  - Gutenberg Blocks 
 * Author:wprentals.org
 * Author URI: https://wprentals.org
 * Version: 2.3
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
