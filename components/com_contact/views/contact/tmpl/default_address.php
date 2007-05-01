<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ( ( $this->contact->params->get( 'address_check' ) > 0 ) &&  ( $this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode ) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<?php if ( $this->contact->params->get( 'address_check' ) > 0 ) : ?>
<tr>
	<td rowspan="6" valign="top" width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_address' ); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->address && $this->contact->params->get( 'show_street_address' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo nl2br($this->contact->address); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->suburb && $this->contact->params->get( 'show_suburb' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $this->contact->suburb; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->state && $this->contact->params->get( 'show_state' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $this->contact->state; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->postcode && $this->contact->params->get( 'show_postcode' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $this->contact->postcode; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->country && $this->contact->params->get( 'show_country' ) ) : ?>
<tr>
	<td valign="top">
		<?php echo $this->contact->country; ?>
	</td>
</tr>
<?php endif; ?>
</table>
<br />
<?php endif; ?>
<?php if ( ($this->contact->email_to && $this->contact->params->get( 'show_email' )) || $this->contact->telephone  || $this->contact->fax ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<?php if ( $this->contact->email_to && $this->contact->params->get( 'show_email' ) ) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_email' ); ?>
	</td>
	<td>
		<?php echo $this->contact->email_to; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->telephone && $this->contact->params->get( 'show_telephone' ) ) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_telephone' ); ?>
	</td>
	<td>
		<?php echo nl2br($this->contact->telephone); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->fax && $this->contact->params->get( 'show_fax' ) ) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_fax' ); ?>
	</td>
	<td>
		<?php echo nl2br($this->contact->fax); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->mobile && $this->contact->params->get( 'show_mobile' ) ) :?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
	</td>
	<td>
		<?php echo nl2br($this->contact->mobile); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->webpage && $this->contact->params->get( 'show_webpage' )) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
	</td>
	<td>
		<a href="<?php echo $this->contact->webpage; ?>" target="_blank">
			<?php echo $this->contact->webpage; ?>
		</a>
	</td>
</tr>
<?php endif; ?>
</table>
<?php endif; ?>
<br />
<?php if ( $this->contact->misc && $this->contact->params->get( 'show_misc' ) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" valign="top" >
		<?php echo $this->contact->params->get( 'marker_misc' ); ?>
	</td>
	<td>
		<?php echo $this->contact->misc; ?>
	</td>
</tr>
</table>
<br />
<?php endif; ?>