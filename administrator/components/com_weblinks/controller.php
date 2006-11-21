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
	function edit() 
	{
		JRequest::setVar( 'view', 'weblink' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);
	
		parent::display();	
	}
	
	function save()
	{
		global $mainframe;

		$db	=& JFactory::getDBO();
		$row =& JTable::getInstance('weblink', 'Table');
		if (!$row->bind(JRequest::get('post'))) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		// save params
		$params = JRequest::getVar( 'params', '', 'post', 'array' );
		if (is_array( $params )) {
			$txt = array();
			foreach ( $params as $k=>$v) {
				$txt[] = "$k=$v";
			}
			$row->params = implode( "\n", $txt );
		}

		$row->date = date( 'Y-m-d H:i:s' );
		if (!$row->check()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = "catid = " . $row->catid ;
			$row->ordering = $row->getNextOrder ( $where );
		}

		if (!$row->store()) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$row->checkin();

		switch (JRequest::getVar('task'))
		{
			case 'apply':
				$msg = JText::_( 'Changes to Weblink saved' );
				$link = 'index.php?option=com_weblinks&amp;task=edit&amp;cid[]='. $row->id .'&amp;hidemainmenu=1';
				break;

			case 'save':
			default:
				$msg = JText::_( 'Weblink saved' );
				$link = 'index.php?option=com_weblinks';
				break;
		}

		$mainframe->redirect($link, $msg);
	}
	
	function remove()
	{
		global $mainframe, $option;
		
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$db =& JFactory::getDBO();
		if (!is_array( $cid ) || count( $cid ) < 1) {
			echo "<script> alert('". JText::_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
			exit;
		}
		if (count( $cid )) {
			$cids = implode( ',', $cid );
			$query = "DELETE FROM #__weblinks"
				. "\n WHERE id IN ( $cids )"
				;
			$db->setQuery( $query );
			if (!$db->query()) {
				echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			}
		}

		$mainframe->redirect( 'index.php?option=com_weblinks' );
	}
		

	function publish()
	{
		global $mainframe, $option;
		
		$publish = 1;
		
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$db		=& JFactory::getDBO();
		$user 	=& JFactory::getUser();
		$catid	= JRequest::getVar( 'catid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
			echo "<script> alert('". JText::_( 'Select an item to' ) . $action ."'); window.history.go(-1);</script>\n";
			exit;
		}

		$cids = implode( ',', $cid );

		$query = "UPDATE #__weblinks"
			. "\n SET published = " . intval( $publish )
			. "\n WHERE id IN ( $cids )"
			. "\n AND ( checked_out = 0 OR ( checked_out = " .$user->get('id'). " ) )"
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (count( $cid ) == 1) {
			$row =& JTable::getInstance('weblink', 'Table');
			$row->checkin( $cid[0] );
		}
		$mainframe->redirect( "index.php?option=". $option );
	}
		

	function unpublish()
	{
		global $mainframe, $option;
		
		$publish = 0;
		
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$db		=& JFactory::getDBO();
		$user 	=& JFactory::getUser();
		$catid	= JRequest::getVar( 'catid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
			echo "<script> alert('". JText::_( 'Select an item to' ) . $action ."'); window.history.go(-1);</script>\n";
			exit;
		}

		$cids = implode( ',', $cid );

		$query = "UPDATE #__weblinks"
			. "\n SET published = " . intval( $publish )
			. "\n WHERE id IN ( $cids )"
			. "\n AND ( checked_out = 0 OR ( checked_out = " .$user->get('id'). " ) )"
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (count( $cid ) == 1) {
			$row =& JTable::getInstance('weblink', 'Table');
			$row->checkin( $cid[0] );
		}
		$mainframe->redirect( "index.php?option=". $option );
	}
	
	function cancel()
	{
		global $mainframe;

		$id	= JRequest::getVar( 'id', 0, '', 'int' );
		$db =& JFactory::getDBO();
		$row =& JTable::getInstance('weblink', 'Table');
		$row->checkin($id);

		$mainframe->redirect( 'index.php?option=com_weblinks' );
	}
		

	function orderup()
	{
		global $mainframe, $option;

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$db =& JFactory::getDBO();
		$row =& JTable::getInstance('weblink', 'Table');
		$row->load( $cid[0] );
		$row->move( -1, "catid = $row->catid AND published >= 0" );

		$mainframe->redirect( 'index.php?option='. $option );
	}
		
	function orderdown()
	{
		global $mainframe, $option;

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$db =& JFactory::getDBO();
		$row =& JTable::getInstance('weblink', 'Table');
		$row->load( $cid[0] );
		$row->move( 1, "catid = $row->catid AND published >= 0" );

		$mainframe->redirect( 'index.php?option='. $option );
	}
		
	function saveorder()
	{
		global $mainframe;
		
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$db			=& JFactory::getDBO();
		$total		= count( $cid );
		$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		$row =& JTable::getInstance('weblink', 'Table');
		$groupings = array();
		
		// update ordering values
		for( $i=0; $i < $total; $i++ ) 
		{
			$row->load( (int) $cid[$i] );
			// track categories
			$groupings[] = $row->catid;

			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					//TODO - convert to JError
					echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
					exit();
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder("catid = $group");
		}

		$msg = 'New ordering saved';
		$mainframe->redirect( 'index.php?option=com_weblinks', $msg );
	}
}
?>