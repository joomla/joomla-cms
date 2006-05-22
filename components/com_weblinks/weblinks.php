<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Load the html output class and the model class
 */
require_once (JApplicationHelper::getPath('front_html'));
require_once (JApplicationHelper::getPath('class'));

// First thing we want to do is set the page title
$mainframe->setPageTitle(JText::_('Web Links'));

// Next, let's get the pathway object so that we can manipulate it
$pathway = & $mainframe->getPathWay();

// Now that we have the pathway object, let's set the component name in the pathway
$pathway->setItemName(1, JText::_('Links'));

// Get some request variables
$task = JRequest::getVar( 'task' );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ($task)
{
	case 'new' :
		WeblinksController::editWebLink();
		break;

	case 'edit' :
		WeblinksController::editWebLink();
		break;

	case 'save' :
		WeblinksController::saveWebLink();
		break;

	case 'cancel' :
		WeblinksController::cancelWebLink();
		break;

	case 'view' :
		WeblinksController::showItem();
		break;

	default : 
		$document =& $mainframe->getDocument();
		if($document->getType() == 'feed') {
			WeblinksController::showCategoryFeed();
		} else {
			WeblinksController::showCategory();
		}
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
	function showCategory()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$pathway	= & $mainframe->getPathWay();
		$document	= & $mainframe->getDocument();
		$gid		= $user->get('gid');
		$page		= '';
		$category	= null;

		// Get some request variables
		$limit				= JRequest::getVar('limit', 0, '', 'int');
		$limitstart			= JRequest::getVar('limitstart', 0, '', 'int');
		$filter_order		= JRequest::getVar('filter_order', 'ordering');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir', 'DESC');
		$catid				= JRequest::getVar( 'catid', 0, '', 'int' );
		
		//add alternate feed link
		$link    = $mainframe->getBaseURL() .'feed.php?option=com_weblinks&amp;catid='.$catid.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		// Load the menu object and parameters
		$menu = JMenu::getInstance();
		$menu = $menu->getItem($Itemid);

		$params = new JParameter($menu->params);
		$params->def('page_title', 1);
		$params->def('header', $menu->name);
		$params->def('pageclass_sfx', '');
		$params->def('headings', 1);
		$params->def('hits', $mainframe->getCfg('hits'));
		$params->def('item_description', 1);
		$params->def('other_cat_section', 1);
		$params->def('other_cat', 1);
		$params->def('description', 1);
		$params->def('description_text', JText::_('WEBLINKS_DESC'));
		$params->def('image', -1);
		$params->def('weblink_icons', '');
		$params->def('image_align', 'right');
		$params->def('back_button', $mainframe->getCfg('back_button'));

		// pagination parameters
		$params->def('display', 1);
		$params->def('display_num', $mainframe->getCfg('list_limit'));

		if ($catid) {
			// Initialize variables
			$rows = array ();

			// Ordering control
			$orderby = "\n ORDER BY $filter_order $filter_order_Dir, ordering";

			$query = "SELECT COUNT(id) as numitems" .
					"\n FROM #__weblinks" .
					"\n WHERE catid = $catid" .
					"\n AND published = 1";
			$db->setQuery($query);
			$counter = $db->loadObjectList();
			$total = $counter[0]->numitems;
			$limit = $limit ? $limit : $params->get('display_num');
			if ($total <= $limit) {
				$limitstart = 0;
			}

			jimport('joomla.presentation.pagination');
			$page = new JPagination($total, $limitstart, $limit);

			// We need to get a list of all weblinks in the given category
			$query = "SELECT id, url, title, description, date, hits, params" .
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
			$db->loadObject($category);

			/*
			 * Check if the category is published or if access level allows access
			 */
			// Check to see if the category is published or if access level allows access
			if (!$category->name) {
				JError::raiseError( 404, JText::_( 'You need to login.' ));
				return;
			}
		} else {
			/*
			 * If we are at the WebLink component root (no category id set) certain
			 * defaults need to be set based on parameter values.
			 */

			// Handle the type
			$params->set('type', 'section');

			// Handle the page description
			if ($params->get('description')) {
				$category->description = $params->get('description_text');
			}

			// Handle the page image
			if ($params->get('image') != -1) {
				$category->image = $params->get('image');
				$category->image_position = $params->get('image_align');
			}
		}

		/*
		* Query to retrieve all categories that belong under the web links section
		* and that are published.
		*/
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

		// Handle page header, page title, and pathway
		if (empty ($category->name)) {
			/*
			 * We do not have a name set for the category, so we should get the default
			 * information from the parameters.
			 */
			$category->name = $params->get('header');

			// Set page title
			$document->setTitle($menu->name);
		} else {
			/*
			 * A name is set for the current category so let's use it.
			 */

			// Set page title based on category name
			$document->setTitle($menu->name.' - '.$category->name);

			// Add pathway item based on category name
			$pathway->addItem($category->name, '');
		}

		// Define image tag attributes
		if (isset ($category->image)) {
			$imgAttribs['align'] = '"'.$category->image_position.'"';
			$imgAttribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$category->imgTag = mosHTML::Image('/images/stories/'.$category->image, JText::_('Web Links'), $imgAttribs);
		}

		// used to show table rows in alternating colours
		$tabclass = array ('sectiontableentry1', 'sectiontableentry2');

		// table ordering
		if ($filter_order_Dir == 'DESC') {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';

		WeblinksView::showCategory($categories, $rows, $catid, $category, $params, $tabclass, $lists, $page);
	}

	function showCategoryFeed()
	{
		global $mainframe;

		$database =& $mainframe->getDBO();
		$document =& $mainframe->getDocument();
		
		// Get some request variables
		$limit		= 10;

		$where  = "\n WHERE published = 1";
        $catid  = JRequest::getVar('catid', 0);

        if ( $catid ) {
            $where .= "\n AND catid = $catid";
        }

    	$query = "SELECT *,"
    	. "\n title AS title,"
    	. "\n url AS link,"
    	. "\n description AS description,"
    	. "\n date AS date"
    	. "\n FROM #__weblinks"
    	. $where
    	. "\n ORDER BY ordering"
     	;
		$database->setQuery( $query, 0, $limit );
    	$rows = $database->loadObjectList();
		
		echo $database->getErrorMsg();

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = $mainframe->getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}

			$link = 'index.php?option=com_weblinks&task=view&id='. $row->id . '&catid='.$row->catid.$_Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->description;
			$date = ( $row->date ? date( 'r', $row->date ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= 'Weblinks';

			// loads item info into rss array
			$document->addItem( $item );
		}
	}

	/**
	 * Log the hit and redirect to the link
	 *
	 * @param	int		$id		Web Link id
	 * @param	int		$catid	Web Link category id
	 * @since 1.0
	 */
	function showItem()
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		// Initialize variables
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$document	= & $mainframe->getDocument();
		$id			= JRequest::getVar( 'id', 0, '', 'int' );

		// Get the weblink table object and load it
		$weblink = & new JTableWeblink($db);
		$weblink->load($id);

		// Check if link is published
		if (!$weblink->published) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Get the category table object and load it
		$cat = & new JTableCategory($db);
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
		$weblink->hit();

		if ($weblink->url) {
			// redirects to url if matching id found
			josRedirect($weblink->url);
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
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$pathway	= & $mainframe->getPathWay();
		$document	= & $mainframe->getDocument();

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
		$id = JRequest::getVar( 'id', 0, '', 'int' );


		// Create and load a weblink table object
		$row = & new JTableWeblink($db);
		$row->load($id);

		// Is this link checked out?  If not by me fail
		if ($row->isCheckedOut($user->get('id'))) {
			josRedirect("index2.php?option=$option", "The module $row->title is currently being edited by another administrator.");
		}

		// Edit or Create?
		if ($id) {
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
		} else {
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
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', JRequest::getVar('option'), intval($row->catid));

		WeblinksView::editWeblink($row, $lists);
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
		$db		= & $mainframe->getDBO();
		$user	= & $mainframe->getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Create and load a web link table
		$row = new JTableWeblink($db);
		$row->load(JRequest::getVar( 'id', 0, 'post', 'int' ));

		// Checkin the weblink
		$row->checkin();

		josRedirect('index.php');
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
		$db		= & $mainframe->getDBO();
		$user	= & $mainframe->getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Create a web link table
		$row = new JTableWeblink($db);

		// Bind the $_POST array to the web link table
		if (!$row->bind($_POST, "published")) {
			JError::raiseError( 500, $row->getError());
			return;
		}

		// Is the web link a new one?
		$isNew = $row->id < 1;

		// Create the timestamp for the date
		$row->date = date('Y-m-d H:i:s');

		// until full edit capabilities are given for weblinks - limit saving to new weblinks only
		$row->id = 0;

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
			josSendAdminMail($adminRow->name, $adminRow->email, '', 'Weblink', $row->title, $user->get('username'), $mainframe->getBaseURL());
		}

		$msg = $isNew ? JText::_('THANK_SUB') : '';
		josRedirect('index.php?option=com_weblinks&task=new&Itemid='.$Itemid, $msg);
	}
}
?>