<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Base class for rendering a display layout
 *
 * @see    https://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
class JLayoutBase implements JLayout
{
	/**
	 * Options object
	 *
	 * @var    Registry
	 * @since  3.2
	 */
	protected $options = null;

	/**
	 * Debug information messages
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $debugMessages = array();

	/**
	 * Set the options
	 *
	 * @param   array|Registry  $options  Array / Registry object with the options to load
	 *
	 * @return  JLayoutBase  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function setOptions($options = null)
	{
		// Received Registry
		if ($options instanceof Registry)
		{
			$this->options = $options;
		}
		// Received array
		elseif (is_array($options))
		{
			$this->options = new Registry($options);
		}
		else
		{
			$this->options = new Registry;
		}

		return $this;
	}

	/**
	 * Get the options
	 *
	 * @return  Registry  Object with the options
	 *
	 * @since   3.2
	 */
	public function getOptions()
	{
		// Always return a Registry instance
		if (!($this->options instanceof Registry))
		{
			$this->resetOptions();
		}

		return $this->options;
	}

	/**
	 * Function to empty all the options
	 *
	 * @return  JLayoutBase  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function resetOptions()
	{
		return $this->setOptions(null);
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since   3.0
	 */
	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Get the debug messages array
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getDebugMessages()
	{
		return $this->debugMessages;
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   object  $displayData  Object which properties are used inside the layout file to build displayed output
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.0
	 */
	public function render($displayData)
	{
		return '';
	}

	/**
	 * Render the list of debug messages
	 *
	 * @return  string  Output text/HTML code
	 *
	 * @since   3.2
	 */
	public function renderDebugMessages()
	{
		return implode($this->debugMessages, "\n");
	}

	/**
	 * Add a debug message to the debug messages array
	 *
	 * @param   string  $message  Message to save
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function addDebugMessage($message)
	{
		$this->debugMessages[] = $message;
	}
}
