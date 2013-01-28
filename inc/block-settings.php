<?php
	
/**
 * Add blocks Settings page
 * @since 0.1
 * @return the settings menu
 */

function blocks_settings_page_init() {
	$settings_page = add_submenu_page( 'edit.php?post_type=blocks', __('Settings', 'blocks'), __('Settings', 'blocks'), 'manage_options', 'blocks-settings', 'blocks_settings_page' );
	add_action( "load-{$settings_page}", 'blocks_load_settings_page' );
}
add_action( 'admin_menu', 'blocks_settings_page_init' );


/**
 * Load and save the 
 * Settings page
 * @since 0.1
 */

function blocks_load_settings_page() {
	if ( isset( $_POST["blocks_save"] ) && $_POST["blocks_save"] == 'save' ) {
		check_admin_referer( "blocks-settings-page" );
		blocks_save_plugin_options();
		$url_parameters = isset( $_GET['tab'] ) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
		wp_redirect( admin_url( 'edit.php?post_type=blocks&page=blocks-settings&' . $url_parameters ) );
		exit;
	}
}


/**
 * Save the settings 
 * multi-array
 * @since 0.1
 */

function blocks_save_plugin_options() {
	$settings = get_option( 'blocks' );

	$tmp_area = ( isset( $_POST['areas'] ) ? $_POST['areas'] : '' );
	
	$areas = array();

	if( ! empty( $tmp_area ) ) { 

		for ( $i = count( $tmp_area['area'] ) - 1; $i >= 0; $i-- ) {
			$area  = $tmp_area['area'][$i];
			$name  = $tmp_area['name'][$i];
			$desc  = $tmp_area['desc'][$i];

			if( !array_key_exists( $area, $areas ) && blocks_check_area_values( $area, $name, $desc ) ) {
				$areas[$area] = array(
					'name' => $name,
					'desc' => $desc
				);
			}
		}
	}

	if ( $_GET['page'] == 'blocks-settings' ) { 
		if ( isset ( $_GET['tab'] ) ) {
	        $tab = $_GET['tab']; 
	    } else {
	        $tab = 'general'; 
    	}

	    switch ( $tab ) { 
	    	case 'general' : 	  	
				$settings['areas'] = $areas;
			break;
	        case 'advanced' :
	        	$settings['class'] = ( isset( $_POST['class'] ) ? $_POST['class'] : '' );
	        	$settings['cache'] = ( isset( $_POST['cache'] ) ? $_POST['cache'] : '' );
	        	$settings['edit']  = ( isset( $_POST['edit'] ) ? $_POST['edit'] : '' );
			break; 
	    }
	}
	update_option( 'blocks', $settings );
}


/**
 * Register tabs for the
 * Settings page
 * @since 0.1
 * @param $current, highlighte current tab
 * @return current tab
 */

function blocks_admin_tabs( $current = 'general' ) { 
    $tabs = array( 
    	'general'   => __('General', 'blocks' ), 
    	'advanced'  => __('Advanced', 'blocks' )
    ); 

    $links = array();

    $output = '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ) {
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        $output .= "<a class='nav-tab$class' href='?post_type=blocks&page=blocks-settings&tab=$tab'>$name</a>";
	    }
    $output .= '</h2>';

    echo $output;
}


/**
 * The options for 
 * The registed tabs
 * @since 0.1
 * @return Settings page
 */

function blocks_settings_page() {
	$settings = get_option( 'blocks' );
	?>	
	<div class="wrap">		
		<?php

			if( isset( $_GET['updated'] ) && 'true' == esc_attr( $_GET['updated'] ) ) {
				echo '<div class="updated" ><p>' . __('Block settings updated.','blocks') . '</p></div>';
			}

			if( isset ( $_GET['tab'] ) ) {
				blocks_admin_tabs( $_GET['tab'] );
			} else {
				blocks_admin_tabs('general');
			}

		?>

		<div id="poststuff">
			<form method="post" action="<?php network_admin_url( 'edit.php?post_type=blocks&page=blocks-settings' ); ?>">
				<?php
				wp_nonce_field( "blocks-settings-page" ); 
				
				if ( $_GET['page'] == 'blocks-settings' ) { 

					if ( isset ( $_GET['tab'] ) ) {
						$tab = $_GET['tab'];
					} else {
						$tab = 'general';
					} 

					switch ( $tab ) {

						case 'general' : 

						if( isset( $settings['areas'] ) ) {
							$areas = $settings['areas'];
						} else {
							$areas = array();
						}

						reset($areas);
						$firstBlock = 'example';
						if(count($areas) > 0)
						{
							$firstBlock = key($areas);
						}
						?>

						<table class="form-table">
							<tbody class="add-area">

								<tr>
									<th scope="row">
										<h3><?php _e('Add new areas', 'blocks'); ?>.</h3>
										<p>
											<?php _e('Here you can edit or add new areas', 'blocks'); ?>.
											<?php _e('Add a key, Name and Description', 'blocks'); ?>.
											<?php _e('The Name and Description will be visable in WordPress-admin', 'blocks'); ?>.
										</p>
										
										<p><a href="#add-block-areas-instructions" class="show-pointer" rel="add-block-areas-instructions"><?php _e("Instructions",'blocks'); ?></a></p>

										<div id="add-block-areas-instructions" style="display:none;">
											<h3><?php _e("Add areas to your theme",'blocks'); ?></h3>
											<p><?php _e("First you need to have one ore more <b>Areas</b>, then you need to define them in your theme where you want them to show up.",'blocks'); ?></p>
											<p><?php _e("When you created the areas you defined a <b>Key</b>, witch you use to call that area from the theme.",'blocks'); ?></p>
											<ol>
												<li><?php _e("Go to your theme folder and select the template where you want to show blocks, e.g page.php",'blocks'); ?></li>
												<li>
													<?php _e("In the top of the file add a comment like this:", 'blocks'); ?> <br />
													<code><span><</span><span>php</span> // Blocks Areas: <?php echo $firstBlock; ?> ?></code>
												</li>
												<li>
													<?php _e("Then you need to define where in that file you want the blocks, call this function:", 'blocks'); ?> <br />
													<code><span><</span><span>?php</span> get_blocks( "<?php echo $firstBlock; ?>" ); ?></code>
												</li>
												<li><?php _e('Go and', 'blocks'); ?> <a target="_blank" href="post-new.php?post_type=blocks"><?php _e('create', 'blocks'); ?></a> <?php _e('a block and add it to a <b>Page</b>', 'blocks'); ?></li>
												<li><?php _e("Read more about how to use and customize blocks in the",'blocks'); ?> <a target"_blank" href="#"><?php _e('Documentation'); ?></a></li>
											</ol>	
										</div>
									</th>

									<td>
										<div class="wp-box">
											<table class="widefat">
												<thead>
													<tr>
														<th><?php _e('Key', 'blocks'); ?></th>
														<th><?php _e('Name', 'blocks'); ?></th>
														<th><?php _e('Description', 'blocks'); ?></th>
													</tr>
												</thead>
												<tbody>
													<tr class="blocks-area-row">
														<td><input type="text" name="areas[area][]" placeholder="<?php _e('Key','blocks'); ?>" /></td>
														<td><input type="text" name="areas[name][]" placeholder="<?php _e('Name','blocks'); ?>" /></td>
														<td><input type="text" name="areas[desc][]" placeholder="<?php _e('Description','blocks'); ?>" /></td>
													</tr>
													<?php foreach ($areas as $areaKey => $area) : ?>
														<tr class="blocks-area-row">
															<td><input type="text" name="areas[area][]" value="<?php echo $areaKey; ?>" /></td>
															<td><input type="text" name="areas[name][]" value="<?php echo $area['name']; ?>" /></td>
															<td>
																<input type="text" name="areas[desc][]" value="<?php echo $area['desc']; ?>" />
																<a class="button blocks-remove-area"><?php _e('Remove', 'blocks'); ?></a>
															</td>
														</tr>
													<?php endforeach; ?>
													
												</tbody>
											</table>
										</div>
									</td>

								</tr>

								<!-- 

								<tr>
									<th scope="row">
										<h3><?php _e('Control Post Types', 'blocks'); ?>.</h3>
										<p>
											<?php _e('Enable or disable blocks on specific post type', 'blocks'); ?>.
										</p>
									</th>

									<td>
										<div class="wp-box">
											<table class="widefat post-types">
												<thead>
													<tr>
														<th><?php _e('Post type', 'blocks'); ?></th>
														<th><?php _e('Status', 'blocks'); ?></th>
														<th><?php _e('Update', 'blocks'); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php foreach( blocks_post_types() as $post_type ) : ?>
														<tr>
															<td><?php echo ucfirst( $post_type ); ?></td>
															<td><span class="status active"><?php _e('Active', 'blocks'); ?></span></td>
															<td><a class="button blocks-remove-area"><?php _e('Activate', 'blocks'); ?></a></td>
														</tr>
													<?php endforeach; ?>
													
												</tbody>
											</table>
										</div>
									</td> 

									-->

								</tr>
								
							</tbody>
						</table>

						<?php
						break;

						case 'advanced' :
						?>

						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<h3><?php _e('Enable Cache', 'blocks'); ?>.</h3>
										<p>
											<?php _e('Blocks have a built in cache soulution, it uses the Transients API', 'blocks'); ?>.
											<?php _e('This will work on sites like intranets because it uses the database to cache and not static HTML', 'blocks'); ?>.

										</p>
									</th>
									<td>
										<input id="cache" name="cache" type="checkbox" value="1" <?php checked( $settings['cache'], 1 ); ?> /> 
									</td>

									<?php if( $settings['cache'] ) : ?>
										<td class="empty-cache-holder">
											<a href="#" class="button empty-cache"><?php _e('Empty cache'); ?></a>
										</td>
									<?php endif; ?>
								</tr>

								<tr>
									<th scope="row">
										<h3><?php _e('Enable edit-button', 'blocks'); ?>.</h3>
										<p><?php _e('Enable this if you want a link on eatch block in the front-end that goes to the edit-screen of the block','blocks'); ?>.</p>
									</th>
									<td>
										<input id="blocks-edit" name="edit" type="checkbox" value="1" <?php checked( $settings['edit'], 1 ); ?> /> 
									</td>
								</tr>

								<tr>
									<th scope="row">
										<h3><?php _e('Add your own css-class to the blocks', 'blocks'); ?>.</h3>
										<p><?php _e('Here you can add you own css-class that will be printed on the front-end', 'blocks'); ?>.</p>
									</th>
									<td>
										<input id="blocks-class" name="class" type="text" value="<?php if ( isset( $settings["class"] ) ) echo $settings["class"]; ?>" placeholder="<?php _e('class', 'blocks'); ?>" />
									</td>
								</tr>

							</tbody>
						</table>

						<?php
					break; 
					}
				}
				?>
				<p class="submit">
					<input type="submit" name="Submit"  class="button-primary button-large button" value="<?php _e('Save Settings', 'blocks'); ?>" />
					<input type="hidden" name="blocks_save" value="save" />
				</p>
			</form>	
		</div>
	</div>
<?php
}
