<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<fieldset title="<?php echo JText::_('Langs_Desc_FTP_Title'); ?>">
		<legend><?php echo JText::_('Langs_Desc_FTP_Title'); ?></legend>

		<?php echo JText::_('Langs_Desc_FTP'); ?>

		<?php if (JError::isError($ftp)): ?>
			<p class="warning"><?php echo JText::_($ftp->message); ?></p>
		<?php endif; ?>

		<table class="adminform">
			<tbody>
				<tr>
					<td width="120">
						<label for="username"><?php echo JText::_('Langs_Username'); ?>:</label>
					</td>
					<td>
						<input type="text" id="username" name="username" class="inputbox" size="70" value="" />
					</td>
				</tr>
				<tr>
					<td width="120">
						<label for="password"><?php echo JText::_('Langs_Password'); ?>:</label>
					</td>
					<td>
						<input type="password" id="password" name="password" class="inputbox" size="70" value="" />
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
