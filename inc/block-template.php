<?php

/**
 * This is the default block-template
 * If you want to override the output
 * you can add a file called 
 * blocks-{templatename}.php to your
 * theme-root and in that file add:
 *
 * Block Template: My block template
 *
 * @since 0.1
 * @param string $area, get the block in this area
 * @param string $id, for shortcode to get a single block
 * @return the block layout and content
 */

function get_blocks( $area, $id = null ) {
	global $post;

	// Get the blocks id:s
	$blocks_id = get_post_meta( $post->ID, '_blocks', true );

	// Get all the settings
	$settings = get_option( 'blocks' );

	if ( false === ( $query = get_transient( 'blocks_cache_'. $post->ID ) ) ) {

		if( isset( $area ) && ! isset( $id ) && ! empty( $blocks_id ) ) {
			$query = new WP_Query( 
        		array(
	    			'post_type' 	 => 'blocks',
	    			'posts_per_page' => '-1',
	    			'orderby'		 => 'post__in',
					'post__in'		 => $blocks_id[$area]
	    		)
	    	);
    	} else {
    		$query = new WP_Query( 
	    		array(
	    			'post_type' 	 => 'blocks',
	    			'posts_per_page' => '1',
					'p'		 		 => $id
	    		)
	    	);
    	}
    }

	if( $settings['cache'] ) {
		set_transient('cache_'. $post->ID, $query);
	}

	$i = 1;

	$output = '';

	while ( $query->have_posts() ) : $query->the_post();
		$block_id 	  = get_the_id();
		$blocks_meta  = get_post_meta( $post->ID, '_blocks_meta', true );
		
		if( isset( $blocks_meta['template'] ) ) {
			include( TEMPLATEPATH . '/'. $blocks_meta['template'] );
		} else {

			// Hook into blocks_template if you want to modify the template output
			do_action('blocks_template');

			$output .= '<div class="block block-'. $i++ .''. ( ! empty( $settings['class'] ) ? $settings['class'] : '' ) .''. ( ! empty( $blocks_meta['class'] ) ? $blocks_meta['class'] : '' ) .'">';
				
				$output .= '<div class="block-holder">';

					if( has_post_thumbnail( $block_id ) ) {
						$output .= '<div class="block-img">';
							$output .= get_the_post_thumbnail( $block_id, 'medium' );
						$output .= '</div>';
					}

					if( ! empty( $blocks_meta['link'] ) ) {
						$output .= '<a href="'. $blocks_meta['link'] .'" title="'. esc_attr( get_the_title( $post->ID ) ) .'">';
						$output .= get_the_title();
						$output .= '</a>';
					} else {
						$output .= '<div class="block-title">';
							$output .= get_the_title();
						$output .= '</div>';
					}

					$output .= '<div class="block-content">';
						$output .= apply_filters( 'the_content', get_the_content() );
					$output .= '</div>';

				$output .= '</div>';

				if( is_user_logged_in() && isset( $settings['edit'] ) ) {
					$output .= '<a href="'. get_edit_post_link( $post->ID ) .'" class="block-edit-link" title="'. __('Edit block', 'blocks') .'">'. __( 'Edit', 'blocks' ) .'</a>';
				}

			$output .= '</div>';
		}
	endwhile;

	wp_reset_postdata();

	echo $output;
}
