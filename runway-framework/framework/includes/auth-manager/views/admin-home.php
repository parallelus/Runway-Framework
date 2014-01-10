<?php if(!$this->auth) : ?>
<form name="loginform" id="loginform" action="<?php echo $this->self_url('auth'); ?>" method="post">
	<p>
		<label for="user_login">Username<br>
		<input type="text" name="log" id="user_login" class="input" value="" size="20"></label>
	</p>
	<p>
		<label for="user_pass">Password<br>
		<input type="password" name="pwd" id="user_pass" class="input" value="" size="20"></label>
	</p>		
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Log In">				
	</p>
</form>
<?php else: ?>
<!-- TODO: user logged in -->
<?php endif; ?>