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
		$link = "index.php?option=com_weblinks&amp;task=category&amp;catid=$catid&amp;Itemid=$Itemid";
		echo $page->getLimitBox($link);
	endif; ?>
	</td>
</tr>
<?php if ( $params->get( 'headings' ) ) : ?>
<tr>
	<td width="10" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_('Num'); ?>
	</td>
	<?php if ( $image ) : ?>
	<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
		&nbsp;
	</td>
	<?php endif; ?>
	<td width="90%" height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php mosCommonHTML::tableOrdering( 'Web Link', 'title', $lists ); ?>
	</td>
	<?php if ( $params->get( 'hits' ) ) : ?>
	<td width="30" height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" align="right" nowrap="nowrap">
		<?php mosCommonHTML::tableOrdering( 'Hits', 'hits', $lists ); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php foreach ($rows as $row) : ?>
<tr class="sectiontableentry<?php echo $row->odd + 1; ?>">
	<td align="center">
		<?php echo $page->rowNumber( $row->count ); ?>
	</td>
	<?php if ( $image ) : ?>
	<td width="100" height="20" align="center">
		&nbsp;&nbsp;<?php echo $image;?>&nbsp;&nbsp;
	</td>
	<?php endif; ?>
	<td height="20">
		<?php echo $row->link; ?>
		<?php if ( $params->get( 'item_description' ) ) : ?>
		<br />
		<?php echo $row->description; ?>
		<?php endif; ?>
	</td>
	<?php if ( $params->get( 'hits' ) ) : ?>
	<td align="center">
		<?php echo $row->hits; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php
		$link = "index.php?option=com_weblinks&amp;task=category&amp;catid=$catid&amp;Itemid=$Itemid";
		echo $page->writePagesLinks($link);
	?>
	</td>
</tr>
<tr>
	<td colspan="4" align="right">
		<?php echo $page->writePagesCounter(); ?>
	</td>
</tr>
</table>

<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>