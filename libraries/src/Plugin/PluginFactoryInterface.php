<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

defined('_JEXEC') or die;

/**
 * Interface defining a factory which can create Plugin objects
 *
 * @since  __DEPLOY_VERSION__
 */
interface PluginFactoryInterface
{
	/**
	 * Method to get an array of instances of a plugin type.
	 *
	 * The plugins are cached, means a second call with the same
	 * parameters, returns the same plugin object as on the first call.
	 *
	 * @param   string  $type  The name of the type
	 *
	 * @return  CMSPlugin[]  Plugin instances
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPlugins($type);

	/**
	 * Method to get an instance of a plugin.
	 *
	 * The plugins are cached, means a second call with the same
	 * parameters, returns the same plugin object as on the first call.
	 *
	 * @param   string  $name  The name of the plugin
	 * @param   string  $type  The type of th plugin
	 *
	 * @return  CMSPlugin  Plugin instance.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPlugin($name, $type);
}
