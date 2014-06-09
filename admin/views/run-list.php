<?php
/**
 * Represents the view when we are about to run a wpfav.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Wp Favs
 * @author    Damian Logghe <info@timersys.com>
 * @license   GPL-2.0+
 * @link      http://wp.timersys.com/wpfavs
 * @copyright 2014 Timersys
 */

?>




<a href="<?php echo admin_url( 'tools.php?page='. $this->plugin_slug );?>">&larr; Go Back</a>
<div id="wpfav-response">
	<form id="wpfavs" method="post">
	<?php if( !empty( $_GET['wpfav'] ) ) {

		Wpfavs_Admin::print_plugins_table( $this->api_key_response[$_GET['wpfav']]['plugins'] );

	} else {

		echo $this->message_box( 'error', __( "You didn't selected a Wp Fav to show", $this->plugin_slug ) );

	}
	?>
	</form>
</div>


