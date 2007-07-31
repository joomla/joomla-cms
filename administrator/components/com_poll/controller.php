<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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
 * @package		Joomla
 * @subpackage	Config
 */
class PollController extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		$this->registerTask( 'add' , 'editPoll' );
		$this->registerTask( 'edit', 'editPoll' );

		$this->registerTask( 'save', 'savePoll');
		$this->registerTask( 'apply', 'savePoll');

		$this->registerTask( 'remove', 'removePoll');
		$this->registerTask( 'publish', 'publishPolls');
		$this->registerTask( 'unpublish', 'publishPolls');

		$this->registerTask( 'cancel', 'cancelPoll');
		$this->registerTask( 'preview', 'previewPoll');
	}

	function showPolls()
	{
		global $mainframe, $option;

		$db					=& JFactory::getDBO();
		$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order",		'filter_order',		'm.id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'',		'word' );
		$filter_state		= $mainframe->getUserStateFromRequest( "$option.filter_state",		'filter_state',		'',		'word' );
		$search				= $mainframe->getUserStateFromRequest( "$option.search",			'search',			'',		'string' );
		$search				= JString::strtolower( $search );

		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

		$where = array();

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'm.published = 1';
			}
			else if ($filter_state == 'U' )
			{
				$where[] = 'm.published = 0';
			}
		}
		if ($search)
		{
			$where[] = 'LOWER(m.title) LIKE '.$db->Quote('%'.$search.'%');
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir;

		$query = 'SELECT COUNT(m.id)'
		. ' FROM #__polls AS m'
		. $where
		;
		$db->setQuery( $query );
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		$query = 'SELECT m.*, u.name AS editor, COUNT(d.id) AS numoptions'
		. ' FROM #__polls AS m'
		. ' LEFT JOIN #__users AS u ON u.id = m.checked_out'
		. ' LEFT JOIN #__poll_data AS d ON d.pollid = m.id AND d.text <> ""'
		. $where
		. ' GROUP BY m.id'
		. $orderby
		;
		$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		$rows = $db->loadObjectList();

		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		require_once( JPATH_COMPONENT.DS.'views'.DS.'poll'.DS.'view.php' );
		PollView::showPolls( $rows, $pageNav, $option, $lists );
	}

	function editPoll( )
	{
		$db		=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$cid 	= JRequest::getVar( 'cid', array(0), '', 'array' );
		$option = JRequest::getCmd( 'option');
		$uid 	= (int) @$cid[0];

		$row =& JTable::getInstance('poll', 'Table');
		// load the row from the db table
		$row->load( $uid );

		// fail if checked out not by 'me'
		if ($row->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The poll' ), $row->title );
			$this->setRedirect( 'index.php?option='. $option, $msg );
		}

		if ($row->id == 0)
		{
			// defaults
			$row->published	= 1;
		}

		$options = array();

		if ($uid)
		{
			$row->checkout( $user->get('id') );
			$query = 'SELECT id, text'
			. ' FROM #__poll_data'
			. ' WHERE pollid = '.(int) $uid
			. ' ORDER BY id'
			;
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}
		else
		{
			$row->lag = 3600*24;
		}

		require_once( JPATH_COMPONENT.DS.'views'.DS.'poll'.DS.'view.php' );
		PollView::editPoll($row, $options );
	}

	function savePoll()
	{
		$db		=& JFactory::getDBO();

		// save the poll parent information
		$row	=& JTable::getInstance('poll', 'Table');
		$post	= JRequest::get( 'post' );
		if (!$row->bind( $post ))
		{
			JError::raiseError(500, $row->getError() );
		}
		$isNew = ($row->id == 0);

		if (!$row->check())
		{
			JError::raiseError(500, $row->getError() );
		}

		if (!$row->store())
		{
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();
		// save the poll options
		$options = JArrayHelper::getValue( $post, 'polloption', array(), 'array' );

		foreach ($options as $i=>$text)
		{
			if ($isNew)
			{
				$query = 'INSERT INTO #__poll_data'
				. ' ( pollid, text )'
				. ' VALUES ( '.(int) $row->id.', '.$db->Quote($text).' )'
				;
				$db->setQuery( $query );
				$db->query();
			}
			else
			{
				$query = 'UPDATE #__poll_data'
				. ' SET text = '. $db->Quote($text)
				. ' WHERE id = '. (int) $i
				. ' AND pollid = '.(int) $row->id
				;
				$db->setQuery( $query );
				$db->query();
			}
		}

		switch ($this->_task)
		{
			case 'apply':
				$msg = JText::_( 'Changes to Poll saved' );
				$link = 'index.php?option=com_poll&task=edit&cid[]='. $row->id .'';
				break;

			case 'save':
			default:
				$msg = JText::_( 'Poll saved' );
				$link = 'index.php?option=com_poll';
				break;
		}

		$this->setRedirect($link);
	}

	function removePoll()
	{
		$db		=& JFactory::getDBO();
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		$option = JRequest::getCmd( 'option', 'com_poll', '', 'string' );

		JArrayHelper::toInteger($cid);
		$msg = '';

		for ($i=0, $n=count($cid); $i < $n; $i++)
		{
			$poll =& JTable::getInstance('poll', 'Table');
			if (!$poll->delete( $cid[$i] ))
			{
				$msg .= $poll->getError();
			}
		}
		$this->setRedirect( 'index.php?option='. $option, $msg );
	}

	/**
	* Publishes or Unpublishes one or more records
	* @param array An array of unique category id numbers
	* @param integer 0 if unpublishing, 1 if publishing
	* @param string The current url option
	*/
	function publishPolls()
	{
		global $mainframe;

		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$cid		= JRequest::getVar( 'cid', array(), '', 'array' );
		$publish	= ( $this->_task == 'publish' ? 1 : 0 );
		$option		= JRequest::getCmd( 'option', 'com_poll', '', 'string' );

		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1)
		{
			$action = $publish ? 'publish' : 'unpublish';
			JError::raiseError(500, JText::_( 'Select an item to' .$action, true ) );
		}

		$cids = implode( ',', $cid );

		$query = 'UPDATE #__polls'
		. ' SET published = ' . (int) $publish
		. ' WHERE id IN ( '. $cids .' )'
		. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
		;
		$db->setQuery( $query );
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}

		if (count( $cid ) == 1)
		{
			$row =& JTable::getInstance('poll', 'Table');
			$row->checkin( $cid[0] );
		}
		$mainframe->redirect( 'index.php?option='. $option );
	}

	function cancelPoll()
	{
		global $option;

		$id		= JRequest::getVar( 'id', 0, '', 'int' );
		$db		=& JFactory::getDBO();
		$row	=& JTable::getInstance('poll', 'Table');

		$row->checkin( $id );
		$this->setRedirect( 'index.php?option='. $option );
	}

	function previewPoll()
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('Poll Preview'));

		$db 	=& JFactory::getDBO();
		$pollid = JRequest::getVar( 'pollid', 0, '', 'int' );
		$css	= JRequest::getVar( 't', '' );

		$query = 'SELECT title'
			. ' FROM #__polls'
			. ' WHERE id = '.(int) $pollid
		;
		$db->setQuery( $query );
		$title = $db->loadResult();

		$query = 'SELECT text'
			. ' FROM #__poll_data'
			. ' WHERE pollid = '.(int) $pollid
			. ' ORDER BY id'
		;
		$db->setQuery( $query );
		$options = $db->loadResultArray();

		require_once( JPATH_COMPONENT.DS.'views'.DS.'poll'.DS.'view.php' );
		PollView::previewPoll($title, $options);
	}
}
?>
