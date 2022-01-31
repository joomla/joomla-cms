<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Banners\Administrator\View\Banners\HtmlView $this */

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_banners&task=banners.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_banners&view=banners'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="bannerList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_BANNERS_BANNERS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_STICKY', 'a.sticky', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'client_name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_IMPRESSIONS', 'impmade', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CLICKS', 'clicks', $listDirn, $listOrder); ?>
								</th>
								<?php if (Multilanguage::isEnabled()) : ?>
									<th scope="col" class="w-10 d-none d-md-table-cell">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
							<?php foreach ($this->items as $i => $item) :
								$ordering  = ($listOrder == 'ordering');
								$item->cat_link = Route::_('index.php?option=com_categories&extension=com_banners&task=edit&type=other&cid[]=' . $item->catid);
								$canCreate  = $user->authorise('core.create',     'com_banners.category.' . $item->catid);
								$canEdit    = $user->authorise('core.edit',       'com_banners.category.' . $item->catid);
								$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || is_null($item->checked_out);
								$canChange  = $user->authorise('core.edit.state', 'com_banners.category.' . $item->catid) && $canCheckin;
								?>
								<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->catid; ?>">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
									</td>
									<td class="text-center d-none d-md-table-cell">
										<?php
										$iconClass = '';

										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler <?php echo $iconClass ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" name="order[]" size="5"
												value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'banners.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									</td>
									<th scope="row">
										<div class="break-word">
											<?php if ($item->checked_out) : ?>
												<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'banners.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit) : ?>
												<a href="<?php echo Route::_('index.php?option=com_banners&task=banner.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
													<?php echo $this->escape($item->name); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->name); ?>
											<?php endif; ?>
											<div class="small break-word">
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
											</div>
											<div class="small">
												<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
											</div>
										</div>
									</th>
									<td class="text-center d-none d-md-table-cell">
										<?php echo HTMLHelper::_('banner.pinned', $item->sticky, $i, $canChange); ?>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->client_name; ?>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo Text::sprintf('COM_BANNERS_IMPRESSIONS', $item->impmade, $item->imptotal ?: Text::_('COM_BANNERS_UNLIMITED')); ?>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->clicks; ?> -
										<?php echo sprintf('%.2f%%', $item->impmade ? 100 * $item->clicks / $item->impmade : 0); ?>
									</td>
									<?php if (Multilanguage::isEnabled()) : ?>
										<td class="small d-none d-md-table-cell">
											<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
										</td>
									<?php endif; ?>
									<td class="d-none d-md-table-cell">
										<?php echo $item->id; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // Load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_banners')
						&& $user->authorise('core.edit', 'com_banners')
						&& $user->authorise('core.edit.state', 'com_banners')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							[
								'title' => Text::_('COM_BANNERS_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer')
							],
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
