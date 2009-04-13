<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Categories Controller
 *
 * @package		Joomla.Administrator
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
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db			=& JFactory::getDBO();
		$extension 	= JRequest::getCmd( 'extension', 'com_content' );
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
		if($row->id != 0)
		{
			$query = 'SELECT lft, rgt, parent_id FROM #__categories WHERE id = '.$row->id;
			$db->setQuery($query);
			$current = $db->loadObject();
			if($current->parent_id != $row->parent_id)
			{
				$query = 'SELECT lft, rgt FROM #__categories WHERE id = '.(int)$row->parent_id;
				$db->setQuery($query);
				$new_parent = $db->loadObject();

				$query = 'UPDATE #__categories SET rgt = rgt + '.($current->rgt - $current->lft).' '.
						'WHERE rgt >= '.$new_parent->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'UPDATE #__categories SET lft = lft + '.($current->rgt - $current->lft).' '.
						'WHERE lft > '.$new_parent->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'UPDATE #__categories SET ';
				if($current->lft > $new_parent->rgt)
				{
					$query .= 'lft = lft - '.($current->lft - $new_parent->rgt - 1);
				} else {
					$query .= 'lft = lft + '.($new_parent->rgt - $current->lft + 1);
				}
				$query .= ' WHERE lft BETWEEN '.$current->lft.' AND '.$current->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'UPDATE #__categories SET ';
				if($current->lft > $new_parent->rgt)
				{
					$query .= 'rgt = rgt - '.($current->lft - $new_parent->rgt - 1);
				} else {
					$query .= 'rgt = rgt + '.($new_parent->rgt - $current->lft + 1);
				}
				$query .= ' WHERE rgt BETWEEN '.$current->lft.' AND '.$current->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'UPDATE #__categories SET rgt = rgt - '.($current->rgt - $current->lft).' '.
						'WHERE rgt >= '.$current->lft.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'UPDATE #__categories SET lft = lft - '.($current->rgt - $current->lft).' '.
						'WHERE lft > '.$current->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'SELECT lft, rgt, parent_id FROM #__categories WHERE id = '.$row->id;
				$db->setQuery($query);
				$current = $db->loadObject();
				
				$row->lft = $current->lft;
				$row->rgt = $current->rgt;
			} elseif($row->parent_id == $current->parent_id && $row->parent_id == 0) {
				$row->lft = $current->lft;
				$row->rgt = $current->rgt;
			}
		} else {
			if($row->parent_id > 0)
			{
				$query = 'SELECT lft, rgt FROM #__categories WHERE id = '.(int)$row->parent_id;
				$db->setQuery($query);
				$new_parent = $db->loadObject();

				$query = 'UPDATE #__categories SET rgt = rgt + 2 '.
						'WHERE rgt >= '.$new_parent->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$query = 'UPDATE #__categories SET lft = lft + 2 '.
						'WHERE lft > '.$new_parent->rgt.' AND extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$db->Query();
				$row->lft = $new_parent->rgt;
				$row->rgt = $new_parent->rgt + 1;
			} else {
				$query = 'SELECT MAX(rgt) FROM #__categories WHERE extension = '.$db->Quote($row->extension);
				$db->setQuery($query);
				$rgt = $db->loadResult();
				$row->lft = $rgt + 1;
				$row->rgt = $rgt + 2;
			}
		}
		if(!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();

		switch ( $task )
		{
			case 'apply':
				$msg = JText::_( 'Changes to Category saved' );
				$mainframe->redirect( 'index.php?option=com_categories&extension='. $extension .'&task=edit&cid[]='. $row->id, $msg );
				break;

			case 'save':
			default:
				$msg = JText::_( 'Category saved' );
				$mainframe->redirect( 'index.php?option=com_categories&extension='. $extension, $msg );
				break;
		}
	}

	function copysave()
	{
		$mainframe = JFactory::getApplication();

		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
		$mainframe = JFactory::getApplication();

		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db =& JFactory::getDBO();
		$extension = JRequest::getCmd( 'extension', 'com_content' );
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select a section to delete', true ) );
		}

		$cids = implode( ',', $cid );

		if (strpos( $extension, 'com_' ) === 0) {
			$table = substr( $extension, 4 );
		} else {
			$table = $extension;
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
		$this->setRedirect( 'index.php?option=com_categories&extension='.$extension, $msg );
	}


	function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('category');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$extension = JRequest::getCmd( 'extension' );
		$this->setRedirect( 'index.php?option=com_categories&extension='.$extension );
	}


	function unpublish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('category');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$extension = JRequest::getCmd( 'extension' );
		$this->setRedirect( 'index.php?option=com_categories&extension='.$extension );
	}

	function cancel()
	{
		// Checkin the section
		$model = $this->getModel('category');
		$model->checkin();

		$redirect = JRequest::getCmd( 'extension', '', 'post' );

		$this->setRedirect( 'index.php?option=com_categories&extension='. $redirect );
	}


	function orderup()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('category');
		$model->move(-1);

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section );
	}

	function orderdown()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('category');
		$model->move(1);

		$section = JRequest::getCmd( 'section', 'com_content' );
		$this->setRedirect( 'index.php?option=com_categories&section='.$section );
	}

	function saveorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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