<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Registry\Registry;

/**
 * Application sub-interface defining an application class which is aware of its configuration
 *
 * @since  __DEPLOY_VERSION__
 */
interface ConfigurationAwareApplicationInterface extends ApplicationInterface
{
	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($key, $default = null);

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set($key, $value = null);

	/**
	 * Sets the configuration for the application.
	 *
	 * @param   Registry  $config  A registry object holding the configuration.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setConfiguration(Registry $config);
}
