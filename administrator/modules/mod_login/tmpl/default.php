<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="form-login">
	<fieldset class="loginform">

				<label id="mod-login-username-lbl" for="mod-login-username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
				<input name="username" id="mod-login-username" type="text" class="inputbox" size="15" />

				<label id="mod-login-password-lbl" for="mod-login-password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
				<input name="passwd" id="mod-login-password" type="password" class="inputbox" size="15" />

				<label id="mod-login-language-lbl" for="lang"><?php echo JText::_('MOD_LOGIN_LANGUAGE'); ?></label>
				<?php echo $langs; ?>

				<div class="button-holder">
					<div class="button1">
						<div class="next">
							<a href="#" onclick="document.getElementById('form-login').submit();">
								<?php echo JText::_('MOD_LOGIN_LOGIN'); ?></a>
						</div>
					</div>
				</div>

		<div class="clr"></div>
		<input type="submit" class="hidebtn" value="<?php echo JText::_( 'MOD_LOGIN_LOGIN' ); ?>" />
		<input type="hidden" name="option" value="com_login" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
