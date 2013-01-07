<?php
/**
 * Get all post types and exclude blocks
 * @return array of post types
 */

function blocks_post_types() {
	$post_types = get_post_types( array( 'show_ui' => true ), 'names' );

	$key = array_search( 'blocks', $post_types );

	if ( $key !== false ) {
		unset( $post_types[$key] );
	}
	return $post_types;
}


/**
 * Delete blocks transients
 * On edit by blocks or areas
 * Delete all on edit or new block
 * @since 0.1
 */

function blocks_flush_transients() {
	global $wpdb;

	$post_id = isset( $_POST['post_id'] );
	// Get all the settings from settingspage
	$settings = get_option( 'blocks_settings' );

	if( $settings['blocks_cache'] ) {
		delete_transient( 'blocks_cache_' . $post_id );

		if( isset( $_POST['post_type'] ) == 'blocks' || isset( $_POST['action'] ) == 'empty_cache' ) {
			$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE ("%_blocks_cache_%")' );
		} 
	}
}
add_action('save_post','blocks_flush_transients');
add_action('wp_ajax_blocks_save', 'blocks_flush_transients');
add_action('wp_ajax_empty_cache', 'blocks_flush_transients');


/**
 * Save function for post and pages
 * Saves the Blocks in Right area on AJAX-call
 * @since 0.1
 */

function blocks_save_metadata() {

	$post_id = $_POST['post_id'];

	// check if user has the rights
	if ( ! current_user_can('edit_page', $post_id ) ) {
		die( __("You don't have premission to edit or create blocks", 'blocks') );
	}

	// set post ID if is a revision
	if ( wp_is_post_revision( $post_id ) ) {
	    $post_id = wp_is_post_revision( $post_id );
	}

	if( isset( $post_id ) ) {

		$data 	  = get_post_meta( $post_id, '_blocks', true );
	    $area 	  = $_POST['area'];
	    $order    = trim( $_POST['order'] ); // we check on empty array but explode always return one item
	    $blocks   = explode( ',', $order );

		if( ! empty( $order ) ) {
			$data[$area] = array_unique( $blocks );
		}
		else {
			unset( $data[$area] );
		}

		foreach( $data as $key => $value ) {
    		if( empty( $value ) || ( is_array( $value ) && count( $value ) == 0 ) ) {
       			unset( $data[$key] );
    		}
		}

		if( count( $data ) > 0 ) {
			update_post_meta( $post_id,'_blocks', $data );
		}
		else {
			delete_post_meta( $post_id,'_blocks' );
		}
	
	    die;
	}
}
add_action('wp_ajax_save_blocks', 'blocks_save_metadata');


/**
 * Save metadata for blocks as an Array
 * @since 0.1
 * @param string $post_id
 */
     
function blocks_save_blocks_meta( $post_id ) {

	// check if user has the rights
	if ( isset( $_POST['post_type'] ) == 'blocks' && ! current_user_can('edit_page', $post_id ) ) {
		die( __("You don't have premission to edit or create blocks", 'blocks') );
	}

	// set post ID if is a revision
	if ( wp_is_post_revision( $post_id ) ) {
	    $post_id = wp_is_post_revision( $post_id );
	}

	// Security-check
	if ( isset( $_POST['_blocks_meta'] ) && check_admin_referer('blocks_save_data','blocks_meta_data') ) {

		// Get the posted data
		$blocks_meta = $_POST['_blocks_meta'];

		foreach( $blocks_meta as $key => $value ) {
    		if( empty( $value ) ) {
       			unset( $blocks_meta[$key]);
    		}
		}
		
		if( empty( $blocks_meta ) ) {
  			delete_post_meta( $post_id,'_blocks_meta' );
		} else {
			update_post_meta( $post_id,'_blocks_meta', $blocks_meta );
		}
	}
}
add_action('save_post', 'blocks_save_blocks_meta');


/**
 * Save new block-areas
 * Must have a valid key, name and descripton
 * @since 0.1
 * @param string $area KEY of the area
 * @param string $name NAME of the area
 * @param string $desc DESCRIPTION of the area
 * @return validated areas
 */

function blocks_check_area_values( $area, $name, $desc ) {
	if ( ! empty( $area ) && ! empty( $name ) && ! empty( $desc ) ) {
		return true;
	}
	return false;
}


/**
 * Shortcode function lets you add a single block
 * With shortcode [block id="211"]
 * @since 0.1
 * @param string $atts 
 */

function blocks_shortcode( $atts ) {
	get_blocks( false , $atts['id'] );
}
add_shortcode( 'block', 'blocks_shortcode' );


/**
 * Retrieve the path of the highest priority template file that exists.
 * Parse the blocks contents to retrieve blocks's metadata.
 * @return Array of areas from Block Areas - tag.
 * @since 0.1
 */

function blocks_get_defined_areas() {
	global $post, $pagenow;

	$settings = get_option('blocks_settings');

	if( $pagenow == 'post.php' ) {

		$template = get_post_meta( $post->ID, '_wp_page_template', true );

		// Check page-templates, if not set -> page.php
		if( $post->post_type == 'page' && ( empty( $template ) || 'default' == $template ) ) {
			$template = 'page.php';
		}
		elseif( empty( $template ) ) {
			// Check single-templates, single-{post_type}.php -> single.php order
			if( file_exists( TEMPLATEPATH .'/single-'. $post->post_type .'.php' ) ) {
				$template = 'single-'. $post->post_type .'.php';
			}
			elseif( ( $post->post_type == 'attachment' || $post->post_type == 'page' ) && file_exists( TEMPLATEPATH .'/'. $post->post_type .'.php' ) ) {
				$template = $post->post_type .'.php';
			}
			elseif( $post->post_type == 'attachment' ) {
				$template = '';
			}
			else {
				$template = 'single.php';
			}
		}

		blocks_find_areas( array( 'area' => 'Block Areas' ), $template );
	}
}
add_action( 'admin_head', 'blocks_get_defined_areas' );


/** 
 * Simple function to find areas in templates
 * @since 0.1
 * @param string $template Path to the file
 * @param array $find List of headers, in the format array('area' => 'Block Areas')
 * @return defined areas
*/

function blocks_find_areas( $find, $template ) {
	global $defined_areas;
	
	$defined_areas = get_file_data( TEMPLATEPATH .'/' . $template, $find );

	// have to look this over
	$defined_areas = array_filter( array_map( 'trim', explode( ',', strtolower( $defined_areas['area'] ) ) ) );

	return $defined_areas;
}


/** 
 * Let's create a post_list
 * @since 0.1
 * @param string $post_id post id of the parent
 * @param string $output 
 * @param array $children all children_ids 
 * @param array $area all available areas 
 * @return post_list
*/

function blocks_create_child_tree( $post_id, &$output, $children ) {
	if( isset( $children[$post_id] ) ) {
		
		if( $post_id != 0 ) {
			$output .= '<ul class="children">';
		}

		foreach ( $children[$post_id] as $child ) {
			if( isset( $children[$child->ID] ) ) {
				$output .= '<li class="parent" data-id="'. $child->ID .'">
					<span class="block-parent"></span></span>
						<p>'. get_the_title( $child->ID ) .'</p><span>'. __('Parent', 'blocks') .'
					</span>
				';
			} else {
				$output .= '<li data-id="'. $child->ID .'"><p>'. get_the_title( $child->ID ) .'</p><span>'. $child->post_type .'</span>';
			}

			$output .= blocks_create_child_tree( $child->ID, $output, $children );

			$output .= '</li>';
		}
		
		if( $post_id != 0 ) {
			$output .= '</ul>';
		}
	}
}


/** 
 * Remove blocks data from db
 * When uninstalling
 * @since 0.1
*/

function blocks_uninstall_settings() {

	global $wpdb;

	if( ! defined('WP_UNINSTALL_PLUGIN') )  {
		exit();
	} 

	// Delete all data
	delete_option('blocks_settings');

	// Delete postmeta contains _block
	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "_blocks" ' );

	// Delete transient from _options
	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE ("%_blocks_cache_%") ' );

	// Delete post_type block from _posts
	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . 'posts WHERE post_type LIKE ("%_blocks%") ' );
}