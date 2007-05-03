<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Polls
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	case 'vote':
		PollController::vote();
		break;

	default:
		PollController::display();
		break;
}

/**
 * Static class to hold controller functions for the Poll component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Poll
 * @since		1.5
 */
class PollController
{
	function display()
	{
		global $mainframe;

		$db 	=& JFactory::getDBO();
		$pathway =& $mainframe->getPathWay();

		$poll_id = JRequest::getVar( 'id', 0, '', 'int' );

		$poll =& JTable::getInstance('poll', 'Table');
		$poll->load( $poll_id );

		// if id value is passed and poll not published then exit
		if ($poll->id > 0 && $poll->published != 1) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		// Adds parameter handling
		$menu   =& JMenu::getInstance();
		$item   = $menu->getActive();
		$params = $mainframe->getPageParameters();

		//Set page title information
		$mainframe->SetPageTitle($poll->title);

		//Set pathway information
		$pathway->addItem($poll->title, '');

		$params->def( 'page_title',	1 );
		$params->def( 'header', $item->name );

		$first_vote = '';
		$last_vote 	= '';
		$votes		= '';

		// Check if there is a poll corresponding to id and if poll is published
		if ($poll->id > 0)
		{
			if (empty( $poll->title )) {
				$poll->id = 0;
				$poll->title = JText::_( 'Select Poll from the list' );
			}

			$query = 'SELECT MIN( date ) AS mindate, MAX( date ) AS maxdate'
				. ' FROM #__poll_date'
				. ' WHERE poll_id = '. $poll->id;
			$db->setQuery( $query );
			$dates = $db->loadObject();

			if (isset( $dates->mindate )) {
				$first_vote = JHTML::Date( $dates->mindate, DATE_FORMAT_LC2 );
				$last_vote 	= JHTML::Date( $dates->maxdate, DATE_FORMAT_LC2 );
			}

			$query = 'SELECT a.id, a.text, a.hits, b.voters'
				. ' FROM #__poll_data AS a'
				. ' INNER JOIN #__polls AS b ON b.id = a.pollid'
				. ' WHERE a.pollid = '. $poll->id
				. ' AND a.text <> ""'
				. ' ORDER BY a.hits DESC';
			$db->setQuery( $query );
			$votes = $db->loadObjectList();
		} else {
			$votes = array();
		}

		// list of polls for dropdown selection
		$query = 'SELECT id, title'
			. ' FROM #__polls'
			. ' WHERE published = 1'
			. ' ORDER BY id'
		;
		$db->setQuery( $query );
		$polls = $db->loadObjectList();

		$lists = array();

		// dropdown output
		$link = JRoute::_( 'index.php?option=com_poll&task=results&id='.$poll->id );

		array_unshift( $polls, JHTML::_('select.option',  '', JText::_( 'Select Poll from the list' ), 'id', 'title' ));

		$lists['polls'] = JHTML::_('select.genericlist',   $polls, 'id',
			'class="inputbox" size="1" style="width:200px" onchange="if (this.options[selectedIndex].value != \'\') {document.location.href=\''. $link .'\'}"',
			'id', 'title',
			$poll->id
			);

		require_once (JPATH_COMPONENT.DS.'views'.DS.'poll'.DS.'view.php');
		$view = new PollViewPoll();

		$view->assign('first_vote',	$first_vote);
		$view->assign('last_vote',	$last_vote);

		$view->assignRef('lists',	$lists);
		$view->assignRef('params',	$params);
		//$view->assignRef('data',	$data);
		$view->assignRef('poll',	$poll);
		$view->assignRef('votes',	$votes);

		$view->display();
	}

	/**
 	 * Add a vote to an option
 	 */
	function vote()
	{
		global $mainframe;
		
		//check the token before we do anything else
		$token = JUtility::getToken();
		if(!JRequest::getVar( $token, 0, 'post' )) {
			JError::raiseError(403, 'Request Forbidden');
		} 

		$db			=& JFactory::getDBO();

		$poll_id	= JRequest::getVar( 'id', 0, '', 'int' );
		$option_id	= JRequest::getVar( 'voteid', 0, 'post', 'int' );

		$poll =& JTable::getInstance('poll','Table');
		if (!$poll->load( $poll_id ) || $poll->published != 1) {
			JError::raiseWarning( 404, JText::_('ALERTNOTAUTH') );
			return;
		}

		$siteName	= $mainframe->getCfg( 'live_site' );
		$cookieName	= JUtility::getHash( $siteName . 'poll' . $poll_id );
		// ToDo - may be adding those information to the session?
		$voted = JRequest::getVar( $cookieName, '0', 'COOKIE', 'INT');

		if ($voted) {
			JError::raiseWarning( 404, JText::_('You already voted for this poll today!') );
			PollController::display();
			return;
		}

		if (!$option_id) {
			JError::raiseWarning( 404, JText::_('WARNSELECT') );
			PollController::display();
			return;
		}

		setcookie( $cookieName, '1', time() + $poll->lag );

		require_once(JPATH_COMPONENT.DS.'models'.DS.'poll.php');
		$model = new PollModelPoll();
		$model->addVote( $poll_id, $option_id );

		$mainframe->redirect( 'index.php?option=com_poll&task=results&id='. $poll_id, JText::_( 'Thanks for your vote!' ) );
	}
}
?>
