<?php
/**
 * @version		$Id: default.php 08/01/2012 8.12
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
//JHtml::_('behavior.keepalive');
jimport('joomla.form.form');
jimport('joomla.formfield');
//var_dump(JURI::root());
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="form-login">
	<!--<fieldset class="userdata">	-->
		<p id="form-login-captcha">
			<?php  
			$formpath=JPATH_SITE.DS.'plugins'.DS.'system'.DS.'acaptcha'.DS.'acaptcha'.DS.'modcaptcha.xml'; 
				//  $formpath=JPATH_SITE.DS.'modules'.DS.'mod_login'.DS.'tmpl'.DS.'captcha.xml'; 
//var_dump($formpath);
$form = &JForm::getInstance('mod_login.captcha',$formpath);
	$dispatcher	= JDispatcher::getInstance();
			// Trigger the form preparation event.
				$dispatcher->trigger('onContentPrepareForm', array($form, ''));
					$form->bind('');
						 ?>

<label for="form-login-captcha">
<?php foreach ($form->getFieldset('acaptcha') as $name => $field): ?>
				<label for="modlgn-capthca"><?php echo $field->label; ?></label>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
	
</p>	
	<!--</fieldset>	-->
	<fieldset class="loginform">

				<label id="mod-login-username-lbl" for="mod-login-username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
				<input name="username" id="mod-login-username" type="text" class="inputbox" size="15" />

				<label id="mod-login-password-lbl" for="mod-login-password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
				<input name="passwd" id="mod-login-password" type="password" class="inputbox" size="15" />

				<label id="mod-login-language-lbl" for="lang"><?php echo JText::_('MOD_LOGIN_LANGUAGE'); ?></label>
				<?php echo $langs; ?>
				
<!--
				<div class="button-holder">
					<div class="button1">
						<div class="next">
							<a href="#" onclick="document.getElementById('form-login').submit();">
								<?php echo JText::_('MOD_LOGIN_LOGIN'); ?></a>
						</div>
					</div>
				</div>
-->
		<div class="clr"></div>
		
	</fieldset>
	<button type="submit" name="Submit" class="button validate2"><?php echo JText::_('MOD_LOGIN_LOGIN') ?>"</button>
		<!--<input type="submit" class="hidebtn" value="<?php echo JText::_( 'MOD_LOGIN_LOGIN' ); ?>" />-->
		<input type="hidden" name="option" value="com_login" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>