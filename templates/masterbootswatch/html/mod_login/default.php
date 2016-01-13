<?php
	/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */defined('_JEXEC') or die;
	JHtml::_('jquery.framework');
	JHtml::_('behavior.keepalive');
	/*JHtml::_('bootstrap.tooltip');*/
	?>
<form action="<?php  echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post">
	<?php  if ($params->get('pretext')) : ?>
	<div class="pretext">
		<p><?php  echo $params->get('pretext'); ?></p>
	</div>
	<?php  endif; ?>
		<div id="form-login-username" class="form-group">
			<?php  if (!$params->get('usetext')) : ?>
			<label for="modlgn-username" class="element-invisible"><?php  echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
			<div class="input-group">
			<div class="input-group-addon"> <span class="glyphicon glyphicon-user tip" title="<?php  echo JText::_('MOD_LOGIN_VALUE_USERNAME')  ?>"></span> </div>
			<input id="modlgn-username" type="text" name="username" class="form-control input-small" tabindex="0" placeholder="<?php  echo JText::_('MOD_LOGIN_VALUE_USERNAME')  ?>" />
			</div>
		</div>
		<?php  else : ?>
		<label for="modlgn-username"><?php  echo JText::_('MOD_LOGIN_VALUE_USERNAME')  ?></label>
		<input id="modlgn-username" type="text" name="username" class="form-control input-small" tabindex="0" placeholder="<?php  echo JText::_('MOD_LOGIN_VALUE_USERNAME')  ?>" />
		<?php  endif; ?>
	<div id="form-login-password" class="form-group">
		<div class="controls">
			<?php  if (!$params->get('usetext')) : ?>
			<label for="modlgn-passwd" class="element-invisible"><?php  echo JText::_('JGLOBAL_PASSWORD'); ?> </label>
			<div class="input-group"> <span class="input-group-addon"> <span class="glyphicon glyphicon-lock tip" title="<?php  echo JText::_('JGLOBAL_PASSWORD')  ?>"> </span> </span>
				<input id="modlgn-passwd" type="password" name="password" class="form-control input-small" tabindex="0" placeholder="<?php  echo JText::_('JGLOBAL_PASSWORD')  ?>" />
			</div>
			<?php  else : ?>
			<label for="modlgn-passwd"><?php  echo JText::_('JGLOBAL_PASSWORD')  ?></label>
			<input id="modlgn-passwd" type="password" name="password" class="form-control input-small" tabindex="0" placeholder="<?php  echo JText::_('JGLOBAL_PASSWORD')  ?>" />
			<?php  endif; ?>
		</div>
	</div>
	<?php  if (JPluginHelper::isEnabled('system', 'remember')) : ?>
	<div id="form-login-remember" class="checkbox">
		<label for="modlgn-remember"><?php  echo JText::_('MOD_LOGIN_REMEMBER_ME')  ?>
			<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
		</label>
	</div>
	<?php  endif; ?>
	<button type="submit" tabindex="0" name="Submit" class="btn btn-primary btn-block margin-bottom-sm"><?php  echo JText::_('JLOGIN')  ?></button>
	<?php
 $usersConfig = JComponentHelper::getParams('com_users');if ($usersConfig->get('allowUserRegistration')) : ?>
	<ul class="unstyled">
		<li> <a href="<?php  echo JRoute::_('index.php?option=com_users&view=registration'); ?>"> <?php  echo JText::_('MOD_LOGIN_REGISTER'); ?> <span class="icon-arrow-right"></span></a> </li>
		<li> <a href="<?php  echo JRoute::_('index.php?option=com_users&view=remind'); ?>"> <?php  echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a> </li>
		<li> <a href="<?php  echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php  echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a> </li>
	</ul>
	<?php  endif; ?>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php  echo $return; ?>" />
	<?php  echo JHtml::_('form.token'); ?>
	<?php  if ($params->get('posttext')) : ?>
	<div class="posttext">
		<p><?php  echo $params->get('posttext'); ?></p>
	</div>
	<?php  endif; ?>
</form>