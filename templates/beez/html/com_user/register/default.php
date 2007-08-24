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

 -->
</script>

<?php
echo '<form action="'.JRoute::_( 'index.php?option=com_user#content' ).'" method="post" id="josForm" name="josForm" class="form-validate user" >';
echo '<h'.$hlevel.' class="componentheading">'. JText::_( 'Registration' ).'</h'.$hlevel.'>';
if(isset($this->message))
{
	$this->display('message');
}

echo '<fieldset>';
echo '<p>'.JText::_( 'REGISTER_REQUIRED' ).'</p>';
echo '<div class="name"><label id="namemsg" for="name">'.JText::_( 'Name' ).': *</label>';
echo '<input type="text" name="name" id="name"  value="'.$this->user->get( 'name' ).'" class="inputbox validate required none namemsg" maxlength="50" />'.'</div>';

echo '<div class="user"> <label id="usernamemsg" for="username">'.JText::_( 'Username' ).': *</label>';
echo '<input type="text" id="username" name="username"  value="'.$this->user->get( 'username' ).'" class="inputbox validate required username usernamemsg" maxlength="25" />'.'</div>';

echo '<div class="email"><label id="emailmsg" for="email">'.JText::_( 'Email' ).': *</label>';
echo '<input type="text" id="email" name="email"  value="'. $this->user->get( 'email' ).'" class="inputbox validate required email emailmsg" maxlength="100" />'.'</div>';
echo '</fieldset>';
echo '<fieldset>';
echo '<div class="pass"><label id="pwmsg" for="password">'.JText::_( 'Password' ).': *</label>';
echo '<input class="inputbox required validate-password" type="password" id="password" name="password"  value="" />'.'</div>';

echo '<div class="verify_pass"><label id="pw2msg" for="password2">'.JText::_( 'Verify Password' ).': *</label>';
echo '<input class="inputbox required validate-passverify" type="password" id="password2" name="password2"  value="" />'.'</div>';

echo '</fieldset>';
echo '<button class="button validate" type="submit">'. JText::_('Register').'</button>';
echo '<input type="hidden" name="task" value="register_save" />';
echo '<input type="hidden" name="id" value="0" />';
echo '<input type="hidden" name="gid" value="0" />';
echo '<input type="hidden" name="'.JUtility::getToken().'" value="1" />';
echo '</form>';