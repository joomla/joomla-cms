<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	//Ordering allowed ?
	$ordering = ($this->filter->order == 'b.ordering');
	JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php');?>" method="post" name="adminForm">

	<fieldset class="filter">
		<div class="left">
			<?php echo JText::_('Filter'); ?>:
			<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Filter Reset'); ?></button>
		</div>
		<div class="right">
			<?php
			echo JHtml::_('list.category',  'filter_catid', 'com_banner', (int) $this->filter->catid, 'onchange="document.adminForm.submit();"');
			echo JHtml::_('grid.state',  $this->filter->state);
			?>
		</div>
	</fieldset>

	<table class="adminlist">
	<thead>
		<tr>
			<th width="20">
				<?php echo JText::_('Num'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value=""  onclick="checkAll(<?php echo count($this->rows); ?>);" />
			</th>
			<th nowrap="nowrap" class="title">
				<?php echo JHtml::_('grid.sort',  'Name', 'b.name', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Client', 'c.name', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Category', 'cc.title', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Published', 'b.showBanner', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Order', 'b.ordering', @$this->filter->order_Dir, @$this->filter->order); ?>
				<?php echo JHtml::_('grid.order',  $this->rows); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Sticky', 'b.Sticky', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Impressions', 'b.impmade', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="80">
				<?php echo JHtml::_('grid.sort',   'Clicks', 'b.clicks', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JText::_('Tags'); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'ID', 'b.bid', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="13">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count($this->rows); $i < $n; $i++) {
		$row = &$this->rows[$i];

		$link		= JRoute::_('index.php?option=com_banners&task=edit&bid[]='. $row->bid);

		if ($row->imptotal <= 0) {
			$row->imptotal	=  JText::_('unlimited');
		}

		if ($row->impmade != 0) {
			$percentClicks = 100 * $row->clicks/$row->impmade;
		} else {
			$percentClicks = 0;
		}

		$row->published = $row->showBanner;
		$published		= JHtml::_('grid.published', $row, $i);
		$checked		= JHtml::_('grid.checkedout',   $row, $i, 'bid');
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_('Edit');?>::<?php echo $row->name; ?>">
				<?php
				if (JTable::isCheckedOut($this->user->get ('id'), $row->checked_out)) {
					echo $row->name;
				} else {
					?>

					<a href="<?php echo $link; ?>">
						<?php echo $row->name; ?></a>
					<?php
				}
				?>
				</span>
			</td>
			<td align="center">
				<?php echo $row->client_name;?>
			</td>
			<td align="center">
				<?php echo $row->category_name;?>
			</td>
			<td align="center">
				<?php echo $published;?>
			</td>
			<td class="order">
				<span><?php echo $this->pagination->orderUpIcon($i, ($row->category_name == @$this->rows[$i-1]->category_name), 'orderup', 'Move Up', $ordering); ?></span>
				<span><?php echo $this->pagination->orderDownIcon($i, $n, ($row->category_name == @$this->rows[$i+1]->category_name), 'orderdown', 'Move Down', $ordering); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<?php echo $row->sticky ? JText::_('Yes') : JText::_('No');?>
			</td>
			<td align="center">
				<?php echo $row->impmade.' '.JText::_('of').' '.$row->imptotal?>
			</td>
			<td align="center">
				<?php echo $row->clicks;?> -
				<?php echo sprintf('%.2f%%', $percentClicks);?>
			</td>
			<td>
				<?php echo $row->tags; ?>
			</td>
			<td align="center">
				<?php echo $row->bid; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>

<input type="hidden" name="c" value="banner" />
<input type="hidden" name="option" value="com_banners" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
