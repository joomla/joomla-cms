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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

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

		$query = 'INSERT INTO #__poll_date'
			. ' SET date = NOW(), vote_id = '. (int) $option_id . ', poll_id = ' . (int) $poll_id
		;
		$db->setQuery( $query );
		$db->query();
	}
}

?>
