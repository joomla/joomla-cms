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
	<legend><?php echo Text::_('COM_ADMIN_RELEVANT_PHP_SETTINGS'); ?></legend>
	<table class="table">
		<thead>
			<tr>
				<th scope="col" style="width:250px">
					<?php echo Text::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th scope="col">
					<?php echo Text::_('COM_ADMIN_VALUE'); ?>
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
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_OPEN_BASEDIR'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.string', $this->php_settings['open_basedir']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DISPLAY_ERRORS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->php_settings['display_errors']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_SHORT_OPEN_TAGS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->php_settings['short_open_tag']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_FILE_UPLOADS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->php_settings['file_uploads']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_OUTPUT_BUFFERING'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->php_settings['output_buffering']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_SESSION_SAVE_PATH'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.string', $this->php_settings['session.save_path']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_SESSION_AUTO_START'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.integer', $this->php_settings['session.auto_start']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_XML_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->php_settings['xml']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_ZLIB_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->php_settings['zlib']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_ZIP_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->php_settings['zip']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DISABLED_FUNCTIONS'); ?>
				</th>
				<td class="break-word">
					<?php echo HTMLHelper::_('phpsetting.string', $this->php_settings['disable_functions']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_MBSTRING_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->php_settings['mbstring']); ?>
				</td>
			</tr>
			<tr>
					<th scope="row">
					<?php echo Text::_('COM_ADMIN_ICONV_AVAILABLE'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->php_settings['iconv']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_MAX_INPUT_VARS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.integer', $this->php_settings['max_input_vars']); ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
