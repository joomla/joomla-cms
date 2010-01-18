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
	<legend><?php echo JText::_('Admin_Configuration_File'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="300">
						<?php echo JText::_('Admin_Setting'); ?>
					</th>
					<th>
						<?php echo JText::_('Admin_Value'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->config as $key=>$value):?>
					<tr>
						<td>
							<?php echo $key;?>
						</td>
						<td>
							<?php echo $value;?>
						</td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
</fieldset>
