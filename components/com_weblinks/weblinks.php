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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Set the table directory
JTable::addTableDir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_weblinks'.DS.'tables');

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Web Links'));

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	case 'new' :
		WeblinksController::editWebLink();
		break;

	case 'save' :
		WeblinksController::saveWebLink();
		break;

	case 'cancel' :
		WeblinksController::cancelWebLink();
		break;

	case 'view' :
		WeblinksController::displayWeblink();
		break;

	case 'category' :
		WeblinksController::displayCategory();
		break;

	default :
		WeblinksController::display();
		break;
}

/**
 * Static class to hold controller functions for the Weblink component
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksController
{
	/**
	 * Show a web link category
	 *
	 * @param	int	$catid	Web Link category id
	 * @since	1.0
	 */
	function display()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$pathway	= & $mainframe->getPathWay();
		$gid		= $user->get('gid');
		$page		= '';

		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_('Links'));

		// Load the menu object and parameters
		$menus  = &JMenu::getInstance();
		$menu   = $menus->getItem($Itemid);
		$params = new JParameter($menu->params);

		//Query to retrieve all categories that belong under the web links section and that are published.
		$query = "SELECT *, COUNT(a.id) AS numlinks FROM #__categories AS cc" .
				"\n LEFT JOIN #__weblinks AS a ON a.catid = cc.id" .
				"\n WHERE a.published = 1" .
				"\n AND section = 'com_weblinks'" .
				"\n AND cc.published = 1" .
				"\n AND cc.access <= $gid" .
				"\n GROUP BY cc.id" .
				"\n ORDER BY cc.ordering";
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		require_once (JPATH_COMPONENT.DS.'views'.DS.'categories'.DS.'view.html.php');
		$view = new WeblinksViewCategories();

		$view->assignRef('params'    , $params);
		$view->assignRef('categories', $categories);
	
		$view->display();
	}

	/**
	 * Show a web link category
	 *
	 * @param	int	$catid	Web Link category id
	 * @since	1.0
	 */
	function displayCategory()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$document	= & JFactory::getDocument();
		$gid		= $user->get('gid');
		$page		= '';

		// Get the paramaters of the active menu item
		$menus  = &JMenu::getInstance();
		$menu   = $menus->getItem($Itemid);
		$params = new JParameter($menu->params);

		// Get some request variables
		$limit				= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart			= JRequest::getVar('limitstart', 0, '', 'int');
		$filter_order		= JRequest::getVar('filter_order', 'ordering');
		$filter_order_dir	= JRequest::getVar('filter_order_Dir', 'DESC');
		$catid				= JRequest::getVar( 'catid', (int) $params->get('category_id'), '', 'int' );

		// Ordering control
		$orderby = "\n ORDER BY $filter_order $filter_order_dir, ordering";

		$query = "SELECT COUNT(id) as numitems" .
				"\n FROM #__weblinks" .
				"\n WHERE catid = ". (int)$catid .
				"\n AND published = 1";
		$db->setQuery($query);
		$counter = $db->loadObjectList();

		$total = $counter[0]->numitems;
		// Always set at least a default of viewing 5 at a time
		$limit = $limit ? $limit : 5;

		if ($total <= $limit) {
			$limitstart = 0;
		}

		// We need to get a list of all weblinks in the given category
		$query = "SELECT id, url, title, description, date, hits, params, catid" .
				"\n FROM #__weblinks" .
				"\n WHERE catid = $catid" .
				"\n AND published = 1" .
				"\n AND archived = 0".$orderby;
		$db->setQuery($query, $limitstart, $limit);
		$rows = $db->loadObjectList();

		// current category info
		$query = "SELECT id, name, description, image, image_position" .
				"\n FROM #__categories" .
				"\n WHERE id = $catid" .
				"\n AND section = 'com_weblinks'" .
				"\n AND published = 1" .
				"\n AND access <= $gid";
		$db->setQuery($query);
		$category = $db->loadObject();

		// Check to see if the category is published or if access level allows access
		if (!$category->name) {
			JError::raiseError( 404, JText::_( 'You need to login.' ));
			return;
		}

		// table ordering
		if ($filter_order_dir == 'DESC') {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';

		$type = $document->getType();
		require_once (JPATH_COMPONENT.DS.'views'.DS.'category'.DS.'view.'.$type.'.php');
		$view = new WeblinksViewCategory();

		$view->assign('total', $total);
		$view->assign('catid', $catid);
		$view->assign('limit', $limit);
		$view->assign('limitstart', $limitstart);

		$view->assignRef('lists'   , $lists);
		$view->assignRef('params'  , $params);
		$view->assignRef('category', $category);
		$view->assignRef('items'   , $rows);
		
		$view->display();
	}

	/**
	 * Log the hit and redirect to the link
	 *
	 * @param	int		$id		Web Link id
	 * @param	int		$catid	Web Link category id
	 * @since 1.0
	 */
	function displayWeblink()
	{
		global $mainframe;

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
			WeblinksController::showCategory($cat->id);
		}
	}

	/**
	 * Edit a web link record
	 *
	 * @since 1.0
	 */
	function editWebLink()
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
		$link = 'index.php?option=com_weblinks&task=new';
		$query = "SELECT id"
		. "\n FROM #__menu"
		. "\n WHERE link LIKE '%$link%'"
		. "\n AND published = 1"
		;
		$db->setQuery( $query );
		$exists = $db->loadResult();
		if ( !$exists ) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

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

		require_once (JPATH_COMPONENT.DS.'views'.DS.'weblink'.DS.'view.html.php');
		$view = new WeblinksViewWeblink();

		$view->assign('returnid', $returnid);

		$view->assignRef('lists'   , $lists);
		$view->assignRef('data'    , $data);
		$view->assignRef('weblink' , $row);
		
		$view->setLayout('form');
		$view->display();
	}

	/**
	 * Cancel the editing of a web link
	 *
	 * @since 1.0
	 */
	function cancelWebLink()
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
		$row =& JTable::getInstance('weblink', $db, 'Table');
		$row->load(JRequest::getVar( 'id', 0, 'post', 'int' ));

		// Checkin the weblink
		$row->checkin();

		$mainframe->redirect('index.php');
	}

	/**
	 * Saves the record on an edit form submit
	 *
	 * @since 1.0
	 */
	function saveWeblink()
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
		$row =& JTable::getInstance('weblink', $db, 'Table');

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
}
?>