<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Contact
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Contact component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Contact
 * @since 1.0
 */
class ContactsViewContact extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();

		// Set toolbar items for the page
		JRequest::setVar( 'hidemainmenu', 1 );

		$edit		= JRequest::getVar('edit',true);
		$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Contact' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if (!$edit)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		JToolBarHelper::help( 'screen.contact.edit' );

		//get the contact
		$contact	=& $this->get('data');
		$isNew		= ($contact->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The contact' ), $contact->name );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout( $user->get('id') );
		}
		else
		{
			// initialise new record
			$contact->published = 1;
			$contact->approved 	= 1;
			$contact->order 	= 0;
			$contact->catid 	= JRequest::getVar( 'catid', 0, 'post', 'int' );
		}

		// build the html select list for ordering
		$order_query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__contact_details'
			. ' WHERE catid = ' . (int) $contact->catid
			. ' ORDER BY ordering';

		//clean data
		JFilterOutput::objectHTMLSafe( $contact, ENT_QUOTES, 'description' );
		JFilterOutput::objectHTMLSafe( $contact, ENT_QUOTES, 'misc' );

		if ('' == $contact->image) {
			$contact->image = 'blank.png';
		}

		$file 	= JPATH_COMPONENT.DS.'models'.DS.'contact.xml';
		$params = new JParameter( $contact->params, $file );

		$this->assignRef('contact',		$contact);
		$this->assignRef('params',		$params);
		$this->assignRef('order_query',	$order_query);

		parent::display($tpl);
	}
}