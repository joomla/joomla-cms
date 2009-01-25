<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Config
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Poll component
 *
 * @static
 * @package		Joomla.Administrator
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
