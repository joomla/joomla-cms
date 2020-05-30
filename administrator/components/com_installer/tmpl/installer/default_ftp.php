<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<fieldset class="option-fieldset options-form">
	<legend><?php echo Text::_('COM_INSTALLER_MSG_DESCFTPTITLE'); ?></legend>

	<?php echo Text::_('COM_INSTALLER_MSG_DESCFTP'); ?>

	<?php if ($this->ftp instanceof Exception) : ?>
		<p><?php echo Text::_($this->ftp->getMessage()); ?></p>
	<?php endif; ?>

	<table class="adminform">
		<tbody>
			<tr>
				<td style="width:120">
					<label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
				</td>
				<td>
					<input type="text" id="username" name="username" class="form-control" size="70" value="">
				</td>
			</tr>
			<tr>
				<td style="width:120">
					<label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
				</td>
				<td>
					<input type="password" id="password" name="password" class="form-control" size="70" value="">
				</td>
			</tr>
		</tbody>
	</table>

</fieldset>
