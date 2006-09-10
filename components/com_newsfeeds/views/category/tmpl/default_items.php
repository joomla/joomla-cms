<form action="index.php" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php if ($params->get('display')) : ?>
<tr>
	<td align="right" colspan="4">
	<?php
		echo JText::_('Display Num') .'&nbsp;';
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
<?php foreach ($items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
	<td align="center" width="5">
		<?php echo $item->count + 1; ?>
	</td>
	<?php if ( $params->get( 'name' ) ) : ?>
	<td height="20" width="90%">
		<a href="<?php echo $item->link; ?>" class="category<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $item->name; ?>
		</a>
	</td>
	<?php endif; ?>
	<?php if ( $params->get( 'articles' ) ) : ?>
	<td height="20" width="10%" align="center">
		<?php echo $item->numarticles; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php

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