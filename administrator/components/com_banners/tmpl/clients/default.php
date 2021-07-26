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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Banners\Administrator\View\Clients\HtmlView $this */

HTMLHelper::_('behavior.multiselect');

$purchaseTypes = [
	'1' => 'UNLIMITED',
	'2' => 'YEARLY',
	'3' => 'MONTHLY',
	'4' => 'WEEKLY',
	'5' => 'DAILY',
];

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$params     = $this->state->params ?? new CMSObject;
?>
<form action="<?php echo Route::_('index.php?option=com_banners&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
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
					<table class="table">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_BANNERS_CLIENTS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'a.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-15 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CONTACT', 'a.contact', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-3 text-center d-none d-md-table-cell">
									<span class="icon-check" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?>"></span>
									<span class="visually-hidden"><?php echo Text::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?></span>
								</th>
								<th scope="col" class="w-3 text-center d-none d-md-table-cell">
									<span class="icon-times" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?>"></span>
									<span class="visually-hidden"><?php echo Text::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?></span>
								</th>
								<th scope="col" class="w-3 text-center d-none d-md-table-cell">
									<span class="icon-folder icon-fw" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?>"></span>
									<span class="visually-hidden"><?php echo Text::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?></span>
								</th>
								<th scope="col" class="w-3 text-center d-none d-md-table-cell">
									<span class="icon-trash" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?>"></span>
									<span class="visually-hidden"><?php echo Text::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?></span>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_PURCHASETYPE', 'a.purchase_type', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-3 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) :
								$canCreate  = $user->authorise('core.create',     'com_banners');
								$canEdit    = $user->authorise('core.edit',       'com_banners');
								$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || is_null($item->checked_out);
								$canChange  = $user->authorise('core.edit.state', 'com_banners') && $canCheckin;
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'clients.', $canChange); ?>
									</td>
									<th scope="row" class="has-context">
										<div>
											<?php if ($item->checked_out) : ?>
												<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'clients.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit) : ?>
												<a href="<?php echo Route::_('index.php?option=com_banners&task=client.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
													<?php echo $this->escape($item->name); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->name); ?>
											<?php endif; ?>
										</div>
									</th>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->contact; ?>
									</td>
									<td class="text-center btns d-none d-md-table-cell itemnumber">
										<a class="btn <?php echo ($item->count_published > 0) ? 'btn-success' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=1'); ?>">
											<?php echo $item->count_published; ?></a>
									</td>
									<td class="text-center btns d-none d-md-table-cell itemnumber">
										<a class="btn <?php echo ($item->count_unpublished > 0) ? 'btn-danger' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=0'); ?>">
											<?php echo $item->count_unpublished; ?></a>
									</td>
									<td class="text-center btns d-none d-md-table-cell itemnumber">
										<a class="btn <?php echo ($item->count_archived > 0) ? 'btn-info' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=2'); ?>">
											<?php echo $item->count_archived; ?></a>
									</td>
									<td class="text-center btns d-none d-md-table-cell itemnumber">
										<a class="btn <?php echo ($item->count_trashed > 0) ? 'btn-inverse' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=-2'); ?>">
											<?php echo $item->count_trashed; ?></a>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php if ($item->purchase_type < 0) : ?>
											<?php echo Text::sprintf('COM_BANNERS_DEFAULT', Text::_('COM_BANNERS_FIELD_VALUE_' . $purchaseTypes[$params->get('purchase_type')])); ?>
										<?php else : ?>
											<?php echo Text::_('COM_BANNERS_FIELD_VALUE_' . $purchaseTypes[$item->purchase_type]); ?>
										<?php endif; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $item->id; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // Load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
