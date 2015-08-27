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
class Wpfavs_Table extends WP_List_Table {

	/**
	 * Columns used on the able
	 *
	 * @since 1.0.0
	 * @var array
	 */
	var $columns;

	/**
	 * Function to set items and avoid stric standard notice
	 * @return  void
	 */
	function set_items( $items ) {
		
		$this->items 			= $items;
	}

	/**
	 * Returns an array of columns.
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'title' 	=> 'Title',
			'description' 	=> 'Description',
			'plugins' 	=> 'Plugins in List',
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
		$this->_column_headers 	= array($columns, $hidden, $sortable);
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
		      return $item[ $column_name ];
		    default:
		      return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		  }
	}

	/**
	 * function that will display all plugins on the wpfav list
	 * @param  array $item        item array
	 * @param  string $column_name column name string
	 * @return string              return the item name
	 */
	function column_plugins( $item ) {

		if( !empty( $item['plugins'] ) ) {

			foreach( $item['plugins'] as $p ) {
				echo '<a href="' . $p['link'] . '" target="_blank">'. $p['title'] .'</a><br />';
			}

		} else {
			 _e( 'This list has not plugins yet', $this->plugin_slug );
		}

	}
	/**
	 * Show actions to do on the list
	 * @param  array $item The item displayed
	 * @return array       Array of actions to perform
	 */
	function column_title( $item ) {
		
		$wpfav = Wpfavs_Admin::get_instance();

		$actions = array(
            'edit'    => sprintf('<a href="' . $wpfav->api_url . 'my-wpfavs/edit/%s/" target="_blank">Edit Wp Fav</a>',$item['id']),
            'run' => sprintf('<a href="?page=%s&action=%s&wpfav=%s">Run this list</a>',$wpfav->plugin_slug,'run-wpfav',$item['id']),
        );
        
        //If we are importing wp user favorites remove edit from actions
		if( 7331 === $item['id'] )
			unset( $actions['edit'] );

  		return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions) );
	}

	function usort_reorder( $a, $b ) {
 		// Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
	}
}