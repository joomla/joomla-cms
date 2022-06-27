<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_messages&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table">
				<caption class="visually-hidden">
					<?php echo Text::_('COM_MESSAGES_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				</caption>
				<thead>
					<tr>
						<td class="w-1 text-center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</td>
						<th scope="col" class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MESSAGES_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-1 text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MESSAGES_HEADING_READ', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-15">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_MESSAGES_HEADING_FROM', 'a.user_id_from', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-20 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.date_time', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canChange = $user->authorise('core.edit.state', 'com_messages');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->message_id, false, 'cid', 'cb', $item->subject); ?>
						</td>
						<th scope="row">
							<a href="<?php echo Route::_('index.php?option=com_messages&view=message&message_id=' . (int) $item->message_id); ?>">
								<?php echo $this->escape($item->subject); ?></a>
						</th>
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

			<?php // load the pagination. ?>
			<?php echo $this->pagination->getListFooter(); ?>

		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
