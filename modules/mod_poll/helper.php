<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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

		$Itemid = JRequest::getVar( 'Itemid' );
		$db		=& JFactory::getDBO();

		$query = "SELECT p.id, p.title" .
			"\n FROM #__polls AS p, #__poll_menu AS pm" .
			"\n WHERE (pm.menuid = ".(int) $Itemid." OR pm.menuid = 0)" .
			"\n AND p.id = pm.pollid" .
			"\n AND p.published = 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($db->getErrorNum()) {
			echo $db->stderr(true);
			return;
		}

		return $rows;
	}

	function getPollOptions($id)
	{
		$db	=& JFactory::getDBO();

		$query = "SELECT id, text" .
			"\n FROM #__poll_data" .
			"\n WHERE pollid = $id" .
			"\n AND text <> ''" .
			"\n ORDER BY id";
		$db->setQuery($query);

		if (!($options = $db->loadObjectList())) {
			echo "MD ".$db->stderr(true);
			return;
		}

		return $options;
	}
}
?>