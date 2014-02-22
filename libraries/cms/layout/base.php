<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Base class for rendering a display layout
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since       3.0
 */
class JLayoutBase implements JLayout
{
	/**
	 * Options object
	 *
	 * @var    JRegistry
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
	 * @param   mixed  $options  Array / JRegistry object with the options to load
	 *
	 * @return  JLayoutBase  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function setOptions($options = null)
	{
		// Received JRegistry
		if ($options instanceof JRegistry)
		{
			$this->options = $options;
		}
		// Received array
		elseif (is_array($options))
		{
			$this->options = new JRegistry($options);
		}
		else
		{
			$this->options = new JRegistry;
		}

		return $this;
	}

	/**
	 * Get the options
	 *
	 * @return  JRegistry  Object with the options
	 *
	 * @since   3.2
	 */
	public function getOptions()
	{
		// Always return a JRegistry instance
		if (!($this->options instanceof JRegistry))
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
