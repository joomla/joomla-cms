<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input JSON Class
 *
 * This class decodes a JSON string from the raw request data and makes it available via
 * the standard JInput interface.
 *
 * @package     Joomla.Platform
 * @subpackage  Input
 * @since       12.2
 */
class JInputJSON extends JInput
{
	/**
	 * @var    string  The raw JSON string from the request.
	 * @since  12.2
	 */
	private $_raw;

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is the raw HTTP input decoded from JSON)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   12.2
	 */
	public function __construct(array $source = null, array $options = array())
	{
		if (isset($options['filter']))
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = JFilterInput::getInstance();
		}

		if (is_null($source))
		{
			$this->_raw = file_get_contents('php://input');
			$this->data = json_decode($this->_raw, true);
		}
		else
		{
			$this->data = & $source;
		}

		// Set the options for the class.
		$this->options = $options;
	}

	/**
	 * Gets the raw JSON string from the request.
	 *
	 * @return  string  The raw JSON string from the request.
	 *
	 * @since   12.2
	 */
	public function getRaw()
	{
		return $this->_raw;
	}
}
