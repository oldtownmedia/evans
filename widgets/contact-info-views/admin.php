<!-- Title -->
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo "Title:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:96%;" />
</p>

<!-- Address Line 1 -->
<p>
	<label for="<?php echo $this->get_field_id( 'address1' ); ?>"><?php echo "Address Line 1:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'address1' ); ?>" name="<?php echo $this->get_field_name( 'address1' ); ?>" value="<?php echo $instance['address1']; ?>" style="width:96%;" />
</p>

<!-- Address Line 2 -->
<p>
	<label for="<?php echo $this->get_field_id( 'address2' ); ?>"><?php echo "Address Line 2:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'address2' ); ?>" name="<?php echo $this->get_field_name( 'address2' ); ?>" value="<?php echo $instance['address2']; ?>" style="width:96%;" />
</p>

<!-- City -->
<p>
	<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php echo "City:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>" value="<?php echo $instance['city']; ?>" style="width:96%;" />
</p>

<!-- State, ZIP -->
<p>
	<label for="<?php echo $this->get_field_id( 'state' ); ?>" style="width:100%;display:block;"><?php echo "State, ZIP:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'state' ); ?>" name="<?php echo $this->get_field_name( 'state' ); ?>" value="<?php echo $instance['state']; ?>" style="width:44%; float:left" />
	<input id="<?php echo $this->get_field_id( 'zip' ); ?>" name="<?php echo $this->get_field_name( 'zip' ); ?>" value="<?php echo $instance['zip']; ?>" style="width:45%; float:right;" />
</p>

<div style="clear:both;display:table;"></div>

<!-- Phone # -->
<p>
	<label for="<?php echo $this->get_field_id( 'phone' ); ?>"><?php echo "Phone #:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'phone' ); ?>" name="<?php echo $this->get_field_name( 'phone' ); ?>" value="<?php echo $instance['phone']; ?>" style="width:96%;" />
</p>

<!-- Fax # -->
<p>
	<label for="<?php echo $this->get_field_id( 'fax' ); ?>"><?php echo "Fax #:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'fax' ); ?>" name="<?php echo $this->get_field_name( 'fax' ); ?>" value="<?php echo $instance['fax']; ?>" style="width:96%;" />
</p>

<!-- Email Address -->
<p>
	<label for="<?php echo $this->get_field_id( 'email' ); ?>"><?php echo "Email Address:"; ?></label>
	<input id="<?php echo $this->get_field_id( 'email' ); ?>" name="<?php echo $this->get_field_name( 'email' ); ?>" value="<?php echo $instance['email']; ?>" style="width:96%;" />
</p>

<!-- Show Map (or not) -->
<p>
	<label for="<?php echo $this->get_field_id( 'map' ); ?>"><?php echo "Show Map? "; ?></label>
	<input id="<?php echo $this->get_field_id( 'map' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'map' ); ?>" <?php if ( $instance['map'] ) echo "checked"; ?> value="true"> Yes
</p>