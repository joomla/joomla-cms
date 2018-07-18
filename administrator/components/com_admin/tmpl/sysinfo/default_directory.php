<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset class="adminform">
	<legend><?php echo Text::_('COM_ADMIN_DIRECTORY_PERMISSIONS'); ?></legend>
	<table class="table">
		<thead>
			<tr>
				<th style="width:650px">
					<?php echo Text::_('COM_ADMIN_DIRECTORY'); ?>
				</th>
				<th>
					<?php echo Text::_('COM_ADMIN_STATUS'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&#160;</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($this->directory as $dir => $info) : ?>
				<tr>
					<td>
						<?php echo HTMLHelper::_('directory.message', $dir, $info['message']); ?>
					</td>
					<td>
						<?php echo HTMLHelper::_('directory.writable', $info['writable']); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>
