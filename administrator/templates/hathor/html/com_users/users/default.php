<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$canDo 		= UsersHelper::getActions();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$loggeduser = JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=users');?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('COM_USERS_SEARCH_USERS'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_USERS_SEARCH_USERS'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_USERS'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_RESET'); ?></button>
		</div>

		<div class="filter-select">
			<span class="faux-label")><?php echo JText::_('COM_USERS_FILTER_LABEL'); ?></span>

			<label class="selectlabel" for="filter_state">
				<?php echo JText::_('COM_USERS_FILTER_LABEL'); ?>
			</label>
			<select name="filter_state" class="inputbox" id="filter_state">
				<option value="*"><?php echo JText::_('COM_USERS_FILTER_STATE');?></option>
				<?php echo JHtml::_('select.options', UsersHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
			</select>

			<label class="selectlabel" for="filter_active">
				<?php echo JText::_('COM_USERS_FILTER_ACTIVE'); ?>
			</label>
			<select name="filter_active" class="inputbox" id="filter_active">
				<option value="*"><?php echo JText::_('COM_USERS_FILTER_ACTIVE');?></option>
				<?php echo JHtml::_('select.options', UsersHelper::getActiveOptions(), 'value', 'text', $this->state->get('filter.active'));?>
			</select>

			<label class="selectlabel" for="filter_group_id">
				<?php echo JText::_('COM_USERS_FILTER_USERGROUP'); ?>
			</label>
			<select name="filter_group_id" class="inputbox" id="filter_group_id">
				<option value=""><?php echo JText::_('COM_USERS_FILTER_USERGROUP');?></option>
				<?php echo JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $this->state->get('filter.group_id'));?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>
<?php 
$headerrows = $this->table->getRows(1);
$this->table->setActiveRow(array_shift($headerrows));
$this->table->setRowCell('checkbox', '', array('class' => 'checkmark-col'), false)
	->setRowCell('name', '', array('class' => 'title'), false)
	->setRowCell('username', '', array('class' => 'nowrap width-10'), false)
	->setRowCell('enabled', '', array('class' => 'nowrap width-5'), false)
	->setRowCell('activated', '', array('class' => 'nowrap width-5'), false)
	->setRowCell('usergroups', '', array('class' => 'nowrap width-10'), false)
	->setRowCell('email', '', array('class' => 'nowrap width-15'), false)
	->setRowCell('lastvisit', '', array('class' => 'nowrap width-15'), false)
	->setRowCell('registerdate', '', array('class' => 'nowrap width-15'), false)
	->setRowCell('id', '', array('class' => 'nowrap id-col'), false);

echo $this->table;
?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
