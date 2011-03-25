<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JInputCLI
{
	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct($source = null, $options = array ())
	{
		if (isset ($options['filter'])) {
			$this->filter = $options['filter'];
		} else {
			$this->filter = JFilterInput::getInstance();
		}

		if (is_null($source)) {
			$this->_data = $_REQUEST;
		} else {
			$this->_data = $source;
		}

		// Set the options for the class.
		$this->options = $options;
	}
}
