<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	if ($this->params->get('show_snapshot'))
		JHtml::_('weblink.snapshotinit', $this->params->get('snapshot_width'), $this->params->get('snapshot_height'));
	else
		JHtml::_('behavior.tooltip');
?>

<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>

<form action="<?php echo $this->action; ?>" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php if ($this->params->get( 'show_display_num' )): ?>
<tr>
	<td align="right" colspan="4">
	<?php
		echo JText::_('Display Num') .'&nbsp;';
		echo $this->pagination->getLimitBox();
	?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->params->def( 'show_headings', 1 ) ) : ?>
<tr>
	<?php if ($this->params->get( 'show_numbers' )): ?>
	<td width="10" style="text-align:right;" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_('Num'); ?>
	</td>
	<?php endif; ?>
	<td width="90%" height="20" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo JHtml::_('grid.sort',  'Web Link', 'title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
	</td>
	<?php if ( $this->params->get( 'show_link_hits' ) ) : ?>

	<td width="30" height="20" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="text-align:center;" nowrap="nowrap">
		<?php echo JHtml::_('grid.sort',  'Hits', 'hits', $this->lists['order_Dir'], $this->lists['order'] ); ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get( 'show_report' )): ?>
	<td width="10" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_('Report'); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php foreach ($this->items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
	<?php if ($this->params->get( 'show_numbers' )): ?>
	<td align="right">
		<?php echo $this->pagination->getRowOffset( $item->count ); ?>
	</td>
	<?php endif; ?>
	<td height="20">
		<?php if ( $item->image ) : ?>
		&nbsp;&nbsp;<?php echo $item->image;?>&nbsp;&nbsp;
		<?php endif; ?>
		<span id="<?php echo $item->url_snapshot; ?>" class="<?php echo $this->params->get('show_snapshot') ? 'hasSnapshot' : 'hasTip' ?>" title="<?php echo $item->title; ?>::<?php echo $item->description; ?>">
		<?php echo $item->link; ?>
		</span>
		<?php if ( $this->params->get( 'show_link_description' ) ) : ?>
		<br /><span class="description"><?php echo nl2br($item->description); ?></span>
		<?php endif; ?>
	</td>
	<?php if ( $this->params->get( 'show_link_hits' ) ) : ?>
	<td align="center">
		<?php echo $item->hits; ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get( 'show_report' )): ?>
	<td align="center">
		<a href="<?php echo JRoute::_($item->report_link); ?>"><?php echo JHtml::_('image.site', 'report', null, null, null, JText::_('Report this link')); ?></a>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->pagination->getPagesLinks(); ?>
	</td>
</tr>
<tr>
	<td colspan="4" align="right" class="pagecounter">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
</table>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>