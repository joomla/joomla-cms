<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_user
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>

<form action="<?php echo JRoute::_( 'index.php', true, $this->params->get('usesecure')); ?>" method="post" name="com-login" id="com-form-login">
<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<tr>
	<td colspan="2">
		<?php if ( $this->params->get( 'show_login_title' ) ) : ?>
		<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
			<?php echo $this->params->get( 'header_login' ); ?>
		</div>
		<?php endif; ?>
		<div>
			<?php if (($this->params->get('login_image')!='')) :?>
				<img src="<?php echo $this->params->get('login_image'); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/>
			<?php endif; ?>
			<?php if ( $this->params->get( 'description_login' ) ) : ?>
				<?php echo $this->params->get( 'description_login_text' ); ?>
				<br /><br />
			<?php endif; ?>
		</div>
	</td>
</tr>

</table>
<fieldset class="input">
	<p id="com-form-login-username">
		<label for="username"><?php echo JText::_('COM_USERS_LOGIN_USERNAME_LABEL') ?></label><br />
		<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
	</p>
	<p id="com-form-login-password">
		<label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label><br />
		<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="com-form-login-remember">
		<label for="remember"><?php echo JText::_('JGLOBAL_REMEMBER_ME') ?></label>
		<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="<?php echo JText::_('REMEMBER_ME') ?>" />
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
</fieldset>
<ul>
	<li>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>">
		<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
	</li>
	<li>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>">
		<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?></a>
	</li>
	<?php
	$usersConfig = JComponentHelper::getParams( 'com_users' );
	if ($usersConfig->get('allowUserRegistration')) : ?>
	<li>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=register' ); ?>">
			<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?></a>
	</li>
	<?php endif; ?>
</ul>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
