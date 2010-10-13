<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

class ContactViewContact extends JView
{
	protected $state;
	protected $item;
	
	public function display()
	{
		// Get model data.
		$state = $this->get('State');
		$item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$doc = JFactory::getDocument();
		$doc->setMetaData('ContentType','text/directory', true);
		$mimeType = 'text/directory';
		$doc->setMimeEncoding($mimeType);
		// Initialise variables.		
		$app		= JFactory::getApplication();
		$params 	= $app->getParams();		
		$user		= JFactory::getUser();
		$dispatcher =& JDispatcher::getInstance();
		
		// Compute lastname, firstname and middlename
		$item->name=trim($item->name);
		$namearray = explode(',',$item->name);
		if (count($namearray) > 1) {
			$lastname=$namearray[0];
			$namearray=explode(' ',trim($namearray[1]));
			$firstname=$namearray[0];
			if (count($namearray) > 1) {
				$middlename=$namearray[1];
			}
			else {
				$middlename='';
			}
		}
		else {
			$namearray=explode(' ',$item->name);
			$firstname=$namearray[0]; 
			$lastname=end($namearray);
			if (count($namearray) > 2){
				$middlename = $namearray[1];
			}
			else {
				$middlename='';
			}
		}
		
		$rev = date('c',strtotime($item->modified));
		JResponse::setHeader('Content-disposition: attachment; filename="'.$item->name.'.vcf"', true);
		
		$vcard = array();
		$vcard[].= 'BEGIN:VCARD';
		$vcard[].= 'VERSION:3.0';
		$vcard[] = 'N:'.$lastname.';'.$firstname.';'.$middlename;
		$vcard[] = 'FN:'. $item->name;
		$vcard[] = 'TITLE:'.$item->con_position;
		$vcard[] = 'TEL;TYPE=WORK,VOICE:'.$item->telephone;
		$vcard[] = 'TEL;TYPE=WORK,FAX:'.$item->fax;
		$vcard[] = 'TEL;TYPE=WORK,MOBILE:'.$item->mobile;
		$vcard[] = 'ADR;TYPE=WORK:;;'.$item->address.';'.$item->suburb.';'.$item->state.';'.$item->postcode.';'.$item->country;
		$vcard[] = 'LABEL;TYPE=WORK:'.$item->address."\n".$item->suburb."\n".$item->state."\n".$item->postcode."\n".$item->country;
		$vcard[] = 'EMAIL;TYPE=PREF,INTERNET:'.$item->email_to;
		$vcard[] = 'URL:'.$item->webpage;
		$vcard[] = 'REV:'.$rev.'Z';
		$vcard[] = 'END:VCARD';

		echo implode("\n",$vcard);
		return true;;	
	}	
}	

