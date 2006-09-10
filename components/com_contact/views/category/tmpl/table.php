<?php if ( $params->get( 'page_title' ) ) : ?>
<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ($category->name) : 
	echo $params->get('header').' - '.$category->name;
else :
	echo $params->get('header');
endif; ?>
</div>
<?php endif; ?>
<div class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if ($category->image || $category->description) : ?>
	<div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php if ($params->get('image') != -1 && $params->get('image') != '') : ?>
		<img src="images/stories/<?php echo $params->get('image'); ?>" align="<?php echo $params->get('image_align'); ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
	<?php elseif ($category->image) : ?>
		<img src="images/stories/<?php echo $category->image; ?>" align="<?php echo $category->image_position; ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
	<?php endif; ?>
	<?php echo $params->get('description_text', $category->description); ?>
	</div>
<?php endif; ?>
<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>
<form action="index.php" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<thead>
		<tr>
			<td align="right" colspan="6">
			<?php if ($params->get('display')) : 
				echo JText::_('Display Num') .'&nbsp;';
				echo $pagination->getLimitBox($link);
			endif; ?>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td align="center" colspan="6" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $pagination->writePagesLinks($link); ?>
			</td>
		</tr>
		<tr>
			<td colspan="6" align="right">
				<?php echo $pagination->writePagesCounter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php if ($params->get( 'headings' )) : ?>
		<tr>
			<td width="5" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo JText::_('Num'); ?>
			</td>
			<td height="20" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php mosCommonHTML::tableOrdering( 'Name', 'cd.name', $lists ); ?>
			</td>
			<?php if ( $params->get( 'position' ) ) : ?>
			<td height="20" class="sectiontableheader<?php echo  $params->get( 'pageclass_sfx' ); ?>">
				<?php mosCommonHTML::tableOrdering( 'Position', 'cd.con_position', $lists ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $params->get( 'email' ) ) : ?>
			<td height="20" width="20%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo JText::_( 'Email' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $params->get( 'telephone' ) ) : ?>
			<td height="20" width="15%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo JText::_( 'Phone' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $params->get( 'fax' ) ) : ?>
				<td height="20" width="15%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php echo JText::_( 'Fax' ); ?>
				</td>
			<?php endif; ?>
		</tr>
	<?php endif; ?>
	<?php echo $this->loadTemplate('items'); ?>
</tbody>
</table>

<input type="hidden" name="option" value="com_contact" />
<input type="hidden" name="catid" value="<?php echo $category->id;?>" />
<input type="hidden" name="Itemid" value="<?php echo $Itemid;?>" />
<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
</div>