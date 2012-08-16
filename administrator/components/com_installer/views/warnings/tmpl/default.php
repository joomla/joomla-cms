<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="installer-warnings">
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings');?>" method="post" name="adminForm" id="adminForm">
	<!-- Begin Sidebar -->
	<div id="sidebar" class="span2">
		<div class="sidebar-nav">
			<?php
				// Display the submenu position modules
				$this->modules = JModuleHelper::getModules('submenu');
				foreach ($this->modules as $module)
				{
					$output = JModuleHelper::renderModule($module);
					$params = new JRegistry;
					$params->loadString($module->params);

				}
			?>
		</div>
	</div>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
	<?php

	if (!count($this->messages)) {
		echo '<div class="alert alert-info"><a class="close" data-dismiss="alert" href="#">&times;</a>'. JText::_('COM_INSTALLER_MSG_WARNINGS_NONE').'</div>';
	} else {
		echo JHtml::_('sliders.start', 'warning-sliders', array('useCookie' => 1));
		foreach($this->messages as $message) {
			echo JHtml::_('sliders.panel', $message['message'], str_replace(' ', '', $message['message']));
			echo '<div style="padding: 5px;" >'.$message['description'].'</div>';
		}
		echo JHtml::_('sliders.panel', JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'), 'furtherinfo-pane');
		echo '<div style="padding: 5px;" >'. JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC') .'</div>';
		echo JHtml::_('sliders.end');
	}
	?>
		<div class="clr"> </div>
		<div>
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
	<!-- End Content -->
</form>
</div>
