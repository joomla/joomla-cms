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
	<legend><?php echo JText::_('Admin_Relevant_PHP_Settings'); ?></legend>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="250">
					<?php echo JText::_('Admin_Setting'); ?>
				</th>
				<th>
					<?php echo JText::_('Admin_Value'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&nbsp;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td>
					<?php echo JText::_('Admin_Safe_Mode'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['safe_mode']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Open_basedir'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.string',$this->php_settings['open_basedir']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Display_Errors'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['display_errors']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Short_Open_Tags'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['short_open_tag']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_File_Uploads'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['file_uploads']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Magic_Quotes'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['magic_quotes_gpc']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Register_Globals'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['register_globals']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Output_Buffering'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.boolean',$this->php_settings['output_buffering']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Session_Save_Path'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.string',$this->php_settings['session.save_path']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Session_Auto_Start'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.integer',$this->php_settings['session.auto_start']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_XML_Enabled'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set',$this->php_settings['xml']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Zlib_Enabled'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set',$this->php_settings['zlib']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Disabled_Functions'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.string',$this->php_settings['disable_functions']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Mbstring_Enabled'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set',$this->php_settings['mbstring']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_Iconv_Available'); ?>:
				</td>
				<td>
					<?php echo JHtml::_('phpsetting.set',$this->php_settings['iconv']); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Admin_WYSIWYG_Editor'); // TO BE REMOVED: present in the default_config ?>:
				</td>
				<td>
					<?php echo $this->editor; // TO BE REMOVED: present in the default_config ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
