<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_messages&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th style="width:1%" class="nowrap text-center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th class="title nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MESSAGES_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MESSAGES_HEADING_READ', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th style="width:15%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MESSAGES_HEADING_FROM', 'a.user_id_from', $listDirn, $listOrder); ?>
						</th>
						<th style="width:20%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.date_time', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="5">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canChange = $user->authorise('core.edit.state', 'com_messages');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->message_id); ?>
						</td>
						<td>
							<a href="<?php echo Route::_('index.php?option=com_messages&view=message&message_id=' . (int) $item->message_id); ?>">
								<?php echo $this->escape($item->subject); ?></a>
						</td>
						<td class="text-center">
							<?php echo HTMLHelper::_('messages.status', $i, $item->state, $canChange); ?>
						</td>
						<td>
							<?php echo $item->user_from; ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('date', $item->date_time, Text::_('DATE_FORMAT_LC2')); ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
