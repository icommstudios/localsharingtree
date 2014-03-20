<table class="wpgp_input widefat" id="wpgp_gallery_options">
	<tbody>
		
		<?php do_action( 'wpgp_options_meta_box_start', $gallery_id ); ?>

		<tr id="slider_skin">
			
			<td class="label">
				<label>
					<?php _e( 'Skin', 'wpgp' ); ?>
				</label>
				<p class="description"></p>
			</td>
			<td>
				<select name="skin">
					
					<?php foreach ( $skins as $skin ): ?>
						<option value="<?php echo $skin['path'] ?>" <?php selected( $skin['path'], $active_skin ) ?>>
							<?php echo $skin['name'] ?>
						</option>
					<?php endforeach; ?>
					
					<?php do_action( 'wpgp_html_skin_select' ) ?>
				</select>
			</td>
		</tr>

		<tr id="slider_slideshow">
			
			<td class="label">
				<label>
					<?php _e( 'Slideshow', 'wpgp' ); ?>
				</label>
				<p class="description">
					<?php _e( 'Start slideshow', 'wpgp' ); ?>
				</p>
			</td>
			<td>
				<p>
					<label>
						<input type="radio" name="gallery_options[slideshow]" value="true" <?php checked( true, $gallery_options['slideshow'] ) ?>
						/> <?php _e( 'Yes', 'wpgp' ); ?>
					</label>
				</p>

				<p>
					<label>
						<input type="radio" name="gallery_options[slideshow]" value="false" <?php checked( false, $gallery_options['slideshow'] ) ?>
						/> <?php _e( 'No', 'wpgp' ); ?>
					</label>
				</p>

			</td>
		</tr>

		

		<tr id="slider_height">
			
			<td class="label">
				<label>
					<?php _e( 'Height', 'wpgp' ); ?>
				</label>
				<p class="description">
					<?php _e( 'Thumbnail size', 'wpgp' ); ?>
				</p>
			</td>
			<td>

				<p>
					<label>
						<input type="text" style='width: 80%' name="gallery_options[height]" value="<?php echo $gallery_options['height'] ?>" placeholder="The value in this textbox would only be effective if it's integer and non-empty" /> Pixels( px ) 
					</label>
				</p>

			</td>
		</tr>

		<tr id="slider_width">
			
			<td class="label">
				<label>
					<?php _e( 'Width', 'wpgp' ); ?>
				</label>
				<p class="description">
					<?php _e( 'Thumbnail size', 'wpgp' ); ?>
				</p>
			</td>
			<td>

				<p>
					<label>
						<input type="text" style='width: 80%' name="gallery_options[width]" value="<?php echo $gallery_options['width'] ?>" placeholder="The value in this textbox would only be effective if it's integer and non-empty" /> Pixels( px ) 
					</label>
				</p>

			</td>
		</tr>

		<tr id="slider_cycle_speed">
			
			<td class="label">
				<label>
					<?php _e( 'Slideshow interval', 'wpgp' ); ?>
				</label>
				<p class="description">
					
				</p>
			</td>
			<td>
				<p>
					<label>
						<input type="text" style="width: 80%" name="gallery_options[cycle_speed]" value="<?php echo $gallery_options['cycle_speed'];  ?>" /> <?php _e( 'Seconds', 'wpgp' ); ?>
					</label>
				</p>

			</td>
		</tr>

		<tr id="slider_animation_speed">
			
			<td class="label">
				<label>
					<?php _e( 'Animation speed', 'wpgp' ); ?>
				</label>
			</td>
			<td>
				<p>
					<label>
						<input type="text" style="width: 80%" name="gallery_options[animation_speed]" value="<?php echo $gallery_options['animation_speed'];  ?>" /> <?php _e( 'Seconds', 'wpgp' ); ?>
					</label>
				</p>

			</td>
		</tr>

		<?php do_action( 'wpgp_options_before_control_option', $gallery_id ); ?>

		<tr id="slider_controls">
			
			<td class="label">
				<label>
					
				</label>
				<p class="description">
					<?php _e( 'Enable or Disable different control options' , 'wpgp' ); ?>
				</p>
			</td>
			<td>
				<p>
					<label>
						<input type="checkbox" name="gallery_options[direction_nav]" value="true" <?php checked( true, $gallery_options['direction_nav'] ) ?>
						/> Previous/Next
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" name="gallery_options[keyboard_nav]" value="true" <?php checked( true, $gallery_options['keyboard_nav'] ) ?>
						/> Keyboard navigation
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" name="gallery_options[pause_on_hover]" value="true" <?php checked( true, $gallery_options['pause_on_hover'] ) ?>
						/> Pause on hover
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" name="gallery_options[closeOnContentClick]" value="true" <?php checked( true, $gallery_options['closeOnContentClick'] ) ?>
						/> Close lightbox when user clicks on content of it.
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" name="gallery_options[closeOnBgClick]" value="true" <?php checked( true, $gallery_options['closeOnBgClick'] ) ?>
						/> Close the lightbox when user clicks on the dark overlay.
					</label>
				</p>

				<p>
					<label>
						<input type="checkbox" name="gallery_options[enableEscapeKey]" value="true" <?php checked( true, $gallery_options['enableEscapeKey'] ) ?>
						/> Controls whether pressing the escape key will dismiss the active lightbox or not.
					</label>
				</p>

				<?php do_action( 'wpgp_options_end_control_options', $gallery_id ); ?>

			</td>
		</tr>

		<?php do_action( 'wpgp_options_meta_box_end', $gallery_id ); ?>

	</tbody>
</table>