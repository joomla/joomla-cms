<?php
/** $Id$ */
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<?php foreach($this->items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
	<td align="right" width="5">
		<?php echo $item->count +1; ?>
	</td>
	<td height="20">
		<a href="<?php echo $item->link; ?>" class="category<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $item->name; ?></a>
	</td>
	<?php if ( $this->params->get( 'show_position' ) ) : ?>
	<td>
		<?php echo $item->con_position; ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_email' ) ) : ?>
	<td width="20%">
		<?php echo $item->email_to; ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_telephone' ) ) : ?>
	<td width="15%">
		<?php echo $item->telephone; ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_mobile' ) ) : ?>
	<td width="15%">
		<?php echo $item->mobile; ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_fax' ) ) : ?>
	<td width="15%">
		<?php echo $item->fax; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
