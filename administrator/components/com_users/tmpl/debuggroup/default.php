<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_users&view=debuggroup&group_id=' . (int) $this->state->get('group_id')); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="table-responsive">
			<table class="table">
				<caption class="visually-hidden">
					<?php echo Text::_('COM_USERS_DEBUG_GROUP_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				</caption>
				<thead>
					<tr>
						<th scope="col">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ASSET_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th scope="col">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ASSET_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<?php foreach ($this->actions as $key => $action) : ?>
						<th scope="col" class="w-6 text-center">
							<?php echo Text::_($key); ?>
						</th>
						<?php endforeach; ?>
						<th scope="col" class="w-6">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_LFT', 'a.lft', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-3">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row0">
							<th scope="row">
								<?php echo $this->escape(Text::_($item->title)); ?>
							</th>
							<td>
								<?php echo LayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level + 1)) . $this->escape($item->name); ?>
							</td>
							<?php foreach ($this->actions as $action) : ?>
								<?php
								$name  = $action[0];
								$check = $item->checks[$name];
								if ($check === true) :
									$class  = 'text-success icon-check';
									$button = 'btn-success';
									$text   = Text::_('COM_USERS_DEBUG_EXPLICIT_ALLOW');
								elseif ($check === false) :
									$class  = 'text-danger icon-times';
									$button = 'btn-danger';
									$text   = Text::_('COM_USERS_DEBUG_EXPLICIT_DENY');
								elseif ($check === null) :
									$class  = 'text-danger icon-minus-circle';
									$button = 'btn-warning';
									$text   = Text::_('COM_USERS_DEBUG_IMPLICIT_DENY');
								else :
									$class  = '';
									$button = '';
									$text   = '';
								endif;
								?>
							<td class="text-center">
								<span class="<?php echo $class; ?>" aria-hidden="true"></span>
								<span class="visually-hidden"> <?php echo $text; ?></span>
							</td>
							<?php endforeach; ?>
							<td>
								<?php echo (int) $item->lft; ?>
								- <?php echo (int) $item->rgt; ?>
							</td>
							<td>
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="legend">
				<span class="text-danger icon-minus-circle" aria-hidden="true"></span>&nbsp;<?php echo Text::_('COM_USERS_DEBUG_IMPLICIT_DENY'); ?>&nbsp;
				<span class="text-success icon-check" aria-hidden="true"></span>&nbsp;<?php echo Text::_('COM_USERS_DEBUG_EXPLICIT_ALLOW'); ?>&nbsp;
				<span class="text-danger icon-times" aria-hidden="true"></span>&nbsp;<?php echo Text::_('COM_USERS_DEBUG_EXPLICIT_DENY'); ?>
			</div>

			<?php // load the pagination. ?>
			<?php echo $this->pagination->getListFooter(); ?>

		</div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
