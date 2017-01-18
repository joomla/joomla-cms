<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (count($this->data) > 0) : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%" class="nowrap text-center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="title nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap text-center">
						<?php echo JHtml::_('searchtools.sort', 'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap text-center">
						<?php echo JHtml::_('searchtools.sort', 'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4">
					<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$i = 0;
				foreach ($this->data as $folder => $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $item->group; ?>" onclick="Joomla.isChecked(this.checked);" />
						</td>
						<td>
							<label for="cb<?php echo $i; ?>">
								<strong><?php echo $item->group; ?></strong>
							</label>
						</td>
						<td class="text-center">
							<?php echo $item->count; ?>
						</td>
						<td class="text-center">
							<?php echo JHtml::_('number.bytes', $item->size*1024); ?>
						</td>
					</tr>
				<?php $i++; endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
