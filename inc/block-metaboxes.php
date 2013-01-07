<?php

/**
 * Register the metaboxes
 * @since 0.1
 * @return Block metaboxes
 */

function blocks_metadata() {
	add_meta_box( 'blocks_single_settings', __('Block Settings', 'blocks'), 'blocks_single_settings', 'blocks', 'side', 'low' );
	add_meta_box( 'blocks_single_pages', __('Add block', 'blocks'), 'blocks_single_pages', 'blocks', 'normal' );
}
add_action('add_meta_boxes', 'blocks_metadata');


/**
 * Register the metaboxes
 * @since 0.1
 * @return list of all pages and areas
 */

function blocks_single_pages() {
	global $wpdb ,$post, $defined_areas;

	$settings   = get_option('blocks_settings');
	$areas      = $settings['blocks_area'];
	$types 	    = blocks_post_types();
	$type_areas = array();
	$postobject = $post; // Make a clone of the Post-Object

	unset( $types['page'] );

	foreach ( $types as $type ) {
		$template = '';
	
		// Check single-templates, single-{post_type}.php -> single.php order
		if( file_exists( TEMPLATEPATH .'/single-'. $type .'.php' ) ) {
			$template = 'single-'. $type .'.php';
		}
		elseif( ( $type != 'post' && ( $type == 'attachment' || $type == 'page' ) ) && file_exists( TEMPLATEPATH .'/'. $type .'.php' ) ) {
			$template = $type .'.php';
		}
		else {
			$template = 'single.php';
		}

		blocks_find_areas( array( 'area' => 'Block Areas' ), $template );

		foreach ( $defined_areas as $defined_area ) {
			$type_areas[$defined_area][] = $type;
		}
	}

	// Return the available page templates
	$templates            = get_page_templates();
	$templates['default'] = 'page.php'; // Add default-page
	$template_areas       = array();
	$templates_with_areas = array();

	foreach ( array_keys( $templates ) as $template ) {

		blocks_find_areas( array( 'area' => 'Block Areas' ), $templates[$template] );

		foreach ( $defined_areas as $defined_area ) {
			if( $template == 'default' ) {
				// If it's the default page, add "default" instead of template-filename
				$template_areas[$defined_area][] = 'default';
				$templates_with_areas[] = 'default';
			}
			else {
				$template_areas[$defined_area][] = $templates[$template];
				$templates_with_areas[] = $templates[$template];
			}
		}
	}

	// Remove duplicates
	$templates_with_areas = array_unique($templates_with_areas);

	echo "<pre>";
	$blockPages = array();
		
	// Get all post_id:s that have this post_id(block_id) and area
	// Remove when WP_Query adds support for REGEX in meta_query
	// http://core.trac.wordpress.org/ticket/18736
	// $sql = 'SELECT post_id FROM kwido_postmeta WHERE meta_value REGEXP \'"'. $area['area'] .'";a:[[:digit:]]+:{[^}]*"'. $postobject->ID .'"\'';

	// // Get the SQL-result
	// $results = $wpdb->get_results( $sql, ARRAY_A );

	// $post_ids = array();

	// foreach ( $results as $result ) {
	// 	$post_ids[] = $result['post_id'];
	// }

	$args = array(
		'post_type'	   => $types,
		'numberposts'  => '-1'//,
		//'post__not_in' => $post_ids
	);
	
	$blockPages = get_posts( $args );


	$args = array(
	   'post_type'    => 'page',
	   'meta_key'     => '_wp_page_template',
	   //'post__not_in' => $post_ids,
	   'meta_query'   => array(
	       array(
	           'value' => $templates_with_areas,
	       )
	   	)
	 );

	$query = new WP_Query( $args );

	// Merge page with custom post types
	$blockPages = array_merge( $blockPages, $query->posts );

	// Sort array by key in reverse order
	krsort( $blockPages);

	$children = array();
	foreach ( $blockPages as $post ) {
	    $children[$post->post_parent][] = $post;
	}

	echo "</pre>";
	
	//$area_exists = array();

	// foreach ( $areas as $area ) {
	// 	$area_exists[$area['area']] = ( ( isset($type_areas[$area['area']] ) && count( $type_areas[$area['area']] ) > 0 ) || ( isset($template_areas[$area['area']] ) && count( $template_areas[$area['area']] ) > 0 ) );
	// }

	if( count($blockPages) > 0 ) {

		$output = '<div id="blocks-area-control">';

			$output .= '<div class="blocks-tabs">';

				$output .= '<div class="posts-holder">';

					$output .= '<div class="search-head">';
						$output .= '<input class="block-pages-search" type="text" placeholder="' . __('Search page', 'blocks') . '...">';
					$output .= '</div>';

					$output .= '<ul class="list list-pages" data-action="update">';

						#blocks_create_child_tree( 0, $output, $children );

						$output .= '<li class="parent" data-id="1" data-area="left"><span title="Show children" class="block-parent"></span></span><p>Page parent</p><span title="Add this page" class="add"></span><span title="Add on areas" class="add-areas">Add on areas</span><span title="Remove" class="delete"></span>';
							$output .= '<ul class="children">';
								$output .= '<li data-id="2" data-area="left"></span><p>Page child</p><span title="Add this page" class="add"></span><span class="add-areas">Add on areas</span><span title="Remove" class="delete"></span></li>';
								$output .= '<li data-id="3" data-area="left"></span><p>Page child</p><span title="Add this page" class="add"></span><span class="add-areas">Add on areas</span><span title="Remove" class="delete"></span></li>';
								$output .= '<li data-id="4" data-area="left"></span><p>Page child</p><span title="Add this page" class="add"></span><span class="add-areas">Add on areas</span><span title="Remove" class="delete"></span></li>';
								$output .= '<li data-id="5" data-area="left"></span><p>Page child</p><span title="Add this page" class="add"></span><span class="add-areas">Add on areas</span><span title="Remove" class="delete"></span></li>';
								$output .= '<li data-id="6" data-area="left"></span><p>Page child</p><span title="Add this page" class="add"></span><span class="add-areas">Add on areas</span><span title="Remove" class="delete"></span></li>';
								$output .= '<li data-id="7" data-area="left"></span><p>Page child</p><span title="Add this page" class="add"></span><span class="add-areas">Add on areas</span><span title="Remove" class="delete"></span></li>';
							$output .= '</ul>';

							$output .= '<ul class="areas">';
								$output .= '<span></span>';
								$output .= '<li title="Add on Left column"><span class="saved"></span>Left column</li>';
								$output .= '<li title="Add on Right column"><span></span>Right column</li>';
								$output .= '<li title="Add on Header"><span></span>Header</li>';
							$output .= '</ul>';
							
						$output .= '</li>';

						$output .= '<li data-id="1" data-area="left"></span><p>News 1</p><span title="Add this post" class="add"></span><span class="delete"></span><span title="Add on areas" class="add-areas">Add on areas</span><span title="Remove" class="delete"></span>';
							$output .= '<ul class="areas">';
								$output .= '<span></span>';
								$output .= '<li title="Add on Left column"><span class="saved"></span>Left column</li>';
								$output .= '<li title="Add on Right column"><span></span>Right column</li>';
								$output .= '<li title="Add on Header"><span></span>Header</li>';
							$output .= '</ul>';
						$output .= '</li>';

						$output .= '<li data-id="1" data-area="right"></span><p>News 2</p><span title="Add this post" class="add"></span><span title="Add on areas" class="add-areas">Add on areas</span><span title="Remove" class="delete"></span>';
							$output .= '<ul class="areas">';
								$output .= '<span></span>';
								$output .= '<li title="Add on Left column"><span class="saved"></span>Left column</li>';
								$output .= '<li title="Add on Right column"><span></span>Right column</li>';
								$output .= '<li title="Add on Header"><span></span>Header</li>';
							$output .= '</ul>';
						$output .= '</li>';

						$output .= '<li data-id="1" data-area="right"></span><p>News 3</p><span title="Add this post" class="add"></span><span title="Add on areas" class="add-areas">Add on areas</span><span title="Remove" class="delete"></span>';
							$output .= '<ul class="areas">';
								$output .= '<span></span>';
								$output .= '<li title="Add on Left column"><span class="saved"></span>Left column</li>';
								$output .= '<li title="Add on Right column"><span></span>Right column</li>';
								$output .= '<li title="Add on Header"><span></span>Header</li>';
							$output .= '</ul>';
						$output .= '</li>';

					$output .= '</ul>';

				$output .= '</div>';


				// Saved block area
				$output .= '<div class="posts-holder">';
					$output .= '<ul class="list save-block" data-action="delete">';


					$output .= '</ul>';
				$output .= '</div>';


			$output .= '</div>';

		$output .= '</div>';
	} else {
		$output = '<div class="no-areas">';
			$output .= '<h2>'. __('You have not defined any areas in any of your theme templates, please do so') . '.</h2>';
			$output .= '<a href="edit.php?post_type=blocks&page=blocks-settings" class="button button-primary button-large">'. __('Getting started with Blocks') . '</a>';
		$output .= '</div>';
	}

	echo $output;				

}


/**
 * Add settings for single Block
 * @since 0.1
 * @return Settings for single block
 */

function blocks_single_settings() {
	global $post;

	$blocks_meta = get_post_meta( $post->ID, '_blocks_meta', true );
	
	$output = '<p class="description">' . __('Here you can add specific settings for the block', 'blocks') . '.</p>';

	/*
	 * Go through all template files for files that start with "blocks"
	 * then check if they have a template name
	 */

	$root       = STYLESHEETPATH;
	$dir 		= dir( $root ) or die( __("Couldn't open: {$root}", 'blocks') );
	$templates  = array();

	while ( ($file = $dir->read() ) !== false ) {
		if( substr( $file, 0, 5 ) == 'block' ) {
			$template_data = implode( '', file( $root . '/' . $file ) );
			$name = '';
			
			if ( preg_match( '|Block Template:(.*)$|mi', $template_data, $name ) )
				$name = _cleanup_header_comment( $name[1] );
			
			if ( ! empty( $name ) ) {
				$templates[ trim( $name ) ] = $file;
			}
		}
	}

	// Creates a select list of the blocks-templates
	if( count( $templates ) > 0 ) :
		$template = ( isset( $blocks_meta['template'] ) && ! empty( $blocks_meta['template'] ) ) ? $blocks_meta['template'] : '';

		$output .= '<p>';
			$output .= '<div>';
			
				$output .= '<select name="_blocks_meta[template]" id="blocks-template">';
					$output .= '<option value="">' . __('Default Template', 'blocks') . '</option>';
					foreach( $templates as $t_name => $t_file ): 
						$output .= '<option' . selected( $template, $t_file, false ) . ' value="' . $t_file . '">' . $t_name . '</option>';
					endforeach;
				$output .= '</select>';
			
			$output .= '</div>';
		$output .= '</p>';
	endif; 

	// Link-field
	$output .= '<p><input type="text" name="_blocks_meta[link]" class="widefat" id="blocks-link" placeholder="' . __('Add a link to the title','blocks') . '" value="' . ( ! empty( $blocks_meta['link'] ) ? $blocks_meta['link'] : '' ) . '" /></p>';

	// css-field
	$output .= '<p><input type="text" name="_blocks_meta[class]" class="widefat" id="blocks-class" placeholder="' . __('Add Block-specific class','blocks') . '"" value="' . ( ! empty( $blocks_meta['class'] ) ? $blocks_meta['class'] : '' ) . '" /></p>';

	wp_nonce_field('blocks_save_data','blocks_meta_data'); 

	echo $output;
}