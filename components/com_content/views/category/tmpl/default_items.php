<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">

	function tableOrdering( order, dir, task )
	{
		var form = document.adminForm;

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		document.adminForm.submit( task );
	}
</script>
<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php if ($this->params->get('filter') || $this->params->get('display')) : ?>
<tr>
	<td colspan="5">
		<table>
		<tr>
		<?php if ($this->params->get('filter')) : ?>
			<td align="left" width="60%" nowrap="nowrap">
				<?php echo JText::_('Filter').'&nbsp;'; ?>
				<input type="text" name="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.adminForm.submit();" />
			</td>
		<?php endif; ?>
		<?php if ($this->params->get('display')) : ?>
			<td align="right" width="40%" nowrap="nowrap">
			<?php
				echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
				echo $this->pagination->getLimitBox();
			?>
			</td>
		<?php endif; ?>
		</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->get('headings')) : ?>
<tr>
	<td class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="5">
		<?php echo JText::_('Num'); ?>
	</td>
	<?php if ($this->params->get('title')) : ?>
 	<td class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="45%">
		<?php JCommonHTML::tableOrdering( 'Item Title', 'a.title', $this->lists ); ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('date')) : ?>
	<td class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="25%">
		<?php JCommonHTML::tableOrdering( 'Date', 'a.created', $this->lists ); ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('showAuthor')) : ?>
	<td class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>"  width="20%">
		<?php JCommonHTML::tableOrdering( 'Author', 'author', $this->lists ); ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('hits')) : ?>
	<td align="center" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="5%" nowrap="nowrap">
		<?php JCommonHTML::tableOrdering( 'Hits', 'a.hits', $this->lists ); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php foreach ($this->items as $item) : ?>
<tr class="sectiontableentry<?php echo ($item->odd +1 ) . $this->params->get( 'pageclass_sfx' ); ?>" >
	<td align="center">
		<?php echo $this->pagination->getRowOffset( $item->count ); ?>
	</td>
	<?php if ($this->params->get('title')) : ?>
	<?php if ($item->access <= $this->user->get('aid', 0)) : ?>
	<td>
		<a href="<?php echo $item->link; ?>">
			<?php echo $item->title; ?></a>
			<?php $this->item = $item; echo $this->getIcon('edit') ?>
	</td>
	<?php else : ?>
	<td>
		<?php
			echo $item->title.' : ';
			$link = JRoute::_('index.php?option=com_user&task=register');
		?>
		<a href="<?php echo $link; ?>">
			<?php echo JText::_( 'Register to read more...' ); ?>
		</a>
	</td>
	<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->params->get('date')) : ?>
	<td>
		<?php echo $item->created; ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('showAuthor')) : ?>
	<td >
		<?php echo $item->created_by_alias ? $item->created_by_alias : $item->author; ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('hits')) : ?>
	<td align="center">
		<?php echo $item->hits ? $item->hits : '-'; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if ($this->params->get('navigation')) : ?>
<tr>
	<td colspan="5">&nbsp;</td>
</tr>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</td>
</tr>
<tr>
	<td colspan="5" align="right">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>

<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="sectionid" value="<?php echo $this->category->sectionid; ?>" />
<input type="hidden" name="task" value="<?php echo $this->lists['task']; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
