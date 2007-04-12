<?php
defined('_JEXEC') or die('Restricted access');

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
        $templateParams = new JParameter($content);
} else {
        $templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$hlevel = $templateParams->get('headerLevelComponent', '2');
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');

?>

<script type="text/javascript">
<!--
	Window.onDomReady(function(){
		document.formvalidator.setHandler('passverify', function (value) { return ($('password').value == value); }	);
	});

function validateForm( frm ) {
	var valid = document.formvalidator.isValid(frm);
	if (valid == false) {
		// do field validation
		if (frm.name.invalid) {
			alert( "<?php echo JText::_( 'Please enter your name.', true );?>" );
		} else if (frm.username.invalid) {
			alert( "<?php echo JText::_( 'Please enter a user name.', true );?>" );
		} else if (frm.email.invalid) {
			alert( "<?php echo JText::_( 'Please enter a valid e-mail address.', true );?>" );
		} else if (frm.password.invalid) {
			alert( "<?php echo JText::_( 'REGWARN_PASS', true );?>" );
		} else if (frm.password2.invalid) {
			alert( "<?php echo JText::_( 'Please verify the password.', true );?>" );
		}
		return false;
	} else {
		frm.submit();
	}
}
// -->
</script>

<?php
if(isset($this->message))
{
	$this->display('message');
}

echo '<form action="'.JRoute::_( 'index.php?option=com_user' ).'" method="post" id="josForm" name="josForm" class="form-validate">';
echo '<h'.$hlevel.' class="componentheading">'. JText::_( 'Registration' ).'</h'.$hlevel.'>';

echo '<p>'.JText::_( 'REGISTER_REQUIRED' ).'</p>';

echo '<p><label id="namemsg" for="name">'.JText::_( 'Name' ).': *</label>';
echo '<input type="text" name="name" id="name"  value="'.$this->user->get( 'name' ).'" class="inputbox validate required none namemsg" maxlength="50" />'.'</p>';

echo '<p> <label id="usernamemsg" for="username">'.JText::_( 'Username' ).': *</label>';
echo '<input type="text" id="username" name="username"  value="'.$this->user->get( 'username' ).'" class="inputbox validate required username usernamemsg" maxlength="25" />'.'</p>';

echo '<p><label id="emailmsg" for="email">'.JText::_( 'Email' ).': *</label>';
echo '<input type="text" id="email" name="email"  value="'. $this->user->get( 'email' ).'" class="inputbox validate required email emailmsg" maxlength="100" />'.'</p>';

echo '<p><label id="pwmsg" for="password">'.JText::_( 'Password' ).': *</label>';
echo '<input class="inputbox validate required password pwmsg" type="password" id="password" name="password"  value="" />'.'</p>';

echo '<p><label id="pw2msg" for="password2">'.JText::_( 'Verify Password' ).': *</label>';
echo '<input class="inputbox validate required passverify pw2msg" type="password" id="password2" name="password2"  value="" />'.'</p>';


echo '<button class="button" type="submit" onclick="validateForm( this.form );return false;">'. JText::_('Register').'</button>';
echo '<input type="hidden" name="task" value="register_save" />';
echo '<input type="hidden" name="id" value="0" />';
echo '<input type="hidden" name="gid" value="0" />';
echo '<input type="hidden" name="'.JUtility::getToken().'" value="1" />';
echo '</form>';