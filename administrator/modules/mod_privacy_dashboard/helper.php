<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper class for admin privacy dashboard module
 *
 * @since  3.9.0
 */
class ModPrivacyDashboardHelper
{
	/**
	 * Method to retrieve information about the site privacy requests
	 *
	 * @return  array  Array containing site privacy requests
	 *
	 * @since   3.9.0
	 */
	public static function getData()
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/models', 'PrivacyModel');

		/** @var PrivacyModelDashboard $model */
		$model = JModelLegacy::getInstance('Dashboard', 'PrivacyModel');

		try
		{
			return $model->getRequestCounts();
		}
		catch (JDatabaseException $e)
		{
			return array();
		}
	}
}
