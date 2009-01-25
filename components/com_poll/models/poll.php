<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Polls
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
* @package		Joomla
* @subpackage	Polls
*/
class PollModelPoll extends JModel
{
	/**
	 * Add vote
	 * @param int The id of the poll
	 * @param int The id of the option selected
	 */
	function vote( $poll_id, $option_id )
	{
		$db = $this->getDBO();
		$poll_id	= (int) $poll_id;
		$option_id	= (int) $option_id;

		$query = 'UPDATE #__poll_data'
			. ' SET hits = hits + 1'
			. ' WHERE pollid = ' . (int) $poll_id
			. ' AND id = ' . (int) $option_id
			;
		$db->setQuery( $query );
		$db->query();

		$query = 'UPDATE #__polls'
			. ' SET voters = voters + 1'
			. ' WHERE id = ' . (int) $poll_id
			;
		$db->setQuery( $query );
		$db->query();

		$date =& JFactory::getDate();

		$query = 'INSERT INTO #__poll_date'
			. ' SET date = ' . $db->Quote($date->toMySQL())
			. ', vote_id = ' . (int) $option_id
			. ', poll_id = ' . (int) $poll_id
		;
		$db->setQuery( $query );
		$db->query();
	}
}
