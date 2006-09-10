<?php if ( ( $contact->params->get( 'address_check' ) > 0 ) &&  ( $contact->address || $contact->suburb  || $contact->state || $contact->country || $contact->postcode ) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<?php if ( $contact->params->get( 'address_check' ) > 0 ) : ?>
<tr>
	<td rowspan="6" valign="top" width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $contact->params->get( 'marker_address' ); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->address && $contact->params->get( 'street_address' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo nl2br($contact->address); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->suburb && $contact->params->get( 'suburb' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $contact->suburb; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->state && $contact->params->get( 'state' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $contact->state; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->country && $params->get( 'country' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $contact->country; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->postcode && $contact->params->get( 'postcode' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $contact->postcode; ?>
	</td>
</tr>
<?php endif; ?>
</table>
<br />
<?php endif; ?>
<?php if ( ($contact->email_to && $contact->params->get( 'email' )) || $contact->telephone  || $contact->fax ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<?php if ( $contact->email_to && $contact->params->get( 'email' ) ) : ?>
<tr>
	<td width="<?php echo $contact->params->get( 'column_width' ); ?>" >
		<?php echo $params->get( 'marker_email' ); ?>
	</td>
	<td>
		<?php echo $contact->email_to; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->telephone && $contact->params->get( 'telephone' ) ) : ?>
<tr>
	<td width="<?php echo $contact->params->get( 'column_width' ); ?>" >
		<?php echo $contact->params->get( 'marker_telephone' ); ?>
	</td>
	<td>
		<?php echo nl2br($contact->telephone); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->fax && $contact->params->get( 'fax' ) ) : ?>
<tr>
	<td width="<?php echo $contact->params->get( 'column_width' ); ?>" >
		<?php echo $contact->params->get( 'marker_fax' ); ?>
	</td>
	<td>
		<?php echo nl2br($contact->fax); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->mobile ) :?>
<tr>
	<td width="<?php echo $contact->params->get( 'column_width' ); ?>" >
	</td>
	<td>
		<?php echo nl2br($contact->mobile); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $contact->webpage ) : ?>
<tr>
	<td width="<?php echo $params->get( 'column_width' ); ?>" >
	</td>
	<td>
		<a href="<?php echo $contact->webpage; ?>" target="_blank">
			<?php echo $contact->webpage; ?>	
		</a>
	</td>
</tr>
<?php endif; ?>
</table>
<?php endif; ?>
<br />
<?php if ( $contact->misc && $contact->params->get( 'misc' ) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td width="<?php echo $params->get( 'column_width' ); ?>" valign="top" >
		<?php echo $contact->params->get( 'marker_misc' ); ?>
	</td>
	<td>
		<?php echo $contact->misc; ?>
	</td>
</tr>
</table>
<br />
<?php endif; ?>