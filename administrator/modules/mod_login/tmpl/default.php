<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login">
	<fieldset class="loginform">
		<label id="mod-login-username-lbl" for="mod-login-username"><?php echo JText::_('Username'); ?></label>
		<input name="username" id="mod-login-username" type="text" class="inputbox" size="15" />

		<label id="mod-login-password-lbl" for="mod-login-password"><?php echo JText::_('Password'); ?></label>
		<input name="passwd" id="mod-login-password" type="password" class="inputbox" size="15" />

		<label for="lang"><?php echo JText::_('Language'); ?></label>
		<?php echo $langs; ?>

		<div class="button-holder">
			<div class="button1">
				<div class="next">
					<a href="#" onclick="login.submit();">
						<?php echo JText::_('Log_in'); ?></a>
				</div>
			</div>
		</div>
		<div class="clr"></div>
		<input type="submit" style="border: 0; padding: 0; margin: 0; width: 0px; height: 0px;" value="<?php echo JText::_( 'Login' ); ?>" />
		<input type="hidden" name="option" value="com_login" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>