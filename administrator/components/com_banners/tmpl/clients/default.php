<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$purchaseTypes = array(
		'1' => 'UNLIMITED',
		'2' => 'YEARLY',
		'3' => 'MONTHLY',
		'4' => 'WEEKLY',
		'5' => 'DAILY',
);

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$params     = $this->state->params ?? new JObject;
?>
<form action="<?php echo Route::_('index.php?option=com_banners&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) : ?>
            <div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
            </div>
		<?php endif; ?>
        <div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_BANNERS_CLIENTS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'a.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:15%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CONTACT', 'a.contact', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:3%" class="text-center d-none d-md-table-cell">
                                    <span class="icon-publish hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?>">
                                        <span class="sr-only"><?php echo Text::_('COM_BANNERS_COUNT_PUBLISHED_ITEMS'); ?></span>
                                    </span>
								</th>
								<th scope="col" style="width:3%" class="text-center d-none d-md-table-cell">
                                    <span class="icon-unpublish hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?>">
                                        <span class="sr-only"><?php echo Text::_('COM_BANNERS_COUNT_UNPUBLISHED_ITEMS'); ?></span>
                                    </span>
								</th>
								<th scope="col" style="width:3%" class="text-center d-none d-md-table-cell">
                                    <span class="icon-archive hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?>">
                                        <span class="sr-only"><?php echo Text::_('COM_BANNERS_COUNT_ARCHIVED_ITEMS'); ?></span>
                                    </span>
								</th>
								<th scope="col" style="width:3%" class="text-center d-none d-md-table-cell">
                                    <span class="icon-trash hasTooltip" aria-hidden="true" title="<?php echo Text::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?>">
                                        <span class="sr-only"><?php echo Text::_('COM_BANNERS_COUNT_TRASHED_ITEMS'); ?></span>
                                    </span>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_PURCHASETYPE', 'a.purchase_type', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:3%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) :
								$canCreate  = $user->authorise('core.create',     'com_banners');
								$canEdit    = $user->authorise('core.edit',       'com_banners');
								$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
								$canChange  = $user->authorise('core.edit.state', 'com_banners') && $canCheckin;
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'clients.', $canChange); ?>
										</div>
									</td>
									<th scope="row" class="has-context">
										<div>
											<?php if ($item->checked_out) : ?>
												<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'clients.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit) : ?>
												<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_banners&task=client.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->name)); ?>">
													<?php echo $this->escape($item->name); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->name); ?>
											<?php endif; ?>
										</div>
									</th>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->contact; ?>
									</td>
									<td class="center btns d-none d-lg-table-cell">
										<a class="badge <?php echo ($item->count_published > 0) ? 'badge-success' : 'badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=1'); ?>">
											<?php echo $item->count_published; ?></a>
									</td>
									<td class="center btns d-none d-lg-table-cell">
										<a class="badge <?php echo ($item->count_unpublished > 0) ? 'badge-danger' : 'badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=0'); ?>">
											<?php echo $item->count_unpublished; ?></a>
									</td>
									<td class="center btns d-none d-lg-table-cell">
										<a class="badge <?php echo ($item->count_archived > 0) ? 'badge-info' : 'badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=2'); ?>">
											<?php echo $item->count_archived; ?></a>
									</td>
									<td class="center btns d-none d-lg-table-cell">
										<a class="badge <?php echo ($item->count_trashed > 0) ? 'badge-inverse' : 'badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_banners&view=banners&filter[client_id]=' . (int) $item->id . '&filter[published]=-2'); ?>">
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

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
