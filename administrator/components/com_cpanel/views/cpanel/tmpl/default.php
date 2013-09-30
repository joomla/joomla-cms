<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
?>
<?php if ($this->postinstall_message_count): ?>
<div id="messagesModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="messagesModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="messagesModalLabel">
			<?php echo JText::_('COM_CPANEL_MESSAGES_TITLE'); ?>
		</h3>
	</div>
	<div class="modal-body">
		<p>
			<?php echo JText::_('COM_CPANEL_MESSAGES_BODY'); ?>
		</p>
		<p>
			<?php echo JText::_('COM_CPANEL_MESSAGES_BODYMORE'); ?>
		</p>
	</div>
	<div class="modal-footer">
		<a href="index.php?option=com_postinstall&eid=700" class="btn btn-primary btn-large" >
			<?php echo JText::_('COM_CPANEL_MESSAGES_REVIEW'); ?>
		</a>
		<button class="btn" data-dismiss="modal" aria-hidden="true">
			<?php echo JText::_('COM_CPANEL_MESSAGES_CLOSE'); ?>
		</button>
	</div>
</div>
<?php endif; ?>
<div class="row-fluid">
	<div class="span2">
		<div class="sidebar-nav">
			<ul class="nav nav-list">
				<li class="nav-header"><?php echo JText::_('COM_CPANEL_HEADER_SUBMENU'); ?></li>
				<li class="active"><a href="<?php echo $this->baseurl; ?>"><?php echo JText::_('COM_CPANEL_LINK_DASHBOARD'); ?></a></li>
				<li class="nav-header"><?php echo JText::_('COM_CPANEL_HEADER_SYSTEM'); ?></li>
			<?php if ($user->authorise('core.admin')):?>
				<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_config"><?php echo JText::_('COM_CPANEL_LINK_GLOBAL_CONFIG'); ?></a></li>
				<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_admin&view=sysinfo"><?php echo JText::_('COM_CPANEL_LINK_SYSINFO'); ?></a></li>
			<?php endif;?>
			<?php if ($user->authorise('core.manage', 'com_cache')):?>
				<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_cache"><?php echo JText::_('COM_CPANEL_LINK_CLEAR_CACHE'); ?></a></li>
			<?php endif;?>
			<?php if ($user->authorise('core.admin', 'com_checkin')):?>
				<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_checkin"><?php echo JText::_('COM_CPANEL_LINK_CHECKIN'); ?></a></li>
			<?php endif;?>
			<?php if ($user->authorise('core.manage', 'com_installer')):?>
				<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_installer"><?php echo JText::_('COM_CPANEL_LINK_EXTENSIONS'); ?></a></li>
			<?php endif;?>
			</ul>
		</div>
	</div>
	<div class="span6">
		<?php
		foreach ($this->modules as $module)
		{
			$output = JModuleHelper::renderModule($module, array('style' => 'well'));
			$params = new JRegistry;
			$params->loadString($module->params);
			echo $output;
		}
		?>
	</div>
	<div class="span4">
		<?php
		// Display the submenu position modules
		$this->iconmodules = JModuleHelper::getModules('icon');
		foreach ($this->iconmodules as $iconmodule)
		{
			$output = JModuleHelper::renderModule($iconmodule, array('style' => 'well'));
			$params = new JRegistry;
			$params->loadString($iconmodule->params);
			echo $output;
		}
		?>
	</div>
</div>
