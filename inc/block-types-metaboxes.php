<?php

/**
 * Register metaboxes
 * On wanted pages 
 * @since 0.1
 * @return The Block metabox
 */

function blocks_register_metaboxes() {
		
	$types = blocks_post_types();

	if( $types ) {
		foreach( $types as $type ) {
			add_meta_box( 'blocks_save', __('Your Areas', 'blocks'), 'blocks_save_blocks', $type, 'normal', 'high' );
		}
	}
}
add_action('add_meta_boxes', 'blocks_register_metaboxes');


/**
 * List all the blocks
 * @since 0.1
 * @param string $post_id
 * @return the Block-saving-areas and available Blocks 
 */

function blocks_save_blocks( $post_id ) {
	global $defined_areas;

	$settings = get_option('blocks');

	$areas = $settings['area'];

	// Get the Blocks id:s
	$blocks_id = get_post_meta( $post_id->ID, '_blocks', true );

	// Hook into blocks_types_metabox
	// If you want to modify the output of metaboxes
	do_action('blocks_types_metabox');

	if( $defined_areas ) {
		$output = '<div class="header">';
			$output .= '<span>'. __('Blocks', 'blocks'). '</span>';
			$output .='<input class="search" placeholder="' . __('Search blocks','blocks') . '.." />';
		$output .= '</div>';

		$args = array(
			'post_type'		 =>	'blocks',
			'post_status'	 => 'publish', // emh, this should be the default value
			'posts_per_page' => '-1'	
		);

		$query = new WP_Query( $args );

		$output .='<div class="inner-list"><ul class="list">';

			while ( $query->have_posts() ) : $query->the_post();

				$block_id = get_the_id();
				$excerpt  = get_the_excerpt();

				$output .= '<li data-id="' . $block_id . '" class="block"><span title="'. __('Remove Block', 'block') .'" class="remove-block">x</span>';
					$output .= '<div class="block-title">' . ( has_post_thumbnail() || ! empty( $excerpt ) ? '<div class="sidebar-name-arrow"><br></div>' : '') .'';
						$output .= get_the_title();
					$output .= '</div>';

					if( has_post_thumbnail() ) {
						$output .= get_the_post_thumbnail( $block_id, 'medium' );
					}	

					if( ! empty( $excerpt ) ) {
						$output .= '<div class="block-info">';

							$output .= '<div class="block-excerpt">';

							 $output .= '<p>' . $excerpt . '</p>';

							$output .= '</div>';
					
						$output .= '</div>';
					}

				$output .= '</li>';

			endwhile;

			wp_reset_postdata();

		$output .= '</ul></div>';	

		$output .= '<div class="paging-holder"><ul class="paging"></ul></div>';

		$output .= '<div class="blocks-wrap">';

			foreach ( $areas as $area ) {

				if( in_array( $area['area'], $defined_areas ) ) {
					$output .= '<div class="blocks-holder">';

						$output .= '<div class="blocks-title">';
							$output .= '<div class="blocks-inner">';
								$output .= '<h2>'. $area['name'] .'</h2>';
								$output .= '<p class="description">' . $area['desc'] . '</p>';
							$output .= '</div>';
						$output .= '</div>';
						
						$output .= '<ul class="blocks-area blocks-' . $area['area'] . '" data-area="' . $area['area'] . '">';

							if( ! empty( $blocks_id[ $area['area'] ] ) ) {
								$args = array(
									'post_type'		 =>	'blocks',
									'posts_per_page' => '-1',
									'orderby'		 => 'post__in',
									'post__in'		 => $blocks_id[ $area['area'] ]
								);

								$query = new WP_Query( $args );

								while ( $query->have_posts() ) : $query->the_post();

									$block_id = $query->post->ID;

									$excerpt = get_the_excerpt();

									$output .= '<li data-id="' . $block_id . '" class="block"><span title="'. __('Remove Block', 'block') .'" class="remove-block">x</span>';
										$output .= '<div class="block-title">';
											$output .= get_the_title();
										$output .= '</div>';

										if( ! empty( $excerpt ) ) {
											$output .= '<div class="block-info">';

												$output .= '<div class="block-excerpt">';

												 $output .= '<p>' . $excerpt . '</p>';

												$output .= '</div>';
										
											$output .= '</div>';
										}

									$output .= '</li>';

								endwhile;

								wp_reset_postdata();
							}

						$output .= '</ul>';
					$output .= '</div>';
				}
			}	

		echo $output;
	}
}
