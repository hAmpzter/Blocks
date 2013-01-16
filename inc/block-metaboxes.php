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
	global $wpdb ,$post;

	$settings   = get_option('blocks');
	$areas      = $settings['areas'];
	$types 	    = blocks_post_types();
	$type_areas = array();
	$area_types = array();
	$postobject = $post; // Make a clone of the Post-Object

	unset( $types['page'] );

	foreach ( $types as $type ) {
		$template = blocks_get_template_by_type( $type );

		$defined_areas = blocks_find_areas( array( 'area' => 'Block Areas' ), $template );

		foreach ( $defined_areas as $defined_area ) {
			$type_areas[$defined_area][] = $type;
			$area_types[$type][] = $defined_area;
		}
	}

	// Return the available page templates
	$pageTemplates            = get_page_templates();
	$pageTemplates['default'] = 'page.php'; // Add default-page
	$template_areas       = array();
	$pageTemplatesWithAreas = array();

	foreach ( array_keys( $pageTemplates ) as $template ) {

		$defined_areas = blocks_find_areas( array( 'area' => 'Block Areas' ), $pageTemplates[$template] );

		foreach ( $defined_areas as $defined_area ) {
			if( $template == 'default' ) {
				// If it's the default page, add "default" instead of template-filename
				$template_areas[$defined_area][] = 'default';
				$pageTemplatesWithAreas['default'][] = $defined_area;
			}
			else {
				$template_areas[$defined_area][] = $pageTemplates[$template];
				$pageTemplatesWithAreas[$pageTemplates[$template]][] = $defined_area;
			}
		}
	}

	$templates['page'] = $pageTemplatesWithAreas;
	$templates['post_type'] = $area_types;

	// Remove duplicates
	$pageTemplatesWithAreas = array_unique($pageTemplatesWithAreas);

	$postsWithoutAreas = array();
		
	$args = array(
		'post_type'	   => $types,
		'numberposts'  => '-1'
	);
	
	$postsWithoutAreas = get_posts( $args );

	$args = array(
	   'post_type'    => 'page',
	   'meta_key'     => '_wp_page_template',
	   'meta_query'   => array(
	       array(
	           'value' => array_keys( $pageTemplatesWithAreas )
	       )
	   	)
	 );

	$templatePages = new WP_Query( $args );

	// Merge page with custom post types
	$postsWithoutAreas = array_merge( $postsWithoutAreas, $templatePages->posts );

	// Variable not needed any more
	unset( $templatePages );

	// Sort array by key in reverse order to get latest first
	krsort( $postsWithoutAreas );
	
	$postsWithAreas = array();

	foreach ($areas as $areaKey => $area) {
		//TODO: Get correct prefix
		$sql = 'SELECT post_id FROM kwido_postmeta WHERE meta_value REGEXP \'"'. $areaKey .'";a:[[:digit:]]+:{[^}]*"[[:digit:]]"\'';

		// Get the SQL-result
		$items = $wpdb->get_results( $sql, ARRAY_A );

		foreach ($items as $item)
		{
			// Future fix so that pages that are already saved with blocks information may be removed
			// if the template is changed without one or two block areas.

			// Move the page to added posts
			if(!array_key_exists($item["post_id"], $postsWithAreas))
			{
				foreach ($postsWithoutAreas as $blockPageKey => $blockPage)
				{
					if($blockPage->ID == $item["post_id"])
					{
						$postsWithAreas[$item["post_id"]] = array('post' => $blockPage);
						unset($postsWithoutAreas[$blockPageKey]);
						break;
					}
				}
			}

			if(array_key_exists($item["post_id"], $postsWithAreas)) {
				$postsWithAreas[$item["post_id"]]['areas'][] = $areaKey;
			}
		}
	}

	// Disabled childrens-list with a simple hack! ;)
	$postsNotAdded[0] = $postsWithoutAreas;
	$postsAdded[0] = $postsWithAreas;

	if( count( $postsNotAdded ) > 0 || count( $postsAdded ) > 0 ) {

		$output = '<div id="blocks-area-control">';

			$output .= '<div class="blocks-tabs">';

				$output .= '<div class="posts-holder">';

					$output .= '<div class="search-head">';
						$output .= '<input class="block-pages-search" type="text" placeholder="' . __('Search page', 'blocks') . '...">';
					$output .= '</div>';

					$output .= '<ul class="list list-pages" data-action="update">';

						// Full function not used yet, but just to present a flat list
						blocks_create_child_tree( 0, $output, $postsNotAdded, $templates, $settings['areas'] );

					$output .= '</ul>';

				$output .= '</div>';


				// Saved block area
				$output .= '<div class="posts-holder">';
					$output .= '<ul class="list save-block" data-action="delete">';

						blocks_create_child_tree( 0, $output, $postsAdded, $templates, $settings['areas'] );

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
 * Let's create a post_list
 * @since 0.1
 * @param string $post_id post id of the parent
 * @param string $output 
 * @param array $children all children_ids 
 * @param array $area all available areas 
 * @return post_list
*/

function blocks_create_child_tree( $parent_id, &$output, $children, $templates, $definedAreas ) {
	if( isset( $children[$parent_id] ) ) {
		
		// Do not output children ul on root-level
		if( $parent_id != 0 ) {
			$output .= '<ul class="children">';
		}

		foreach ( $children[$parent_id] as $childTemp ) {
			$child = null;
			$savedAreas = array();

			if( is_object( $childTemp )) {
				// Normal post object
				$child = $childTemp;
			}
			elseif( is_array( $childTemp )) {
				// Array with saved information
				$child = $childTemp['post'];
				$savedAreas = $childTemp['areas'];
			}
			else {
				return;
			}

			if( isset( $children[$child->ID] ) ) {
				$output .= '<li class="parent" data-id="'. $child->ID .'">
					<span class="block-parent"></span></span>
						<p>'. get_the_title( $child->ID ) .'</p><span>'. __('Parent', 'blocks') .'
					</span>
				';
			} else {
				$output .= '<li data-id="'. $child->ID .'"></span><p>'. get_the_title( $child->ID ) .'</p><span title="'. __('Add this', '') .' '. $child->post_type .'" class="add"></span><span class="delete"></span><span title="Add on areas" class="add-areas">'. __('Add on areas', 'blocks') .'</span><span title="Remove" class="delete"></span>';
				
				$output .= '<ul class="areas">';
				$output .= '<span></span>';

				$templateAreas = array();	
				
				if($child->post_type == 'page')
				{
					$template = $template = get_post_meta( $child->ID, '_wp_page_template', true );

					if(empty($template)) {
						$template = 'default';
					}

					$templateAreas = $templates['page'][$template];
				}
				else
				{
					// Fetch the template for a post type
					$templateAreas = $templates['post_type'][$child->post_type];
				}

				foreach ($templateAreas as $templateArea) {
					$saved = '';

					if( in_array($templateArea, $savedAreas)) {
						$saved = ' class="saved"';
					}

					$output .= '<li title="Add on '.$definedAreas[$templateArea]['name'].'"><span'.$saved.'></span>'.$definedAreas[$templateArea]['name'].'</li>';
				}

				$output .= '</ul>';
			}

			$output .= blocks_create_child_tree( $child->ID, $output, $children, $templates, $definedAreas );

			$output .= '</li>';
		}
		
		if( $parent_id != 0 ) {
			$output .= '</ul>';
		}
	}
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