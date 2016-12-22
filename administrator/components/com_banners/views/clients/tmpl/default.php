<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');


$purchaseTypes = array(
		'1' => 'UNLIMITED',
		'2' => 'YEARLY',
		'3' => 'MONTHLY',
		'4' => 'WEEKLY',
		'5' => 'DAILY',
);

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$params     = (isset($this->state->params)) ? $this->state->params : new JObject;
?>
<form action="<?php echo JRoute::_('index.php?option=com_banners&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-warning alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%" class="text-xs-center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap text-xs-center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="hidden-sm-down text-xs-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_CONTACT', 'a.contact', $listDirn, $listOrder); ?>
						</th>
						<th width="3%" class="nowrap text-xs-center hidden-sm-down">
							<i class="icon-publish hasTooltip" title="<?php echo JText::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?>"></i>
						</th>
						<th width="3%" class="nowrap text-xs-center hidden-sm-down">
							<i class="icon-unpublish hasTooltip" title="<?php echo JText::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?>"></i>
						</th>
						<th width="3%" class="nowrap text-xs-center hidden-sm-down">
							<i class="icon-archive hasTooltip" title="<?php echo JText::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?>"></i>
						</th>
						<th width="3%" class="nowrap text-xs-center hidden-sm-down">
							<i class="icon-trash hasTooltip" title="<?php echo JText::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?>"></i>
						</th>
						<th width="10%" class="nowrap hidden-sm-down text-xs-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_PURCHASETYPE', 'a.purchase_type', $listDirn, $listOrder); ?>
						</th>
						<th width="3%" class="nowrap hidden-sm-down text-xs-center">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="11">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) :
						$canCreate  = $user->authorise('core.create',     'com_banners');
						$canEdit    = $user->authorise('core.edit',       'com_banners');
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
						$canChange  = $user->authorise('core.edit.state', 'com_banners') && $canCheckin;
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-xs-center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="text-xs-center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'clients.', $canChange); ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="float-xs-left">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'clients.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_banners&task=client.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->name); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->name); ?>
									<?php endif; ?>
								</div>
							</td>
							<td class="small hidden-sm-down text-xs-center">
								<?php echo $item->contact; ?>
							</td>
							<td class="text-xs-center btns hidden-sm-down">
								<a class="tag <?php if ($item->count_published > 0) echo "tag-success"; ?>" href="<?php echo JRoute::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=1'); ?>">
									<?php echo $item->count_published; ?></a>
							</td>
							<td class="text-xs-center btns hidden-sm-down">
								<a class="tag <?php if ($item->count_unpublished > 0) echo "tag-danger"; ?>" href="<?php echo JRoute::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=0'); ?>">
									<?php echo $item->count_unpublished; ?></a>
							</td>
							<td class="text-xs-center btns hidden-sm-down">
								<a class="tag <?php if ($item->count_archived > 0) echo "tag-info"; ?>" href="<?php echo JRoute::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=2'); ?>">
									<?php echo $item->count_archived; ?></a>
							</td>
							<td class="text-xs-center btns hidden-sm-down">
								<a class="tag <?php if ($item->count_trashed > 0) echo "tag-inverse"; ?>" href="<?php echo JRoute::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=-2'); ?>">
									<?php echo $item->count_trashed; ?></a>
							</td>
							<td class="small hidden-sm-down text-xs-center">
								<?php if ($item->purchase_type < 0) : ?>
									<?php echo JText::sprintf('COM_BANNERS_DEFAULT', JText::_('COM_BANNERS_FIELD_VALUE_' . $purchaseTypes[$params->get('purchase_type')])); ?>
								<?php else : ?>
									<?php echo JText::_('COM_BANNERS_FIELD_VALUE_' . $purchaseTypes[$item->purchase_type]); ?>
								<?php endif; ?>
							</td>
							<td class="hidden-sm-down text-xs-center">
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
