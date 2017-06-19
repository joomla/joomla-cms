<?php
/**
 * Items Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th style="width:1%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th style="width:10%" class="nowrap hidden-sm-down">
		<?php echo JText::_('COM_WORKFLOW_NAME'); ?>
	</th>
	<th style="width:10%" class="nowrap text-center hidden-sm-down">
		<?php echo JText::_('COM_WORKFLOW_CREATION_DATE'); ?>
	</th>
	<th style="width:10%" class="nowrap text-center hidden-sm-down">
		<?php echo JText::_('COM_WORKFLOW_MODIFICATION_DATE'); ?>
	</th>
</tr>
