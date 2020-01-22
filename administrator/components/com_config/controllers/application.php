<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller for global configuration
 *
 * @since       1.5
 * @deprecated  4.0
 */
class ConfigControllerApplication extends JControllerLegacy
{
	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.5
	 * @deprecated  4.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Method to save the configuration.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use ConfigControllerApplicationSave instead.
	 */
	public function save()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use ConfigControllerApplicationSave instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$controller = new ConfigControllerApplicationSave;

		return $controller->execute();
	}

	/**
	 * Cancel operation.
	 *
	 * @return  boolean  True if successful; false otherwise.
	 *
	 * @deprecated  4.0  Use ConfigControllerApplicationCancel instead.
	 */
	public function cancel()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use ConfigControllerApplicationCancel instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$controller = new ConfigControllerApplicationCancel;

		return $controller->execute();
	}

	/**
	 * Method to remove the root property from the configuration.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use ConfigControllerApplicationRemoveroot instead.
	 */
	public function removeroot()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use ConfigControllerApplicationRemoveroot instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$controller = new ConfigControllerApplicationRemoveroot;

		return $controller->execute();
	}
}
