<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
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

		$this->registerTask( 'apply', 		'save');
		$this->registerTask( 'unpublish', 	'publish');
		$this->registerTask( 'preview', 	'display');
		$this->registerTask( 'edit', 		'display');
		$this->registerTask( 'add' , 		'display' );

	}

	function display( )
	{
		switch($this->getTask())
		{
			case 'add'     :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view', 'poll'  );
				JRequest::setVar( 'edit', false  );
			} break;
			case 'edit'    :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view', 'poll'  );
				JRequest::setVar( 'edit', true  );
			} break;

			case 'preview' :
			{
				JRequest::setVar( 'tmpl', 'component' );
				JRequest::setVar( 'view', 'poll'  );
			} break;
		}

		//Set the default view, just in case
		$view = JRequest::getCmd('view');
		if(empty($view)) {
			JRequest::setVar('view', 'polls');
		};

		parent::display();
	}

	function save()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

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
			$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
			if ($isNew)
			{
				$obj = new stdClass();
				$obj->pollid = (int)$row->id;
				$obj->text   = $text;
				$db->insertObject('#__poll_data', $obj);
			}
			else
			{
				$obj = new stdClass();
				$obj->id     = (int)$i;
				$obj->text   = $text;
				$db->updateObject('#__poll_data', $obj, 'id');
			}
		}

		switch ($this->_task)
		{
			case 'apply':
				$msg = JText::_( 'Changes to Poll saved' );
				$link = 'index.php?option=com_poll&view=poll&task=edit&cid[]='. $row->id .'';
				break;

			case 'save':
			default:
				$msg = JText::_( 'Poll saved' );
				$link = 'index.php?option=com_poll';
				break;
		}

		$this->setRedirect($link);
	}

	function remove()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$db		=& JFactory::getDBO();
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );

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
		$this->setRedirect( 'index.php?option=com_poll', $msg );
	}

	/**
	* Publishes or Unpublishes one or more records
	* @param array An array of unique category id numbers
	* @param integer 0 if unpublishing, 1 if publishing
	* @param string The current url option
	*/
	function publish()
	{
		global $mainframe;

		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$cid		= JRequest::getVar( 'cid', array(), '', 'array' );
		$publish	= ( $this->getTask() == 'publish' ? 1 : 0 );

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
		$mainframe->redirect( 'index.php?option=com_poll' );
	}

	function cancel()
	{

		$id		= JRequest::getVar( 'id', 0, '', 'int' );
		$db		=& JFactory::getDBO();
		$row	=& JTable::getInstance('poll', 'Table');

		$row->checkin( $id );
		$this->setRedirect( 'index.php?option=com_poll' );
	}
}