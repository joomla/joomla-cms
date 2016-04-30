<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_ADMIN_RELEVANT_PHP_SETTINGS'); ?></legend>
	<table class="table table-striped">
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
					<?php echo JText::_('COM_ADMIN_SAFE_MODE'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['safe_mode']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_OPEN_BASEDIR'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.string', $this->php_settings['open_basedir']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_DISPLAY_ERRORS'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['display_errors']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_SHORT_OPEN_TAGS'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['short_open_tag']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_FILE_UPLOADS'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['file_uploads']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_MAGIC_QUOTES'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['magic_quotes_gpc']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_REGISTER_GLOBALS'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['register_globals']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_OUTPUT_BUFFERING'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean', $this->php_settings['output_buffering']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_SESSION_SAVE_PATH'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.string', $this->php_settings['session.save_path']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_SESSION_AUTO_START'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.integer', $this->php_settings['session.auto_start']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_XML_ENABLED'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set', $this->php_settings['xml']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_ZLIB_ENABLED'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set', $this->php_settings['zlib']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_ZIP_ENABLED'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set', $this->php_settings['zip']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_DISABLED_FUNCTIONS'); ?>
				</td>
				<td class="break-word">
					<?php echo JHtml::_('phpsetting.string', $this->php_settings['disable_functions']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_MBSTRING_ENABLED'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set', $this->php_settings['mbstring']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_ICONV_AVAILABLE'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set', $this->php_settings['iconv']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_MCRYPT_ENABLED'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set', $this->php_settings['mcrypt']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_ADMIN_MAX_INPUT_VARS'); ?>
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.integer', $this->php_settings['max_input_vars']); ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
