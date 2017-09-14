<?php
/**
 * Represents the view for the main dashboard.
 *
 * @package   Wp Favs
 * @author    Damian Logghe <info@timersys.com>
 * @license   GPL-2.0+
 * @link      http://wp.timersys.com/wpfavs
 * @copyright 2014 Timersys
 */

?>


	<p><?php _e( sprintf('If you are new to Wp favs, first go to <a href="%1$s" target="_blank">%1$s</a> and create your first list! Or you can <a href="%2$s" target="_blank">browse</a> the existing Wp favs', 'https://wpfavs.com', 'http://wpfavs.com/wpfavs/'), $this->plugin_slug );?></p>


	<form name="wpfavs-form" id="wpfavs-form" action="post">
		<h4>API Key</h4>
		<input type="text" name="wpfav_apikey" id="wpfav_apikey" placeholder="<?php _e( 'Insert your API key here to load your lists', $this->plugin_slug );?>" class="regular-text" value="<?php echo $this->api_key;?>" />
		<input class="button-primary wpfav_apikey-button" data-what="apikey" type="submit" value="<?php _e( 'Get Wp Favs', $this->plugin_slug );?>" name="Submit">
		<h4>Quick Key</h4>
		<input type="text" name="wpfav_quickuse" id="wpfav_quickkey" placeholder="<?php _e( 'Insert Wp Fav quick use key to load just one list', $this->plugin_slug );?>" class="regular-text" value="<?php echo $this->quick_key;?>" />
		<input class="button-primary wpfav_apikey-button" data-what="quickkey" type="submit" value="<?php _e( 'Quick Load', $this->plugin_slug );?>" name="Submit">
		<h4>Wordpress.org username</h4>
		<input type="text" name="wpfav_wp_username" id="wpfav_wp_username" placeholder="<?php _e( 'Insert wordpress.org username to load your favorite plugins', $this->plugin_slug );?>" class="regular-text" value="<?php echo $this->wp_user;?>" />
		<input class="button-primary wpfav_apikey-button" data-what="wp_username" type="submit" value="<?php _e( 'Load Favorites', $this->plugin_slug );?>" name="Submit">
		<h4>Other Tools</h4>
		<?php
		$all_plugins = get_plugins();
		if( $all_plugins ){
			foreach ($all_plugins as $path => $plugin){
				$installed[] = str_replace(['/','.php'],'',strstr($path,'/'));
			}
		}
		$active = get_option('active_plugins');
		if( $active ){
			foreach ($active as $plugin){
				$activated[] = str_replace(['/','.php'],'',strstr($plugin,'/'));
			}
		}
		?>
		<a href="https://wpfavs.com/wpfav/create/<?= implode(',',$installed);?>" class="button-primary" target="_blank">Export installed plugins (<?= count($installed);?>)</a>
		<a href="https://wpfavs.com/wpfav/create/<?= implode(',',$activated);?>" class="button-primary" target="_blank">Export active plugins (<?= count($activated);?>)</a>
	</form>

	<div id="wplists">
		<div class="loading">
			<?php _e( 'Establishing connection to remote server, please wait', $this->plugin_slug );?>
			<img src="<?php echo site_url('wp-admin/images/wpspin_light.gif');?>" alt="loading" class="spin"/>
		</div>
		<div id="wpfav-response">
			<?php
			if( !empty($this->api_key_response) )
				$this->print_wpfavs( $this->api_key_response );
			?>
		</div>

	</div><!--wplists-->

</div><!--post-body-->	