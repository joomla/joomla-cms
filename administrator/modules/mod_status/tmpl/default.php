<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$hideLinks = $app->input->getBool('hidemainmenu');

?>
<div class="ml-auto">
	<ul class="nav text-center">
		<?php if (JLanguageMultilang::isEnabled()) : ?>
			<?php $module = JModuleHelper::getModule('mod_multilangstatus'); ?>
			<?php echo JModuleHelper::renderModule($module); ?>
		<?php endif; ?>	

		<li class="nav-item">
			<a class="nav-link" href="<?php echo JUri::root(); ?>" title="<?php echo JText::sprintf('MOD_STATUS_PREVIEW', $sitename); ?>" target="_blank">
				<span class="fa fa-external-link-square" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JHtml::_('string.truncate', $sitename, 28, false, false); ?></span>
			</a>
		</li>

		<li class="nav-item">
			<a class="nav-link dropdown-toggle" href="<?php echo JRoute::_('index.php?option=com_messages'); ?>" title="<?php echo JText::_('MOD_STATUS_PRIVATE_MESSAGES'); ?>">
				<span class="fa fa-envelope" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('MOD_STATUS_PRIVATE_MESSAGES'); ?></span>
				<?php $countUnread = JFactory::getSession()->get('messages.unread'); ?>
				<?php if ($countUnread > 0) : ?>
					<span class="badge badge-pill badge-success"><?php echo $countUnread; ?></span>
				<?php endif; ?>
			</a>
		</li>

		<?php if ($user->authorise('core.manage', 'com_postinstall')) : ?>
		<li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" title="<?php echo JText::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>">
				<span class="fa fa-bell" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?></span>
				<?php if (count($messages) > 0) : ?>
					<span class="badge badge-pill badge-success"><?php echo count($messages); ?></span>
				<?php endif; ?>
			</a>
			<div class="dropdown-menu dropdown-menu-right dropdown-notifications">
				<div class="list-group">
					<?php if (empty($messages)) : ?>
					<p class="list-group-item text-center">
						<strong><?php echo JText::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?></strong>
					</p>
					<?php endif; ?>
					<?php foreach ($messages as $message) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_postinstall&amp;eid=700'); ?>" class="list-group-item list-group-item-action">
						<h5 class="list-group-item-heading"><?php echo JHtml::_('string.truncate', JText::_($message->title_key), 28, false, false); ?></h5>
						<p class="list-group-item-text small">
							<?php echo JHtml::_('string.truncate', JText::_($message->description_key), 120, false, false); ?>
						</p>
					</a>
					<?php endforeach; ?>
				</div>
			</div>
		</li>
		<?php endif; ?>

		<li class="nav-item dropdown header-profile">
			<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" title="<?php echo JText::_('MOD_STATUS_USER_MENU'); ?>">
				<span class="fa fa-user" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('MOD_STATUS_USER_MENU'); ?></span>
			</a>
			<div class="dropdown-menu dropdown-menu-right">
				<div class="dropdown-item header-profile-user">
					<span class="fa fa-user" aria-hidden="true"></span>
					<?php echo $user->name; ?>
				</div>
				<?php $route = 'index.php?option=com_admin&amp;task=profile.edit&amp;id=' . $user->id; ?>
				<a class="dropdown-item" href="<?php echo JRoute::_($route); ?>">
					<?php echo JText::_('MOD_STATUS_EDIT_ACCOUNT'); ?></a>
				<a class="dropdown-item" href="<?php echo JRoute::_('index.php?option=com_login&task=logout&'
					. JSession::getFormToken() . '=1') ?>"><?php echo JText::_('JLOGOUT'); ?></a>
			</div>
		</li>

	</ul>
</div>
