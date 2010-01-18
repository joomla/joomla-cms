<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('Admin_System_Information'); ?></legend>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="250">
					<?php echo JText::_('Admin_Setting'); ?>
				</th>
				<th>
					<?php echo JText::_('Admin_Value'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&nbsp;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_PHP_Built_On'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['php'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_Database_Version'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['dbversion'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_Database_Collation'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['dbcollation'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_PHP_Version'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['phpversion'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_Web_Server'); ?>:</strong>
				</td>
				<td>
					<?php echo JHtml::_('system.server',$this->info['server']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_WebServer_to_PHP_Interface'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['sapi_name'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_Joomla_Version'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['version'];?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo JText::_('Admin_User_Agent'); ?>:</strong>
				</td>
				<td>
					<?php echo $this->info['useragent'];?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
