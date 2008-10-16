<?php
/**
 * @version		$Id: $
 * @package		Joomla
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Categories Controller
 *
 * @package		Joomla
 * @subpackage	Categories
 * @since 1.5
 */
class CategoriesController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'copyselect', 'display' );
		$this->registerTask( 'moveselect', 'display' );
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'go2menu', 'save' );
		$this->registerTask( 'go2menuitem', 'save' );
	}

	function display( )
	{
		switch($this->getTask())
		{
			case 'add':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view'  , 'category');
				JRequest::setVar( 'edit', false );

				// Checkout the section
				$model = $this->getModel('category');
				$model->checkout();
			} break;
			case 'edit':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view'  , 'category');
				JRequest::setVar( 'edit', true );

				// Checkout the section
				$model = $this->getModel('category');
				$model->checkout();
			} break;
			case 'copyselect':
			case 'moveselect':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view'  , 'copyselect');
			} break;
		}

		parent::display();
	}

	function save()
	{
		global $mainframe, $option;

		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$db			=& JFactory::getDBO();
		$menu		= JRequest::getVar( 'menu', 'mainmenu', 'post', 'string' );
		$menuid		= JRequest::getVar( 'menuid', 0, 'post', 'int' );
		$oldtitle	= JRequest::getVar( 'oldtitle', '', '', 'post', 'string' );
		$task		= JRequest::getVar( 'task', '', '', 'post', 'string' );
		$redirect 	= JRequest::getCmd( 'redirect', '', 'post' );

		$post = JRequest::get('post');

		// fix up special html fields
		$post['description'] = JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );

		$row =& JTable::getInstance('category');
		if (!$row->bind($post)) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->check()) {
			JError::raiseError(500, $row->getError() );
		}
		if ( $oldtitle ) {
			if ( $oldtitle <> $row->title ) {
				$query = 'UPDATE #__menu'
				. ' SET name = '.$db->Quote($row->title)
				. ' WHERE name = '.$db->Quote($oldtitle)
				. ' AND type = "content_category"'
				;
				$db->setQuery( $query );
				$db->query();
			}
		}

		// if new item order last in appropriate group
		if (!$row->id) {
			$row->ordering = $row->getNextOrder();
		}

		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();

		// Update Section Count
		if ($row->section != 'com_contact_details' &&
			$row->section != 'com_newsfeeds' &&
			$row->section != 'com_weblinks') {
			$query = 'UPDATE #__sections SET count=count+1'
			. ' WHERE id = '.$db->Quote($row->section)
			;
			$db->setQuery( $query );
		}

		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}

		switch ( $task )
		{
			case 'go2menu':
				$mainframe->redirect( 'index.php?option=com_menus&menutype='. $menu );
				break;

			case 'go2menuitem':
				$mainframe->redirect( 'index.php?option=com_menus&menutype='. $menu .'&task=edit&id='. $menuid );
				break;

			case 'apply':
				$msg = JText::_( 'Changes to Category saved' );
				$mainframe->redirect( 'index.php?option=com_categories&section='. $redirect .'&task=edit&cid[]='. $row->id, $msg );
				break;

			case 'save':
			default:
				$msg = JText::_( 'Category saved' );
				$mainframe->redirect( 'index.php?option=com_categories&section='. $redirect, $msg );
				break;
		}
	}

	function copysave()
	{
		global $mainframe;

		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		// Initialize variables
		$db =& JFactory::getDBO();

		$sectionMove 	= JRequest::getInt( 'sectionmove' );
		$sectionOld = JRequest::getCmd( 'section', 'com_content', 'post' );
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		//Check to see if a section was selected to copy the items too
		if (!$sectionMove)
		{
			$msg = JText::_('Please select a section from the list');
			$this->setRedirect( 'index.php?option=com_categories&task=copyselect&section='. $sectionOld . '&cid[]='. $cid, $msg );
			return;
		}

		$contentid 		= JRequest::getVar( 'item', null, '', 'array' );
		JArrayHelper::toInteger($contentid);

		$category =& JTable::getInstance('category');

		foreach( $cid as $id )
		{
			$category->load( $id );
			$category->id 		= NULL;
			$category->title 	= JText::sprintf( 'Copy of', $category->title );
			$category->name 	= JText::sprintf( 'Copy of', $category->name );
			$category->section 	= $sectionMove;
			if (!$category->check()) {
				JError::raiseError(500, $category->getError());
			}

			if (!$category->store()) {
				JError::raiseError(500, $category->getError());
			}
			$category->checkin();
			// stores original catid
			$newcatids[]["old"] = $id;
			// pulls new catid
			$newcatids[]["new"] = $category->id;
		}

		$content =& JTable::getInstance('content');
		foreach( $contentid as $id) {
			$content->load( $id );
			$content->id 		= NULL;
			$content->sectionid = $sectionMove;
			$content->title 	= JText::sprintf( 'Copy of', $content->title );
			$content->hits 		= 0;
			foreach( $newcatids as $newcatid ) {
				if ( $content->catid == $newcatid["old"] ) {
					$content->catid = $newcatid["new"];
				}
			}
			if (!$content->check()) {
				JError::raiseError(500, $content->getError());
			}

			if (!$content->store()) {
				JError::raiseError(500, $content->getError());
			}
			$content->checkin();
		}

		$sectionNew =& JTable::getInstance('section');
		$sectionNew->load( $sectionMove );

		$msg = JText::sprintf( 'Categories copied to', count($cid), $sectionNew->title );
		$this->setRedirect( 'index.php?option=com_categories&section='. $sectionOld, $msg );
	}

	function movesave()
	{
		global $mainframe;

		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$db =& JFactory::getDBO();
		$sectionMove = JRequest::getCmd( 'sectionmove' );
		$sectionOld = JRequest::getCmd( 'section', 'com_content', 'post' );
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		//Check to see of a section was selected to copy the items too
		if (!$sectionMove)
		{
			$msg = JText::_('Please select a section from the list');
			$this->setRedirect( 'index.php?option=com_categories&task=moveselect&section='. $sectionOld . '&cid[]='. $cid, $msg );
			return;
		}

		JArrayHelper::toInteger($cid, array(0));

		$sectionNew =& JTable::getInstance('section');
		$sectionNew->load( $sectionMove );

		//Remove the categories was in destination section
		$cids = implode( ',', $cid );

		$query = 'SELECT id, title'
		. ' FROM #__categories'
		. ' WHERE id IN ( '.$cids.' )'
		. ' AND section = '.$db->Quote($sectionMove)
		;
		$db->setQuery( $query );

		$scid   = $db->loadResultArray(0);
		$title  = $db->loadResultArray(1);

		$cid = array_diff($cid, $scid);

		if ( !empty($cid) ) {
			$cids = implode( ',', $cid );
			$total = count( $cid );

			$query = 'UPDATE #__categories'
			. ' SET section = '.$db->Quote($sectionMove)
			. ' WHERE id IN ( '.$cids.' )'
			;
			$db->setQuery( $query );
			if ( !$db->query() ) {
				JError::raiseError(500, $db->getErrorMsg() );
			}
			$query = 'UPDATE #__content'
			. ' SET sectionid = '.$db->Quote($sectionMove)
			. ' WHERE catid IN ( '.$cids.' )'
			;
			$db->setQuery( $query );
			if ( !$db->query() ) {
				JError::raiseError(500, $db->getErrorMsg());
			}

			$msg = JText::sprintf( 'Categories moved to', $sectionNew->title );
		}
		if ( !empty($title) && is_array($title) ) {
			if ( count($title) == 1 ) {
				$msg = JText::sprintf( 'Category already in', implode( ',', $title ), $sectionNew->title );
			} else {
				$msg = JText::sprintf( 'Categories already in', implode( ',', $title ), $sectionNew->title );
			}
		}

		$this->setRedirect( 'index.php?option=com_categories&section='. $sectionOld, $msg );
	}

	function remove()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$db =& JFactory::getDBO();
		$section = JRequest::getCmd( 'section', 'com_content' );
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select a section to delete', true ) );
		}

		$cids = implode( ',', $cid );

		if (intval( $section ) > 0) {
			$table = 'content';
		} else if (strpos( $section, 'com_' ) === 0) {
			$table = substr( $section, 4 );
		} else {
			$table = $section;
		}

		$tablesAllowed = $db->getTableList();
		if (!in_array($db->getPrefix().$table, $tablesAllowed)) {
			$table = 'content';
		}

		$query = 'SELECT c.id, c.name, c.title, COUNT( s.catid ) AS numcat'
		. ' FROM #__categories AS c'
		. ' LEFT JOIN #__'.$table.' AS s ON s.catid = c.id'
		. ' WHERE c.id IN ( '.$cids.' )'
		. ' GROUP BY c.id'
		;
		$db->setQuery( $query );

		if (!($rows = $db->loadObjectList())) {
			JError::raiseError( 500, $db->stderr() );
			return false;
		}

		$err = array();
		$names = array();
		$cid = array();
		foreach ($rows as $row) {
			if ($row->numcat == 0) {
				$cid[]	= (int) $row->id;
				$name[]	= $row->name;
			} else {
				$err[]	= $row->name;
			}
		}

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__categories'
			. ' WHERE id IN ( '.$cids.' )'
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				JError::raiseError( 500, $db->stderr() );
				return false;
			}
		}

		if (count( $err ))
		{
			$cids = implode( ", ", $err );
			$msg = JText::sprintf( 'WARNNOTREMOVEDRECORDS', $cids );
		}
		else
		{
			$names = implode( ', ', $name );
			$msg = JText::sprintf( 'Categories successfully deleted', $names );
		}
		$this->setRedirect( 'index.php?option=com_categories&section='.$section, $msg );
	}


	function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('category');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$section = JRequest::getCmd( 'section' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section );
	}


	function unpublish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('category');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$section = JRequest::getCmd( 'section' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section );
	}

	function cancel()
	{
		// Checkin the section
		$model = $this->getModel('category');
		$model->checkin();

		$redirect = JRequest::getCmd( 'redirect', '', 'post' );

		$this->setRedirect( 'index.php?option=com_categories&section='. $redirect );
	}


	function orderup()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$model = $this->getModel('category');
		$model->move(-1);

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section );
	}

	function orderdown()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$model = $this->getModel('category');
		$model->move(1);

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section );
	}

	function saveorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('category');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section, $msg );
	}

	function accesspublic()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('category');
		if(!$model->setAccess($cid, 0)) {
			$msg = $model->getError();
		}

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section, $msg );
	}

	function accessregistered()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('category');
		if(!$model->setAccess($cid, 1)) {
			$msg = $model->getError();
		}

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section, $msg );
	}

	function accessspecial()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('category');
		if(!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section, $msg );
	}
}