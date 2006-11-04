<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>

<form action="index.php" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td align="right" colspan="4">
	<?php if ($this->params->get('display')) :
		echo JText::_('Display Num') .'&nbsp;';
		echo $this->pagination->getLimitBox();
	endif; ?>
	</td>
</tr>
<?php if ( $this->params->def( 'headings', 1 ) ) : ?>
<tr>
	<td width="10" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_('Num'); ?>
	</td>
	<td width="90%" height="20" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php JCommonHTML::tableOrdering( 'Web Link', 'title', $this->lists ); ?>
	</td>
	<?php if ( $this->params->get( 'hits' ) ) : ?>
	
	<td width="30" height="20" class="sectiontableheader<?php echo $this->params->get( 'pageclass_sfx' ); ?>" align="right" nowrap="nowrap">
		<?php JCommonHTML::tableOrdering( 'Hits', 'hits', $this->lists ); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php foreach ($this->items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
	<td align="center">
		<?php echo $this->pagination->getRowOffset( $item->count ); ?>
	</td>
	<td height="20">
		<?php if ( $item->image ) : ?>
		&nbsp;&nbsp;<?php echo $item->image;?>&nbsp;&nbsp;
		<?php endif; ?>
		<?php echo $item->link; ?>
		<?php if ( $this->params->get( 'item_description' ) ) : ?>
		<br />
		<?php echo $item->description; ?>
		<?php endif; ?>
	</td>
	<?php if ( $this->params->get( 'hits' ) ) : ?>
	<td align="center">
		<?php echo $item->hits; ?>
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
	<td colspan="4" align="right">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
</table>
<input type="hidden" name="option" value="com_weblinks" />
<input type="hidden" name="view" value="weblink" />
<input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
<input type="hidden" name="Itemid" value="<?php echo $Itemid;?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_dir" value="" />
</form>