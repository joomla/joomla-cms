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

?>
<fieldset class="adminform">
	<legend><?php echo Text::_('COM_ADMIN_CONFIGURATION_FILE'); ?></legend>
	<table class="table">
		<thead>
			<tr>
				<th style="width:300px">
					<?php echo Text::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th>
					<?php echo Text::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&#160;</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($this->config as $key => $value) : ?>
				<tr>
					<td>
						<?php echo $key; ?>
					</td>
					<td>
						<?php echo htmlspecialchars($value, ENT_QUOTES); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>
