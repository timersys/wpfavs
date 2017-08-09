<?php
/**
 * Wp Favs
 *
 * @package   Wpfavs_Admin
 * @author    Damian Logghe <info@timersys.com>
 * @license   GPL-2.0+
 * @link      http://wpfavs.com
 * @copyright 2014 Damian Logghe
 */

/**
 * Main Class of Wp Favs Plugin
 *
 * @package Wpfavs_Admin
 * @author Damian Logghe <info@timersys.com>
 */
class Wpfavs_Admin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.1';
	
	/**
	 * API Url to do the remote calls
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	public $api_url = 'https://wpfavs.com/api/v1';

	/**
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	var $plugin_slug = 'wpfavs';

	protected $wpfav_id = null;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Holds the api key entered
	 *
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	var $api_key = '';

	/**
	 * Holds the api key response(transient)
	 *
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	var $api_key_reponse = '';

	/**
	 * Holds WordPress response(transient)
	 *
	 * @since 1.0.2
	 * 
	 * @var string
	 */
	var $wp_reponse = '';

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		//Ajax actions
		add_action( 'wp_ajax_wpfav_apikey', array( $this, 'wpfav_apikey_cb' ) );
		add_action( 'wp_ajax_wpfav_quickkey', array( $this, 'wpfav_quickkey_cb' ) );
		add_action( 'wp_ajax_wpfav_wp_username', array( $this, 'get_wp_favorite_plugins' ) );

		//load options
		$this->load_wpfav_options();

		//TGM
		require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';

		add_action( 'tgmpa_register', [$this,'register_required_plugins'] );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.1
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Load options if exist to prefill fields
	 * @since 1.0.1
	 * @return void
	 */
	private function load_wpfav_options() {

		if( $this->screen_check() ) {

			$this->api_key 			= get_option( $this->plugin_slug . 'wpfav_apikey' );
			$this->quick_key 		= get_option( $this->plugin_slug . 'wpfav_quickkey' );
			$this->wp_user 			= get_option( $this->plugin_slug . 'wpfav_wpuser' );
			$this->api_key_response = json_decode( get_transient( 'wpfav_install'),TRUE );


		}
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {


		if ( $this->screen_check() ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), self::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		
		if ( $this->screen_check() ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), self::VERSION );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'wpfavs', array('ajax_url' =>  admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'wpfav-nonce' ) ) );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {


		$this->plugin_screen_hook_suffix = add_submenu_page(
			'tools.php',
			__( 'Wp Favs', $this->plugin_slug ),
			__( 'Wp Favs', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		include_once( 'views/header.php' );

		//If we are running an action, we are running the plugin lists
		if( isset( $_GET['run_wpfav'] ) )
		{
			$this->wpfav_id = esc_attr($_GET['run_wpfav']);

		#	$html = ob_get_clean();
		#	include_once( 'views/run-list.php' );
			
		} else {

			include_once( 'views/main.php' );

		}	

		include_once( 'views/footer.php' );
	}

	public function register_required_plugins(){

		$cache = json_decode(get_transient('wpfav_install'), true);

		if($cache && isset($cache['id']) )
			$wpfav = $cache;
		else {
			$wpfav_id = filter_input(INPUT_GET,'wpfav_id',FILTER_SANITIZE_NUMBER_INT);
			$type = filter_input(INPUT_GET,'type',FILTER_SANITIZE_STRING);
			if( empty($wpfav_id) || ! in_array($type, ['wpfav_token_key_response','wpfav_apikey_response','wpfav_wp_response']))
				return;
			$data = get_transient('wpfav_install');
			$wpfavs = json_decode($data,true);

			if( $type == 'wpfav_apikey_response') {
				$wpfav = array_values(array_filter( $wpfavs, function( $v ) use ($wpfav_id){

					if ( $v['id'] == $wpfav_id )
						return true;
				}))[0];
			} elseif( $type == 'wpfav_wp_response') {
				$wpfav = $wpfavs[7331];
			} else {
				$wpfav = $wpfavs;
			}
			if(empty($wpfav))
				return;
			// save for next screen
			set_transient( 'wpfav_install', json_encode($wpfav), DAY_IN_SECONDS);
		}
		/**
		 * Filter TGM args to change page title
		 * @param $args
		 */
		add_filter('tgmpa_admin_menu_args',  function($args) use($wpfav) {
			$args['page_title'] = $wpfav['name'];
			return $args;
        });
		$plugins = [];
		if( isset($wpfav['plugins'])) {
			foreach ( $wpfav['plugins'] as $plugin ) {
				$plugins[] = [
					'name'      => $plugin['name'],
					'slug'      => $plugin['slug'],
					'source'    => $plugin['data']['download_link'],
				];
			}
		}


		$config = array(
			'id'           => 'wpfavs',                 // Unique ID for hashing notices for multiple instances of TGMPA.
			'menu'         => 'wpfavs-install', // Menu slug.
			'parent_slug'  => 'tools.php',            // Parent menu slug.
			'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'message'      => '<p>'.$wpfav['description'].'</p></p>',
		);
		tgmpa( $plugins, $config );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'tools.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Ajax function that gets the api key and            
	 * do the remote call to retrieve the wpfavs lists          
	 *
	 * @since    1.0.0
	 */
	public function wpfav_apikey_cb() {
			
		$nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'wpfav-nonce' ) )
        	die ( 'Wrong nonce!');

        //apikey
        $wpfav_apikey = $_POST['api_key'];


		// Call the API
		$response = wp_remote_get( $this->api_url .'/wpfavs/'.$wpfav_apikey );

		
		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
  			echo self::message_box( $error_string );
  			die();
		}

		// Decode response
		$response = apply_filters( 'wpfav_api_response', json_decode( wp_remote_retrieve_body( $response ), TRUE ) );

		//check for api errors
		if( isset( $response['error'] ) ) {
  			echo self::message_box( $response['error'] );
  			die();
		}
		// If we made it to here let's save it and load our table class
		update_option( $this->plugin_slug . 'wpfav_apikey', $wpfav_apikey );

		set_transient( 'wpfav_install', json_encode($response), 15 * DAY_IN_SECONDS );

		$this->print_wpfavs( $response );

		die();
	}
	
	/**
	 * Ajax function that gets the api key and            
	 * do the remote call to retrieve the wpfavs lists          
	 *
	 * @since    1.0.0
	 */
	public function wpfav_quickkey_cb() {
			
		$nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'wpfav-nonce' ) )
        	die ( 'Wrong nonce!');

        //quickkey
        $wpfav_quickkey = $_POST['api_key'];

		// Call the API
		$response = wp_remote_get( $this->api_url .'/wpfav/'.$wpfav_quickkey );


		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
  			echo self::message_box( $error_string );
  			die();
		}

		// Decode response
		$response = apply_filters( 'wpfav_quickkey_response', json_decode( wp_remote_retrieve_body( $response ), TRUE ) );


		//check for api errors
		if( isset( $response['error'] ) ) {
  			echo self::message_box( $response['error'] );
  			die();
		}

		// If we made it to here let's save it and load our table class
		update_option( $this->plugin_slug . 'wpfav_quickkey', $wpfav_quickkey );

		set_transient( 'wpfav_install', json_encode($response), 15 * DAY_IN_SECONDS );

		$this->print_wpfavs( $response );

		die();
	}

	
	/**
	 * Ajax function that gets the wordpress.org username and
	 * do the remote call to retrieve the favorite plugins from wordpress.org
	 *
	 * @since    1.0.1
	 */
	public function get_wp_favorite_plugins() {

		$nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'wpfav-nonce' ) )
        	die ( 'Wrong nonce!');

        // wp username
        $wpfav_wp_username = $_POST['api_key'];

        // Include plugins api
        include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$response = plugins_api( 'query_plugins', array( 'user' => $wpfav_wp_username ) );
		
		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
  			echo self::message_box( $error_string );
  			die();
		}

		// Check that we have plugins on it
		if ( empty( $response->plugins ) ) {
			$error_string = __( "No favorites plugins found on Wordpress.org for the given username", $this->plugin_slug );
  			echo self::message_box( $error_string );
  			die();
		}
		$i=0;
		// prepare plugins array
		foreach( $response->plugins as $plugin ) {
			$i++;
			$temp_a = array(
				'id' 			=> $i,
				'name' 		    => $plugin->name,
		        'slug' 			=> $plugin->slug,
		        'link' 			=> 'http://wpfavs.com/plugin/' . $plugin->slug .'/',
		        'download_link' => 'https://downloads.wordpress.org/plugin/' . $plugin->slug . '.' . $plugin->version . '.zip',
		        'last_updated' 	=> 'unknown',
		        'version' 		=> $plugin->version
				);
			$plugins[$i] = $temp_a;
			
		}
		$response = apply_filters( 'wpfav_wp_user_response',
			array(
				7331 => array(
							'name' 		=> __( 'Wordpress.org Favorites', $this->plugin_slug ),
							'id' 			=> 7331,
							'description' 	=> __( sprintf('%s\'s favorites plugins in Wordpress.org', $wpfav_wp_username ), $this->plugin_slug ),
							'link' 			=> 'https://profiles.wordpress.org/' . $wpfav_wp_username,
							'plugins' 		=> $plugins,
						) 
			) 
		);

		// If we made it to here let's save the user and load our table class
		update_option( $this->plugin_slug . 'wpfav_wpuser', $wpfav_wp_username );

		//Save to the db
		set_transient( 'wpfav_install', json_encode($response), 3 * DAY_IN_SECONDS );

		$this->print_wpfavs( $response );

		die();
	}

	/**
	 * Prints a wp table with all the wpfavs
	 * @return void           prints the wp table
	 */
	public function print_wpfavs ( $wpfavs ) {

		$type = 'wpfav_apikey_response';
		// check if we are retrieving single wpfav or users favorites
		if( !isset($wpfavs[0] ) && !isset($wpfavs[7331] )){
			$wpfavs = [$wpfavs];
			$type = 'wpfav_token_key_response';
		}
		if( isset($wpfavs[7331] ) ){
			$type = 'wpfav_wp_response';
		}
		if( $wpfavs ){
			foreach ($wpfavs as $wpfav) {
				if( $wpfav['id'] == '7331')
					$url = $wpfav['link'].'#content-favorites';
				else
					$url = "https://wpfavs.com/wpfavs/" . $wpfav['slug'];
				?>
				<div class="wpfav-box postbox ">
					<div class="title">
						<h3 class=""><a href="<?= $url;?>" title="<?= _e('View it on Wpfavs.com','wpfavs');?>" target="_blank">
							<span><?= $wpfav['name'];?></span>
						</a> - <a href="<?= admin_url('tools.php?page=wpfavs-install&wpfav_id='.$wpfav['id']).'&type='.$type;?>" title="<?= _e('Run this list','wpfavs');?>" class="button-primary">
							Run this list
						</a></h3>
						<div class="inside">
							<?php
							echo '<p>'.$wpfav['description'].'</p>';
							if(!empty($wpfav['plugins'])){
								foreach ($wpfav['plugins'] as $plugin) {
									echo '<span class="plugin-name">'.$plugin['name'].'</span>';
								}
							}
							?>
						</div>
					</div>
				</div>
				<?php
			}
		}

	}

	/**
	 * Prints a wp table with all the plugins of the wpfav list
	 * @param  array $columns columns that we are going to display
	 * @param  array $items   items that we are going to display
	 * @return void           prints the wp table
	 */
	public static function print_plugins_table ( $items ) {

		require_once( 'includes/class-plugins-table.php');

		$myList = new Wpfavs_Plugins_Table( array('screen' => 'wpfavs' ) );

		$myList->set_items( $items );
		$myList->prepare_items();
		$myList->display(); 
	}

	/**
	 * Print wordpress boxes
	 * @param  string $type the type of box to display
	 * @param  string $text Text to be display in the box
	 * @return string       the box
	 */
	public static function message_box ( $text, $type = 'error' ) {
		return '<div id="message" class="' . $type . '"><p>' . $text . '</p></div>';
	}

	/**
	 * THANKS TO Thomas Griffin (thomasgriffinmedia.com) 
	 * from https://github.com/thomasgriffin/TGM-Plugin-Activation 
	 * for the following two functions
	 */
	
     /**
     * Set file_path key for each installed plugin.
     * @param array Api or Wp aki response that contains plugins on the list
     * @since 1.0.0
     */
    protected function populate_file_path( &$response ) {

        // Add file_path key for all plugins.
        foreach ( $response as $key => $wpfav ) {
        	if( !empty( $wpfav['plugins'] ) ) {
        		
	        	foreach ( $wpfav['plugins'] as $p_key => $plugin ) {

	            	$file_path = $this->_get_plugin_basename_from_slug( $plugin['slug'] );

	            	$response[$key]['plugins'][$p_key]['file_path'] = $file_path;

	            	if( empty( $file_path ) ) {

						$response[$key]['plugins'][$p_key]['status'] = 'not-installed';

					} elseif( is_plugin_active( $file_path ) ) {

						$response[$key]['plugins'][$p_key]['status'] = 'active';

					} else {

						$response[$key]['plugins'][$p_key]['status'] = 'inactive';
					}
	        	}
        	}
        }

    }

    /**
     * Helper function to extract the file path of the plugin file from the
     * plugin slug, if the plugin is installed.
     *
     * @since 1.0.0
     *
     * @param string $slug Plugin slug (typically folder name) as provided by the developer.
     * @return string      Either file path for plugin if installed, or just the plugin slug.
     */
    protected function _get_plugin_basename_from_slug( $slug ) {
    	
    	if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}	

        $keys = array_keys( get_plugins() );

        foreach ( $keys as $key ) {
            if ( preg_match( '|^' . $slug .'/|', $key ) ) {
                return $key;
            }
        }

        return '';

    }

    /**	
     * We check that we are on the options page on our plugin
     * @return boolean True if we are in our plugin's page
     * @since 1.0.0
     */
    protected function screen_check() {


		if ( isset( $_GET['page'] ) && $this->plugin_slug == $_GET['page'] ) {
			return true;
		}	

		return false;
    }


}
