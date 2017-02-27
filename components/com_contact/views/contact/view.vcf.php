<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to create a VCF for a contact item
 *
 * @since  1.6
 */
class ContactViewContact extends JViewLegacy
{
	/**
	 * The item model state
	 *
	 * @var         \Joomla\Registry\Registry
	 * @deprecated  4.0  Variable not used
	 */
	protected $state;

	/**
	 * The contact item
	 *
	 * @var   JObject
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Get model data.
		$item = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		JFactory::getDocument()->setMimeEncoding('text/directory', true);

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
				$card_name = $firstname . ' ' . ($middlename ? $middlename . ' ' : '') . $card_name;
			}
		}
		// "Firstname Middlename Lastname" format support
		else
		{
			$namearray = explode(' ', $item->name);

			$middlename = (count($namearray) > 2) ? $namearray[1] : '';
			$firstname = array_shift($namearray);
			$lastname = count($namearray) ? end($namearray) : '';
			$card_name = $firstname . ($middlename ? ' ' . $middlename : '') . ($lastname ? ' ' . $lastname : '');
		}

		$rev = date('c', strtotime($item->modified));

		JFactory::getApplication()->setHeader('Content-disposition', 'attachment; filename="' . $card_name . '.vcf"', true);

		$vcard = array();
		$vcard[] .= 'BEGIN:VCARD';
		$vcard[] .= 'VERSION:3.0';
		$vcard[]  = 'N:' . $lastname . ';' . $firstname . ';' . $middlename;
		$vcard[]  = 'FN:' . $item->name;
		$vcard[]  = 'TITLE:' . $item->con_position;
		$vcard[]  = 'TEL;TYPE=WORK,VOICE:' . $item->telephone;
		$vcard[]  = 'TEL;TYPE=WORK,FAX:' . $item->fax;
		$vcard[]  = 'TEL;TYPE=WORK,MOBILE:' . $item->mobile;
		$vcard[]  = 'ADR;TYPE=WORK:;;' . $item->address . ';' . $item->suburb . ';' . $item->state . ';' . $item->postcode . ';' . $item->country;
		$vcard[]  = 'LABEL;TYPE=WORK:' . $item->address . "\n" . $item->suburb . "\n" . $item->state . "\n" . $item->postcode . "\n" . $item->country;
		$vcard[]  = 'EMAIL;TYPE=PREF,INTERNET:' . $item->email_to;
		$vcard[]  = 'URL:' . $item->webpage;
		$vcard[]  = 'REV:' . $rev . 'Z';
		$vcard[]  = 'END:VCARD';

		echo implode("\n", $vcard);
	}
}
