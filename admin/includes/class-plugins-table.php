<?php
/**
 * We load the Wordpress class. It's suppose to be private but I like to take Risks
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 *  Table class to handle Wpfavs
 */
class Wpfavs_Plugins_Table extends WP_List_Table {

	/**
	 * Columns used on the able
	 *
	 * @since 1.0.0
	 * @var array
	 */
	var $columns;	

	/**
	 * Plugins list with all info
	 *
	 * @since 1.0.0
	 * @var array
	 */
	var $plugins;

	/**
	 * Function to set items and avoid stric standard notice
	 * @return  void
	 */
	function set_items( $items ) {
		
		$this->plugins 			= $items;
	}

        /**
         * Returns an array of columns.
         * @return array
         */
	function get_columns(){
		$columns = array(
			'cb'        		=> '<input type="checkbox" />',
			'title' 		=> 'Plugin Name', 
			'version'		=> 'Plugin Version',
			'last_updated'		=> 'Last Updated',
			'status'		=> 'Status',
  		);
  		return $columns;
	}

	/**
	 * Prepare all the items to be displayed
	 * @return void
	 */
	function prepare_items() {
	  
		$hidden 				= array();
		$sortable 				= array();
		$columns 				= $this->get_columns();

		//handle bulk and single actions
		$this->handle_actions();

		$this->_column_headers 	= array($columns, $hidden, $sortable);
		
		$this->items 			= $this->plugins ;
	
	}

	/**
	 * Overwrite of the get_bulk_actions method
	 * @return Array of actions
	 */
	function get_bulk_actions() {
	  
	  $actions = array(
	    'install'    => 'Install',
	    'activate'   => 'Activate',
	    'deactivate' => 'Deactivate',
	    'delete' 	 => 'Delete',
	  );
	  return $actions;
	}

	/**
	 * We handle all the actions here
	 * @return void
	 */
	function handle_actions(){

			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.

			// we get current installed plugins
			$current = get_option( 'active_plugins' );

		if( 'install' === $this->current_action() ) {

			require_once( 'class-wpfavs-plugin-installer-skin.php' ); // Needed for installs.


			// Install a single plugin
			if( !isset( $_POST['plugin'] ) && isset( $_GET['plugin_id'] ) && 'install' == $_GET['action'] ) {


				// Create a new instance of Plugin_Upgrader.
	            $upgrader = new Plugin_Upgrader ( $skin = new WpFavs_Plugin_Installer_Skin( ) );

	            // Perform the action and install the plugin from wordpress.org
	            $upgrader->install(  $this->plugins[$_GET['plugin_id']]['dlink'] );

	            //We change status
	            $this->plugins[$_GET['plugin_id']]['status'] = 'inactive';

			}	

			//Install Bulk
			if( isset( $_POST['plugin'] ) ) {


				//We update status
				foreach( $this->plugins as $plugin_id => $plugin ) {

					//We check if the plugin is installed (if is not installed we cannot uninstall it)
					if( in_array( $plugin_id, $_POST['plugin'] ) && 'not-installed' == $plugin['status'] ) {
					
						// Create a new instance of Plugin_Upgrader.
	            		$upgrader = new Plugin_Upgrader ( $skin = new WpFavs_Plugin_Installer_Skin( array( 'multi' => 'true' ) ) );
	            	
						// Perform the action and install the plugin from wordpress.org
	            		$upgrader->install(  $this->plugins[$plugin_id]['dlink'] );
						
						$this->plugins[$plugin_id]['status'] = 'inactive';

					}
				}

			}	

            // Flush plugins cache so we can make sure that the installed plugins list is always up to date.
            wp_cache_flush();

		}	
		if( 'activate' === $this->current_action() ) {

			// Activate a single plugin
			if( !isset( $_POST['plugin'] ) && isset( $_GET['plugin_id'] ) && 'activate' == $_GET['action'] ) {

				activate_plugins( $this->plugins[$_GET['plugin_id']]['file_path'] );
	            
	            //We change status
	            $this->plugins[$_GET['plugin_id']]['status'] = 'active';
				
			}

			//Activate Bulk
			if( isset( $_POST['plugin'] ) ) {

				activate_plugins( $_POST['plugin'] );

				//We update status
				foreach( $this->plugins as $plugin_id => $plugin ) {
					//We check if the plugin is inactive (if is not installed we cannot activate it)
					if( in_array( $plugin['file_path'], $_POST['plugin'] ) && 'inactive' == $plugin['status'] ) {

						$this->plugins[$plugin_id]['status'] = 'active';

					}
				}
			}
         

            // Flush plugins cache so we can make sure that the installed plugins list is always up to date.
            wp_cache_flush();

		}			
		if( 'deactivate' === $this->current_action() ) {

			// Deactivate a single plugin
			if( !isset( $_POST['plugin'] ) && isset( $_GET['plugin_id'] ) && 'deactivate' == $_GET['action'] ) {

            	deactivate_plugins( $this->plugins[$_GET['plugin_id']]['file_path'] );
	            
	            //We change status
	            $this->plugins[$_GET['plugin_id']]['status'] = 'inactive';

            }	

            //Deactivate Bulk
			if( isset( $_POST['plugin'] ) ) {

				$this->deactive_bulk( $_POST['plugin'] );
			
			}

            // Flush plugins cache so we can make sure that the installed plugins list is always up to date.
            wp_cache_flush();

		}		

		if( 'delete' === $this->current_action() ) {


			// Delete a single plugin
			if( !isset( $_POST['plugin'] ) && isset( $_GET['plugin_id'] ) && 'delete' == $_GET['action'] ) {

				delete_plugins( array($this->plugins[$_GET['plugin_id']]['file_path'] ) );

         	   	//We change status
          		$this->plugins[$_GET['plugin_id']]['status'] = 'not-installed';

            }	

            // Delete Bulk
			if( isset( $_POST['plugin'] ) ) {

				$this->deactive_bulk( $_POST['plugin'] );
				@delete_plugins( $_POST['plugin'] );

				//We update status
				foreach( $this->plugins as $plugin_id => $plugin ) {
					//We check if the plugin is inactive (if is not inactive we cannot uninstall it)
					if( in_array( $plugin['file_path'], $_POST['plugin'] ) && 'inactive' == $plugin['status'] ) {

						$this->plugins[$plugin_id]['status'] = 'not-installed';

					}
				}
			}

            // Flush plugins cache so we can make sure that the installed plugins list is always up to date.
            wp_cache_flush();

		}	
	}

	/**
	 * Fucntion to deativate plugins in bulk. Used in deactivate action and delete action
	 * @param  array $plugins Array of plugin passed in posts var
	 * @return void
	 */
	private function deactive_bulk( $plugins ) {

		deactivate_plugins( $plugins );

		//We update status
		foreach( $this->plugins as $plugin_id => $plugin ) {
			//We check if the plugin is active (if is not installed we cannot deactivate it)
			if( in_array( $plugin['file_path'], $plugins ) && 'active' == $plugin['status'] ) {

				$this->plugins[$plugin_id]['status'] = 'inactive';

			}
		}	
	}

	/**
	 * Default behaviour for columns
	 * @param  array $item        item array
	 * @param  string $column_name column name string
	 * @return string              return the item name
	 */
	function column_default( $item, $column_name ) {
		
		switch( $column_name ) { 
		    case 'title':
		    case 'description':
		    case 'link':		    
		    case 'version':
		    case 'last_updated':
		      return $item[ $column_name ];
		    default:
		      return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		  }
	}
	/**
	 * Checkbox column
	 * @param  array $item        item array
	 * @return string              return the item name
	 */
	function column_cb( $item ) {

		
		$cb = ! empty( $item['file_path'] ) ? $item['file_path'] : $item['id'];
		
        return sprintf(
            '<input type="checkbox" name="plugin[]" value="%s" />', $cb
        );    
    }

	/**
	 * Show actions to do on the list
	 * @param  array $item The item displayed
	 * @return array       Array of actions to perform
	 */
	function column_title( $item ) {
		
		$wpfav = Wpfavs_Admin::get_instance();

		$actions = array(
            'view'    => sprintf('<a href="%s" target="_blank">View Plugin Page</a>',$item['link']),           
        );

        if( $item['status'] == 'not-installed') {
        	
        	$actions['install'] 	= '<a href="' . wp_nonce_url( admin_url( sprintf('tools.php?page=%s&action=%s&plugin_id=%d&wpfav=%d', $wpfav->plugin_slug, 'install', $item['id'], $_GET['wpfav'] ) ) ) . '">Install</a>';

        } elseif ( $item['status'] == 'inactive' ) {

        	$actions['activate'] 	= '<a href="' . wp_nonce_url( admin_url( sprintf('tools.php?page=%s&action=%s&plugin_id=%d&wpfav=%d', $wpfav->plugin_slug, 'activate', $item['id'], $_GET['wpfav'] ) ) ) . '">Activate</a>';
        	$actions['delete'] 		= '<span class="delete"><a href="' . wp_nonce_url( admin_url( sprintf('tools.php?page=%s&action=%s&plugin_id=%d&wpfav=%d', $wpfav->plugin_slug, 'delete', $item['id'], $_GET['wpfav'] ) ) ) . '">Delete</a></span>';
        
        } elseif ( $item['status'] == 'active' ) {	

        	$actions['deactivate'] 	= '<a href="' . wp_nonce_url( admin_url( sprintf('tools.php?page=%s&action=%s&plugin_id=%d&wpfav=%d', $wpfav->plugin_slug, 'deactivate', $item['id'], $_GET['wpfav'] ) ) ) . '" >Deactivate</a>';
        
        }	

  		return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions) );
	}

	function column_status( $item ) {

		$wpfav = Wpfavs_Admin::get_instance();

		switch ( $item['status'] ) {
			
			case 'not-installed':
				return '<span class="not-installed">' . __( "Not Installed", $wpfav->plugin_slug ) . '</span>';
				break;			

			case 'active':
				return '<span class="active">' . __( "Active", $wpfav->plugin_slug ) . '</span>';
				break;

			case 'inactive':
				return '<span class="inactive">' . __( "Inactive", $wpfav->plugin_slug ) . '</span>';
				break;
			
			default:
				return '<span class="not-installed">' . __( "Not Installed", $wpfav->plugin_slug ) . '</span>';
				break;
		}
	}
}

