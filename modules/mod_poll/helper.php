<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPollHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();
		$result	= array();

		if ($id = $params->get( 'id', 0 ))
		{
			$query = 'SELECT id, title'
			.' FROM #__polls'
			.' WHERE id = '.(int) $id
			.' AND published = 1'
			;
			$db->setQuery($query);
			$result = $db->loadObjectList();

			if ($db->getErrorNum()) {
				JError::raiseWarning( 500, $db->stderr(true) );
			}
		}

		return $result;
	}

	function getPollOptions($id)
	{
		$db	=& JFactory::getDBO();

		$query = 'SELECT id, text' .
			' FROM #__poll_data' .
			' WHERE pollid = '.$id .
			' AND text <> ""' .
			' ORDER BY id';
		$db->setQuery($query);

		if (!($options = $db->loadObjectList())) {
			echo "MD ".$db->stderr(true);
			return;
		}

		return $options;
	}
}
?>
