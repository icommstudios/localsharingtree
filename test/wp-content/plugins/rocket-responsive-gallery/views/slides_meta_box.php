<div class="slides_header">
	<table class="wpgp widefat">
		<thead>
			<tr>
				<th class="slide_order">
					<?php _e('Image Order', 'wpgp' ); ?>
				</th>
				<th class="slide_label">
					<?php _e('Image Label', 'wpgp' ); ?>
				</th>
			</tr>
		</thead>
	</table>
</div>

<div class="slides">
	
	<div class="no_slides_message" style="<?php echo $style_display ?>">
		No images. Click the <strong>+ Add Image</strong> to create your first Image.
	</div>

	<?php 
	foreach( $slides as $key => $slide ): 
		$slide = wp_parse_args( $slide, $default_slide ); 
	?>

		<div class="slide" id="<?php echo $key; ?>" style="position:relative">
			
			<input type="hidden" class="slide_attachment" name='<?php echo "slides[{$key}][attachment]" ?>' value="<?php echo $slide['url'] ?>" />
			<div class="slide_meta">
				<table class="wpgp widefat">
					<tbody>
						<tr>
							<td class="slide_order">
								<span class="circle"><?php echo $key+1; ?></span>
							</td>
							
							<td class="slide_label">
								<strong>
									<a>
										<?php echo $slide['label']; ?>
									</a>
								</strong>
								<div class="row_options">
									<span>
										<a class="edit_slide" data-id="<?php echo $key ?>">
											<?php _e( 'Edit Image', 'wpgp' ); ?>
										</a>
									</span> |
									<span>
										<a class="delete_slide" data-id="<?php echo $key ?>">
											<?php _e( 'Delete Image', 'wpgp' ); ?>
										</a>
									</span>
								</div>
							</td>

						</tr>

					</tbody>
				</table>
			</div>
			<div class="slide_form_mask" style="display: none">
			<div class="slide_form">
				<table class="wpgp_input widefat wpgp_slide_form_table">
					<tbody>
						<tr class="slide_label">
							<td class="label">
								<label>
									<span class="required">*</span>
									<?php _e( 'Image Label', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input class="slide_label_input" name='<?php echo "slides[{$key}][label]" ?>' type='text' value="<?php echo $slide['label'] ?>" data-id="<?php echo $key; ?>" />
							</td>
						</tr>

						<tr class="slide_image"  data-id="<?php echo $key ?>">
							<td class="label">
								<label>
									<?php _e( 'Image', 'wpgp' ); ?>
								</label>
								
								<p class="description">
									<?php _e( '', 'wpgp' ); ?>
								</p>
								
							</td>
							<td>
								<input readonly class='slide_image_input' type='text' value="<?php echo $slide['url'] ?>" placeholder="Click on Add Image button" /> 

								<a class="wpgp-button grey add_image" data-id="<?php echo $key ?>">
									<?php _e( 'Upload Image', 'wpgp' ) ?>
								</a>

								<div class="slide_image_preview">
									<img src="<?php echo $slide['url'] ?>" />
								</div>

							</td>
						</tr>

						<tr class="slide_caption">
							<td class="label">
								<label>
									
									<?php _e( 'Caption', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input class="slide_caption_input" name='<?php echo "slides[{$key}][caption]" ?>' type='text' value="<?php echo $slide['caption'] ?>" data-id="<?php echo $key; ?>" />
							</td>
						</tr>

						<tr class="slide_alt">
							<td class="label">
								<label>
									
									<?php _e( 'Alt Text', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input class="slide_alt_input" name='<?php echo "slides[{$key}][alt]" ?>' type='text' value="<?php echo $slide['alt'] ?>" data-id="<?php echo $key; ?>" />
							</td>
						</tr>

						<tr class="slide_link">
							<td class="label">
								<label>
									
									<?php _e( 'Link', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input class="slide_link_input" name='<?php echo "slides[{$key}][link]" ?>' type='text' value="<?php echo $slide['link'] ?>" data-id="<?php echo $key; ?>" />
							</td>
						</tr>

						<tr class="slide_html"  data-id="<?php echo $key ?>">
							<td class="label">
								<label>
									<?php _e( 'HTML', 'wpgp' ); ?>
								</label>
								<p class="description">
										<?php _e( 'This feature is only available in the premium version.', 'wpgp' ); ?>
								</p>
							</td>
							<td>
								<?php if ( function_exists( 'wp_editor' ) && defined( 'PRO_VERSION_ENABLED' ) ): ?>
									<?php wp_editor( $slide['html'], "slide_editor_{$key}", array( 'textarea_name' => "slides[{$key}][html]" ) ); ?>
								<?php else: ?>

									<?php if ( defined( 'PRO_VERSION_ENABLED' ) ): ?>
										<textarea rows="10" name='<?php echo "slides[{$key}][html]" ?>'><?php echo $slide['html'] ?></textarea>
									<?php else: ?>
										<img src="<?php echo plugins_url( 'images/wpgp-html-feature.png', GALLERY_PLUGIN_MAIN_FILE ) ?>" />
									<?php endif;?>
								<?php endif; ?>
							</td>
						</tr>

					<?php do_action( 'wpgp_slide_options' ) ?>

					<tr class="slide_edit_close">
						<td class="label"></td>
						<td>
							<a data-id="<?php echo $key ?>" class="wpgp-button edit_slide grey" style="width: 10%">
								<?php _e( 'Close', 'wpgp' ); ?>
							</A>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
	</div>
		</div>
	
	<?php endforeach; ?>

</div>

<div class="table_footer">
	<div class="order_message">
		<?php _e( 'Drag and drop to reorder', 'wpgp' ); ?>
	</div>
	<a href="javascript:;" id="add_slide_button" class="wpgp-button">
		<?php _e( '+ Add Image', 'wpgp' ); ?>
	</a>
</div>

<input type="hidden" value="<?php echo count($slides); ?>" id="next_slide_id" />

<?php include wpgp_view_path('new_slide_template.php') ?>