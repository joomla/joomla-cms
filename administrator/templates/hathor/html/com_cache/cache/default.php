<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

// Get additional language strings prefixed with TPL_HATHOR
$lang =& JFactory::getLanguage();
$lang->load('tpl_hathor', JPATH_ADMINISTRATOR)
|| $lang->load('tpl_hathor', JPATH_ADMINISTRATOR.DS.'templates/hathor');


?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
<table class="adminlist">
	<thead>
		<tr>
			<th class="title row-number-col">
				<?php echo JText::_('COM_CACHE_NUM'); ?>
			</th>
			<th class="checkmark-col">
				<input type="checkbox" name="toggle" value="" title="<?php echo JText::_('TPL_HATHOR_CHECKMARK_ALL'); ?>" onclick="checkAll(<?php echo count($this->data);?>);" />
			</th>
			<th class="title nowrap">
				<?php echo JText::_('COM_CACHE_GROUP'); ?>
			</th>
			<th class="width-5 center nowrap">
				<?php echo JText::_('COM_CACHE_NUMBER_OF_FILES'); ?>
			</th>
			<th class="width-10 center">
				<?php echo JText::_('COM_CACHE_SIZE'); ?>
			</th>
		</tr>
	</thead>

	<tbody>
		<?php
		$i = 0;
		foreach ($this->data as $folder => $item): ?>
		<tr class="row<?php echo $i % 2; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" title="<?php echo JText::sprintf('JGRID_CHECKBOX_ROW_N', ($i + 1)); ?>" value="<?php echo $item->group; ?>" onclick="isChecked(this.checked);" />
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
				<?php echo $item->size ?>
			</td>
		</tr>
		<?php $i++; endforeach; ?>
	</tbody>
</table>

<?php echo $this->pagination->getListFooter(); ?>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
