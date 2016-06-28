<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_checkin'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
		<div class="clearfix"></div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_CHECKIN_NO_ITEMS'); ?>
			</div>
		<?php else : ?>
			<table id="global-checkin" class="table table-striped">
				<thead>
					<tr>
						<th width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
						<th><?php echo JHtml::_('searchtools.sort', 'COM_CHECKIN_DATABASE_TABLE', 'table', $listDirn, $listOrder); ?></th>
						<th><?php echo JHtml::_('searchtools.sort', 'COM_CHECKIN_ITEMS_TO_CHECK_IN', 'count', $listDirn, $listOrder); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="3">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php $i = 0; ?>
					<?php foreach ($this->items as $table => $count): ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center"><?php echo JHtml::_('grid.id', $i, $table); ?></td>
							<td>
								<label for="cb<?php echo $i ?>">
									<?php echo JText::sprintf('COM_CHECKIN_TABLE', $table); ?>
								</label>
							</td>
							<td>
								<span class="label label-warning"><?php echo $count; ?></span>
							</td>
						</tr>
						<?php $i++; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif;?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
