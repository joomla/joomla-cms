<?php
/**
 * @package	 Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$lang = JFactory::getLanguage();
$source = JPATH_ADMINISTRATOR . '/components/' . 'com_userlogs';

$lang->load("com_userlogs", JPATH_ADMINISTRATOR, null, false, true)
	|| $lang->load("com_userlogs", $source, null, false, true);
?>
<h1>
	<?php echo JText::_('PLG_SYSTEM_USERLOGS_EMAIL_SUBJECT'); ?>
</h1>
<h2>
	<?php echo JText::_('PLG_SYSTEM_USERLOGS_EMAIL_DESC'); ?>
</h2>
<table>
	<thead>
		<th><?php echo JText::_('COM_USERLOGS_MESSAGE'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_DATE'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_EXTENSION'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_USER'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_IP_ADDRESS'); ?></th>
	</thead>
	<tbody>
		<tr>
			<td><?php echo $displayData['message']; ?></td>
			<td><?php echo $displayData['log_date']; ?></td>
			<td><?php echo $displayData['extension']; ?></td>
			<td><?php echo $displayData['username']; ?></td>
			<td><?php echo $displayData['ip']; ?></td>
		</tr>
	</tbody>
</table>
