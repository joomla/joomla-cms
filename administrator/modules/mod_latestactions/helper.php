<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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
		JLoader::register('ActionlogsModelActionlogs', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlogs.php');
		JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

		/* @var ActionlogsModelActionlogs $model */
		$model = BaseDatabaseModel::getInstance('Actionlogs', 'ActionlogsModel', array('ignore_request' => true));

		// Set the Start and Limit
		$model->setState('list.start', 0);
		$model->setState('list.limit', $params->get('count', 5));
		$model->setState('list.ordering', 'a.id');
		$model->setState('list.direction', 'DESC');

		$rows = $model->getItems();

		// Load all actionlog plugins language files
		ActionlogsHelper::loadActionLogPluginsLanguage();

		foreach ($rows as $row)
		{
			$row->message = ActionlogsHelper::getHumanReadableLogMessage($row);
		}

		return $rows;
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param   \Joomla\Registry\Registry  $params  The module parameters.
	 *
	 * @return  string    The alternate title for the module.
	 *
	 * @since   3.9.1
	 */
	public static function getTitle($params)
	{
		return Text::plural('MOD_LATESTACTIONS_TITLE', $params->get('count', 5));
	}
}
