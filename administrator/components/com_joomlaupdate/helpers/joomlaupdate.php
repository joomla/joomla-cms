<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Joomla! update helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @since       2.5.2
 */
class JoomlaupdateHelper
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * 
	 * @since	2.5.2
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_joomlaupdate';

		$actions = array(
			'core.admin', 'core.manage', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}
