<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This script will fetch the update information for all extensions and store them in the database, speeding up your administrator.
 *
 * @since  3.5
 */
class CliCommandUpdatecheck extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Get the update cache time
		$component = JComponentHelper::getComponent('com_installer');

		/** @var \Joomla\Registry\Registry $params */
		$params = $component->params;
		$cache_timeout = 3600 * (int) $params->get('cachetimeout', 6);

		// Find all updates
		$this->getApplication()->out('Fetching updates...');
		JUpdater::getInstance()->findUpdates(0, $cache_timeout);
		$this->getApplication()->out('Finished fetching updates');

		return true;
	}
}
