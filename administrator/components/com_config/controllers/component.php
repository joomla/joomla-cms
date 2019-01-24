<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Note: this view is intended only to be opened in a popup
 *
 * @since       1.5
 * @deprecated  4.0
 */
class ConfigControllerComponent extends JControllerLegacy
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
	 * Cancel operation
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @deprecated  4.0  Use ConfigControllerComponentCancel instead.
	 */
	public function cancel()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use ConfigControllerComponentCancel instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$controller = new ConfigControllerComponentCancel;

		$controller->execute();
	}

	/**
	 * Save the configuration.
	 *
	 * @return  boolean  True if successful; false otherwise.
	 *
	 * @deprecated  4.0  Use ConfigControllerComponentSave instead.
	 */
	public function save()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use ConfigControllerComponentSave instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$controller = new ConfigControllerComponentSave;

		return $controller->execute();
	}
}
