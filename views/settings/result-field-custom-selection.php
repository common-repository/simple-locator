<?php
$post_type_fields = $this->field_repo->getFieldsForPostType($this->post_type);
$post_type = get_post_type_object($this->post_type);
$image_sizes = get_intermediate_image_sizes();
?>
<div class="wpsl-results-field-selector">
	<div class="left">
		<label for="wpsl-fields"><?php echo $post_type->labels->name; ?> <?php _e('Fields', 'simple-locator'); ?></label>
		<select id="wpsl-fields">
			<?php if ( $distance ) : ?> 
			<option value="distance"><?php _e('Distance', 'simple-locator'); ?></option>
			<?php endif; ?>
			<option value="show_on_map"><?php _e('Show on Map', 'simple-locator'); ?></option>
			<?php 
				foreach($post_type_fields as $field) {
					echo '<option value="' . $field . '">' . $field . '</option>';
				}
			?>
		</select>
		<button class="wpsl-field-add button"><?php _e('Add', 'simple-locator');?></button>
	</div>
	<div class="right">
		<label for="wpsl-post-fields"><?php _e('Post Data', 'simple-locator'); ?></label>
		<select id="wpsl-post-fields">
			<option value="post_title"><?php _e('Title', 'simple-locator'); ?></option>
			<option value="post_excerpt"><?php _e('Excerpt', 'simple-locator'); ?></option>
			<option value="post_permalink"><?php _e('Permalink', 'simple-locator'); ?></option>
			<?php foreach($image_sizes as $size) : ?>
			<option value="post_thumbnail_<?php echo $size; ?>"><?php echo __('Thumbnail', 'simple-locator') . ' - ' . $size; ?></option>
			<?php endforeach; ?>
		</select>
		<button class="wpsl-post-field-add button"><?php _e('Add', 'simple-locator');?></button>
	</div>
</div>