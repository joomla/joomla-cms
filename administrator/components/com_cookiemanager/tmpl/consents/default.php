<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.id'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.id' && strtolower($listDirn) == 'desc');

?>

<form action="<?php echo Route::_('index.php?option=com_cookiemanager&view=consents'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="cookieList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_COOKIEMANAGER_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col">
									<?php echo Text::_('COM_COOKIEMANAGER_FIELD_CCUUID_LABEL'); ?>
								</th>
								<th scope="col" class="text-center d-none d-md-table-cell">
									<?php echo Text::_('COM_COOKIEMANAGER_FIELD_UUID_LABEL'); ?>
								</th>
								<th scope="col" class="text-center d-none d-md-table-cell">
									<?php echo Text::_('COM_COOKIEMANAGER_FIELD_CONSENT_DATE_LABEL'); ?>
								</th>
								<th scope="col" class="w-5 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody class="js-draggable" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"><?php endif; ?>
						<?php
						$n = count($this->items);
						foreach ($this->items as $i => $item) :
							$canCreate  = $user->authorise('core.create');
							$canEdit    = $user->authorise('core.edit');
							$canEditOwn = $user->authorise('core.edit.own');
							$canChange  = $user->authorise('core.edit.state');

							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->uuid); ?>
								</td>
								<th scope="row" class="has-context w-20">
									<div>
										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo Route::_('index.php?option=com_cookiemanager&task=consent.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->ccuuid); ?>">
													<?php echo $item->ccuuid; ?></a>

										<?php else : ?>
											<?php echo $item->ccuuid; ?>
										<?php endif; ?>
									</div>
								</th>
								<td class="text-center d-none d-md-table-cell">
									<?php echo $item->uuid; ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php echo $item->consent_date; ?>
								</td>
								<td class="text-center w-5">
									<?php echo $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
