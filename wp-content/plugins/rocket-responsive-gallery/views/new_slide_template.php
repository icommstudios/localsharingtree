<div id="new_slide_template" style="display: none">
	<div class="slide form_open" style="position:relative">
			<input type="hidden" class="slide_type" name="" value="" />
			<input type="hidden" class="slide_attachment" name='' value="" />
			<div class="slide_meta">
				<table class="wpgp widefat">
					<tbody>
						<tr>
							<td class="slide_order">
								<span class="circle"></span>
							</td>
							
							<td class="slide_label">
								<strong>
									<a>change me</a>
								</strong>
								<div class="row_options">
									<span>
										<a class="edit_slide" data-id="">
											<?php _e( 'Edit Image', 'wpgp' ); ?>
										</a>
									</span> |
									<span>
										<a class="delete_slide" data-id="">
											<?php _e( 'Delete Image', 'wpgp' ); ?>
										</a>
									</span>
								</div>
							</td>

							<td class="field_type">
								<?php _e( 'Image', 'wpgp' ); ?>
							</td>

						</tr>

					</tbody>
				</table>
			</div>
		<div class="slide_form_mask" style="display: block">
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
								<input name='' class='slide_label_input' type='text' value="Change ME!" data-id="" />
							</td>
						</tr>

						

						<tr class="slide_html" style="display: none" data-id="">
							<td class="label">
								<label>
									<?php _e( 'HTML', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<textarea rows="10" name=''></textarea>
							</td>
						</tr>

						<tr class="slide_image" data-id="">
							<td class="label">
								<label>
									<span class="required">*</span>
									<?php _e( 'Image', 'wpgp' ); ?>
								</label>
								
								<p class="description">
								</p>
								
							</td>
							<td>
								<input readonly class='slide_image_input' type='text' placeholder="Click on Add Image button" value="" />

								<a class="wpgp-button grey add_image" data-id="">
									<?php _e( 'Upload Image', 'wpgp' ) ?>
								</a>

								<div class="slide_image_preview">
									<img src="" />
								</div>

							</td>
						</tr>

						<tr class="slide_caption" style="" data-id="">
							<td class="label">
								<label>
									<?php _e( 'Caption', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input name='' class='slide_caption_input' type='text' value="" data-id="" />
							</td>
						</tr>

						<tr class="slide_link" style="" data-id="">
							<td class="label">
								<label>
									<?php _e( 'Link', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input name='' class='slide_link_input' type='text' value="" data-id="" />
							</td>
						</tr>

						<tr class="slide_alt" style="" data-id="">
							<td class="label">
								<label>
									<?php _e( 'Alt', 'wpgp' ); ?>
								</label>
							</td>
							<td>
								<input name='' class='slide_alt_input' type='text' value="" data-id="" />
							</td>
						</tr>

					<?php do_action( 'wpgp_slide_options_new_slide' ) ?>

					<tr class="slide_edit_close">
						<td class="label"></td>
						<td>
							<a data-id="" class="wpgp-button edit_slide grey" style="width: 10%">
								<?php _e( 'Close', 'wpgp' ); ?>
							</A>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>