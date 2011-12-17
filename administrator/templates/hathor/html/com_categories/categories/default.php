<?php
/**
 * @version	 $Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$extension	= $this->escape($this->state->get('filter.extension'));
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ordering 	= ($listOrder == 'a.lft');
$saveOrder 	= ($listOrder == 'a.lft' && $listDirn == 'asc');
?>
<div class="categories">
<form action="<?php echo JRoute::_('index.php?option=com_categories&view=categories');?>" method="post" name="adminForm" id="adminForm">

	<fieldset id="filter-bar">
		<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />

			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-select">
			<label class="selectlabel" for="filter_level"><?php echo JText::_('COM_CATEGORIES_OPTION_SELECT_LEVEL'); ?></label>
			<select name="filter_level" class="inputbox" id="filter_level">
				<option value=""><?php echo JText::_('COM_CATEGORIES_OPTION_SELECT_LEVEL');?></option>
				<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'));?>
			</select>

			<label class="selectlabel" for="filter_published"><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></label>
			<select name="filter_published" class="inputbox" id="filter_published">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

            <label class="selectlabel" for="filter_access"><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></label>
			<select name="filter_access" class="inputbox" id="filter_access">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<label class="selectlabel" for="filter_language"><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></label>
			<select name="filter_language" class="inputbox" id="filter_language">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>

<?php
$this->table->setActiveRow(0)
	->setRowCell('checkbox', '', array('class' => 'checkmark-col'), false)
	->setRowCell('title', '', array('class' => 'title'), false)
	->setRowCell('status', '', array('class' => 'nowrap state-col'), false)
	->setRowCell('ordering', '', array('class' => 'nowrap ordering-col'), false)
	->setRowCell('access', '', array('class' => 'access-col'), false)
	->setRowCell('language', '', array('class' => 'language-col'), false)
	->setRowCell('id', '', array('class' => 'nowrap id-col'), false)
;

echo $this->table;
?>

	<div class="clr"> </div>

	<?php //Load the batch processing form. ?>
	<?php echo $this->loadTemplate('batch'); ?>

	<input type="hidden" name="extension" value="<?php echo $extension;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="original_order_values" value="<?php echo implode($this->originalOrders, ','); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
