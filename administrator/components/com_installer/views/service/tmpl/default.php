<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="installer-service" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=service');?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>

	<?php

		echo JHtml::_('sliders.start', 'warning-sliders', array('useCookie' => 1));

		echo $this->loadTemplate('database');

		if (count($this->messages))
		{
			foreach($this->messages as $message)
			{
				echo JHtml::_('sliders.panel', $message['message'], str_replace(' ', '', $message['message']));
				echo '<div style="padding: 5px;" >'.$message['description'].'</div>';
			}
			echo JHtml::_('sliders.panel', JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'), 'furtherinfo-pane');
			echo '<div style="padding: 5px;" >'. JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC') .'</div>';
		}

		echo JHtml::_('sliders.end');
		?>
			<div class="clr"> </div>
			<div>
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
