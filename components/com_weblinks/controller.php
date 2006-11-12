<?php
/**
 * @version $Id: controller.php 5379 2006-10-09 22:39:40Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.controller');

/**
 * Weblink Component Controller
 *
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.5
 */
class WeblinksController extends JController
{
	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		$document =& JFactory::getDocument();
		
		$viewType   = $document->getType();	
		$viewName	= JRequest::getVar( 'view', 'categories' );
		$viewLayout = JRequest::getVar( 'layout', 'default' );
		
		// interceptors to support legacy urls
		switch( $this->getTask())
		{
			//index.php?option=com_weblinks&task=x&catid=xid=x&Itemid=x
			case 'view':
			{
				$viewName	= 'weblink';
			} break;
			
			default:
			{
				if(JRequest::getVar( 'catid', 0)) {
					$viewName = 'category';
				} 
			}
		}

		// Create the view
		$this->setViewName( $viewName, 'WeblinksView', $viewType );
		if ($view = & $this->getView())
		{
			// Get/Create the model
			if ($model = & $this->getModel($viewName, 'WeblinksModel'))
			{
				// Push the model into the view (as default)
				$view->setModel($model, true);
			}
			// Set the layout
			$view->setLayout($viewLayout);
	
			// Display the view
			$view->display();
		}
		else
		{
			return JError::raiseError( 500, 'The view ['.$viewName.'] could not be found' );
		}
	}

	/**
	* Saves the record on an edit form submit
	*
	* @acces public
	* @since 1.5
	*/
	function save()
	{
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Create a web link table
		$row =& JTable::getInstance('weblink','JTable');

		// Get the form fields.
		$fields = JRequest::getVar('jform', array(), 'post', 'array');

		// Bind the form fields to the web link table
		if (!$row->bind($fields, "published")) {
			JError::raiseError( 500, $row->getError());
			return;
		}

		// sanitise id field
		// $row->id = (int) $row->id;
		// until full edit capabilities are given for weblinks - limit saving to new weblinks only
		$row->id = 0;

		// Is the web link a new one?
		$isNew = $row->id < 1;

		// Create the timestamp for the date
		$row->date = date('Y-m-d H:i:s');

		// Make sure the web link table is valid
		if (!$row->check()) {
			JError::raiseError( 500, $row->getError());
			return;
		}

		// Store the web link table to the database
		if (!$row->store()) {
			JError::raiseError( 500, $row->getError());
			return;
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$row->checkin();

		// admin users gid
		$gid = 25;

		// list of admins
		$query = "SELECT email, name" .
				"\n FROM #__users" .
				"\n WHERE gid = $gid" .
				"\n AND sendEmail = 1";
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseError( 500, $db->stderr(true));
			return;
		}
		$adminRows = $db->loadObjectList();

		// send email notification to admins
		foreach ($adminRows as $adminRow) {
			JUtility::sendAdminMail($adminRow->name, $adminRow->email, '', 'Weblink', $row->title, $user->get('username'), JURI::base());
		}

		$msg = $isNew ? JText::_('THANK_SUB') : '';
		$mainframe->redirect('index.php?option=com_weblinks&task=new&Itemid='.$Itemid, $msg);
	}

	/**
	* Cancel the editing of a web link
	*
	* @access	public
	* @since	1.5
	*/
	function cancel()
	{
		global $mainframe, $Itemid;

		// Get some objects from the JApplication
		$db		= & JFactory::getDBO();
		$user	= & JFactory::getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Create and load a web link table
		$row =& JTable::getInstance('weblink', 'Table');
		$row->load(JRequest::getVar( 'id', 0, 'post', 'int' ));

		// Checkin the weblink
		$row->checkin();

		$mainframe->redirect('index.php');
	}
}

?>