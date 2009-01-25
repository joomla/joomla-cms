<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Polls
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Static class to hold controller functions for the Poll component
 *
 * @static
 * @package		Joomla
 * @subpackage	Poll
 * @since		1.5
 */
class PollController extends JController
{
	/**
	 * Method to show the search view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		parent::display();
	}

	/**
 	 * Add a vote to an option
 	 */
	function vote()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$db			=& JFactory::getDBO();
		$poll_id	= JRequest::getVar( 'id', 0, '', 'int' );
		$option_id	= JRequest::getVar( 'voteid', 0, 'post', 'int' );

		$poll =& JTable::getInstance('poll','Table');
		if (!$poll->load( $poll_id ) || $poll->published != 1) {
			JError::raiseWarning( 404, JText::_('ALERTNOTAUTH') );
			return;
		}

		$cookieName	= JUtility::getHash( $mainframe->getName() . 'poll' . $poll_id );
		// ToDo - may be adding those information to the session?
		$voted = JRequest::getVar( $cookieName, '0', 'COOKIE', 'INT');

		if ($voted || !$option_id )
		{
			if($voted) {
				$msg = JText::_('You already voted for this poll today!');
			}

			if(!$option_id){
				$msg = JText::_('WARNSELECT');
			}
		}
		else
		{
			setcookie( $cookieName, '1', time() + $poll->lag );

			require_once JPATH_COMPONENT.DS.'models'.DS.'poll.php';
			$model = new PollModelPoll();
			$model->vote( $poll_id, $option_id );

			$msg = JText::_( 'Thanks for your vote!' );
		}

		// set Itemid id for links
		$menu = &JSite::getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_poll&view=poll');

		$itemid = isset($items[0]) ? '&Itemid='.$items[0]->id : '';

		$this->setRedirect( JRoute::_('index.php?option=com_poll&id='. $poll_id.':'.$poll->alias.$itemid, false), $msg );
	}
}
?>