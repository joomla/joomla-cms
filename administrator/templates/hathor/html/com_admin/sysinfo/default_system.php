<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_ADMIN_SYSTEM_INFORMATION'); ?></legend>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="250">
					<?php echo JText::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&#160;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_PHP_BUILT_ON'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['php'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_DATABASE_VERSION'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['dbversion'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_DATABASE_COLLATION'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['dbcollation'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_PHP_VERSION'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['phpversion'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_WEB_SERVER'); ?></strong>
				</td>
				<td>
					<?php echo JHtml::_('system.server', $this->info['server']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_WEBSERVER_TO_PHP_INTERFACE'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['sapi_name'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_JOOMLA_VERSION'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['version'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_PLATFORM_VERSION'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['platform'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_ADMIN_USER_AGENT'); ?></strong>
				</td>
				<td>
					<?php echo $this->info['useragent'];?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
