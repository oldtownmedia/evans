<!-- Title -->
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'evans-mu' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
</p>

<!-- Category -->
<p>
	<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Specific Category <i>(optional)</i>:', 'evans-mu' ); ?></label>

	<select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" style="width:100%;">
		<option value=""><?php _e( 'Select Category', 'evans-mu' ); ?></option>
		<?php

		$categories = get_categories();

		foreach ( $categories as $category ){

			$selected = '';
			if ( $instance['category'] == $category->slug ){
				$selected = ' selected="selected"';
			}

			echo "<option value='".$category->slug."' $selected>";
				echo $category->cat_name;
			echo "</option>";

		}
		?>
	</select>

</p>

<!-- Number of Posts to Display -->
<p>
	<label for="<?php echo $this->get_field_id( 'num_posts' ); ?>"><?php _e( 'Number of Posts to Show <i>(optional)</i>:', 'evans-mu' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" value="<?php echo $instance['num_posts']; ?>" style="width:100%;" />
</p>

<!-- Length of snippet -->
<p>
	<label for="<?php echo $this->get_field_id( 'char_length' ); ?>"><?php _e( 'Snippet Length in characters <i>(optional)</i>:', 'evans-mu' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'char_length' ); ?>" name="<?php echo $this->get_field_name( 'char_length' ); ?>" value="<?php echo $instance['char_length']; ?>" style="width:100%;" />
</p>