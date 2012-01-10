<?php
/**
 * @version		$Id: default_config.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_ADMIN_CONFIGURATION_FILE'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="300">
						<?php echo JText::_('COM_ADMIN_SETTING'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_ADMIN_VALUE'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&#160;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->config as $key=>$value):?>
					<tr>
						<td>
							<?php echo $key;?>
						</td>
						<td>
							<?php echo htmlspecialchars($value, ENT_QUOTES);?>
						</td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
</fieldset>
