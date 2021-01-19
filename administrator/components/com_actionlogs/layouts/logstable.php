<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Factory::getLanguage()->load("com_actionlogs", JPATH_ADMINISTRATOR, null, false, true);

$messages = $displayData['messages'];
$showIpColumn = $displayData['showIpColumn'];
?>
<h1>
	<?php echo Text::_('COM_ACTIONLOGS_EMAIL_SUBJECT'); ?>
</h1>
<h2>
	<?php echo Text::_('COM_ACTIONLOGS_EMAIL_DESC'); ?>
</h2>
<table>
	<thead>
		<th><?php echo Text::_('COM_ACTIONLOGS_ACTION'); ?></th>
		<th><?php echo Text::_('COM_ACTIONLOGS_DATE'); ?></th>
		<th><?php echo Text::_('COM_ACTIONLOGS_EXTENSION'); ?></th>
		<th><?php echo Text::_('COM_ACTIONLOGS_NAME'); ?></th>
		<?php if ($showIpColumn) : ?>
			<th><?php echo Text::_('COM_ACTIONLOGS_IP_ADDRESS'); ?></th>
		<?php endif; ?>
	</thead>
	<tbody>
		<?php foreach ($messages as $message) : ?>
			<tr>
				<td><?php echo $message->message; ?></td>
				<td><?php echo HTMLHelper::_('date', $message->log_date, 'Y-m-d H:i:s T', 'UTC'); ?></td>
				<td><?php echo $message->extension; ?></td>
				<td><?php echo $displayData['username']; ?></td>
				<?php if ($showIpColumn) : ?>
					<td><?php echo Text::_($message->ip_address); ?></td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
