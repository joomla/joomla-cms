<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Config
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Poll component
 *
 * @static
 * @package		Joomla
 * @subpackage	Poll
 * @since 1.0
 */
class PollViewPoll extends JView
{

	protected $polls;
	protected $options;
	protected $poll;

	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$db		=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$cid 	= JRequest::getVar( 'cid', array(0), '', 'array' );
		$option = JRequest::getCmd( 'option');
		$uid 	= (int) @$cid[0];
		$edit=JRequest::getVar( 'edit', true );

		$poll =& JTable::getInstance('poll', 'Table');
		// load the row from the db table
		if($edit)
		$poll->load( $uid );

		// fail if checked out not by 'me'
		if ($poll->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The poll' ), $poll->title );
			$this->setRedirect( 'index.php?option='. $option, $msg );
		}

		if ($poll->id == 0)
		{
			// defaults
			$row->published	= 1;
		}

		$options = array();

		if ($edit)
		{
			$poll->checkout( $user->get('id') );
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
			$poll->lag = 3600*24;
		}

		$this->assignRef('poll',	$poll);
		$this->assignRef('options',	$options);

		parent::display($tpl);

	}
}
