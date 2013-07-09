<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Note: this view is intended only to be opened in a popup
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 * @deprecated  3.2
 */
class ConfigControllerComponent extends JControllerLegacy
{
	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2
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
	 * @deprecated  3.2  Use ConfigControllerComponentCancel instead.
	 */
	function cancel()
	{

		JLog::add('ConfigControllerComponent is deprecated. Use ConfigControllerComponentCancel instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config');
		$controller = new ConfigControllerComponentCancel;

		$controller->execute();
	}

	/**
	 * Save the configuration
	 * @deprecated  3.2  Use ConfigControllerComponentSave instead.
	 */
	public function save()
	{

		JLog::add('ConfigControllerComponent is deprecated. Use ConfigControllerComponentSave instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config');
		$controller = new ConfigControllerComponentSave;

		return $controller->execute();
	}
}
