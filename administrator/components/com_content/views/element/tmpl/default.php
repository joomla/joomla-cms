<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=id'); ?>" method="post" name="adminForm">

	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('Filter'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
				<button onclick="this.form.getElementById('search').value='';this.form.getElementById('filter_catid').value=0;this.form.submit();"><?php echo JText::_('Reset'); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo JHtml::_('contentgrid.category', 'filter_catid', $this->filter->catid);
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_('Num'); ?>
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort',   'Title', 'c.title', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th width="2%" class="title">
				<?php echo JHtml::_('grid.sort',   'ID', 'c.id', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th  class="title" width="30%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Category', 'cc.title', @$this->filter->order_Dir, @$this->filter->order); ?>
			</th>
			<th align="center" width="10">
				<?php echo JHtml::_('grid.sort',   'Date', 'c.created', @$this->filter->order_Dir, @$this->filter->order); ?>
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
	$k = 0;
	for ($i=0, $n=count($this->rows); $i < $n; $i++)
	{
		$row = &$this->rows[$i];

		$link 	= '';
		$date	= JHtml::_('date',  $row->created, JText::_('DATE_FORMAT_LC4'));
		$access	= JHtml::_('grid.access',   $row, $i, $row->state);
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<a style="cursor: pointer;" onclick="window.parent.jSelectArticle('<?php echo $row->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$row->title); ?>', '<?php echo JRequest::getVar('object'); ?>');">
					<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?></a>
			</td>
			<td>
				<?php echo $row->id; ?>
			</td>
			<td>
				<?php echo $row->cctitle; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo $date; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>

<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>