<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

$username	= JRequest::getVar('username', '', 'method', 'username');
$return		= JRequest::getVar('return', '', 'method', 'base64');
?>
<script language="javascript" type="text/javascript">
function submitbutton( pressbutton ) {
	var form = document.userform;
	var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

	// do field validation
	if (form.username.value == "") {
		alert( "<?php echo JText::_( 'Please enter a user name.', true );?>" );
	} else if (r.exec(form.username.value) || form.username.value.length < 3) {
		alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Username', true ), 3 );?>" );
	} else if ((form.password.value = "") )){
		alert( "<?php echo JText::_( 'Please enter a password', true );?>" );
	} else {
		form.submit();
	}
}
</script>

<div class="componentheading">
	<?php echo JText::_( 'Login' ); ?>
</div>


<form action="index.php" method="post" name="userform" autocomplete="off">
<fieldset class="input">
	<p id="form-login-username">
		<label for="username"><?php echo JText::_( 'User Name' ); ?></label><br>
		<input name="username" id="username" class="inputbox" alt="username" size="18" type="text" value="<?php echo $username ?>" />
	</p>
	<p id="form-login-password">
		<label for="passwd"><?php echo JText::_( 'Password' ); ?></label><br>
		<input name="passwd" class="inputbox" size="18" alt="password" type="password" />
	</p>
	<p id="form-login-remember">
		<label for="remember"><?php echo JText::_( 'Remember Me' ); ?></label>
		<input name="remember" class="inputbox" value="yes" alt="Remember Me" type="checkbox" />
	</p>
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return ?>" />
	<input name="Submit" class="button" value="<?php echo JText::_( 'Login' ); ?>" type="submit" onclick="submitbutton( this.form );return false;">
</fieldset>
</form>