<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('Admin_Directory_Permissions'); ?></legend>
		<table class="adminlist">
			<thead>
				<tr>
					<th width="650">
						<?php echo JText::_('Admin_Directory'); ?>
					</th>
					<th>
						<?php echo JText::_('Admin_Status'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach($this->directory as $dir=>$info):?>
					<tr>
						<td>
							<?php echo JHtml::_('directory.message',$dir,$info['message']);?>
						</td>
						<td>
							<?php echo JHtml::_('directory.writable',$info['writable']);?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
</fieldset>
