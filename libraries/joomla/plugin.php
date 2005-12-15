<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.classes.object' );

/**
* Plugin helper class
* 
* @package Joomla
* @subpackage JFramework
* @since 1.1
*/
class JPluginHelper
{
	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the mambots directory
	*/
	function importGroup( $group )
	{
		static $bots;

		if (!isset($bots)) {
			$bots = JPluginHelper::_load();
		}

		$n = count( $bots);
		for ($i = 0; $i < $n; $i++) {
			if($bots[$i]->folder == $group) {
				JPluginHelper::import( $bots[$i]->folder, $bots[$i]->element, $bots[$i]->published, $bots[$i]->params );
			}
		}
		return true;
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function import( $folder, $element, $published, $params='' )
	{
		global $_MAMBOTS, $mainframe; //needed for backwards compatibility

		$path = JPATH_SITE . '/mambots/' . $folder . '/' . $element . '.php';
		if (file_exists( $path )) {
			require_once( $path );
		}
	}

	function _load() {
		global $mainframe;

		$db =& $mainframe->getDBO();
		$my =& $mainframe->getUser();

		if (is_object( $my )) {
			$gid = $my->gid;
		} else {
			$gid = 0;
		}

		$query = "SELECT folder, element, published, params"
			. "\n FROM #__mambots"
			. "\n WHERE published >= 1"
			. "\n AND access <= $gid"
			. "\n ORDER BY ordering"
			;

		$db->setQuery( $query );

		if (!($bots = $db->loadObjectList())) {
			//echo "Error loading Mambots: " . $database->getErrorMsg();
			return false;
		}

		return $bots;
	}

}
?>