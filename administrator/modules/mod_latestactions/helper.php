<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_latestactions
 *
 * @since  3.9.0
 */
abstract class ModLatestActionsHelper
{
	/**
	 * Get a list of articles.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module parameters.
	 *
	 * @return  mixed  An array of action logs, or false on error.
	 */
	public static function getList(&$params)
	{
		JLoader::register('UserlogsModelUserlogs', JPATH_ADMINISTRATOR . '/components/com_userlogs/models/userlogs.php');
		JLoader::register('UserlogsHelper', JPATH_ADMINISTRATOR . '/components/com_userlogs/helpers/userlogs.php');

		/* @var UserlogsModelUserlogs $model */
		$model = JModelLegacy::getInstance('Userlogs', 'UserlogsModel', array('ignore_request' => true));

		// Set the Start and Limit
		$model->setState('list.start', 0);
		$model->setState('list.limit', $params->get('count', 5));
		$model->setState('list.ordering', 'a.id');
		$model->setState('list.direction', 'DESC');

		$rows = $model->getItems();

		foreach ($rows as $row)
		{
			$row->message = UserlogsHelper::getHumanReadableLogMessage($row);
		}

		return $rows;
	}
}
