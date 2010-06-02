<form id="resetPassword" autocomplete="off">
	<?php
		echo '<input ',
			'type="hidden" ',
			'id="username" ',
			'name="username" ',
			'value="',$inUsername,'" />';
	?>
			
	<input
		type	= "submit"
		id	= "cmdResetPassword"
		name	= "cmdResetPassword"
		value= "Reset Password"
	/>
</form>
