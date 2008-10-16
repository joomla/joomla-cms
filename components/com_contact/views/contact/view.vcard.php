<?php
/**
 * @version		$Id: view.html.php 10094 2008-03-02 04:35:10Z instance $
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class ContactViewContact extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		// Initialize some variables
		$db = & JFactory::getDBO();

		$SiteName  = $mainframe->getCfg('sitename');
		$contactId = JRequest::getVar('contact_id', 0, '', 'int');

		// Get a Contact table object and load the selected contact details
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_contact'.DS.'tables');
		$contact =& JTable::getInstance('contact', 'Table');
		$contact->load($contactId);

		// Get the contact detail parameters
		$pparams = &$mainframe->getParams('com_contact');

		// Should we show the vcard?
		if (!$pparams->get('allow_vcard', 0)) {
			JError::raiseWarning('SOME_ERROR_CODE', 'ContactController::vCard: '.JText::_('NOTAUTH'));
			return false;
		}

		// Parse the contact name field and build the nam information for the vcard.
		$firstname 	= null;
		$middlename = null;
		$surname 	= null;

		// How many parts do we have?
		$parts = explode(' ', $contact->name);
		$count = count($parts);

		switch ($count)
		{
			case 1 :
				// only a first name
				$firstname = $parts[0];
				break;

			case 2 :
				// first and last name
				$firstname = $parts[0];
				$surname = $parts[1];
				break;

			default :
				// we have full name info
				$firstname = $parts[0];
				$surname = $parts[$count -1];
				for ($i = 1; $i < $count -1; $i ++) {
					$middlename .= $parts[$i].' ';
				}
				break;
		}

		// quick cleanup for the middlename value
		$middlename = trim($middlename);

		$document = &JFactory::getDocument();

		$document->setPhoneNumber($contact->telephone, 'PREF;WORK;VOICE');
		$document->setPhoneNumber($contact->fax, 'WORK;FAX');
		$document->setName($surname, $firstname, $middlename, '');
		$document->setAddress('', '', $contact->address, $contact->suburb, $contact->state, $contact->postcode, $contact->country, 'WORK;POSTAL');
		$document->setEmail($contact->email_to);
		$document->setNote($contact->misc);
		$document->setURL( JURI::base(), 'WORK');
		$document->setTitle($contact->con_position);
		$document->setOrg($SiteName);

		$filename = str_replace(' ', '_', $contact->name);

		$document->setFilename($filename);
	}
}