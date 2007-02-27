<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id$
 * @author Design & Accessible Team ( Angie Radtke / Robert Deutz )
 * @package Joomla
 * @subpackage Accessible-Template-Beez
 * @copyright Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

if ( ( $this->contact->params->get( 'address_check' ) > 0 ) &&
	 ( $this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode )
   )
{
	echo '<div class="contact_address"><address>';
	if ( $this->contact->params->get( 'address_check' ) > 0 )
	{
		echo '<span class="marker">'.$this->contact->params->get( 'marker_address' ).'</span>';
	}

	if ( $this->contact->address && $this->contact->params->get( 'street_address' ) )
	{
		echo  '<p>'.nl2br($this->contact->address).'</p>';
	}

	if ( $this->contact->suburb && $this->contact->params->get( 'suburb' ) )
	{
		echo  '<p>'.$this->contact->suburb.'</p>';
	}

	if ( $this->contact->state && $this->contact->params->get( 'state' ) )
	{
		echo  '<p>'.$this->contact->state.'</p>';
	}

	if ( $this->contact->country && $this->contact->params->get( 'country' ) )
	{
		echo  '<p>'.$this->contact->country.'</p>';
	}

	if ( $this->contact->postcode && $this->contact->params->get( 'postcode' ) )
	{
		echo  '<p>'.$this->contact->postcode.'</p>';
	}
}

if ( ($this->contact->email_to && $this->contact->params->get( 'email' )) || $this->contact->telephone  || $this->contact->fax )
{
	if ( $this->contact->email_to && $this->contact->params->get( 'email' ) )
	{
		echo '<p><span class="marker">'.$this->contact->params->get( 'marker_email' ).'</span>';
		echo $this->contact->email_to . '</p>';
	}

	if ( $this->contact->telephone && $this->contact->params->get( 'telephone' ) )
	{
		echo '<p><span class="marker">'.$this->contact->params->get( 'marker_telephone' ).'</span>';
		echo nl2br($this->contact->telephone). '</p>';
	}

	if ( $this->contact->fax && $this->contact->params->get( 'fax' ) )
	{
		echo '<p><span class="marker">'.$this->contact->params->get( 'marker_fax' ).'</span>';
		echo nl2br($this->contact->fax). '</p>';
	}

	if ( $this->contact->mobile && $this->contact->params->get( 'mobile' ) )
	{
		echo '<p>'.nl2br($this->contact->mobile).'</p>';
	}

	if ( $this->contact->webpage && $this->contact->params->get( 'webpage' ))
	{
		echo '<p><a href="'.$this->contact->webpage.'" target="_blank">';
		echo $this->contact->webpage. '</a></p>';
	}
}

echo '<br />';

if ( $this->contact->misc && $this->contact->params->get( 'misc' ) )
{
	echo '<p><span class="marker">'.$this->contact->params->get( 'marker_misc' ).'</span>';
	echo $this->contact->misc .'</p>';
}

echo '</address></div>';
?>