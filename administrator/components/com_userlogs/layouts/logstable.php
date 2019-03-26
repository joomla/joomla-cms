<?php
/**
 * @package	Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getLanguage()->load("com_userlogs", JPATH_ADMINISTRATOR, null, false, true);

$messages = $displayData['messages'];
?>
<h1>
	<?php echo JText::_('COM_USERLOGS_EMAIL_SUBJECT'); ?>
</h1>
<h2>
	<?php echo JText::_('COM_USERLOGS_EMAIL_DESC'); ?>
</h2>
<table>
	<thead>
		<th><?php echo JText::_('COM_USERLOGS_MESSAGE'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_DATE'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_EXTENSION'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_NAME'); ?></th>
		<th><?php echo JText::_('COM_USERLOGS_IP_ADDRESS'); ?></th>
	</thead>
	<tbody>
        <?php
            foreach ($messages as $message)
            {
            ?>
                <tr>
                    <td><?php echo $message->message; ?></td>
                    <td><?php echo $message->log_date; ?></td>
                    <td><?php echo $message->extension; ?></td>
                    <td><?php echo $displayData['username']; ?></td>
                    <td><?php echo $message->ip_address; ?></td>
                </tr>
            <?php
            }
        ?>
	</tbody>
</table>
