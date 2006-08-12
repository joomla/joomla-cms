<form action="index.php" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php if ($params->get('display')) : ?>
<tr>
	<td align="right" colspan="4">
	<?php
		echo JText::_('Display Num') .'&nbsp;';
		$link = "index.php?option=com_newsfeeds&amp;task=category&amp;catid=$catid&amp;Itemid=$Itemid";
		echo $pagination->getLimitBox($link);
	?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $params->get( 'headings' ) ) : ?>
<tr>
	<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5">
		<?php echo JText::_('Num'); ?>
	</td>
	<?php if ( $params->get( 'name' ) ) : ?>
	<td height="20" width="90%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_( 'Feed Name' ); ?>
	</td>
	<?php endif; ?>
	<?php if ( $params->get( 'articles' ) ) : ?>
	<td height="20" width="10%" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" align="center" nowrap="nowrap">
		<?php echo JText::_( 'Num Articles' ); ?>
	</td>
	<?php endif; ?>
 </tr>
<?php endif; ?>
<?php foreach ($rows as $row) : ?>
<tr class="sectiontableentry<?php echo $row->odd + 1; ?>">
	<td align="center" width="5">
		<?php echo $row->count; ?>
	</td>
	<?php if ( $params->get( 'name' ) ) : ?>
	<td height="20" width="90%">
		<a href="<?php echo $row->link; ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $row->name; ?>
		</a>
	</td>
	<?php endif; ?>
	<?php if ( $params->get( 'articles' ) ) : ?>
	<td height="20" width="10%" align="center">
		<?php echo $row->numarticles; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<tr>	
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php
		$link = "index.php?option=com_weblinks&amp;task=category&amp;catid=$catid&amp;Itemid=$Itemid";
		echo $pagination->writePagesLinks($link);
	?>
	</td>
</tr>
<tr>
	<td colspan="4" align="right">
		<?php echo $pagination->writePagesCounter(); ?>
	</td>
</tr>
</table>
</form>