<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<?php if ($type == 'logout') : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" class="form-vertical">
<?php if ($params->get('greeting')) : ?>
	<div class="login-greeting">
	<?php if($params->get('name') == 0) : {
		echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
	} else : {
		echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
	} endif; ?>
	</div>
<?php endif; ?>
	<div class="logout-button">
		<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGOUT'); ?>" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php else : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" >
	<?php if ($params->get('pretext')): ?>
		<div class="pretext">
		<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>
	<fieldset class="userdata">
	<div id="form-login-username" class="control-group">
		<div class="controls">
          <div class="input-prepend">
            <span class="add-on"><i class="icon-user tip" title="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>"></i></span><input id="modlgn-username" type="text" name="username" class="input-small"  size="18" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>" />
          </div>
        </div>
	</div>
	<div id="form-login-password" class="control-group">
		<div class="controls">
		  <div class="input-prepend">
		    <span class="add-on"><i class="icon-lock tip" title="<?php echo JText::_('JGLOBAL_PASSWORD') ?>"></i></span><input id="modlgn-passwd" type="password" name="password" class="input-small" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
		  </div>
		</div>
	</div>
	<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
	<div id="form-login-remember" class="control-group">
		<label for="modlgn-remember" class="checkbox"><input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/> <?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?></label>
	</div>
	<?php endif; ?>
	<div class="btn-group">
		<button type="submit" name="Submit" class="btn btn-primary"><?php echo JText::_('JLOGIN') ?></button>
		<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
			<span class="caret"></span>
		</a>
		<ul class="dropdown-menu">
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
				<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
			</li>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
				<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
			</li>
			<?php
			$usersConfig = JComponentHelper::getParams('com_users');
			if ($usersConfig->get('allowUserRegistration')) : ?>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
					<?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</fieldset>
	<?php if ($params->get('posttext')): ?>
		<div class="posttext">
		<p><?php echo $params->get('posttext'); ?></p>
		</div>
	<?php endif; ?>
</form>
<?php endif; ?>
