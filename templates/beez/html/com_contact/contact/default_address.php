<?php
defined('_JEXEC') or die('Restricted access');

if ( ( $this->contact->params->get( 'address_check' ) > 0 ) && ( $this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode )  )
{
	echo '<div class="contact_address"><address>';
	if ( $this->contact->params->get( 'address_check' ) > 0 )
	{
		echo '<span class="marker">'.$this->contact->params->get( 'marker_address' ).'</span>';
	}

	if ( $this->contact->address && $this->contact->params->get( 'show_street_address' ) )
	{
		echo  '<p>'.nl2br($this->contact->address).'</p>';
	}

	if ( $this->contact->suburb && $this->contact->params->get( 'show_suburb' ) )
	{
		echo  '<p>'.$this->contact->suburb.'</p>';
	}

	if ( $this->contact->state && $this->contact->params->get( 'show_state' ) )
	{
		echo  '<p>'.$this->contact->state.'</p>';
	}

	if ( $this->contact->country && $this->contact->params->get( 'show_country' ) )
	{
		echo  '<p>'.$this->contact->country.'</p>';
	}

	if ( $this->contact->postcode && $this->contact->params->get( 'show_postcode' ) )
	{
		echo  '<p>'.$this->contact->postcode.'</p>';
	}
	echo '</address></div>';
}

if ( ($this->contact->email_to && $this->contact->params->get( 'show_email' )) || $this->contact->telephone  || $this->contact->fax )
{
	if ( $this->contact->email_to && $this->contact->params->get( 'show_email' ) )
	{
		echo '<p><span class="marker">'.$this->contact->params->get( 'marker_email' ).'</span>';
		echo $this->contact->email_to . '</p>';
	}

	if ( $this->contact->telephone && $this->contact->params->get( 'show_telephone' ) )
	{
		echo '<p><span class="marker">'.$this->contact->params->get( 'marker_telephone' ).'</span>';
		echo nl2br($this->contact->telephone). '</p>';
	}

	if ( $this->contact->fax && $this->contact->params->get( 'show_fax' ) )
	{
		echo '<p><span class="marker">'.$this->contact->params->get( 'marker_fax' ).'</span>';
		echo nl2br($this->contact->fax). '</p>';
	}

	if ( $this->contact->mobile && $this->contact->params->get( 'show_mobile' ) )
	{
		echo '<p>'.nl2br($this->contact->mobile).'</p>';
	}

	if ( $this->contact->webpage && $this->contact->params->get( 'show_webpage' ))
	{
		echo '<p><a href="'.$this->contact->webpage.'" target="_blank">';
		echo $this->contact->webpage. '</a></p>';
	}
}

echo '<br />';

if ( $this->contact->misc && $this->contact->params->get( 'show_misc' ) )
{
	echo '<p><span class="marker">'.$this->contact->params->get( 'marker_misc' ).'</span>';
	echo $this->contact->misc .'</p>';
}

?>
