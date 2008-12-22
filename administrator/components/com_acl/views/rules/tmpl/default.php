<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');

	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHtml::_('behavior.tooltip');
	$aclType = $this->state->get('list.acl_type', 1);
?>
<style type="text/css">
/* @TODO Mode to stylesheet */
.scroll {
	overflow: auto;
}
</style>

<form action="<?php echo JRoute::_('index.php?option=com_acl&view=rules');?>" method="post" name="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search"><?php echo JText::_('Search'); ?>:</label>
			<input type="text" name="fitler_search" id="search" value="<?php echo $this->state->get('filter.serach'); ?>" size="40" title="<?php echo JText::_('Search in note'); ?>" />
			<button type="submit"><?php echo JText::_('Go'); ?></button>
			<button type="button" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Clear'); ?></button>
		</div>
		<div class="right">
			<label for="filter_section">
				<?php echo JText::_('Acl Filter Show Section'); ?>
			</label>
			<?php echo JHtml::_('acl.sections', 'filter_section', $this->state->get('list.section_value'), 'class="inputbox" onchange="this.form.submit()"', $aclType); ?>

			<label for="filter_type">
				<?php echo JText::_('Acl Filter Show Acl Type'); ?>
			</label>
			<?php echo JHtml::_('acl.types', 'filter_type', $aclType, 'class="inputbox" onchange="this.form.submit()"'); ?>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items);?>)" />
				</th>
				<th class="left">
					<?php echo JHtml::_('grid.sort', 'ACL Col Note', 'a.note', $this->state->orderDirn, $this->state->orderCol); ?>
				</th>
				<th nowrap="nowrap" align="center">
					<?php echo JText::_('ACL Col User Groups'); ?>
				</th>
				<th nowrap="nowrap" align="center">
					<?php echo JText::_('ACL Col Permissions'); ?>
				</th>
				<?php if ($aclType == 2) : ?>
				<th nowrap="nowrap" align="center">
					<?php echo JText::_('ACL Col Applies to Items'); ?>
				</th>
				<?php endif; ?>
				<?php if ($aclType == 3) : ?>
				<th nowrap="nowrap" align="center">
					<?php echo JText::_('ACL Col Applies to Levels'); ?>
				</th>
				<?php endif; ?>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'ACL Col Allowed', 'a.allow', $this->state->orderDirn, $this->state->orderCol); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort', 'ACL Col Enabled', 'a.enabled', $this->state->orderDirn, $this->state->orderCol); ?>
				</th>
				<th nowrap="nowrap" width="1%" align="center">
					<?php echo JText::_('Col ID'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			$i = 0;
			foreach ($this->items as $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td style="text-align:center">
					<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_acl&task=acl.edit&id='.$item->id);?>">
						<?php echo $item->note; ?></a>
				</td>
				<td align="left" valign="top">
					<div class="scroll" style="height: 100px;">
						<?php echo JHtml::_('acl.userslist', $item->references); ?>
						<?php echo JHtml::_('acl.usergroupslist', $item->references); ?>
					</div>
				</td>
				<td align="left" valign="top">
					<div class="scroll" style="height: 100px;">
						<?php echo JHtml::_('acl.actionslist', $item->references); ?>
					</div>
				</td>

				<?php if ($aclType > 1) : ?>
				<td align="left" valign="top">
					<div class="scroll" style="height: 100px;">
						<?php
						if ($aclType == 2) :
							echo JHtml::_('acl.assetslist', $item->references);
						else :
							echo JHtml::_('acl.assetgroupslist', $item->references);
						endif; ?>
					</div>
				</td>
				<?php endif; ?>
				<td align="center">
					<?php echo JHtml::_('acl.allowed', $item->allow, $item->id); ?>
				</td>
				<td align="center">
					<?php echo JHtml::_('acl.enabled', $item->enabled, $item->id); ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<blockquote>
		<?php echo JText::_('ACL Rules Type 1 Desc'); ?>
	</blockquote>

	<input type="hidden" name="acl_type" value="<?php echo $aclType;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('orderCol'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('orderDirn'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
