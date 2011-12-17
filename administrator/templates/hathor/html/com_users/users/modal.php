<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');

$field		= JRequest::getCmd('field');
$function	= 'jSelectUser_'.$field;
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=users&layout=modal&tmpl=component&groups='.JRequest::getVar('groups', '', 'default', 'BASE64').'&excluded='.JRequest::getVar('excluded', '', 'default', 'BASE64'));?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_IN_NAME'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			<button type="button" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('', '<?php echo JText::_('JLIB_FORM_SELECT_USER') ?>');"><?php echo JText::_('JOPTION_NO_USER')?></button>
		</div>

		<div class="filter-select">
			<label for="filter_group_id">
				<?php echo JText::_('COM_USERS_FILTER_USER_GROUP'); ?>
			</label>
			<?php echo JHtml::_('access.usergroup', 'filter_group_id', $this->state->get('filter.group_id')); ?>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
<?php 
$this->table->setTableOptions(array('class' => 'adminlist modal'));

$headerrows = $this->table->getRows(1);
$this->table->setActiveRow(array_shift($headerrows));
$this->table->setRowCell('name', '', array('class' => 'title'), false)
	->setRowCell('username', '', array('class' => 'nowrap width-25'), false)
	->setRowCell('groups', '', array('class' => 'nowrap width-25'), false);

$bodyrows = $this->table->getRows();
foreach($bodyrows as $id) {
	$this->table->setActiveRow($id)
		->setRowCell('groups', '', array('class' => 'title'), false);
}
echo $this->table;
?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
