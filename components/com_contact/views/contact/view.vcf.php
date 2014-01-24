<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 */
class ContactViewContact extends JViewLegacy
{
	protected $state;

	protected $item;

	public function display()
	{
		// Get model data.
		$item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		JFactory::getDocument()->setMetaData('Content-Type', 'text/directory', true);

		// Compute lastname, firstname and middlename
		$item->name = trim($item->name);

		// "Lastname, Firstname Midlename" format support
		// e.g. "de Gaulle, Charles"
		$namearray = explode(',', $item->name);
		if (count($namearray) > 1 )
		{
			$lastname = $namearray[0];
			$card_name = $lastname;
			$name_and_midname = trim($namearray[1]);

			$firstname = '';
			if (!empty($name_and_midname))
			{
				$namearray = explode(' ', $name_and_midname);

				$firstname = $namearray[0];
				$middlename = (count($namearray) > 1) ? $namearray[1] : '';
				$card_name = $firstname . ' ' . ($middlename ? $middlename . ' ' : '') .  $card_name;
			}
		}
		// "Firstname Middlename Lastname" format support
		else {
			$namearray = explode(' ', $item->name);

			$middlename = (count($namearray) > 2) ? $namearray[1] : '';
			$firstname = array_shift($namearray);
			$lastname = count($namearray) ? end($namearray) : '';
			$card_name = $firstname . ($middlename ? ' ' . $middlename : '') . ($lastname ? ' ' . $lastname : '');
		}

		$rev = date('c', strtotime($item->modified));

		$app->setHeader('Content-disposition', 'attachment; filename="'.$card_name.'.vcf"', true);

		$vcard = array();
		$vcard[] .= 'BEGIN:VCARD';
		$vcard[] .= 'VERSION:3.0';
		$vcard[]  = 'N:'.$lastname.';'.$firstname.';'.$middlename;
		$vcard[]  = 'FN:'. $item->name;
		$vcard[]  = 'TITLE:'.$item->con_position;
		$vcard[]  = 'TEL;TYPE=WORK,VOICE:'.$item->telephone;
		$vcard[]  = 'TEL;TYPE=WORK,FAX:'.$item->fax;
		$vcard[]  = 'TEL;TYPE=WORK,MOBILE:'.$item->mobile;
		$vcard[]  = 'ADR;TYPE=WORK:;;'.$item->address.';'.$item->suburb.';'.$item->state.';'.$item->postcode.';'.$item->country;
		$vcard[]  = 'LABEL;TYPE=WORK:'.$item->address."\n".$item->suburb."\n".$item->state."\n".$item->postcode."\n".$item->country;
		$vcard[]  = 'EMAIL;TYPE=PREF,INTERNET:'.$item->email_to;
		$vcard[]  = 'URL:'.$item->webpage;
		$vcard[]  = 'REV:'.$rev.'Z';
		$vcard[]  = 'END:VCARD';

		echo implode("\n", $vcard);
		return true;
	}
}
