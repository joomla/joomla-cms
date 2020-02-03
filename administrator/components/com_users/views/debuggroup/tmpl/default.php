<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$colSpan   = 4 + count($this->actions);
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=debuggroup&group_id=' . (int) $this->state->get('group_id')); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_ASSET_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_ASSET_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<?php foreach ($this->actions as $key => $action) : ?>
					<th width="5%" class="center">
						<span class="hasTooltip" title="<?php echo JHtml::_('tooltipText', $key, $action[1]); ?>"><?php echo JText::_($key); ?></span>
					</th>
					<?php endforeach; ?>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_LFT', 'a.lft', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $colSpan; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<tr class="row0">
						<td>
							<?php echo $this->escape($item->title); ?>
						</td>
						<td class="nowrap">
							<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level + 1)) . $this->escape($item->name); ?>
						</td>
						<?php foreach ($this->actions as $action) : ?>
							<?php
							$name  = $action[0];
							$check = $item->checks[$name];
							if ($check === true) :
								$class  = 'icon-ok';
								$button = 'btn-success';
							elseif ($check === false) :
								$class  = 'icon-remove';
								$button = 'btn-danger';
							elseif ($check === null) :
								$class  = 'icon-ban-circle';
								$button = 'btn-warning';
							else :
								$class  = '';
								$button = '';
							endif;
							?>
						<td class="center">
							<span class="icon-white <?php echo $class; ?>"></span>
						</td>
						<?php endforeach; ?>
						<td class="center">
							<?php echo (int) $item->lft; ?>
							- <?php echo (int) $item->rgt; ?>
						</td>
						<td class="center">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
		<div>
			<?php echo JText::_('COM_USERS_DEBUG_LEGEND'); ?>
			<span class="icon-white icon-ban-circle"></span><?php echo JText::_('COM_USERS_DEBUG_IMPLICIT_DENY'); ?>&nbsp;
			<span class="icon-white icon-ok"></span><?php echo JText::_('COM_USERS_DEBUG_EXPLICIT_ALLOW'); ?>&nbsp;
			<span class="icon-white icon-remove"></span><?php echo JText::_('COM_USERS_DEBUG_EXPLICIT_DENY'); ?>
			<br /><br />
		</div>
	</div>
</form>
