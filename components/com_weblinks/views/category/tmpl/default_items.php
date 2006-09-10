<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>

<form action="index.php?option=com_weblinks&amp;task=category&amp;catid=<?php echo $catid;?>&amp;Itemid=<?php echo $Itemid;?>" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td align="right" colspan="4">
	<?php if ($params->get('display')) :
		echo JText::_('Display Num') .'&nbsp;';
		echo $pagination->getLimitBox($link);
	endif; ?>
	</td>
</tr>
<?php if ( $params->def( 'headings', 1 ) ) : ?>
<tr>
	<td width="10" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_('Num'); ?>
	</td>
	<td width="90%" height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php mosCommonHTML::tableOrdering( 'Web Link', 'title', $lists ); ?>
	</td>
	<?php if ( $this->params->get( 'hits' ) ) : ?>
	<td width="30" height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" align="right" nowrap="nowrap">
		<?php mosCommonHTML::tableOrdering( 'Hits', 'hits', $lists ); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php foreach ($items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
	<td align="center">
		<?php echo $pagination->rowNumber( $item->count ); ?>
	</td>
	<td height="20">
		<?php if ( $item->image ) : ?>
		&nbsp;&nbsp;<?php echo $item->image;?>&nbsp;&nbsp;
		<?php endif; ?>
		<?php echo $item->link; ?>
		<?php if ( $params->get( 'item_description' ) ) : ?>
		<br />
		<?php echo $item->description; ?>
		<?php endif; ?>
	</td>
	<?php if ( $params->get( 'hits' ) ) : ?>
	<td align="center">
		<?php echo $item->hits; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php echo $pagination->writePagesLinks($link); ?>
	</td>
</tr>
<tr>
	<td colspan="4" align="right">
		<?php echo $pagination->writePagesCounter(); ?>
	</td>
</tr>
</table>

<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
<input type="hidden" name="filter_order_dir" value="" />
</form>