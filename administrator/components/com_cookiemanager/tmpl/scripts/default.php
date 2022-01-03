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

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

$positions = array (
		'1' => Text::_('COM_COOKIEMANAGER_SCRIPT_POSITION_AFTER_BEGIN_HEAD'),
		'2' => Text::_('COM_COOKIEMANAGER_SCRIPT_POSITION_BEFORE_END_HEAD'),
		'3' => Text::_('COM_COOKIEMANAGER_SCRIPT_POSITION_AFTER_BEGIN_BODY'),
		'4' => Text::_('COM_COOKIEMANAGER_SCRIPT_POSITION_BEFORE_END_BODY')
);

$types = array (
		'1' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_SCRIPT'),
		'2' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_EXTERNAL_SCRIPT'),
		'3' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_IFRAME'),
		'4' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_EMBED'),
		'5' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_OBJECT'),
		'6' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_IMG'),
		'7' => Text::_('COM_COOKIEMANAGER_SCRIPT_TYPE_LINK')
);
?>

<form action="<?php echo Route::_('index.php?option=com_cookiemanager&view=scripts'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
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
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_COOKIEMANAGER_FIELD_POSITION_LABEL', 'a.position', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_COOKIEMANAGER_FIELD_TYPE_LABEL', 'a.type', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JCATEGORY', 'a.category_title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody class="js-draggable" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"><?php endif; ?>
						<?php
						$n = count($this->items);
						foreach ($this->items as $i => $item) :
							$canCreate  = $user->authorise('core.create',     'com_cookiemanager.category.' . $item->catid);
							$canEdit    = $user->authorise('core.edit',       'com_cookiemanager.category.' . $item->catid);
							$canEditOwn = $user->authorise('core.edit.own',   'com_cookiemanager.category.' . $item->catid);
							$canChange  = $user->authorise('core.edit.state', 'com_cookiemanager.category.' . $item->catid);

							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
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
									<span class="sortable-handler<?php echo $iconClass; ?>">
										<span class="icon-ellipsis-v" aria-hidden="true"></span>
									</span>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'scripts.', $canChange, 'cb'); ?>
								</td>
								<th scope="row" class="has-context">
									<div>
										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo Route::_('index.php?option=com_cookiemanager&task=script.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
												<?php echo $this->escape($item->title); ?></a>
										<?php else : ?>
											<?php echo $this->escape($item->title); ?>
										<?php endif; ?>
										<span class="small">
											<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
										</span>
									</div>
								</th>
								<td class="text-center">
									<?php echo $positions[$item->position]; ?>
								</td>
								<td class="text-center">
									<?php echo $types[$item->type]; ?>
								</td>
								<td class="text-center">
									<?php echo $item->category_title; ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
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
