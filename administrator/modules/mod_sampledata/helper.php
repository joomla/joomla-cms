<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Helper for mod_sampledata
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ModSampledataHelper
{
	/**
	 * Get a list of sampledata.
	 *
	 * @return  mixed  An array of sampledata, or false on error.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getList()
	{
		PluginHelper::importPlugin('sampledata');
		$data = Factory::getApplication()->triggerEvent('onSampledataGetOverview', array('test', 'foo'));

		return $data;
	}
}
