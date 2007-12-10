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
 * Plugins Component Controller
 *
 * @package		Joomla
 * @subpackage	Plugins
 * @since 1.5
 */
class PluginsController extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		$this->registerTask( 'apply', 		'save');
		$this->registerTask( 'unpublish', 	'publish');
		$this->registerTask( 'edit' , 		'display' );
		$this->registerTask( 'add' , 		'display' );
		$this->registerTask( 'orderup'   , 	'order' );
		$this->registerTask( 'orderdown' , 	'order' );
		
		$this->registerTask( 'accesspublic' 	, 	'access' );
		$this->registerTask( 'accessregisterd'  , 	'access' );
		$this->registerTask( 'acessspecial' 	, 	'access' );
	
	}
	
	function display( )
	{
		switch($this->getTask())
		{
			case 'add'     :
			case 'edit'    :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view', 'plugin' );
			} break;
		}
		
		parent::display();	
	}

	function save()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}
		
		$db   =& JFactory::getDBO();
		$row  =& JTable::getInstance('plugin');
		$task = $this->getTask();
		
		$client = JRequest::getWord( 'filter_client', 'site' );

		if (!$row->bind(JRequest::get('post'))) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->check()) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();

		if ($client == 'admin') {
			$where = "client_id=1";
		} else {
			$where = "client_id=0";
		}

		$row->reorder( 'folder = '.$db->Quote($row->folder).' AND ordering > -10000 AND ordering < 10000 AND ( '.$where.' )' );

		switch ( $task ) 
		{
			case 'apply':
				$msg = JText::sprintf( 'Successfully Saved changes to Plugin', $row->name );
				$this->setRedirect( 'index.php?option=com_plugins&view=plugin&client='. $client .'&task=edit&cid[]='. $row->id, $msg );
				break;
				
			case 'save':
			default:
				$msg = JText::sprintf( 'Successfully Saved Plugin', $row->name );
				$this->setRedirect( 'index.php?option=com_plugins&client='. $client, $msg );
				break;
		}
	}

	function publish( )
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}
		
		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();
		$cid     = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$publish = ( $this->getTask() == 'publish' ? 1 : 0 );
		$client  = JRequest::getWord( 'filter_client', 'site' );

		if (count( $cid ) < 1) {
			$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
			JError::raiseError(500, JText::_( 'Select a plugin to '.$action ) );
		}

		$cids = implode( ',', $cid );

		$query = 'UPDATE #__plugins SET published = '.(int) $publish
			. ' WHERE id IN ( '.$cids.' )'
			. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ))'
			;
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}

		if (count( $cid ) == 1) {
			$row =& JTable::getInstance('plugin');
			$row->checkin( $cid[0] );
		}

		$this->setRedirect( 'index.php?option=com_plugins&client='. $client );
	}

	function cancel(  )
	{	
		$client  = JRequest::getWord( 'filter_client', 'site' );

		$db =& JFactory::getDBO();
		$row =& JTable::getInstance('plugin');
		$row->bind(JRequest::get('post'));
		$row->checkin();

		$this->setRedirect( JRoute::_( 'index.php?option=com_plugins&client='. $client, false ) );
	}

	function order(  )
	{
		$db =& JFactory::getDBO();
		
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		
		$uid    = $cid[0];
		$inc    = ( $this->getTask() == 'orderup' ? -1 : 1 );
		$client = JRequest::getWord( 'filter_client', 'site' );
		
		
		// Currently Unsupported
		if ($client == 'admin') {
			$where = "client_id = 1";
		} else {
			$where = "client_id = 0";
		}
		$row =& JTable::getInstance('plugin');
		$row->load( $uid );
		$row->move( $inc, 'folder='.$db->Quote($row->folder).' AND ordering > -10000 AND ordering < 10000 AND ('.$where.')' );

		$this->setRedirect( 'index.php?option=com_plugins' );
	}

	function access( )
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		
		$uid    = $cid[0];
		$access = $this->getTask();

		$db =& JFactory::getDBO();
		switch ( $access ) 
		{
			case 'accesspublic':
				$access = 0;
				break;

			case 'accessregistered':
				$access = 1;
				break;

			case 'accessspecial':
				$access = 2;
				break;
		}

		$row =& JTable::getInstance('plugin');
		$row->load( $uid );
		$row->access = $access;

		if ( !$row->check() ) {
			return $row->getError();
		}
		if ( !$row->store() ) {
			return $row->getError();
		}

		$this->setRedirect( 'index.php?option=com_plugins' );
	}

	function saveorder( )
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}
		
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		
		$db			=& JFactory::getDBO();
		$total		= count( $cid );
		$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		JArrayHelper::toInteger($order, array(0));
		
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$row 		=& JTable::getInstance('plugin');
		$conditions = array();

		// update ordering values
		for ( $i=0; $i < $total; $i++ ) 
		{
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i]) 
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError(500, $db->getErrorMsg() );
				}
				// remember to updateOrder this group
				$condition = 'folder = '.$db->Quote($row->folder).' AND ordering > -10000 AND ordering < 10000 AND client_id = ' . (int) $row->client_id;
				$found = false;
				foreach ( $conditions as $cond ) 
				{
					if ($cond[1]==$condition) {
						$found = true;
						break;
					} 
				}
				if (!$found) $conditions[] = array($row->id, $condition);
			}
		} 

		// execute updateOrder for each group
		foreach ( $conditions as $cond ) {
			$row->load( $cond[0] );
			$row->reorder( $cond[1] );
		} 

		$msg 	= JText::_( 'New ordering saved' );
		$this->setRedirect( 'index.php?option=com_plugins', $msg );
	}
}
?>
