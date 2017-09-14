<?php
/**
 * Header of all views
 *
 * @package   Wp Favs
 * @author    Damian Logghe <info@timersys.com>
 * @license   GPL-2.0+
 * @link      http://wp.timersys.com/wpfavs
 * @copyright 2014 Timersys
 */

?>

<div id="poststuff" class="wrap">
	<div id="post-body" class="metabox-holder columns-2">

		<h2><?php echo esc_html( get_admin_page_title() ) . ' v' . self::VERSION; ?></h2>
		
		<div id="postbox-container-1" class="postbox-container">	
			<div class="postbox ">
				<h3 class="hndle"><span><?php _e( 'Need support?', $this->plugin_slug );?></span></h3>
				<div class="inside">
					<p><?php echo sprintf( __( 'If you need support please go to the <a href="%s">support forums</a>', $this->plugin_slug ), 'http://wordpress.org/support/plugin/wpfavs');?></p>
				</div>
			</div>
			<div class="postbox " style="border: 3px dashed red;">
				<h3 class="hndle"><span><?php _e( 'PREMIUM VERSION', $this->plugin_slug );?></span></h3>
				<div class="inside">
					<p><?php echo sprintf( __( 'Add custom plugins to your list or connect your CodeCanyon account, sign up now on <a href="%s">Wp Favs</a>', $this->plugin_slug ), 'https://wpfavs.com');?></p>
				</div>
			</div>

			<div class="postbox ">
				<h3 class="hndle"><span><?php _e( 'Support Wp Favs!', $this->plugin_slug );?></span></h3>
				<div class="inside">
					<p><?php _e( 'If you like this plugin, consider supporting it by donating.', $this->plugin_slug );?></p>

					<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K4T6L69EV9G2Q" class="button-primary"><?php _e( 'Donate with PayPal', $this->plugin_slug );?></a>

					<p><?php _e( 'Some other ways to support this plugin', $this->plugin_slug );?></p>
					<ul class="ul-square">
						<li><a href="http://wordpress.org/support/view/plugin-reviews/wpfavs?rate=5#postform" target="_blank"><?php _e( 'Leave a  &#9733;&#9733;&#9733;&#9733;&#9733; review on WordPress.org', $this->plugin_slug );?></a></li>
						<li><a href="http://twitter.com/intent/tweet/?text=I+am+using+Wp+Favs+on+my+WordPress+site.+It%27s+great%21&amp;via=chifliiii&amp;url=http%3A%2F%2Fwordpress.org%2Fplugins%2Fwpfavs%2F" target="_blank"><?php _e( 'Tweet about Wp Favs', $this->plugin_slug );?></a></li>
						<li><a href="http://wordpress.org/plugins/wpfavs/#compatibility"><?php _e( 'Vote "works" on the WordPress.org plugin page', $this->plugin_slug );?></a></li>
						<li><?php _e( 'Translate it to your language!', $this->plugin_slug );?></li>
					</ul>
				</div>
			</div>	
			<div class="postbox ">
				<h3 class="hndle"><span><?php _e( 'Leave Feedback', $this->plugin_slug );?></span></h3>
				<div class="inside">
					<p><?php echo sprintf( __( 'Please send me your feedback <a href="%s">here</a>', $this->plugin_slug ), 'https://wpfavs.com/contact-us/');?></p>
				</div>
			</div>		

		</div>	