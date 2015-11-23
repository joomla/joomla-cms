<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == - 2 ? true : false;
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_cjforum&task=ranks.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'rankList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
$assoc = JLanguageAssociations::isEnabled();
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}

	Joomla.submitbutton = function(pressbutton) 
	{ 
		if(pressbutton == 'ranks.sync')
		{
			jQuery('#sync-progress').show();
			doSyncRanks(0, 0, 0);
		}
		else
		{
			Joomla.submitform(pressbutton);
		}
	}

	function doSyncRanks(startId, endId, lastId)
	{
		try
		{
			startId = parseInt(startId);
			endId = parseInt(endId);
			lastId = parseInt(lastId);
		}
		catch(e){}
		
		jQuery.ajax({
			url: '<?php echo JRoute::_('index.php?option=com_cjforum&task=ranks.execute&format=json', false);?>',
			dataType: 'json',
			data: {
				'startId': startId,
				'endId': endId
			},
			beforeSend: function( xhr ) {
				jQuery('#sync-progress').show();
			}
		}).done(function(r){
			if(r.success)
			{
				if(r.data == -1)
				{
					doSyncRanks(endId + 1, endId + 250, lastId);
				}
				else
				{
					doSyncRanks(r.data.min_id, 250, r.data.max_id);
				}
			}
			else
			{
				if(endId < lastId)
				{
					// there is something wrong or data not found. continue with next
					doSyncRanks(endId + 1, endId + 250, lastId);
				}
				else
				{
					jQuery('#sync-progress').hide();
					alert(r.message);
				}
			}
			
			percent = lastId > 0 ? Math.round((endId - 250) * 100 / lastId) : 0;
			jQuery('#sync-progress').find('.bar').attr('style', 'width: '+percent+'%').find('.pct').text(percent+'%');
		})
		.fail(function(data){
			alert(data.message);
		});
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_cjforum&view=ranks'); ?>"
	method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div class="progress progress-striped" id="sync-progress" style="display: none;">
			<div class="bar" style="width: 0%;"><span class="pct"></span></div>
		</div>
		<div class="messages"></div>

		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="rankList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="hidden-phone">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width: 55px" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%">
							<?php echo JText::_('COM_CJFORUM_RANK_LABEL');; ?>
						</th>
						<th width="10%">
							<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_FIELD_RANK_TYPE_LABEL', 'a.rank_type', $listDirn, $listOrder); ?>
						</th>
						<th width="10%">
							<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_FIELD_MIN_POSTS_LABEL', 'a.min_posts', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
					<?php if ($assoc) : ?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
						</th>
					<?php endif;?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
			<?php
			foreach ($this->items as $i => $item):
				$item->max_ordering = 0; // ??
				$ordering = ($listOrder == 'a.ordering');
				$canCreate = $user->authorise('core.create', 'com_cjforum.category.' . $item->catid);
				$canEdit = $user->authorise('core.edit', 'com_cjforum.rank.' . $item->id);
				$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canEditOwn = $user->authorise('core.edit.own', 'com_cjforum.rank.' . $item->id) && $item->created_by == $userId;
				$canChange = $user->authorise('core.edit.state', 'com_cjforum.rank.' . $item->id) && $canCheckin;
				?>
					<tr class="row<?php echo $i % 2; ?>"
						sortable-group-id="<?php echo $item->catid; ?>">
						<td class="order nowrap center hidden-phone">
							<?php
								$iconClass = '';
								if (! $canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (! $saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>"> <i
								class="icon-menu"></i>
						</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display: none" name="order[]" size="5"
							value="<?php echo $item->ordering; ?>"
							class="width-20 text-area-order " />
							<?php endif; ?>
						</td>
						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'ranks.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
								<?php echo JHtml::_('cjforumadministrator.featured', $item->featured, $i, $canChange); ?>
								<?php
									// Create dropdown items
									$action = $archived ? 'unarchive' : 'archive';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'ranks');
									
									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'ranks');
									
									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
								?>
							</div>
						</td>
						<td class="has-context">
							<div class="pull-left">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'ranks.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($item->language == '*'):?>
									<?php $language = JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif;?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a
									href="<?php echo JRoute::_('index.php?option=com_cjforum&task=rank.edit&id=' . $item->id); ?>"
									title="<?php echo JText::_('JACTION_EDIT'); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<span
									title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
							</div>
						</td>
						<td>
							<?php 
							if(empty($item->rank_image))
							{
								?>
								<div class="label label-<?php echo $item->rank_class;?> rank"><?php echo $this->escape($item->title);?></div>
								<?php
							}
							else
							{
								?>
								<img src="<?php echo JUri::root(false).$this->escape($item->rank_image);?>" alt="<?php echo $item->title;?>">
								<?php
							}
							?>
						</td>
						<td><?php echo $item->rank_type == 0 ? JText::_('COM_CJFORUM_RANK_TYPE_STANDARD') : JText::_('COM_CJFORUM_RANK_TYPE_SPECIAL');?></td>
						<td><?php echo $item->min_posts;?></td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<?php if ($assoc) : ?>
						<td class="hidden-phone">
							<?php if ($item->association) : ?>
								<?php echo JHtml::_('cjforumadministrator.association', $item->id); ?>
							<?php endif; ?>
						</td>
						<?php endif;?>
						<td class="small hidden-phone">
							<?php if ($item->language == '*'):?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php else:?>
								<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif;?>
						</td>
						<td class="nowrap small hidden-phone">
							<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php //Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="task" value="" /> <input type="hidden"
				name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

</form>
