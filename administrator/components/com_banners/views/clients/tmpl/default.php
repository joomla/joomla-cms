<?php defined('_JEXEC') or die('Restricted access');
	JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

	<fieldset class="filter">
		<div class="left">
			<?php echo JText::_('Filter'); ?>:
			<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('Num'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" />
				</th>
				<th nowrap="nowrap" class="title">
					<?php echo JHtml::_('grid.sort',   'Client Name', 'a.name', @$this->filter->order_Dir, @$this->filter->order); ?>
				</th>
				<th nowrap="nowrap" class="title">
					<?php echo JHtml::_('grid.sort',   'Contact', 'a.contact', @$this->filter->order_Dir, @$this->filter->order); ?>
				</th>
				<th align="center" nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort',   'No. of Active Banners', 'bid', @$this->filter->order_Dir, @$this->filter->order); ?>
				</th>
				<th width="1%" nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort',   'ID', 'a.cid', @$this->filter->order_Dir, @$this->filter->order); ?>
				</th>
				<th width="40%">
					&nbsp;
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		for ($i=0, $n=count($this->items); $i < $n; $i++) :
			$row = &$this->items[$i];

			$row->id		= $row->cid;
			$link			= JRoute::_('index.php?option=com_banners&c=client&task=edit&cid[]='. $row->id);

			$checked		= JHtml::_('grid.checkedout',   $row, $i);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php
					if ( JTable::isCheckedOut($this->user->get ('id'), $row->checked_out)) {
						echo $row->name;
					} else {
						?>
							<span class="editlinktip hasTip" title="<?php echo JText::_('Edit');?>::<?php echo $row->name; ?>">
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?></a>
							</span>
						<?php
					}
					?>
				</td>
				<td>
					<?php echo $row->contact; ?>
				</td>
				<td align="center">
					<?php echo $row->nbanners;?>
				</td>
				<td align="center">
					<?php echo $row->cid; ?>
				</td>
				<td>
					&nbsp;
				</td>
			</tr>
			<?php endfor; ?>
		</tbody>
	</table>

	<input type="hidden" name="c" value="client" />
	<input type="hidden" name="option" value="com_banners" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
