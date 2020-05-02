<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
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

$user       = Factory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_users&view=notes'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
				<table class="table">
					<caption id="captionTable" class="sr-only">
						<?php echo Text::_('COM_USERS_NOTES_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
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
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:20%" class="d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_USER', 'u.name', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_REVIEW', 'a.review_time', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:1%" class="d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$canEdit    = $user->authorise('core.edit',       'com_users.category.' . $item->catid);
						$canCheckin = $user->authorise('core.admin',      'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
						$canChange  = $user->authorise('core.edit.state', 'com_users.category.' . $item->catid) && $canCheckin;
						$subject    = $item->subject ?: Text::_('COM_USERS_EMPTY_SUBJECT');
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center checklist">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="text-center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'notes.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
							</td>
							<th scope="row">
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'notes.', $canCheckin); ?>
								<?php endif; ?>
								<?php $subject = $item->subject ?: Text::_('COM_USERS_EMPTY_SUBJECT'); ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_users&task=note.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($subject)); ?>">
										<?php echo $this->escape($subject); ?></a>
								<?php else : ?>
									<?php echo $this->escape($subject); ?>
								<?php endif; ?>
								<div class="small">
									<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
								</div>
							</th>
							<td class="d-none d-md-table-cell">
								<?php echo $this->escape($item->user_name); ?>
							</td>
							<td class="d-none d-md-table-cell">
								<?php if ($item->review_time !== null) : ?>
									<?php echo HTMLHelper::_('date', $item->review_time, Text::_('DATE_FORMAT_LC4')); ?>
								<?php else : ?>
									<?php echo Text::_('COM_USERS_EMPTY_REVIEW'); ?>
								<?php endif; ?>
							</td>
							<td class="d-none d-md-table-cell">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<?php // load the pagination. ?>
				<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>

				<div>
					<input type="hidden" name="task" value="">
					<input type="hidden" name="boxchecked" value="0">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</div>
</form>
