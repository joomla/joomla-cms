<?php
/**
 * @version		$Id$
 * @package  Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * source software licenses. See COPYRIGHT.php for copyright notices and details.
 */

/**
 * False JSite class used to fool the frontend search plugins because they route the results
 */
class JSite extends JObject
{
	/**
	 * False method to fool the frontend search plugins
	 */
	function getMenu()
	{
		$result = new JSite;
		return $result;
	}

	/**
	 * False method to fool the frontend search plugins
	 */
	function getItems()
	{
		return array();
	}
}
