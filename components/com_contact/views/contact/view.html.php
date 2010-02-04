<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Site
 * @subpackage	Contacts
 */
class ContactViewContact extends JView
{
	protected $state = null;
	protected $contact = null;

	function display($tpl = null)
	{
		$app		= &JFactory::getApplication();
		$user		= &JFactory::getUser();
		$pathway	= &$app->getPathway();
		$document	= & JFactory::getDocument();
		$state 		= $this->get('State');
		$contact 	= $this->get('Contact');

		// report any errors and exit if they exist
		$this->reportErrors($this->get('Errors'));

		// Get the parameters of the active menu item
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		$pparams = $this->getModel('contact')->getState()->params;

		// check if access is registered/special
		$groups	= $user->authorisedLevels();

		$return ="";
		if ((!in_array($contact->access, $groups)) || (!in_array($contact->category_access, $groups))) {
			$uri		= JFactory::getURI();
			$return		= (string)$uri;

			$url  = 'index.php?option=com_users&view=login';
			$url .= '&return='.base64_encode($return);

			$app->redirect($url, JText::_('YOU_MUST_LOGIN_FIRST'));

		}

		$options['category_id']	= $contact->catid;
		$options['order by']	= 'cd.default_con DESC, cd.ordering ASC';

		$contacts = &$this->getModel('Category')->getContacts($options);

		// Set the document page title
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'contact' && isset($menu->query['id']) && $menu->query['id'] == $contact->id) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$pparams->set('page_title',	$contact->name);
			}
		} else {
			$pparams->set('page_title',	$contact->name);
		}
		$document->setTitle($pparams->get('page_title'));

		//set breadcrumbs
		if (isset($menu) && isset($menu->query['view']) && $menu->query['view'] != 'contact'){
			$pathway->addItem($contact->name, '');
		}

		// Make contact parameters available to views
		$contact->params = new JParameter($contact->params);

		// Handle email cloaking
		if ($contact->email_to && $pparams->get('show_email')) {
			$contact->email_to = JHtml::_('email.cloak', $contact->email_to);
		}

		if ($pparams->get('show_street_address') || $pparams->get('show_suburb') || $pparams->get('show_state') || $pparams->get('show_postcode') || $pparams->get('show_country'))
		{
			if (!empty ($contact->address) || !empty ($contact->suburb) || !empty ($contact->state) || !empty ($contact->country) || !empty ($contact->postcode)) {
				$pparams->set('address_check', 1);
			}
		} else {
			$pparams->set('address_check', 0);
		}

		 // Manage the display mode for contact detail groups
		switch ($pparams->get('contact_icons'))
		{
			case 1 :
				// text
				$pparams->set('marker_address', 	JText::_('Address').": ");
				$pparams->set('marker_email', 		JText::_('Email').": ");
				$pparams->set('marker_telephone', 	JText::_('Telephone').": ");
				$pparams->set('marker_fax', 		JText::_('Fax').": ");
				$pparams->set('marker_mobile',		JText::_('Mobile').": ");
				$pparams->set('marker_misc', 		JText::_('Information').": ");
				$pparams->set('marker_class', 		'jicons-text');
				break;

			case 2 :
				// none
				$pparams->set('marker_address', 	'');
				$pparams->set('marker_email', 		'');
				$pparams->set('marker_telephone', 	'');
				$pparams->set('marker_mobile', 	'');
				$pparams->set('marker_fax', 		'');
				$pparams->set('marker_misc', 		'');
				$pparams->set('marker_class', 		'jicons-none');
				break;

			default :
				// icons
				$image1 = JHtml::_('image', 'contacts/'.$pparams->get('icon_address','con_address.png'), JText::_('Address').": ", NULL, true);
				$image2 = JHtml::_('image', 'contacts/'.$pparams->get('icon_email','emailButton.png'), JText::_('Email').": ", NULL, true);
				$image3 = JHtml::_('image', 'contacts/'.$pparams->get('icon_telephone','con_tel.png'), JText::_('Telephone').": ", NULL, true);
				$image4 = JHtml::_('image', 'contacts/'.$pparams->get('icon_fax','con_fax.png'), JText::_('Fax').": ", NULL, true);
				$image5 = JHtml::_('image', 'contacts/'.$pparams->get('icon_misc','con_info.png'), JText::_('Information').": ", NULL, true);
				$image6 = JHtml::_('image', 'contacts/'.$pparams->get('icon_mobile','con_mobile.png'), JText::_('Mobile').": ", NULL, true);

				$pparams->set('marker_address', 	$image1);
				$pparams->set('marker_email', 		$image2);
				$pparams->set('marker_telephone', 	$image3);
				$pparams->set('marker_fax', 		$image4);
				$pparams->set('marker_misc',		$image5);
				$pparams->set('marker_mobile', 		$image6);
				$pparams->set('marker_class', 		'jicons-icons');
				break;
		}

		// Use link labels from contact if blank in params
		$loopArray = array('a','b','c','d','e');
		foreach ($loopArray as $letter) {
			$thisLable = 'link'.$letter.'_name';
			$thisLink = 'link'.$letter;
			if (!$pparams->get($thisLable)) {
				if ($contact->params->get($thisLable)) {
					$pparams->set($thisLable, $contact->params->get($thisLable));
				} else {
					$pparams->set($thisLable, $contact->params->get($thisLink));
				}
			}
		}

		JHtml::_('behavior.formvalidation');

		$this->assignRef('contact',		$contact);
		$this->assignRef('contacts',	$contacts);
		$this->assignRef('params',		$pparams);
		$this->assignRef('return',		$return);

		parent::display($tpl);
	}
	/**
	 * Checks for errors and exits with messages if found
	 * @param	Array of errors
	 * @return	null
	 */
	function reportErrors($errors)
	{
		if (!$errors || !is_array($errors)) {
			return;
		}
		foreach ($errors as &$error)
		{
			if ($error instanceof Exception)
			{
				if ($error->getCode() == 404)
				{
					// If there is a 404, throw a hard error.
					JError::raiseError(404, $error->getMessage());
					return false;
				}
				else
				{
					JError::raiseError(500, $error->getMessage());
				}
			}
			else
			{
				JError::raiseWarning(500, $error);
			}
		}
		return false;

	}
}

