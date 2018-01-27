<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<fieldset id="filter-bar">
<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
	<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_INSTALLER_FILTER_LABEL'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

	<div class="filter-select">
			<label class="selectlabel" for="filter_client_id">
				<?php echo JText::_('COM_INSTALLER_VALUE_CLIENT_SELECT'); ?>
			</label>
			<select name="filter_client_id" id="filter_client_id">
				<?php echo JHtml::_('select.options', array('0' => JText::_('JSITE'), '1' => JText::_('JADMINISTRATOR')), 'value', 'text', $this->state->get('filter.client_id'), true);?>
			</select>

            <label class="selectlabel" for="filter_status">
				<?php echo JText::_('COM_INSTALLER_VALUE_STATE_SELECT'); ?>
			</label>
			<select name="filter_status" id="filter_status">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', InstallerHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.status'), true);?>
			</select>

            <label class="selectlabel" for="filter_type">
				<?php echo JText::_('COM_INSTALLER_VALUE_TYPE_SELECT'); ?>
			</label>
			<select name="filter_type" id="filter_type">
				<option value=""><?php echo JText::_('COM_INSTALLER_VALUE_TYPE_SELECT');?></option>
				<?php echo JHtml::_('select.options', InstallerHelper::getExtensionTypes(), 'value', 'text', $this->state->get('filter.type'), true);?>
			</select>

			<label class="selectlabel" for="filter_folder">
				<?php echo JText::_('COM_INSTALLER_VALUE_FOLDER_SELECT'); ?>
			</label>
			<select name="filter_folder" id="filter_folder">
				<option value=""><?php echo JText::_('COM_INSTALLER_VALUE_FOLDER_SELECT');?></option>
				<?php echo JHtml::_('select.options', array_merge(InstallerHelper::getExtensionGroupes(), array('*' => JText::_('COM_INSTALLER_VALUE_FOLDER_NONAPPLICABLE'))), 'value', 'text', $this->state->get('filter.folder'), true);?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>

		</div>

</fieldset>
<div class="clr"></div>
