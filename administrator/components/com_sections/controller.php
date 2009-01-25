<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Sections
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Sections Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	Sections
 * @since 1.5
 */
class SectionsController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'copyselect', 'display' );
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
				JRequest::setVar( 'view'  , 'section');
				JRequest::setVar( 'edit', false );

				// Checkout the section
				$model = $this->getModel('section');
				$model->checkout();
			} break;
			case 'edit':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view'  , 'section');
				JRequest::setVar( 'edit', true );

				// Checkout the section
				$model = $this->getModel('section');
				$model->checkout();
			} break;
			case 'copyselect':
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
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$db			=& JFactory::getDBO();
		$menu		= JRequest::getVar( 'menu', 'mainmenu', 'post', 'string' );
		$menuid		= JRequest::getVar( 'menuid', 0, 'post', 'int' );
		$oldtitle	= JRequest::getVar( 'oldtitle', '', '', 'post', 'string' );
		$task		= JRequest::getVar( 'task', '', '', 'post', 'string' );
		$scope		= JRequest::getVar( 'scope', '' );

		$post = JRequest::get('post');

		// fix up special html fields
		$post['description'] = JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );

		$row =& JTable::getInstance('section');
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
				. ' AND type = "content_section"'
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

		switch ( $task )
		{
			case 'go2menu':
				$mainframe->redirect( 'index.php?option=com_menus&menutype='. $menu );
				break;

			case 'go2menuitem':
				$mainframe->redirect( 'index.php?option=com_menus&menutype='. $menu .'&task=edit&id='. $menuid );
				break;

			case 'apply':
				$msg = JText::_( 'Changes to Section saved' );
				$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope .'&task=edit&cid[]='. $row->id, $msg );
				break;

			case 'save':
			default:
				$msg = JText::_( 'Section saved' );
				$mainframe->redirect( 'index.php?option='. $option .'&scope='. $scope, $msg );
				break;
		}
	}

	function copyselect()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('section');
		if(!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope, $msg );
	}

	function copysave()
	{
		global $mainframe;

		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$db			=& JFactory::getDBO();
		$scope		= JRequest::getString( 'scope' );
		$sectionid	= JRequest::getVar( 'cid' );
		$contentid	= JRequest::getVar( 'content' );
		$categoryid = JRequest::getVar( 'category' );
		JArrayHelper::toInteger($sectionid);
		JArrayHelper::toInteger($contentid);
		JArrayHelper::toInteger($categoryid);

		// copy section
		$copied = array();
		$section =& JTable::getInstance('section');
		foreach( $sectionid as $id ) {
			$section->load( $id );
			$section->id 	= NULL;

			// Adds name to list
			$copied[] = $section->name;

			// Make new section names less ambiguous
			$section->title = JTEXT::sprintf('Copy of', $section->title);
			$section->name 	= JTEXT::sprintf('Copy of', $section->name);

			if ( !$section->check() ) {
				copySectionSelect('com_sections', $sectionid, $scope );
				JError::raiseWarning(500, $section->getError() );
				return;
			}

			if ( !$section->store() ) {
				JError::raiseError(500, $section->getError() );
			}
			$section->checkin();
			$section->reorder( 'scope = '.$db->Quote($section->scope) );
			// stores original catid
			$newsectids[]["old"] = $id;
			// pulls new catid
			$newsectids[]["new"] = $section->id;
		}

		// copy categories
		$category =& JTable::getInstance('category');
		foreach( $categoryid as $id ) {
			$category->load( $id );
			$category->id = NULL;
			// Make new category names less ambiguous
			$category->title = JTEXT::sprintf('Copy of', $category->title);
			$category->name = JTEXT::sprintf('Copy of', $category->name);

			foreach( $newsectids as $newsectid ) {
				if ( $category->section == $newsectid["old"] ) {
					$category->section = $newsectid["new"];
				}
			}
			if (!$category->check()) {
				JError::raiseError(500, $category->getError() );
			}

			if (!$category->store()) {
				JError::raiseError(500, $category->getError() );
			}
			$category->checkin();
			$category->reorder( 'section = '.$db->Quote($category->section) );
			// stores original catid
			$newcatids[]["old"] = $id;
			// pulls new catid
			$newcatids[]["new"] = $category->id;
		}

		$content =& JTable::getInstance('content');
		foreach( $contentid as $id) {
			$content->load( $id );
			$content->id = NULL;
			$content->hits = 0;
			// Make new article names less ambiguous
			$content->title = JText::sprintf('Copy of', $content->title);
			foreach( $newsectids as $newsectid ) {
				if ( $content->sectionid == $newsectid["old"] ) {
					$content->sectionid = $newsectid["new"];
				}
			}
			foreach( $newcatids as $newcatid ) {
				if ( $content->catid == $newcatid["old"] ) {
					$content->catid = $newcatid["new"];
				}
			}
			if (!$content->check()) {
				JError::raiseError(500, $content->getError() );
			}

			if (!$content->store()) {
				JError::raiseError(500, $content->getError() );
			}
			$content->checkin();
		}
		$msg = JText::sprintf( 'DESCCATANDITEMSCOPIED', implode(', ', $copied) );
		$mainframe->redirect( 'index.php?option=com_sections&scope=content', $msg );
	}

	function remove()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$db =& JFactory::getDBO();
		$scope = JRequest::getCmd( 'scope' );
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select a section to delete', true ) );
		}

		$cids = implode( ',', $cid );

		$query = 'SELECT s.id, s.name, COUNT(c.id) AS numcat'
		. ' FROM #__sections AS s'
		. ' LEFT JOIN #__categories AS c ON c.section=s.id'
		. ' WHERE s.id IN ( '.$cids.' )'
		. ' GROUP BY s.id'
		;
		$db->setQuery( $query );
		if (!($rows = $db->loadObjectList())) {
			echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
		}

		$err = array();
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
			$query = 'DELETE FROM #__sections'
			. ' WHERE id IN ( '.$cids.' )'
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
			}
		}

		if (count( $err ))
		{
			$cids = implode( ', ', $err );
			$msg = JText::sprintf( 'DESCCANNOTBEREMOVED', $cids );
		}
		else
		{
			$names = implode( ', ', $name );
			$msg = JText::sprintf( 'Sections successfully deleted', $names );
		}
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope, $msg );
	}


	function publish()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('section');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope );
	}


	function unpublish()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('section');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope );
	}

	function cancel()
	{
		// Checkin the section
		$model = $this->getModel('section');
		$model->checkin();

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope );
	}


	function orderup()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$model = $this->getModel('section');
		$model->move(-1);

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope );
	}

	function orderdown()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$model = $this->getModel('section');
		$model->move(1);

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope );
	}

	function saveorder()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('section');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope, $msg );
	}

	function accesspublic()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('section');
		if(!$model->setAccess($cid, 0)) {
			$msg = $model->getError();
		}

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope, $msg );
	}

	function accessregistered()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('section');
		if(!$model->setAccess($cid, 1)) {
			$msg = $model->getError();
		}

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope, $msg );
	}

	function accessspecial()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('section');
		if(!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}

		$scope = JRequest::getCmd( 'scope' );
		$this->setRedirect( 'index.php?option=com_sections&scope='.$scope, $msg );
	}
}