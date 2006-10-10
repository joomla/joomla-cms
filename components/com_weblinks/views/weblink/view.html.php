<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksViewWeblink extends JView
{
	function display($tpl = null)
	{
		global $mainframe;
		
		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		// Initialize variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$document	= & JFactory::getDocument();
		$id			= JRequest::getVar( 'id', 0, '', 'int' );
		
		// Get the weblink table object and load it
		$weblink =& JTable::getInstance('weblink', $db, 'Table');
		$weblink->load($id);

		// Check if link is published
		if (!$weblink->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Get the category table object and load it
		$cat =& JTable::getInstance('category', $db);
		$cat->load($weblink->catid);

		// Check to see if the category is published
		if (!$cat->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Check whether category access level allows access
		if ($cat->access > $user->get('gid')) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Record the hit
		$weblink->hit(null, $mainframe->getCfg('enable_log_items'));

		if ($weblink->url) {
			// redirects to url if matching id found
			$mainframe->redirect($weblink->url);
		} else {
			// redirects to weblink category page if no matching id found
			//WeblinksController::showCategory($cat->id);
		}

		parent::display($tpl);
	}
	
	function _displayForm($tpl)
	{
		global $mainframe;

		// Get some objects from the JApplication
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$pathway	= & $mainframe->getPathWay();
		$document	= & JFactory::getDocument();

		// Make sure you are logged in
		if ($user->get('gid') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// security check to see if link exists in a menu
		/*$menus =& JMenu::getInstance();
		$exists = $menus->getItems('link', 'index.php?option=com_weblinks&view=weblink&layout=form'); 
		if ( !count($exists) ) {
		    JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}*/

		/*
		 * Disabled until ACL system is implemented.  When enabled the $id variable
		 * will be used instead of a 0
		 */
		$id 	  = JRequest::getVar( 'id', 0, '', 'int' );
		$returnid = JRequest::getVar( 'Returnid', 0, '', 'int' );

		// Create and load a weblink table object
		$row =& JTable::getInstance('weblink', $db, 'Table');
		$row->load($id);

		// Is this link checked out?  If not by me fail
		if ($row->isCheckedOut($user->get('id'))) {
			$mainframe->redirect("index2.php?option=$option", "The module $row->title is currently being edited by another administrator.");
		}

		// Edit or Create?
		if ($id)
		{
			/*
			 * The web link already exists so we are editing it.  Here we want to
			 * manipulate the pathway and pagetitle to indicate this, plus we want
			 * to check the web link out so no one can edit it while we are editing it
			 */
			$row->checkout($user->get('id'));

			// Set page title
			$document->setTitle(JText::_('Links').' - '.JText::_('Edit'));

			// Add pathway item
			$pathway->addItem(JText::_('Edit'), '');
		}
		else
		{
			/*
			 * The web link does not already exist so we are creating a new one.  Here
			 * we want to manipulate the pathway and pagetitle to indicate this.  Also,
			 * we need to initialize some values.
			 */
			$row->published = 0;
			$row->approved = 1;
			$row->ordering = 0;

			// Set page title
			$document->setTitle(JText::_('Links').' - '.JText::_('New'));

			// Add pathway item
			$pathway->addItem(JText::_('New'), '');
		}

		// build list of categories
		$lists['catid'] = JAdminMenus::ComponentCategory('jform[catid]', JRequest::getVar('option'), intval($row->catid));

		$this->assign('returnid', $returnid);

		$this->assignRef('lists'   , $lists);
		$this->assignRef('data'    , $data);
		$this->assignRef('weblink' , $row);
		
		parent::display($tpl);
	}
}
?>