<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */

?>
<div class="sysinfo">
	<table class="table">
		<caption class="sr-only">
			<?php echo Text::_('COM_ADMIN_PHP_SETTINGS'); ?>
		</caption>
		<thead>
			<tr>
				<th scope="col" class="w-30">
					<?php echo Text::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th scope="col">
					<?php echo Text::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_OPEN_BASEDIR'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.string', $this->phpSettings['open_basedir']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DISPLAY_ERRORS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->phpSettings['display_errors']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_SHORT_OPEN_TAGS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->phpSettings['short_open_tag']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_FILE_UPLOADS'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->phpSettings['file_uploads']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_OUTPUT_BUFFERING'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.boolean', $this->phpSettings['output_buffering']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_SESSION_SAVE_PATH'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.string', $this->phpSettings['session.save_path']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_SESSION_AUTO_START'); ?>
				</th>
				<td>
					<?php echo (int) $this->phpSettings['session.auto_start']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_XML_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->phpSettings['xml']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_ZLIB_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->phpSettings['zlib']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_ZIP_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->phpSettings['zip']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DISABLED_FUNCTIONS'); ?>
				</th>
				<td class="break-word">
					<?php echo HTMLHelper::_('phpsetting.string', $this->phpSettings['disable_functions']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_MBSTRING_ENABLED'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->phpSettings['mbstring']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_ICONV_AVAILABLE'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('phpsetting.set', $this->phpSettings['iconv']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_MAX_INPUT_VARS'); ?>
				</th>
				<td>
					<?php echo (int) $this->phpSettings['max_input_vars']; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
