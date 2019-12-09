<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
		<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-select fltrt">
			<label class="selectlabel" for="client_id">
				<?php echo JText::_('COM_CACHE_SELECT_CLIENT'); ?>
			</label>
			<select name="client_id" id="client_id">
				<?php echo JHtml::_('select.options', CacheHelper::getClientOptions(), 'value', 'text', $this->state->get('client_id'));?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>
<table class="adminlist">
	<thead>
		<tr>
			<th class="checkmark-col">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title nowrap">
				<?php echo JHtml::_('grid.sort',  'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
			</th>
			<th class="width-5 center nowrap">
				<?php echo JHtml::_('grid.sort',  'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
			</th>
			<th class="width-10 center">
				<?php echo JHtml::_('grid.sort',  'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
			</th>
		</tr>
	</thead>

	<tbody>
		<?php
		$i = 0;
		foreach ($this->data as $folder => $item) : ?>
		<tr class="row<?php echo $i % 2; ?>">
			<td>
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $item->group; ?>" onclick="Joomla.isChecked(this.checked);" />
			</td>
			<td>
				<span class="bold">
					<?php echo $item->group; ?>
				</span>
			</td>
			<td class="center">
				<?php echo $item->count; ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('number.bytes', $item->size*1024); ?>
			</td>
		</tr>
		<?php $i++; endforeach; ?>
	</tbody>
</table>

<?php echo $this->pagination->getListFooter(); ?>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<?php echo JHtml::_('form.token'); ?>
</div>
</form>
