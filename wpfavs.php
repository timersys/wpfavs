<?php
/**
 * Wp Favs Plugin
 *
 * @package   Wpfavs
 * @author    Damian Logghe <info@timersys.com>
 * @license   GPL-2.0+
 * @link      http://wp.timersys.com
 * @copyright 2014 Timersys
 *
 * @wordpress-plugin
 * Plugin Name:       Wp Favs
 * Plugin URI:        http://wp.timersys.com
 * Description:       Create and import your favorites plugins lists from wpfavs.com
 * Version:           1.2
 * Author:            Damian Logghe
 * Author URI:        http://wp.timersys.com
 * Text Domain:       wpfavs
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/timersys/wpfavs
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define('WPFAVS_VERSION', '1.2');
/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wpfavs-admin.php' );
	add_action( 'plugins_loaded', array( 'Wpfavs_Admin', 'get_instance' ) );

}
