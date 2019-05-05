<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$params    = $this->params;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
?>

<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php if (empty($this->items)) : ?>
	<?php if ($params->get('show_no_users', 1)) : ?>
		<p class="com-users-users__no-users"><?php echo JText::_('COM_USERS_NO_USERS'); ?></p>
	<?php endif; ?>
<?php else : ?>
<div class="com-users-users user-list">
	<form action="<?php echo Route::_('index.php?option=com_users&view=users'); ?>" method="post" name="adminForm" id="adminForm">
		<table class="com-users-users__table users table table-striped table-bordered table-hover">
			<caption id="captionTable" class="sr-only">
				<?php echo Text::_('COM_USERS_USERS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
			</caption>
			<?php if ($params->get('show_headings')) : ?>
				<thead>
				<tr>
					<th scope="col" id="userlist_header_name">
						<?php echo HTMLHelper::_(
							'grid.sort', 'COM_USERS_USERS_NAME',
							'users.name', $listDirn, $listOrder
						); ?>

					</th>
					<?php if ($params->get('show_articles')) : ?>
						<th scope="col" id="userlist_header_articles">
							<?php echo HTMLHelper::_(
								'grid.sort', 'COM_USERS_USERS_NUMBER_ARTICLES',
								'articlesByUser', $listDirn, $listOrder
							); ?>
						</th>
					<?php endif; ?>
					<?php if ($params->get('show_last_visited')) : ?>
						<th scope="col" id="userlist_header_lastvisit">
							<?php echo HTMLHelper::_(
								'grid.sort', 'COM_USERS_USERS_LASTVISIT',
								'users.lastvisitDate', $listDirn, $listOrder
							); ?>
						</th>
					<?php endif; ?>
					<?php if ($params->get('show_online_status')) : ?>
						<th scope="col" id="userlist_header_onlinestatus">
							<?php echo HTMLHelper::_(
								'grid.sort', 'COM_USERS_USERS_ONLINE_STATUS',
								'session.time', $listDirn, $listOrder
							); ?>
						</th>
					<?php endif; ?>
				</tr>
				</thead>
			<?php endif; ?>
			<tbody>
			<?php foreach ($this->items as $i => $item): ?>

				<tr class="cat-list-row<?php $i % 2; ?>">
					<td>
						<a href="<?php echo Route::_('index.php?option=com_users&view=user&id=' . $item->id); ?>">
							<?php echo $this->escape($item->name); ?>
						</a>
					</td>
					<?php if ($params->get('show_articles')) : ?>
						<td>
							<?php echo $item->articlesByUser; ?>
						</td>
					<?php endif; ?>
					<?php if ($params->get('show_last_visited')) : ?>
						<td>
							<?php echo HTMLHelper::_(
								'date', $item->lastvisitDate,
								$this->escape(
									$params->get('date_format', Text::_('DATE_FORMAT_LC1'))
								)
							); ?>
						</td>
					<?php endif; ?>
					<?php if ($params->get('show_online_status')) : ?>
						<td>
							<?php if ($item->time): ?>
								<span class="badge badge-success">
									<?php echo Text::_('COM_USERS_ONLINE'); ?>
								</span>
							<?php else: ?>
								<span class="badge badge-warning">
									<?php echo Text::_('COM_USERS_OFFLINE'); ?>
								</span>
							<?php endif; ?>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="limitstart" value="">
		<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->state->get('list.ordering')); ?>">
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->state->get('list.direction')); ?>">

		<?php // Add pagination links ?>
		<?php if (!empty($this->items)) : ?>
			<?php if (($this->params->def('show_pagination', 2) == 1 || ($this->params->get('show_pagination') == 2))
				&& ($this->pagination->pagesTotal > 1)) : ?>
				<div class="com-users-group__navigation w-100">
					<?php if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="com-users-group__counter counter float-right pt-3 pr-2">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif; ?>
					<div class="com-users-group__pagination">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

	</form>
	<?php endif; ?>
</div>
