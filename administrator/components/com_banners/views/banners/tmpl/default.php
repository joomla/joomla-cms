<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.tabstate');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_banners&task=banners.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
	JHtml::_('draggablelist.draggable');
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_banners&view=banners'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
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
					<table class="table table-striped" id="articleList">
						<thead>
							<tr>
								<th width="1%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th width="1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th width="1%" class="nowrap text-center">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
								</th>
								<th width="10%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_STICKY', 'a.sticky', $listDirn, $listOrder); ?>
								</th>
								<th width="10%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'client_name', $listDirn, $listOrder); ?>
								</th>
								<th width="10%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_IMPRESSIONS', 'impmade', $listDirn, $listOrder); ?>
								</th>
								<th width="10%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_BANNERS_HEADING_CLICKS', 'clicks', $listDirn, $listOrder); ?>
								</th>
								<th width="10%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
								</th>
								<th width="5%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="13">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
							<?php foreach ($this->items as $i => $item) :
								$ordering  = ($listOrder == 'ordering');
								$item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_banners&task=edit&type=other&cid[]=' . $item->catid);
								$canCreate  = $user->authorise('core.create',     'com_banners.category.' . $item->catid);
								$canEdit    = $user->authorise('core.edit',       'com_banners.category.' . $item->catid);
								$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
								$canChange  = $user->authorise('core.edit.state', 'com_banners.category.' . $item->catid) && $canCheckin;
								?>
								<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->catid; ?>">
									<td class="order nowrap text-center hidden-sm-down">
										<?php
										$iconClass = '';

										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler <?php echo $iconClass ?>">
											<span class="icon-menu"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5"
												value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php echo JHtml::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo JHtml::_('jgrid.published', $item->state, $i, 'banners.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
										</div>
									</td>
									<td class="nowrap has-context">
										<div class="float-left">
											<?php if ($item->checked_out) : ?>
												<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'banners.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit) : ?>
												<a href="<?php echo JRoute::_('index.php?option=com_banners&task=banner.edit&id=' . (int) $item->id); ?>">
													<?php echo $this->escape($item->name); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->name); ?>
											<?php endif; ?>
											<span class="small">
												<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
											</span>
											<div class="small">
												<?php echo JText::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
											</div>
										</div>
									</td>
									<td class="text-center hidden-sm-down text-center">
										<?php echo JHtml::_('banner.pinned', $item->sticky, $i, $canChange); ?>
									</td>
									<td class="small hidden-sm-down text-center">
										<?php echo $item->client_name; ?>
									</td>
									<td class="small hidden-sm-down text-center">
										<?php echo JText::sprintf('COM_BANNERS_IMPRESSIONS', $item->impmade, $item->imptotal ? $item->imptotal : JText::_('COM_BANNERS_UNLIMITED')); ?>
									</td>
									<td class="small hidden-sm-down text-center">
										<?php echo $item->clicks; ?> -
										<?php echo sprintf('%.2f%%', $item->impmade ? 100 * $item->clicks / $item->impmade : 0); ?>
									</td>
									<td class="small nowrap hidden-sm-down text-center">
										<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
									</td>
									<td class="hidden-sm-down text-center">
										<?php echo $item->id; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_banners')
						&& $user->authorise('core.edit', 'com_banners')
						&& $user->authorise('core.edit.state', 'com_banners')) : ?>
						<?php echo JHtml::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title' => JText::_('COM_BANNERS_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer')
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
